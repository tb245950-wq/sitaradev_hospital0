<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Patient;

class BackfillNikHash extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'patients:backfill-nik-hash
                            {--force : Force backfill even if nik_hash already exists}
                            {--dry-run : Show what would be done without making changes}';

    /**
     * The console command description.
     */
    protected $description = 'Backfill nik_hash column for existing patients (SHA256 hash of plaintext NIK)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔄 Starting NIK hash backfill process...');
        $this->newLine();

        $force  = $this->option('force');
        $dryRun = $this->option('dry-run');

        // Determine which patients need backfill
        $query = Patient::withTrashed(); // Include soft-deleted patients
        if (!$force) {
            $query->whereNull('nik_hash');
        }

        $patients = $query->get();
        $total    = $patients->count();

        if ($total === 0) {
            $this->info('✅ All patients already have nik_hash. Nothing to do.');
            return Command::SUCCESS;
        }

        $this->info("Found {$total} patient(s) needing nik_hash backfill.");

        if ($dryRun) {
            $this->warn('DRY RUN mode - no changes will be made.');
            $this->table(
                ['id_pasien', 'nrm', 'nik_hash (current)'],
                $patients->take(5)->map(fn ($p) => [
                    $p->id_pasien,
                    $p->nrm,
                    $p->nik_hash ?? '(null)',
                ])->toArray()
            );
            $this->info('Use without --dry-run to apply changes.');
            return Command::SUCCESS;
        }

        if (!$this->confirm('Continue with backfill?', true)) {
            $this->warn('Backfill cancelled.');
            return Command::FAILURE;
        }

        $this->newLine();
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $processed = 0;
        $errors    = 0;
        $errorList = [];

        foreach ($patients as $patient) {
            try {
                // The EncryptedField cast on $patient->nik auto-decrypts.
                // hash() then produces a deterministic SHA-256 digest.
                $plaintextNik = $patient->nik;

                if (empty($plaintextNik)) {
                    $errors++;
                    $errorList[] = "ID {$patient->id_pasien}: NIK is empty";
                    $bar->advance();
                    continue;
                }

                $nikHash = hash('sha256', $plaintextNik);

                // Update via DB query builder to bypass model events / cast side-effects
                DB::table('patients')
                    ->where('id_pasien', $patient->id_pasien)
                    ->update(['nik_hash' => $nikHash]);

                $processed++;

            } catch (\Exception $e) {
                $errors++;
                $errorList[] = "ID {$patient->id_pasien}: {$e->getMessage()}";
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->info('✅ Backfill complete!');
        $this->table(
            ['Status', 'Count'],
            [
                ['✅ Processed', $processed],
                ['❌ Errors',    $errors],
                ['📊 Total',     $total],
            ]
        );

        if ($errors > 0) {
            $this->newLine();
            $this->error('The following patients had errors:');
            foreach ($errorList as $err) {
                $this->line("  - {$err}");
            }
            $this->warn('Please review these patients manually.');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
