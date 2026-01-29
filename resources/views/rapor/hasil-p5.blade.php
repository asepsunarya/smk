<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rapor Projek P5 - {{ $siswa->nama_lengkap ?? 'Siswa' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; margin: 20px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #999; padding: 8px; vertical-align: top; }
        th { background: #f5f5f5; font-weight: bold; }

        .header-sekolah { margin-bottom: 20px; }
        .header-sekolah .nama-sekolah { font-size: 14pt; font-weight: bold; text-align: center; margin-bottom: 4px; }
        .header-sekolah .judul-rapor { font-size: 12pt; font-weight: bold; text-align: center; margin-bottom: 2px; }
        .header-sekolah .tahun-pelajaran { font-size: 10pt; text-align: center; }

        .section-title { font-size: 11pt; font-weight: bold; margin: 16px 0 8px 0; }
        .identitas-box { border: 1px solid #999; margin-bottom: 16px; }
        .identitas-box table { margin: 0; }
        .identitas-box th { width: 200px; text-align: left; background: #fff; border: 1px solid #999; padding: 8px; font-weight: bold; }
        .identitas-box td { border: 1px solid #999; padding: 8px; background: #fff; }

        .projek-nomor { font-size: 11pt; font-weight: bold; margin-top: 18px; margin-bottom: 4px; }
        .projek-judul { font-size: 11pt; font-weight: bold; margin-bottom: 12px; }

        .info-projek-box { border: 1px solid #999; margin-bottom: 16px; }
        .info-projek-box table { margin: 0; }
        .info-projek-box th { width: 120px; text-align: left; background: #f5f5f5; border: 1px solid #999; padding: 6px 8px; font-weight: bold; font-size: 9pt; }
        .info-projek-box td { border: 1px solid #999; padding: 6px 8px; font-size: 9pt; }
        .info-projek-box .deskripsi-cell { font-size: 9pt; line-height: 1.35; }

        .capaian-boxes { margin: 12px 0 16px 0; }
        .capaian-boxes table { margin: 0; border-collapse: collapse; width: 100%; }
        .capaian-boxes td { border: 1px solid #bbb; padding: 10px 12px; width: 25%; vertical-align: top; font-size: 9pt; }
        .capaian-boxes .predikat { font-weight: bold; margin-bottom: 4px; }
        .capaian-boxes .desc { line-height: 1.35; color: #444; }

        .tujuan-table { margin-bottom: 16px; border-collapse: collapse; width: 100%; table-layout: fixed; }
        .tujuan-table th, .tujuan-table td { padding: 6px 4px; font-size: 9pt; border: 1px solid #999; }
        .tujuan-table thead th { background: #e8e8e8; font-weight: bold; text-align: center; padding: 8px 4px; }
        .tujuan-table .no-col { width: 5%; text-align: center; vertical-align: middle; font-weight: bold; }
        .tujuan-table .desc-col { width: 56%; text-align: left; vertical-align: top; padding: 8px 10px; }
        .tujuan-table .desc-col .sub-nama { font-weight: bold; font-size: 9pt; margin-bottom: 3px; display: block; }
        .tujuan-table .desc-col .sub-desc { font-size: 8.5pt; color: #444; line-height: 1.35; padding-left: 6px; }
        .tujuan-table .predikat-col { width: 9.75%; text-align: center; vertical-align: middle; padding: 8px 2px; font-size: 12pt; }

        .catatan-proses { border: 1px solid #bbb; min-height: 80px; padding: 12px 14px; margin-top: 20px; margin-bottom: 20px; font-size: 9pt; color: #444; line-height: 1.4; background: #fafafa; }
        .catatan-proses .placeholder { color: #999; }

        .footer-blocks { margin-top: 24px; }
        .footer-blocks table { margin: 0; width: 100%; }
        .footer-blocks td { border: 1px solid #999; width: 50%; padding: 12px; vertical-align: top; }
        .footer-blocks .head { font-weight: bold; margin-bottom: 40px; }
        .footer-blocks .signature-line { border-bottom: 1px solid #333; margin-top: 4px; height: 1px; }

        .page-footer { font-size: 8pt; color: #666; text-align: right; margin-top: 24px; }
    </style>
</head>
<body>
    {{-- Header: Nama Sekolah, Judul Rapor, Tahun Pelajaran --}}
    <div class="header-sekolah">
        <div class="nama-sekolah">{{ $nama_sekolah ?? 'SMKS Progresia Cianjur' }}</div>
        <div class="judul-rapor">Rapor Projek Penguatan Profil Pelajar Pancasila</div>
        @if(!empty($tahun_pelajaran))
            <div class="tahun-pelajaran">Tahun Pelajaran {{ $tahun_pelajaran }}</div>
        @endif
    </div>

    {{-- Identitas Siswa --}}
    <div class="section-title">Identitas Siswa</div>
    <div class="identitas-box">
        <table>
            <tr><th>Nama Siswa</th><td>{{ $siswa->nama_lengkap ?? '-' }}</td></tr>
            <tr><th>NIS</th><td>{{ $siswa->nis ?? '-' }}</td></tr>
            <tr><th>Rombel</th><td>{{ $siswa->kelas->nama_kelas ?? '-' }}</td></tr>
            <tr><th>Program Keahlian</th><td>{{ $siswa->kelas->jurusan->nama_jurusan ?? '-' }}</td></tr>
        </table>
    </div>

    @if(!empty($p5_projects) && count($p5_projects) > 0)
        @foreach($p5_projects as $index => $project)
            {{-- Judul projek (cetak per projek, tanpa nomor) --}}
            <div class="projek-judul" style="margin-top: 18px;">{{ $project['judul'] ?? $project['tema'] ?? 'Projek P5' }}</div>

            {{-- Informasi Projek: Tema, Judul, Deskripsi | Fase, Tahun Pelajaran --}}
            <div class="section-title">Informasi Projek</div>
            <div class="info-projek-box">
                <table>
                    <tr>
                        <td style="width: 50%; vertical-align: top;">
                            <table style="margin: 0;">
                                <tr><th>Tema</th><td>{{ $project['tema'] ?? $project['dimensi'] ?? '-' }}</td></tr>
                                <tr><th>Judul</th><td>{{ $project['judul'] ?? $project['tema'] ?? '-' }}</td></tr>
                                <tr><th>Deskripsi</th><td class="deskripsi-cell">{{ $project['deskripsi'] ?? '-' }}</td></tr>
                            </table>
                        </td>
                        <td style="width: 50%; vertical-align: top;">
                            <table style="margin: 0;">
                                <tr><th>Fase</th><td>{{ $fase ?? 'E' }}</td></tr>
                                <tr><th>Tahun Pelajaran</th><td>{{ $tahun_pelajaran ?? '-' }}</td></tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>

            {{-- Capaian Siswa: MB, SB, BSH, SAB --}}
            <div class="section-title">Capaian Siswa</div>
            <div class="capaian-boxes">
                <table>
                    <tr>
                        <td><div class="predikat">MB = Mulai Berkembang</div><div class="desc">Siswa masih membutuhkan bimbingan dalam mengembangkan kemampuan.</div></td>
                        <td><div class="predikat">SB = Sedang Berkembang</div><div class="desc">Siswa sedang dalam proses mengembangkan kemampuannya.</div></td>
                        <td><div class="predikat">BSH = Berkembang Sesuai Harapan</div><div class="desc">Siswa telah mengembangkan kemampuan hingga berada dalam tahap ajek.</div></td>
                        <td><div class="predikat">SAB = Sangat Berkembang</div><div class="desc">Siswa mengembangkan kemampuannya melampaui harapan.</div></td>
                    </tr>
                </table>
            </div>

            {{-- Tujuan Pembelajaran: No | Deskripsi | MB | SB | BSH | SAB (checkmark) --}}
            <div class="section-title">Tujuan Pembelajaran</div>
            @if(!empty($project['elemen_sub']))
                <table class="tujuan-table">
                    <thead>
                        <tr>
                            <th class="no-col">No</th>
                            <th class="desc-col">Tujuan Pembelajaran</th>
                            <th class="predikat-col">MB</th>
                            <th class="predikat-col">SB</th>
                            <th class="predikat-col">BSH</th>
                            <th class="predikat-col">SAB</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($project['elemen_sub'] as $i => $es)
                            @php
                                $p = $es['predikat'] ?? null;
                                $subNama = trim((string)($es['sub_elemen'] ?? ''));
                                if ($subNama === '') {
                                    $subNama = '-';
                                }
                                $desc = trim((string)($es['deskripsi_tujuan'] ?? ''));
                                if ($desc === '') {
                                    $desc = '-';
                                }
                            @endphp
                            <tr>
                                <td class="no-col">{{ $i + 1 }}</td>
                                <td class="desc-col">
                                    <span class="sub-nama">{{ $subNama }}</span>
                                    <span class="sub-desc">{{ $desc }}</span>
                                </td>
                                <td class="predikat-col">{{ $p === 'MB' ? '✓' : '' }}</td>
                                <td class="predikat-col">{{ $p === 'SB' ? '✓' : '' }}</td>
                                <td class="predikat-col">{{ $p === 'BSH' ? '✓' : '' }}</td>
                                <td class="predikat-col">{{ $p === 'SAB' ? '✓' : '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="catatan-proses"><span class="placeholder">Belum ada nilai sub elemen.</span></div>
            @endif

            {{-- Catatan Proses (dikebawahkan) --}}
            <div class="section-title" style="margin-top: 28px;">Catatan Proses</div>
            <div class="catatan-proses">
                @if(!empty($project['catatan_proses']))
                    {{ $project['catatan_proses'] }}
                @else
                    <span class="placeholder">Belum ada catatan proses yang dibuat</span>
                @endif
            </div>

            {{-- Orang Tua/Wali | Fasilitator --}}
            <div class="footer-blocks">
                <table>
                    <tr>
                        <td><div class="head">Orang Tua/Wali</div><div class="signature-line"></div></td>
                        <td><div class="head">Fasilitator</div><div>{{ $project['fasilitator_nama'] ?? '-' }}</div></td>
                    </tr>
                </table>
            </div>

            @if($index < count($p5_projects) - 1)
                <div style="page-break-after: always;"></div>
            @endif
        @endforeach
    @else
        <p style="color: #666;">Belum ada data projek P5 untuk siswa ini.</p>
    @endif

    <div class="page-footer" style="margin-top: 24px;">Diunduh pada pukul {{ $waktu_unduh ?? '' }} dari SI Rapor Kurmer</div>
</body>
</html>
