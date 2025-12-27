<?php

namespace App\Http\Controllers\Api\Guru;

use App\Http\Controllers\Controller;
use App\Models\Pkl;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * PklController for Guru
 * 
 * Handles PKL (Praktik Kerja Lapangan) management for teachers as pembimbing
 */
class PklController extends Controller
{
    /**
     * Get PKL records where the current guru is supervising.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function myStudents(Request $request)
    {
        $user = Auth::user();
        $guru = $user->guru;

        if (!$guru) {
            return response()->json([
                'message' => 'Guru tidak ditemukan',
            ], 404);
        }

        // Get PKL where this guru is pembimbing
        $pkl = Pkl::where('pembimbing_sekolah_id', $guru->id)
                  ->with(['tahunAjaran', 'pembimbingSekolah'])
                  ->orderBy('created_at', 'desc')
                  ->get();

        return response()->json([
            'pkl' => $pkl,
            'total' => $pkl->count(),
        ]);
    }

    /**
     * Update PKL record (note: nilai fields were removed in migration).
     *
     * @param  Request  $request
     * @param  Pkl  $pkl
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateNilaiSekolah(Request $request, Pkl $pkl)
    {
        $user = Auth::user();
        $guru = $user->guru;

        // Verify that this PKL is supervised by the current guru
        if ($pkl->pembimbing_sekolah_id !== $guru->id) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses untuk mengubah PKL ini',
            ], 403);
        }

        $request->validate([
            'keterangan' => 'nullable|string|max:500',
        ]);

        $pkl->update($request->only([
            'keterangan',
        ]));

        return response()->json([
            'message' => 'PKL berhasil diperbarui',
            'data' => $pkl->load(['tahunAjaran', 'pembimbingSekolah']),
        ]);
    }
}

