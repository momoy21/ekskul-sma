<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kriteria;
use Illuminate\Http\Request;

// ✅ tambah ini
use App\Http\Requests\UpdateKriteriaRequest;

class KriteriaController extends Controller
{
    /**
     * Daftar kriteria- tidak ada tombol tambah karena kriteria bersifat tetap (C1-C5).
     */
    public function index(Request $request)
    {
        $kriteria = Kriteria::query()
            ->when($request->search, fn($q) =>
                $q->where('nama_kriteria', 'like', "%{$request->search}%")
            )
            ->when($request->status !== null && $request->status !== '', fn($q) =>
                $q->where('is_active', $request->status)
            )
            ->orderBy('urutan_tampil')
            ->paginate(10)
            ->withQueryString();

        return view('admin.kriteria.index', compact('kriteria'));
    }

    /**
     * Form edit kriteria.
     * Kode dan tipe_atribut ditampilkan tapi tidak bisa diubah.
     */
    public function edit(Kriteria $kriteria)
    {
        return view('admin.kriteria.edit', compact('kriteria'));
    }

    /**
     * Update kriteria.
     * Hanya nama_kriteria, deskripsi_siswa, dan is_active yang boleh diubah.
     * Kode dan tipe_atribut dikunci di level validasi.
     */
    public function update(UpdateKriteriaRequest $request, Kriteria $kriteria)
    {
        $data = $request->validated();

        $kriteria->update([
            'nama_kriteria'   => $data['nama_kriteria'],
            'deskripsi_siswa' => $data['deskripsi_siswa'],
            'is_active'       => $data['is_active'],
        ]);

        return redirect()->route('admin.kriteria.index')
            ->with('success', "Kriteria {$kriteria->kode} berhasil diperbarui.");
    }

    public function toggleStatus(Kriteria $kriteria)
    {
        $kriteria->update(['is_active' => ! $kriteria->is_active]);
        $label = $kriteria->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Kriteria {$kriteria->kode} ({$kriteria->nama_kriteria}) berhasil {$label}.");
    }
}
