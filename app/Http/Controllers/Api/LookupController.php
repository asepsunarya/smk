<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\MataPelajaran;
use App\Models\Guru;
use App\Models\TahunAjaran;
use App\Models\DimensiP5;
use Illuminate\Http\Request;

/**
 * LookupController
 * 
 * Provides lookup data for forms and dropdowns
 */
class LookupController extends Controller
{
    /**
     * Get all kelas for dropdown.
     */
    public function kelas()
    {
        $kelas = Kelas::with('jurusan')->get()->map(function ($kelas) {
            return [
                'id' => $kelas->id,
                'nama_kelas' => $kelas->nama_kelas,
                'full_name' => $kelas->full_name,
            ];
        });

        return response()->json($kelas);
    }

    /**
     * Get all jurusan for dropdown.
     */
    public function jurusan()
    {
        $jurusan = Jurusan::select('id', 'kode_jurusan', 'nama_jurusan')->get();
        return response()->json($jurusan);
    }

    /**
     * Get all mata pelajaran for dropdown.
     */
    public function mataPelajaran()
    {
        $mataPelajaran = MataPelajaran::where('is_active', true)
            ->select('id', 'kode_mapel', 'nama_mapel', 'kelompok')
            ->get();
        return response()->json($mataPelajaran);
    }

    /**
     * Get all guru for dropdown.
     */
    public function guru()
    {
        $guru = Guru::with('user')->where('status', 'aktif')->get()->map(function ($guru) {
            return [
                'id' => $guru->id,
                'nuptk' => $guru->nuptk,
                'nama_lengkap' => $guru->nama_lengkap,
                'bidang_studi' => $guru->bidang_studi,
            ];
        });

        return response()->json($guru);
    }

    /**
     * Get all tahun ajaran for dropdown.
     */
    public function tahunAjaran()
    {
        $tahunAjaran = TahunAjaran::select('id', 'tahun', 'semester', 'is_active')->get();
        return response()->json($tahunAjaran);
    }

    /**
     * Get all dimensi P5 for dropdown.
     */
    public function dimensiP5()
    {
        $dimensi = DimensiP5::select('id', 'nama_dimensi', 'deskripsi')->get();
        return response()->json($dimensi);
    }
}