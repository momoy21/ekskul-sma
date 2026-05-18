<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use Illuminate\Http\Request;

// ✅ tambah ini
use App\Http\Requests\StoreKelasRequest;
use App\Http\Requests\UpdateKelasRequest;

class KelasController extends Controller
{
    public function index(Request $request)
    {
        $kelas = Kelas::query()
            ->when($request->search, fn($q) =>
                $q->where('nama_kelas', 'like', "%{$request->search}%")
            )
            ->when($request->tingkat, fn($q) =>
                $q->where('tingkat', $request->tingkat)
            )
            ->when($request->status !== null && $request->status !== '', fn($q) =>
                $q->where('is_active', $request->status)
            )
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->paginate(10)
            ->withQueryString();

        return view('admin.kelas.index', compact('kelas'));
    }

    public function create()
    {
        return view('admin.kelas.create');
    }

    public function store(StoreKelasRequest $request)
    {
        $data = $request->validated();

        Kelas::create([
            'tingkat'    => $data['tingkat'],
            'nama_kelas' => $data['nama_kelas'],
            'is_active'  => 1,
        ]);

        return redirect()->route('admin.kelas.index')
            ->with('success', "Kelas {$data['tingkat']} {$data['nama_kelas']} berhasil ditambahkan.");
    }

    public function edit(Kelas $kelas)
    {
        return view('admin.kelas.edit', compact('kelas'));
    }

    public function update(UpdateKelasRequest $request, Kelas $kelas)
    {
        $data = $request->validated();

        $kelas->update([
            'tingkat'    => $data['tingkat'],
            'nama_kelas' => $data['nama_kelas'],
            'is_active'  => $data['is_active'],
        ]);

        return redirect()->route('admin.kelas.index')
            ->with('success', "Kelas {$kelas->tingkat} {$kelas->nama_kelas} berhasil diperbarui.");
    }

    /** Toggle aktif/nonaktif langsung dari baris tabel. */
    public function toggleStatus(Kelas $kelas)
    {
        $kelas->update(['is_active' => ! $kelas->is_active]);
        $label = $kelas->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Kelas {$kelas->tingkat} {$kelas->nama_kelas} berhasil {$label}.");
    }
}
