<?php

namespace App\Http\Controllers\Api\Guru;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use Illuminate\Support\Facades\Auth;

/**
 * KelasController for Guru
 * 
 * Handles Kelas data retrieval for teachers
 */
class KelasController extends Controller
{
    /**
     * Get all kelas where the authenticated guru teaches.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $guru = Auth::user()->guru;

        if (!$guru) {
            return response()->json([
                'data' => [],
                'message' => 'Guru tidak ditemukan'
            ]);
        }

        // Get kelas from mata_pelajaran pivot
        $kelas = Kelas::whereHas('mataPelajaran', function ($query) use ($guru) {
            $query->where('guru_id', $guru->id);
        })
        ->with(['jurusan'])
        ->orderBy('nama_kelas')
        ->distinct()
        ->get();

        return response()->json([
            'data' => $kelas,
            'message' => 'Kelas berhasil diambil'
        ]);
    }
}
