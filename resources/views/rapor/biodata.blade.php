<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Siswa - {{ $siswa->nama_lengkap }}</title>
    @php
        $bulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        function formatTanggalIndo($date, $bulanIndo) {
            if (!$date) return '-';
            $carbon = \Carbon\Carbon::parse($date);
            return $carbon->format('d') . ' ' . $bulanIndo[(int)$carbon->format('n')] . ' ' . $carbon->format('Y');
        }
    @endphp
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #333; padding: 5px 8px; text-align: left; vertical-align: top; }
        th { background: #eee; width: 200px; }
        .header { margin-bottom: 12px; }
        .header-title { font-size: 14pt; font-weight: bold; text-align: center; margin-bottom: 8px; }
        .header-info { display: table; width: 100%; font-size: 9pt; margin-bottom: 4px; }
        .header-info span { display: table-cell; }
        .header-info span:first-child { text-align: left; width: 33%; }
        .header-info span:nth-child(2) { text-align: center; width: 34%; }
        .header-info span:last-child { text-align: right; width: 33%; }
        .section-title { font-weight: bold; font-size: 10pt; margin-top: 8px; margin-bottom: 4px; }
        .foto-placeholder { width: 100px; height: 120px; border: 1px solid #333; background: #f0f0f0; float: right; margin-left: 12px; margin-bottom: 8px; text-align: center; font-size: 8pt; color: #666; padding-top: 50px; box-sizing: border-box; line-height: 1.2; }
        .content-wrapper { position: relative; overflow: hidden; }
        .content-wrapper::after { content: ""; display: table; clear: both; }
        .footer-wrap { margin-top: 40px; width: 100%; text-align: right; }
        .footer { display: inline-block; text-align: left; width: 220px; }
        .footer-date { margin-bottom: 0; }
        .footer-title { margin-top: 0; margin-bottom: 60px; }
        .footer-name { font-weight: bold; text-decoration: underline; margin-top: 0; margin-bottom: 0; }
        .footer-nrks { font-weight: bold; font-size: 9pt; margin-top: 2px; white-space: nowrap; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-title">PROFIL SISWA</div>
        <div class="header-info">
            <span>Nomor Induk : {{ $siswa->nis ?? '-' }}</span>
            <span>NISN : {{ $siswa->nisn ?? '-' }}</span>
            <span>Thn Masuk : {{ formatTanggalIndo($siswa->tanggal_masuk, $bulanIndo) }}</span>
        </div>
    </div>

    <div class="content-wrapper">
        <div class="foto-placeholder">Foto<br/>Siswa</div>
        
        <div class="section-title">A. KETERANGAN SISWA</div>
        <table>
            <tr><th>Nama Siswa</th><td>{{ $siswa->nama_lengkap ?? '-' }}</td></tr>
            <tr><th>Jenis Kelamin</th><td>{{ $siswa->jenis_kelamin === 'L' ? 'Laki-Laki' : ($siswa->jenis_kelamin === 'P' ? 'Perempuan' : ($siswa->jenis_kelamin ?? '-')) }}</td></tr>
            <tr><th>Kelahiran</th><td>
                a. Tempat : {{ $siswa->tempat_lahir ?? '-' }}<br/>
                b. Tanggal : {{ formatTanggalIndo($siswa->tanggal_lahir, $bulanIndo) }}
            </td></tr>
            <tr><th>Agama</th><td>{{ $siswa->agama ?? '-' }}</td></tr>
            <tr><th>Anak ke</th><td>{{ $siswa->anak_ke ?? '-' }}</td></tr>
            <tr><th>Alamat</th><td>{{ $siswa->alamat ?? '-' }}</td></tr>
            <tr><th>Nomor Telepon</th><td>{{ $siswa->no_hp ?? '-' }}</td></tr>
        </table>

        <div class="section-title">B. KETERANGAN ORANG TUA/WALI SISWA</div>
        <table>
            <tr><th>Nama Orangtua</th><td>
                a. Ayah : {{ $siswa->nama_ayah ?? '-' }}<br/>
                b. Ibu : {{ $siswa->nama_ibu ?? '-' }}
            </td></tr>
            <tr><th>Pekerjaan Orangtua</th><td>
                a. Ayah : {{ $siswa->pekerjaan_ayah ?? '-' }}<br/>
                b. Ibu : {{ $siswa->pekerjaan_ibu ?? '-' }}
            </td></tr>
            <tr><th>Alamat Orangtua</th><td>{{ $siswa->alamat ?? '-' }}</td></tr>
            <tr><th>Telepon Orangtua</th><td>{{ $siswa->no_hp_ortu ?? '-' }}</td></tr>
            <tr><th>Wali Siswa</th><td>
                a. Nama : -<br/>
                b. Pekerjaan : -<br/>
                c. Alamat : -<br/>
                d. Telepon : -
            </td></tr>
        </table>

        <div class="section-title">C. PERKEMBANGAN SISWA</div>
        <table>
            <tr><th>Sekolah Asal</th><td>{{ $siswa->sekolah_asal ?? '-' }}</td></tr>
            <tr><th>Diterima di sekolah ini</th><td>
                <!-- a. Dikelas : {{ $siswa->kelas->nama_kelas ?? '-' }}<br/> -->
                Tanggal {{ formatTanggalIndo($siswa->tanggal_masuk, $bulanIndo) }}
            </td></tr>
        </table>
    </div>

    <div class="footer-wrap">
        <div class="footer">
            <div class="footer-date">{{ $titimangsa_rapor ?? 'Cianjur, ' . formatTanggalIndo(now(), $bulanIndo) }}</div>
            <div class="footer-title">Kepala Sekolah</div>
            <div class="footer-name">{{ $kepala_sekolah->nama_lengkap ?? 'Tedi Kustendi' }}</div>
            <div class="footer-nrks">NRKS. {{ $kepala_sekolah->nuptk ?? '19023L0550207241076740' }}</div>
        </div>
    </div>
</body>
</html>
