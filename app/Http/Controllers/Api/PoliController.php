<?php

namespace App\Http\Controllers\Api;

use App\Models\Poli;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PoliController extends BaseApiController
{
    /** GET /api/polis — semua staff & pasien (read) */
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Poli::orderBy('nama')->get(),
        ]);
    }

    /** GET /api/polis/aktif — hanya poli aktif (untuk booking pasien) */
    public function aktif()
    {
        return response()->json([
            'success' => true,
            'data' => Poli::aktif()->orderBy('nama')->get(),
        ]);
    }

    /** POST /api/polis — admin only */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode'      => 'required|string|max:20|unique:polis,kode|alpha_dash',
            'nama'      => 'required|string|max:100',
            'deskripsi' => 'nullable|string|max:500',
            'status'    => 'sometimes|in:aktif,nonaktif',
        ]);

        $poli = Poli::create($validated);

        return response()->json(['success' => true, 'message' => 'Poli berhasil ditambahkan.', 'data' => $poli], 201);
    }

    /** PUT /api/polis/{id} — admin only */
    public function update(Request $request, Poli $poli)
    {
        $validated = $request->validate([
            'kode'      => ['sometimes', 'string', 'max:20', 'alpha_dash', Rule::unique('polis')->ignore($poli->id)],
            'nama'      => 'sometimes|string|max:100',
            'deskripsi' => 'nullable|string|max:500',
            'status'    => 'sometimes|in:aktif,nonaktif',
        ]);

        $poli->update($validated);

        return response()->json(['success' => true, 'message' => 'Poli berhasil diperbarui.', 'data' => $poli]);
    }

    /** DELETE /api/polis/{id} — admin only */
    public function destroy(Poli $poli)
    {
        $poli->delete();

        return response()->json(['success' => true, 'message' => 'Poli berhasil dihapus.']);
    }
}
