<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pembina;
use Illuminate\Http\Request;
use App\Http\Requests\StorePembinaRequest;
use App\Http\Requests\UpdatePembinaRequest;

class PembinaController extends Controller
{
    public function index(Request $request)
    {
        $pembina = Pembina::query()
            ->when($request->search, fn($q) =>
                $q->where('nama_lengkap', 'like', "%{$request->search}%")
            )
            ->when($request->status !== null && $request->status !== '', fn($q) =>
                $q->where('is_active', $request->status)
            )
            ->orderBy('nama_lengkap')
            ->paginate(10)
            ->withQueryString();

        return view('admin.pembina.index', compact('pembina'));
    }

    public function create()
    {
        return view('admin.pembina.create');
    }

    public function store(StorePembinaRequest $request)
    {
        $data = $request->validated();

        Pembina::create([
            'nama_lengkap' => $data['nama_lengkap'],
            'is_active'    => 1,
        ]);

        return redirect()->route('admin.pembina.index')
            ->with('success', "Pembina {$data['nama_lengkap']} berhasil ditambahkan.");
    }

    public function edit(Pembina $pembina)
    {
        return view('admin.pembina.edit', compact('pembina'));
    }

    public function update(UpdatePembinaRequest $request, Pembina $pembina)
    {
        $data = $request->validated();

        $pembina->update([
            'nama_lengkap' => $data['nama_lengkap'],
            'is_active'    => $data['is_active'],
        ]);

        return redirect()->route('admin.pembina.index')
            ->with('success', "Data pembina {$pembina->nama_lengkap} berhasil diperbarui.");
    }

    public function toggleStatus(Pembina $pembina)
    {
        $pembina->update(['is_active' => ! $pembina->is_active]);
        $label = $pembina->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Pembina {$pembina->nama_lengkap} berhasil {$label}.");
    }
}
