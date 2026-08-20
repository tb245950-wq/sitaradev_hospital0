<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

return new class extends Migration
{
    /**
     * CRITICAL FIX: NIK Uniqueness Broken by Encryption
     *
     * Problem:
     * - NIK field is encrypted using Laravel Crypt with random IV
     * - Each encryption produces different ciphertext even for same plaintext
     * - Database unique constraint on encrypted field doesn't work
     *
     * Solution:
     * - Add nik_hash column (SHA256 hash of plaintext NIK)
     * - Unique constraint on nik_hash (deterministic, works with encryption)
     * - Index on nik_hash for performance
     * - Backfill existing data; resolve any pre-existing duplicate NIKs first
     */
    public function up(): void
    {
        // Step 1: Add nik_hash column (nullable initially, filled before NOT NULL)
        Schema::table('patients', function (Blueprint $table) {
            $table->string('nik_hash', 64)->nullable()->after('nik');
        });

        // Step 2: Resolve any pre-existing duplicate NIKs
        $this->resolveDuplicateNiks();

        // Step 3: Backfill nik_hash for all records
        $this->backfillNikHash();

        // Step 4: Add unique index (column is fully populated now)
        Schema::table('patients', function (Blueprint $table) {
            $table->unique('nik_hash');
        });

        // Step 5: Make column NOT NULL
        DB::statement('ALTER TABLE patients ALTER COLUMN nik_hash SET NOT NULL');
    }

    // ------------------------------------------------------------------ //

    /**
     * Find patients with duplicate plaintext NIKs and make them unique
     * by appending a suffix to the duplicates.
     * We keep the earliest record unchanged and modify later duplicates.
     */
    private function resolveDuplicateNiks(): void
    {
        $patients = DB::table('patients')
            ->select('id_pasien', 'nik')
            ->orderBy('id_pasien') // earliest first
            ->get();

        $seen    = []; // plaintext NIK → first id_pasien
        $toFix   = []; // id_pasien that are duplicates

        foreach ($patients as $patient) {
            try {
                $plainNik = Crypt::decryptString($patient->nik);
            } catch (\Exception $e) {
                // Not encrypted — treat raw value as plaintext
                $plainNik = $patient->nik;
            }

            if (isset($seen[$plainNik])) {
                $toFix[] = ['id_pasien' => $patient->id_pasien, 'original_nik' => $plainNik];
            } else {
                $seen[$plainNik] = $patient->id_pasien;
            }
        }

        if (empty($toFix)) {
            echo "✅ No duplicate NIKs found — skipping deduplication.\n";
            return;
        }

        echo "\n⚠️  Found " . count($toFix) . " duplicate NIK(s). Resolving...\n";

        foreach ($toFix as $entry) {
            // Create a unique NIK by appending a suffix based on id_pasien
            $newNik = $entry['original_nik'] . str_pad($entry['id_pasien'], 4, '0', STR_PAD_LEFT);

            // Trim if over 20 chars; take last 16 chars of original then append
            if (strlen($newNik) > 20) {
                $newNik = substr($entry['original_nik'], 0, 16)
                    . str_pad($entry['id_pasien'], 4, '0', STR_PAD_LEFT);
            }

            $encryptedNew = Crypt::encryptString($newNik);

            DB::table('patients')
                ->where('id_pasien', $entry['id_pasien'])
                ->update(['nik' => $encryptedNew]);

            echo "   🔧 Patient {$entry['id_pasien']}: duplicate NIK suffix appended.\n";
        }

        echo "✅ Deduplication complete.\n";
    }

    /**
     * Populate nik_hash for every patient row.
     */
    private function backfillNikHash(): void
    {
        echo "\n🔄 Backfilling nik_hash for existing patients...\n";

        $patients  = DB::table('patients')->whereNull('nik_hash')->get();
        $total     = $patients->count();
        $processed = 0;
        $errors    = 0;

        foreach ($patients as $patient) {
            try {
                // Try decrypting; fall back to raw value for unencrypted legacy rows
                try {
                    $plainNik = Crypt::decryptString($patient->nik);
                } catch (\Exception $e) {
                    $plainNik = $patient->nik;
                }

                $nikHash = hash('sha256', $plainNik);

                DB::table('patients')
                    ->where('id_pasien', $patient->id_pasien)
                    ->update(['nik_hash' => $nikHash]);

                $processed++;

                if ($processed % 50 === 0) {
                    echo "   ✓ Processed {$processed}/{$total}...\n";
                }

            } catch (\Exception $e) {
                $errors++;
                echo "   ❌ Patient {$patient->id_pasien}: {$e->getMessage()}\n";
            }
        }

        echo "✅ Backfill complete: {$processed}/{$total} patients processed";
        if ($errors) {
            echo " ({$errors} errors)";
        }
        echo "\n\n";
    }

    // ------------------------------------------------------------------ //

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropUnique(['nik_hash']);
            $table->dropColumn('nik_hash');
        });
    }
};
