<?php

namespace App\Http\Requests;

use App\Models\Ekskul;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * StorePendaftaranRequest- validasi form pendaftaran ekskul oleh siswa.
 *
 * Dipakai di: PendaftaranController::simpan()
 *
 * Validasi struktural (Laravel rules):
 * - jumlah_pilihan antara 1–4
 * - ekskul_ids array dengan jumlah sesuai jumlah_pilihan
 * - Setiap ekskul_id harus ada di tabel ekskul
 * - Tanda tangan orang tua wajib ada
 *
 * Validasi bisnis (afterValidator):
 * - Tidak boleh duplikat ekskul
 * - Tidak boleh dua ekskul di hari yang sama
 * - Ekskul yang dipilih harus berstatus aktif
 */
class StorePendaftaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jumlah_pilihan'    => 'required|integer|min:1|max:4',
            'ekskul_ids'        => 'required|array|min:1|max:4',
            // Setiap elemen array harus ada dan valid
            'ekskul_ids.*'      => 'required|integer|exists:ekskul,ekskul_id',
            // Base64 string dari canvas tanda tangan- wajib ada
            'tanda_tangan_ortu' => 'required|string|min:10',
        ];
    }

    public function messages(): array
    {
        return [
            'jumlah_pilihan.required'    => 'Jumlah pilihan wajib dipilih.',
            'jumlah_pilihan.min'         => 'Minimal memilih 1 ekstrakurikuler.',
            'jumlah_pilihan.max'         => 'Maksimal memilih 4 ekstrakurikuler.',
            'ekskul_ids.required'        => 'Pilih minimal satu ekstrakurikuler.',
            'ekskul_ids.min'             => 'Pilih minimal satu ekstrakurikuler.',
            'ekskul_ids.max'             => 'Maksimal 4 pilihan ekstrakurikuler.',
            'ekskul_ids.*.required'      => 'Semua pilihan wajib diisi.',
            'ekskul_ids.*.exists'        => 'Salah satu ekskul yang dipilih tidak valid.',
            'tanda_tangan_ortu.required' => 'Tanda tangan orang tua wajib diisi.',
        ];
    }

    /**
     * Validasi bisnis setelah validasi struktural lolos.
     * Dipanggil otomatis oleh Laravel sebelum request sampai ke controller.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $ekskulIds = array_values(array_filter($this->input('ekskul_ids', [])));

            if (empty($ekskulIds)) {
                return;
            }

            // ── Cek duplikasi ekskul ──────────────────────────────────────────
            if (count($ekskulIds) !== count(array_unique($ekskulIds))) {
                $v->errors()->add('ekskul_ids', 'Tidak boleh memilih ekstrakurikuler yang sama lebih dari sekali.');
                return; // hentikan validasi berikutnya jika sudah ada error duplikasi
            }

            // ── Cek dua ekskul di hari yang sama ─────────────────────────────
            $ekskulDipilih = Ekskul::whereIn('ekskul_id', $ekskulIds)
                ->get(['ekskul_id', 'nama_ekskul', 'hari_pelaksanaan', 'is_active']);

            // Cek semua ekskul masih aktif
            $nonAktif = $ekskulDipilih->where('is_active', 0);
            if ($nonAktif->isNotEmpty()) {
                $namaList = $nonAktif->pluck('nama_ekskul')->join(', ');
                $v->errors()->add('ekskul_ids', "Ekskul berikut sudah tidak aktif: {$namaList}.");
                return;
            }

            // Kelompokkan per hari- kalau satu hari punya lebih dari 1 ekskul, tolak
            $hariGroup = $ekskulDipilih->groupBy('hari_pelaksanaan');
            foreach ($hariGroup as $hari => $list) {
                if ($list->count() > 1) {
                    $namaList = $list->pluck('nama_ekskul')->join(' dan ');
                    $v->errors()->add(
                        'ekskul_ids',
                        "Tidak boleh memilih dua ekskul di hari yang sama. {$namaList} sama-sama dilaksanakan hari {$hari}."
                    );
                }
            }
        });
    }
}
