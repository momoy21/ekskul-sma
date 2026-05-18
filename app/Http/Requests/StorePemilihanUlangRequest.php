<?php

namespace App\Http\Requests;

use App\Models\Ekskul;
use App\Models\PilihanEkskul;
use App\Models\PendaftaranSiswa;
use App\Services\ZonaSeleksiService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * StorePemilihanUlangRequest- validasi form simpan final pemilihan ulang.
 *
 * Dipakai di: PengumumanController::simpanFinal()
 *
 * Validasi struktural (rules):
 * - cadangan array opsional, nilai tiap elemen harus ID ekskul valid
 * - pengganti array opsional, nilai tiap elemen harus ID ekskul valid
 * - hapus array opsional, tiap elemen harus ID pilihan_ekskul valid
 * - tanda_tangan_ortu wajib jika ada zona merah yang diubah
 *
 * Validasi bisnis (withValidator):
 * - Semua zona merah harus ditindak (diganti atau dihapus)
 * - Semua zona kuning harus punya cadangan
 * - Minimal 1 pilihan aktif tersisa setelah perubahan
 * - Tidak boleh ada duplikasi ekskul setelah perubahan
 * - Tidak boleh dua ekskul di hari yang sama setelah perubahan
 */
class StorePemilihanUlangRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // cadangan[pilihan_id] = ekskul_id cadangan → untuk zona kuning
            'cadangan'           => 'nullable|array',
            'cadangan.*'         => 'nullable|integer|exists:ekskul,ekskul_id',

            // pengganti[pilihan_id] = ekskul_id baru → untuk zona merah yang diganti
            'pengganti'          => 'nullable|array',
            'pengganti.*'        => 'nullable|integer|exists:ekskul,ekskul_id',

            // hapus[] = pilihan_id yang dihapus → untuk zona merah yang dihapus
            'hapus'              => 'nullable|array',
            'hapus.*'            => 'nullable|integer|exists:pilihan_ekskul,pilihan_id',

            // TTD ortu wajib jika ada zona merah (dicek di withValidator)
            'tanda_tangan_ortu'  => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'cadangan.*.exists'   => 'Ekskul cadangan yang dipilih tidak valid.',
            'pengganti.*.exists'  => 'Ekskul pengganti yang dipilih tidak valid.',
            'hapus.*.exists'      => 'Pilihan yang akan dihapus tidak ditemukan.',
        ];
    }

    /**
     * Validasi bisnis setelah validasi struktural lolos.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $siswaId     = session('siswa_id');
            $hapusIds    = array_filter($this->input('hapus', []));
            $penggantiMap = array_filter($this->input('pengganti', []));
            $cadanganMap  = array_filter($this->input('cadangan', []));

            // Ambil pendaftaran siswa ini
            $pendaftaran = PendaftaranSiswa::where('siswa_id', $siswaId)
                ->where('status', '!=', 'draft')
                ->latest()
                ->first();

            if (! $pendaftaran) {
                $v->errors()->add('general', 'Data pendaftaran tidak ditemukan.');
                return;
            }

            // Ambil semua pilihan aktif
            $pilihan = PilihanEkskul::with('ekskul')
                ->where('pendaftaran_id', $pendaftaran->pendaftaran_id)
                ->where('is_deleted', 0)
                ->get();

            $zonaMap = app(ZonaSeleksiService::class)->hitungZona($pendaftaran->periode_id);

            // ── Cek semua zona merah sudah ditindak ──────────────────────────
            $zonaMerah = $pilihan->filter(
                fn($p) => ($zonaMap[$p->ekskul_id]['zona'] ?? 'merah') === 'merah'
            );
            foreach ($zonaMerah as $p) {
                $idStr = (string) $p->pilihan_id;
                if (! in_array($idStr, array_map('strval', $hapusIds)) && empty($penggantiMap[$idStr])) {
                    $v->errors()->add(
                        'zona_merah',
                        "Pilihan \"{$p->ekskul->nama_ekskul}\" zona merah belum ditindaklanjuti. Wajib diganti atau dihapus."
                    );
                }
            }

            // ── Cek semua zona kuning punya cadangan ─────────────────────────
            $zonaKuning = $pilihan->filter(
                fn($p) => ($zonaMap[$p->ekskul_id]['zona'] ?? 'merah') === 'kuning'
            );
            foreach ($zonaKuning as $p) {
                $idStr = (string) $p->pilihan_id;
                if (empty($cadanganMap[$idStr])) {
                    $v->errors()->add(
                        'zona_kuning',
                        "Pilihan \"{$p->ekskul->nama_ekskul}\" zona kuning belum memiliki pilihan cadangan."
                    );
                }
            }

            // Hentikan jika sudah ada error di atas
            if ($v->errors()->isNotEmpty()) {
                return;
            }

            // ── Cek minimal 1 pilihan aktif tersisa ──────────────────────────
            $jumlahAktifSetelah = $pilihan->count() - count($hapusIds);
            if ($jumlahAktifSetelah < 1) {
                $v->errors()->add(
                    'hapus',
                    'Minimal harus ada 1 pilihan ekstrakurikuler yang aktif. Tidak bisa menghapus semua pilihan.'
                );
                return;
            }

            // ── Bangun daftar ekskul final setelah perubahan ─────────────────
            // Untuk cek duplikasi dan bentrok hari
            $ekskulFinalIds = [];
            foreach ($pilihan as $p) {
                $idStr = (string) $p->pilihan_id;

                // Skip yang dihapus
                if (in_array($idStr, array_map('strval', $hapusIds))) {
                    continue;
                }

                // Pakai pengganti jika ada, atau ekskul asli
                $ekskulFinalIds[] = ! empty($penggantiMap[$idStr])
                    ? (int) $penggantiMap[$idStr]
                    : $p->ekskul_id;
            }

            // ── Cek duplikasi ekskul setelah perubahan ────────────────────────
            if (count($ekskulFinalIds) !== count(array_unique($ekskulFinalIds))) {
                $v->errors()->add(
                    'pengganti',
                    'Terdapat duplikasi pilihan ekstrakurikuler setelah perubahan. Setiap pilihan harus berbeda.'
                );
                return;
            }

            // ── Cek tidak ada dua ekskul di hari yang sama ───────────────────
            if (! empty($ekskulFinalIds)) {
                $hariEkskul = Ekskul::whereIn('ekskul_id', $ekskulFinalIds)
                    ->pluck('hari_pelaksanaan', 'ekskul_id')
                    ->toArray();

                $hariDipakai = array_values($hariEkskul);
                if (count($hariDipakai) !== count(array_unique($hariDipakai))) {
                    $v->errors()->add(
                        'pengganti',
                        'Terdapat dua ekstrakurikuler di hari yang sama setelah perubahan. Pilih hari yang berbeda.'
                    );
                }
            }

            // ── Cek TTD ortu wajib jika ada perubahan zona merah ─────────────
            $adaPerubahanMerah = ! empty($hapusIds) || ! empty($penggantiMap);
            if ($adaPerubahanMerah && empty($this->input('tanda_tangan_ortu'))) {
                $v->errors()->add(
                    'tanda_tangan_ortu',
                    'Tanda tangan orang tua wajib diisi karena ada perubahan pilihan ekstrakurikuler.'
                );
            }
        });
    }
}
