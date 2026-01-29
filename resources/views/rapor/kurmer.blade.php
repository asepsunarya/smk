<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rapor Hasil Belajar - {{ $siswa->nama_lengkap }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #333; padding: 6px 8px; }
        th { background: #eee; }
        .kop { margin-bottom: 16px; border-bottom: 2px solid #333; padding-bottom: 8px; }
        .identitas { margin-bottom: 16px; }
        .identitas table { width: auto; }
        .identitas td:first-child { width: 140px; }
        .kelompok { margin-top: 14px; font-weight: bold; }
        .ttd { margin-top: 24px; }
        .ttd table { width: 100%; border: none; }
        .ttd td { border: none; padding: 4px; vertical-align: top; width: 50%; }
        .ttd .nama { text-decoration: underline; margin-top: 48px; }
    </style>
</head>
<body>
    <div class="kop text-center">
        <div class="bold" style="font-size: 12pt;">RAPOR HASIL BELAJAR</div>
        <div style="font-size: 9pt;">Satuan Pendidikan – Kurikulum Merdeka</div>
    </div>

    <div class="identitas">
        <table>
            <tr><td>Nama Peserta Didik</td><td>: {{ $siswa->nama_lengkap ?? '-' }}</td></tr>
            <tr><td>NISN</td><td>: {{ $siswa->nisn ?? '-' }}</td></tr>
            <tr><td>NIS</td><td>: {{ $siswa->nis ?? '-' }}</td></tr>
            <tr><td>Kelas</td><td>: {{ $siswa->kelas->nama_kelas ?? '-' }} {{ $siswa->kelas->jurusan->nama_jurusan ?? '' }}</td></tr>
            <tr><td>Semester</td><td>: {{ $semester }}</td></tr>
            <tr><td>Tahun Pelajaran</td><td>: {{ $tahun_ajaran->tahun ?? '-' }}</td></tr>
        </table>
    </div>

    @php
        $kelompokLabel = [
            'umum' => 'A. Mata Pelajaran Umum',
            'kejuruan' => 'B. Mata Pelajaran Kejuruan',
            'muatan_lokal' => 'C. Muatan Lokal',
        ];
    @endphp

    @foreach ($nilai_by_kelompok as $kelompok => $items)
        <div class="kelompok">{{ $kelompokLabel[$kelompok] ?? $kelompok }}</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 4%;">No</th>
                    <th style="width: 36%;">Mata Pelajaran</th>
                    <th style="width: 10%;" class="text-center">KKM</th>
                    <th style="width: 12%;" class="text-center">Nilai</th>
                    <th style="width: 10%;" class="text-center">Predikat</th>
                    <th style="width: 28%;">Deskripsi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $idx => $nilai)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>{{ $nilai->mataPelajaran->nama_mapel ?? '-' }}</td>
                        <td class="text-center">{{ $nilai->mataPelajaran->kkm ?? '-' }}</td>
                        <td class="text-center">{{ $nilai->nilai_rapor ?? '-' }}</td>
                        <td class="text-center">{{ $nilai->predikat ?? '-' }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($nilai->deskripsi ?? '-', 80) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach

    @if ($kehadiran)
        @php
            $sakit = (int) ($kehadiran->sakit ?? 0);
            $izin = (int) ($kehadiran->izin ?? 0);
            $alpha = (int) ($kehadiran->tanpa_keterangan ?? 0);
            $hadir = max(0, 180 - $sakit - $izin - $alpha);
        @endphp
        <div class="kelompok">Kehadiran</div>
        <table>
            <tr>
                <th>Hadir</th>
                <th>Izin</th>
                <th>Sakit</th>
                <th>Alpha</th>
            </tr>
            <tr>
                <td class="text-center">{{ $hadir }}</td>
                <td class="text-center">{{ $izin }}</td>
                <td class="text-center">{{ $sakit }}</td>
                <td class="text-center">{{ $alpha }}</td>
            </tr>
        </table>
    @endif

    @if ($catatan_akademik && ($catatan_akademik->catatan ?? null))
        <div class="kelompok">Catatan Wali Kelas</div>
        <p style="margin: 4px 0;">{{ $catatan_akademik->catatan }}</p>
    @endif

    <div class="ttd">
        <table>
            <tr>
                <td class="text-center">
                    <span class="nama">Wali Kelas</span>
                </td>
                <td class="text-center">
                    <span class="nama">Orang Tua/Wali</span>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
