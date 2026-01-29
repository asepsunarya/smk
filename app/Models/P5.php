<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * P5 (Projek Penguatan Profil Pelajar Pancasila) Model
 * 
 * Represents P5 projects in Kurikulum Merdeka
 */
class P5 extends Model
{
    use HasFactory;

    protected $table = 'p5';

    protected $fillable = [
        'tema',
        'judul',
        'deskripsi',
        'dimensi',
        'elemen',
        'sub_elemen',
        'elemen_sub',
        'koordinator_id',
        'tahun_ajaran_id',
    ];

    protected $casts = [
        'elemen_sub' => 'array',
    ];

    /**
     * Get the koordinator.
     */
    public function koordinator()
    {
        return $this->belongsTo(Guru::class, 'koordinator_id');
    }

    /**
     * Get the tahun ajaran.
     */
    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    /**
     * Get the nilai P5 for this project.
     */
    public function nilaiP5()
    {
        return $this->hasMany(NilaiP5::class);
    }

    /**
     * Get siswa who participated in this P5 (via nilai_p5).
     */
    public function siswa()
    {
        return $this->belongsToMany(Siswa::class, 'nilai_p5')
                    ->distinct();
    }

    /**
     * Get peserta (siswa) assigned to this P5 (via p5_siswa).
     */
    public function peserta()
    {
        return $this->belongsToMany(Siswa::class, 'p5_siswa')
                    ->withPivot('catatan_proses')
                    ->withTimestamps();
    }

    /**
     * Get kelompok (groups with fasilitator and siswa).
     */
    public function kelompok()
    {
        return $this->hasMany(P5Kelompok::class);
    }

    /**
     * Get dimensi assessed in this P5.
     */
    public function dimensi()
    {
        return $this->belongsToMany(DimensiP5::class, 'nilai_p5', 'p5_id', 'dimensi_id')
                    ->distinct();
    }

    /**
     * Get participant count.
     *
     * @return int
     */
    public function getParticipantCountAttribute()
    {
        return $this->siswa()->count();
    }

    /**
     * Get nilai statistics for a specific dimensi.
     *
     * @param  int  $dimensiId
     * @return array
     */
    public function getNilaiStatisticsByDimensi($dimensiId)
    {
        $nilaiCounts = $this->nilaiP5()
                            ->where('dimensi_id', $dimensiId)
                            ->selectRaw('nilai, COUNT(*) as count')
                            ->groupBy('nilai')
                            ->pluck('count', 'nilai')
                            ->toArray();

        return [
            'MB' => $nilaiCounts['MB'] ?? 0,
            'SB' => $nilaiCounts['SB'] ?? 0,
            'BSH' => $nilaiCounts['BSH'] ?? 0,
            'SAB' => $nilaiCounts['SAB'] ?? 0,
        ];
    }
}