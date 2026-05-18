<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ekskul;
use App\Models\KategoriEkskul;
use App\Models\Pembina;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

// tambahkan ini
use App\Http\Requests\StoreEkskulRequest;
use App\Http\Requests\UpdateEkskulRequest;

class EkskulController extends Controller
{
    public function index(Request $request)
    {
        $ekskul = Ekskul::with(['kategori', 'pembina'])
            ->when($request->search, fn($q) => $q->where('nama_ekskul', 'like', "%{$request->search}%"))
            ->when($request->status !== null && $request->status !== '', fn($q) => $q->where('is_active', $request->status))
            ->urutHari()
            ->paginate(10)
            ->withQueryString();

        return view('admin.ekskul.index', compact('ekskul'));
    }

    public function create()
    {
        $kategoriList = KategoriEkskul::aktif()->orderBy('nama_kategori')->get();
        $pembinaList = Pembina::aktif()->orderBy('nama_lengkap')->get();

        return view('admin.ekskul.create', compact('kategoriList', 'pembinaList'));
    }

    public function store(StoreEkskulRequest $request) // ✅ pakai Form Request
    {
        // Upload foto jika ada
        $fotoPath = null;

        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('ekskul', 'public');
        }

        $ekskul = Ekskul::create([
            'nama_ekskul' => $request->nama_ekskul,
            'kategori_ekskul_id' => $request->kategori_ekskul_id,
            'hari_pelaksanaan' => $request->hari_pelaksanaan,
            'lokasi' => $request->lokasi,
            'biaya_tambahan' => (int)$request->biaya_tambahan,
            'fasilitas_level' => (int)$request->fasilitas_level,
            'intensitas_kegiatan' => (int)$request->intensitas_kegiatan,
            'deskripsi_kegiatan' => $request->deskripsi_kegiatan,
            'foto_path' => $fotoPath,
            'kuota_minimal' => 10, // Fixed value
            'is_active' => 1, // New ekskul always active by default
        ]);

        // Hubungkan ke pembina yang dipilih via pivot
        $ekskul->pembina()->attach($request->pembina_ids);

        return redirect()->route('admin.ekskul.index')
            ->with('success', "Ekstrakurikuler {$ekskul->nama_ekskul} berhasil ditambahkan.");
    }

    public function edit(Ekskul $ekskul)
    {
        $ekskul->load('pembina');

        $kategoriList = KategoriEkskul::aktif()->orderBy('nama_kategori')->get();
        $pembinaList = Pembina::aktif()->orderBy('nama_lengkap')->get();
        $pembinaSelected = $ekskul->pembina->pluck('pembina_id')->toArray();

        return view('admin.ekskul.edit', compact('ekskul', 'kategoriList', 'pembinaList', 'pembinaSelected'));
    }

    public function update(UpdateEkskulRequest $request, Ekskul $ekskul) // ✅ pakai Form Request
    {
        // Ganti foto jika ada upload baru- hapus foto lama dulu
        $fotoPath = $ekskul->foto_path;

        if ($request->hasFile('foto')) {
            if ($fotoPath) {
                Storage::disk('public')->delete($fotoPath);
            }

            $fotoPath = $request->file('foto')->store('ekskul', 'public');
        }

        $ekskul->update([
            'nama_ekskul' => $request->nama_ekskul,
            'kategori_ekskul_id' => $request->kategori_ekskul_id,
            'hari_pelaksanaan' => $request->hari_pelaksanaan,
            'lokasi' => $request->lokasi,
            'biaya_tambahan' => (int)$request->biaya_tambahan,
            'fasilitas_level' => (int)$request->fasilitas_level,
            'intensitas_kegiatan' => (int)$request->intensitas_kegiatan,
            'deskripsi_kegiatan' => $request->deskripsi_kegiatan,
            'foto_path' => $fotoPath,
            // kuota_minimal tidak bisa di-edit, selalu 10
            'is_active' => (int)$request->is_active,
        ]);

        // Sync pembina- hapus yang lama, pasang yang baru
        $ekskul->pembina()->sync($request->pembina_ids);

        return redirect()->route('admin.ekskul.index')
            ->with('success', "Ekstrakurikuler {$ekskul->nama_ekskul} berhasil diperbarui.");
    }

    public function toggleStatus(Ekskul $ekskul)
    {
        $ekskul->update(['is_active' => ! $ekskul->is_active]);

        $label = $ekskul->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Ekskul {$ekskul->nama_ekskul} berhasil {$label}.");
    }
}
