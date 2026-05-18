<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ekskul;
use App\Models\Kriteria;
use App\Models\SoalRekomendasi;
use Illuminate\Http\Request;

// tambahkan ini
use App\Http\Requests\StoreSoalRequest;
use App\Http\Requests\UpdateSoalRequest;

class SoalController extends Controller
{
    public function index(Request $request)
    {
        $soal = SoalRekomendasi::with('kriteria')
            ->when($request->search, fn($q) =>
                $q->where('teks_soal', 'like', "%{$request->search}%")
                  ->orWhere('kode_soal', 'like', "%{$request->search}%")
            )
            ->when($request->kriteria_id, fn($q) =>
                $q->where('kriteria_id', $request->kriteria_id)
            )
            ->when($request->status !== null && $request->status !== '', fn($q) =>
                $q->where('is_active', $request->status)
            )
            ->orderBy('urutan_tampil')
            ->paginate(10)
            ->withQueryString();

        $kriteriaList = Kriteria::aktif()->get();

        return view('admin.soal.index', compact('soal', 'kriteriaList'));
    }

    public function create()
    {
        $kriteriaList = Kriteria::aktif()->get();
        $ekskulList   = Ekskul::aktif()->urutHari()->get();
        $kodeBaru     = SoalRekomendasi::generateKodeSoal();

        return view('admin.soal.create', compact('kriteriaList', 'ekskulList', 'kodeBaru'));
    }

    public function store(StoreSoalRequest $request) // ✅ pakai Form Request
    {
        // Hitung urutan tampil = soal terakhir + 1
        $urutanTerakhir = SoalRekomendasi::max('urutan_tampil') ?? 0;

        $soal = SoalRekomendasi::create([
            'kode_soal'    => SoalRekomendasi::generateKodeSoal(),
            'kriteria_id'  => $request->kriteria_id,
            'teks_soal'    => $request->teks_soal,
            'urutan_tampil'=> $urutanTerakhir + 1,
            'is_active'    => 1,
        ]);

        $soal->ekskul()->attach($request->ekskul_ids);

        return redirect()->route('admin.soal.index')
            ->with('success', "Soal {$soal->kode_soal} berhasil ditambahkan.");
    }

    public function edit(SoalRekomendasi $soal)
    {
        $soal->load('ekskul');
        $kriteriaList    = Kriteria::aktif()->get();
        $ekskulList      = Ekskul::aktif()->urutHari()->get();
        $ekskulSelected  = $soal->ekskul->pluck('ekskul_id')->toArray();

        return view('admin.soal.edit', compact('soal', 'kriteriaList', 'ekskulList', 'ekskulSelected'));
    }

    public function update(UpdateSoalRequest $request, SoalRekomendasi $soal) // ✅ pakai Form Request
    {
        $soal->update([
            'kriteria_id' => $request->kriteria_id,
            'teks_soal'   => $request->teks_soal,
            'is_active'   => $request->is_active,
        ]);

        // Sync ekskul terkait
        $soal->ekskul()->sync($request->ekskul_ids);

        return redirect()->route('admin.soal.index')
            ->with('success', "Soal {$soal->kode_soal} berhasil diperbarui.");
    }

    public function toggleStatus(SoalRekomendasi $soal)
    {
        $soal->update(['is_active' => ! $soal->is_active]);
        $label = $soal->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Soal {$soal->kode_soal} berhasil {$label}.");
    }
}
