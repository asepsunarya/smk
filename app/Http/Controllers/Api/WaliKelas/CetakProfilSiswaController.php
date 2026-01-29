<?php

namespace App\Http\Controllers\Api\WaliKelas;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * CetakProfilSiswaController for Wali Kelas
 *
 * Handles "Cetak Profil Siswa" - list siswa by kelas, cetak biodata PDF (format Biodata Rapor).
 */
class CetakProfilSiswaController extends Controller
{
    /**
     * List siswa for cetak profil (filter: kelas_id).
     */
    public function index(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        $user = Auth::user();
        $kelasIds = $user->kelasAsWali()->pluck('id')->toArray();

        if (!in_array((int) $request->kelas_id, $kelasIds)) {
            return response()->json(['message' => 'Anda bukan wali kelas untuk kelas ini'], 403);
        }

        $siswaList = Siswa::where('kelas_id', $request->kelas_id)
            ->where('status', 'aktif')
            ->orderBy('nama_lengkap')
            ->get();

        $result = [];
        $no = 1;
        foreach ($siswaList as $siswa) {
            $result[] = [
                'no' => $no++,
                'id' => $siswa->id,
                'nama_lengkap' => $siswa->nama_lengkap,
                'nisn' => $siswa->nisn ?? '-',
                'nis' => $siswa->nis ?? '-',
            ];
        }

        return response()->json(['data' => $result]);
    }

    /**
     * Download profil siswa (biodata) as PDF (format Biodata Rapor).
     */
    public function download(Request $request, Siswa $siswa)
    {
        $request->validate([
            'titimangsa_rapor' => 'required|date',
        ]);

        $user = Auth::user();
        $kelasIds = $user->kelasAsWali()->pluck('id')->toArray();

        if (!in_array((int) $siswa->kelas_id, $kelasIds)) {
            return response()->json(['message' => 'Anda bukan wali kelas untuk siswa ini'], 403);
        }

        $siswa->load(['kelas.jurusan']);

        // Get kepala sekolah (first user with role kepala_sekolah)
        $kepalaSekolah = \App\Models\User::where('role', 'kepala_sekolah')
            ->with('guru')
            ->first();
        
        $kepalaSekolahGuru = $kepalaSekolah?->guru;

        // Format titimangsa rapor: "Cianjur, 05 Mei 2025" (bulan Indonesia)
        $titimangsa = \Carbon\Carbon::parse($request->titimangsa_rapor);
        $bulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $titimangsaFormatted = 'Cianjur, ' . $titimangsa->format('d') . ' ' . $bulanIndo[(int)$titimangsa->format('n')] . ' ' . $titimangsa->format('Y');

        $data = [
            'siswa' => $siswa,
            'titimangsa_rapor' => $titimangsaFormatted,
            'kepala_sekolah' => $kepalaSekolahGuru,
        ];

        $pdf = Pdf::loadView('rapor.biodata', $data);
        $pdf->setPaper('a4', 'portrait');

        $filename = "profil-siswa-{$siswa->nis}-{$siswa->nama_lengkap}.pdf";
        $filename = preg_replace('/[^a-zA-Z0-9\-_.]/', '-', $filename);

        return $pdf->download($filename);
    }
}
