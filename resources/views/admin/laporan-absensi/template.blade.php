<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 10px; color: #000; }

        .header-sekolah { text-align: center; margin-bottom: 12px; }
        .header-sekolah h2 { font-size: 13px; font-weight: bold; text-transform: uppercase; letter-spacing: .5px; }
        .header-sekolah h3 { font-size: 11px; font-weight: bold; text-transform: uppercase; margin-bottom: 2px; }

        .divider { border-top: 2px solid #000; margin: 6px 0 10px; }

        .info-table { width: 100%; margin-bottom: 10px; }
        .info-table td { padding: 1px 4px; font-size: 9.5px; }
        .info-table td:first-child { width: 120px; font-weight: bold; }

        table.absensi {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
        }
        table.absensi th,
        table.absensi td {
            border: 1px solid #333;
            padding: 3px 4px;
            text-align: center;
        }
        table.absensi th { background: #e8e8e8; font-weight: bold; }
        table.absensi td.nama { text-align: left; white-space: nowrap; }
        table.absensi tr:nth-child(even) td { background: #f9f9f9; }

        .keterangan { margin-top: 8px; font-size: 9px; }
        .ttd-box { margin-top: 16px; display: flex; justify-content: flex-end; }
        .ttd-inner { width: 220px; text-align: center; font-size: 9px; }
        .ttd-inner .ttd-line { border-top: 1px solid #000; margin-top: 50px; padding-top: 3px; }

    </style>
</head>
<body>


<div class="header-sekolah" style="margin-top: 10px;">
    <h3>Daftar Hadir Ekstrakurikuler</h3>
    <h2>SMA Global Indonesia</h2>
</div>
<div class="divider"></div>

<table class="info-table">
    <tr>
        <td>Nama Ekstrakurikuler</td>
        <td>: <strong>{{ $nama_ekskul }}</strong></td>
    </tr>
    <tr>
        <td>Tahun Ajaran</td>
        <td>: {{ $tahun_ajaran }}</td>
    </tr>
    <tr>
        <td>Semester</td>
        <td>: {{ $semester }}</td>
    </tr>
    <tr>
        <td>Pembina</td>
        <td>: {{ $nama_pembina }}</td>
    </tr>
    <tr>
        <td>Hari / Jam</td>
        <td>: {{ $hari }} / 15.00 – 16.00 WIB</td>
    </tr>
    <tr>
        <td>Lokasi</td>
        <td>: {{ $lokasi }}</td>
    </tr>
</table>

<table class="absensi">
    <thead>
        <tr>
            <th style="width:28px">No</th>
            <th style="min-width:130px; text-align:left">Nama Lengkap</th>
            <th style="width:20px">L/P</th>
            <th style="width:30px">Kelas</th>
            @for ($i = 1; $i <= $pertemuan; $i++)
                <th style="width:18px">W{{ $i }}</th>
            @endfor
        </tr>
    </thead>
    <tbody>
        @forelse ($peserta as $no => $p)
            <tr>
                <td>{{ $no + 1 }}</td>
                <td class="nama">{{ $p->snapshot_nama }}</td>
                <td>{{ $p->snapshot_jenis_kelamin }}</td>
                <td>{{ $p->snapshot_label_kelas }}</td>
                @for ($i = 1; $i <= $pertemuan; $i++)
                    <td>&nbsp;</td>
                @endfor
            </tr>
        @empty
            <tr>
                <td colspan="{{ $pertemuan + 3 }}" style="text-align:center;padding:8px">
                    Belum ada peserta terdaftar.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="keterangan">
    Keterangan: &nbsp; <strong>H</strong> = Hadir &nbsp;&nbsp;
    <strong>I</strong> = Izin &nbsp;&nbsp;
    <strong>S</strong> = Sakit &nbsp;&nbsp;
    <strong>A</strong> = Alpha
</div>

<div class="ttd-box">
    <div class="ttd-inner">
        <div>Mengetahui,</div>
        <div class="ttd-line">
            <strong>( {{ $nama_pembina }} )</strong><br>
            Pembina Ekstrakurikuler
        </div>
    </div>
</div>

</body>
</html>
