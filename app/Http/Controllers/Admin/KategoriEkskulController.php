<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriEkskul;
use Illuminate\Http\Request;

// ✅ tambah ini
use App\Http\Requests\StoreKategoriEkskulRequest;
use App\Http\Requests\UpdateKategoriEkskulRequest;

class KategoriEkskulController extends Controller
{
    public function index(Request $request)
    {
        $kategori = KategoriEkskul::query()
            ->when($request->search, fn($q) =>
                $q->where('nama_kategori', 'like', "%{$request->search}%")
            )
            ->when($request->status !== null && $request->status !== '', fn($q) =>
                $q->where('is_active', $request->status)
            )
            ->orderBy('nama_kategori')
            ->paginate(10)
            ->withQueryString();

        return view('admin.kategori-ekskul.index', compact('kategori'));
    }

    public function create()
    {
        return view('admin.kategori-ekskul.create');
    }

    public function store(StoreKategoriEkskulRequest $request)
    {
        $data = $request->validated();

        KategoriEkskul::create([
            'nama_kategori' => $data['nama_kategori'],
            'is_active'     => 1,
        ]);

        return redirect()->route('admin.kategori-ekskul.index')
            ->with('success', "Kategori {$data['nama_kategori']} berhasil ditambahkan.");
    }

    public function edit(KategoriEkskul $kategoriEkskul)
    {
        return view('admin.kategori-ekskul.edit', compact('kategoriEkskul'));
    }

    public function update(UpdateKategoriEkskulRequest $request, KategoriEkskul $kategoriEkskul)
    {
        $data = $request->validated();

        $kategoriEkskul->update([
            'nama_kategori' => $data['nama_kategori'],
            'is_active'     => $data['is_active'],
        ]);

        return redirect()->route('admin.kategori-ekskul.index')
            ->with('success', "Kategori {$kategoriEkskul->nama_kategori} berhasil diperbarui.");
    }

    public function toggleStatus(KategoriEkskul $kategoriEkskul)
    {
        $kategoriEkskul->update(['is_active' => ! $kategoriEkskul->is_active]);
        $label = $kategoriEkskul->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Kategori {$kategoriEkskul->nama_kategori} berhasil {$label}.");
    }
}
