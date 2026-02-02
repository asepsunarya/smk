<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Hasil Belajar - {{ $siswa->nama_lengkap ?? 'Siswa' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #333; padding: 6px 8px; }
        th { background: #eee; }
        .header-dua-kolom { display: table; width: 100%; margin-bottom: 16px; }
        .header-kiri, .header-kanan { display: table-cell; width: 50%; vertical-align: top; }
        .header-kiri table, .header-kanan table { width: auto; }
        .header-kiri td:first-child { width: 120px; }
        .header-kanan td:first-child { width: 100px; }
        /* Identitas tanpa border */
        table.identitas-tabel { border: none !important; }
        table.identitas-tabel th, table.identitas-tabel td { border: none !important; }
        table.identitas-tabel td { padding: 2px 8px 2px 0; }
        .judul-rapor { text-align: center; font-size: 12pt; font-weight: bold; margin-bottom: 12px; }
        .kelompok { margin-top: 14px; font-weight: bold; }
        .page-break { page-break-after: always; }
        .ttd { margin-top: 24px; }
        .ttd table { width: 100%; border: none; }
        .ttd td { border: none; padding: 4px; vertical-align: top; }
        .ttd .nama { text-decoration: underline; margin-top: 48px; }
        .ttd-tanggal { margin-bottom: 8px; }
    </style>
</head>
<body>
    {{-- Halaman 1 --}}
    <div class="judul-rapor">LAPORAN HASIL BELAJAR</div>

    <div class="header-dua-kolom">
        <div class="header-kiri">
            <table class="identitas-tabel">
                <tr><td>Nama</td><td>: {{ $siswa->nama_lengkap ?? '-' }}</td></tr>
                <tr><td>NIS/NISN</td><td>: {{ $siswa->nis ?? '-' }} / {{ $siswa->nisn ?? '-' }}</td></tr>
                <tr><td>Nama Sekolah</td><td>: {{ $nama_sekolah ?? '-' }}</td></tr>
                <tr><td>Alamat</td><td>: {{ $alamat_sekolah ?? '-' }}</td></tr>
            </table>
        </div>
        <div class="header-kanan">
            <table class="identitas-tabel">
                <tr><td>Kelas</td><td>: {{ $kelas_display ?? (optional($siswa->kelas)->nama_kelas ?? '-') }}</td></tr>
                <tr><td>Fase</td><td>: {{ $fase ?? 'E' }}</td></tr>
                <tr><td>Periode</td><td>: {{ $periode ?? '-' }}</td></tr>
                <tr><td>Semester</td><td>: {{ $semester ?? '-' }}</td></tr>
                <tr><td>Tahun Pelajaran</td><td>: {{ optional($tahun_ajaran)->tahun ?? '-' }}</td></tr>
            </table>
        </div>
    </div>

    <div class="kelompok">LAPORAN HASIL BELAJAR</div>
    @if (!empty($nilai_belum_diisi))
        <p style="margin: 8px 0; padding: 8px; background: #fff3cd; border: 1px solid #ffc107; font-size: 9pt;">Nilai untuk semester ini belum diisi. Silakan input nilai melalui menu Penilaian (Guru) terlebih dahulu.</p>
    @endif
    @php
        $kelompokLabel = [
            'umum' => 'Mata Pelajaran Umum',
            'kejuruan' => 'Mata Pelajaran Kejuruan',
            'muatan_lokal' => 'Muatan Lokal',
        ];
    @endphp

    @foreach ($nilai_by_kelompok ?? [] as $kelompok => $items)
        @php $itemsList = is_array($items) ? $items : (method_exists($items, 'all') ? $items->all() : []); @endphp
        @if (!empty($itemsList))
            <div class="kelompok" style="margin-top: 8px;">{{ $kelompokLabel[$kelompok] ?? $kelompok }}</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 35%;">Mata Pelajaran</th>
                        <th style="width: 15%;" class="text-center">Nilai Akhir</th>
                        <th style="width: 45%;">Capaian Kompetensi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($itemsList as $idx => $row)
                        @php
                            $namaMapel = is_array($row) ? ($row['nama_mapel'] ?? '-') : (optional($row->mataPelajaran ?? null)->nama_mapel ?? '-');
                            $nilaiAkhir = is_array($row) ? ($row['nilai_rapor'] ?? '-') : ($row->nilai_rapor ?? '-');
                            $capaianKompetensi = is_array($row) ? ($row['deskripsi'] ?? '-') : ($row->deskripsi ?? '-');
                            if ($namaMapel === '' || $namaMapel === null) { $namaMapel = '-'; }
                            if ($nilaiAkhir === '' || $nilaiAkhir === null) { $nilaiAkhir = '-'; }
                            if ($capaianKompetensi === '' || $capaianKompetensi === null) { $capaianKompetensi = '-'; }
                        @endphp
                        <tr>
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td>{{ $namaMapel }}</td>
                            <td class="text-center">{{ $nilaiAkhir }}</td>
                            <td>{{ $capaianKompetensi }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endforeach

    <div class="page-break"></div>

    {{-- Halaman 2 --}}
    <div class="judul-rapor">LAPORAN HASIL BELAJAR</div>

    <div class="header-dua-kolom">
        <div class="header-kiri">
            <table class="identitas-tabel">
                <tr><td>Nama</td><td>: {{ $siswa->nama_lengkap ?? '-' }}</td></tr>
                <tr><td>NIS/NISN</td><td>: {{ $siswa->nis ?? '-' }} / {{ $siswa->nisn ?? '-' }}</td></tr>
                <tr><td>Nama Sekolah</td><td>: {{ $nama_sekolah ?? '-' }}</td></tr>
                <tr><td>Alamat</td><td>: {{ $alamat_sekolah ?? '-' }}</td></tr>
            </table>
        </div>
        <div class="header-kanan">
            <table class="identitas-tabel">
                <tr><td>Kelas</td><td>: {{ $kelas_display ?? (optional($siswa->kelas)->nama_kelas ?? '-') }}</td></tr>
                <tr><td>Fase</td><td>: {{ $fase ?? 'E' }}</td></tr>
                <tr><td>Periode</td><td>: {{ $periode ?? '-' }}</td></tr>
                <tr><td>Semester</td><td>: {{ $semester ?? '-' }}</td></tr>
                <tr><td>Tahun Pelajaran</td><td>: {{ optional($tahun_ajaran)->tahun ?? '-' }}</td></tr>
            </table>
        </div>
    </div>

    <div class="kelompok">Kegiatan Ekstrakurikuler</div>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 45%;">Kegiatan Ekstrakurikuler</th>
                <th style="width: 15%;" class="text-center">Predikat</th>
                <th style="width: 35%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @if (isset($nilai_ekstrakurikuler) && collect($nilai_ekstrakurikuler)->isNotEmpty())
                @foreach ($nilai_ekstrakurikuler as $idx => $ne)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>{{ optional($ne->ekstrakurikuler)->nama ?? '-' }}</td>
                        <td class="text-center">{{ $ne->predikat ?? '-' }}</td>
                        <td>{{ $ne->keterangan ?? '-' }}</td>
                    </tr>
                @endforeach
            @else
                <tr><td class="text-center">1</td><td>-</td><td class="text-center">-</td><td>-</td></tr>
                <tr><td class="text-center">2</td><td>-</td><td class="text-center">-</td><td>-</td></tr>
            @endif
        </tbody>
    </table>

    <div class="kelompok">Ketidakhadiran</div>
    <table style="width: auto;">
        <tr>
            <th>Sakit</th>
            <th>Izin</th>
            <th>Tanpa Keterangan</th>
        </tr>
        <tr>
            <td class="text-center">{{ $kehadiran ? (int)($kehadiran->sakit ?? 0) : 0 }} hari</td>
            <td class="text-center">{{ $kehadiran ? (int)($kehadiran->izin ?? 0) : 0 }} hari</td>
            <td class="text-center">{{ $kehadiran ? (int)($kehadiran->tanpa_keterangan ?? 0) : 0 }} hari</td>
        </tr>
    </table>

    <div class="kelompok">Praktik Kerja Lapangan</div>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 45%;">DU/DI</th>
                <th style="width: 15%;" class="text-center">Lamanya (bulan)</th>
                <th style="width: 35%;">Predikat</th>
            </tr>
        </thead>
        <tbody>
            @if (isset($nilai_pkl) && $nilai_pkl && ($nilai_pkl->nama_du_di ?? null))
                <tr>
                    <td class="text-center">1</td>
                    <td>{{ $nilai_pkl->nama_du_di ?? '-' }}</td>
                    <td class="text-center">{{ $nilai_pkl->lamanya_bulan ?? '-' }}</td>
                    <td>{{ $nilai_pkl->keterangan ?? '-' }}</td>
                </tr>
            @else
                <tr>
                    <td class="text-center">1</td>
                    <td>-</td>
                    <td class="text-center">-</td>
                    <td>-</td>
                </tr>
            @endif
        </tbody>
    </table>

    @if ($catatan_akademik && ($catatan_akademik->catatan ?? null))
        <div class="kelompok">Catatan Wali Kelas</div>
        <p style="margin: 4px 0; border: 1px solid #333; padding: 8px; min-height: 60px;">{{ $catatan_akademik->catatan }}</p>
    @else
        <div class="kelompok">Catatan Wali Kelas</div>
        <p style="margin: 4px 0; border: 1px solid #333; padding: 8px; min-height: 60px;">-</p>
    @endif

    <div class="ttd">
        <table>
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    Mengetahui Orang Tua/Wali,<br><br><br><br>
                    <span class="nama">_________________________</span>
                </td>
                <td style="width: 50%; text-align: left; vertical-align: top; padding-left: 48px;">
                    <div class="ttd-tanggal">Cianjur, {{ isset($tanggal_rapor) ? (is_object($tanggal_rapor) && method_exists($tanggal_rapor, 'translatedFormat') ? $tanggal_rapor->translatedFormat('d F Y') : \Carbon\Carbon::parse($tanggal_rapor)->locale('id')->translatedFormat('d F Y')) : \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}</div>
                    Wali Kelas,<br><br><br><br>
                    <span class="nama">{{ optional($wali_kelas)->nama_lengkap ?? '_________________________' }}</span><br>
                    NUPTK. {{ optional($wali_kelas)->nuptk ?? '-' }}
                </td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center; vertical-align: top; padding-top: 24px;">
                    Mengetahui Kepala Sekolah<br>
                    @if (($rapor_disetujui ?? false) && !empty($ttd_ks_path) && file_exists($ttd_ks_path))
                        <img src="{{ $ttd_ks_path }}" alt="Tanda Tangan Kepala Sekolah" style="height: 100px;" />
                    @else
                        <br><br><br>
                    @endif
                    <br>
                    <span class="nama">{{ optional($kepala_sekolah)->nama_lengkap ?? '_________________________' }}</span><br>
                    NRKS. {{ config('app.nrks', optional($kepala_sekolah)->nuptk ?? '19023L0550207241076740') }}
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
