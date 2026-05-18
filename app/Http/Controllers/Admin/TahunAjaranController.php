<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TahunAjaran;
use App\Services\TahunAjaranService;
use Illuminate\Http\Request;
use App\Http\Requests\StoreTahunAjaranRequest;

class TahunAjaranController extends Controller
{
    public function index(Request $request)
    {
        $tahunAjaran = TahunAjaran::query()
            ->when($request->search, fn($q) =>
                $q->where('tahun_mulai', 'like', "%{$request->search}%")
                  ->orWhere('tahun_selesai', 'like', "%{$request->search}%")
            )
            ->when($request->status !== null && $request->status !== '', fn($q) =>
                $q->where('is_active', $request->status)
            )
            ->orderByDesc('tahun_mulai')
            ->orderByRaw("FIELD(semester, 'genap', 'ganjil')")
            ->paginate(10)
            ->withQueryString();

        return view('admin.tahun-ajaran.index', compact('tahunAjaran'));
    }

    /**
     * Form buat tahun ajaran baru.
     */
    public function create()
    {
        $tahunAktif = TahunAjaran::aktif()->first();

        if ($tahunAktif) {
            if ($tahunAktif->semester === 'ganjil') {
                $saranMulai    = $tahunAktif->tahun_mulai;
                $saranSelesai  = $tahunAktif->tahun_selesai;
                $saranSemester = 'genap';
            } else {
                $saranMulai    = $tahunAktif->tahun_selesai;
                $saranSelesai  = $tahunAktif->tahun_selesai + 1;
                $saranSemester = 'ganjil';
            }
        } else {
            $saranMulai    = date('Y');
            $saranSelesai  = date('Y') + 1;
            $saranSemester = 'ganjil';
        }

        return view('admin.tahun-ajaran.create', compact(
            'saranMulai', 'saranSelesai', 'saranSemester'
        ));
    }

    /**
     * Buat tahun ajaran baru.
     */
    public function store(StoreTahunAjaranRequest $request, TahunAjaranService $service)
    {
        $data = $request->validated();

        $service->buatTahunAjaranBaru(
            (int) $data['tahun_mulai'],
            (int) $data['tahun_selesai'],
            $data['semester']
        );

        $label = $data['tahun_mulai'] . '/' . $data['tahun_selesai'] . ' - ' . ucfirst($data['semester']);

        return redirect()->route('admin.tahun-ajaran.index')
            ->with('success', "Tahun ajaran {$label} berhasil dibuat.");
    }
}
