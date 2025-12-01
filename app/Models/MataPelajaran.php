<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * MataPelajaran Model
 *
 * Represents subjects taught in the school
 */
class MataPelajaran extends Model
{
    use HasFactory;

    protected $table = 'mata_pelajaran';

    protected $fillable = [
        'kode_mapel',
        'nama_mapel',
        'kkm',
        'guru_id',
        'kelas_id',
        'is_active',
    ];

    protected $casts = [
        'kkm' => 'integer',
        'is_active' => 'boolean',
    ];


    /**
     * Get the capaian pembelajaran.
     */
    public function capaianPembelajaran()
    {
        return $this->hasMany(CapaianPembelajaran::class);
    }

    /**
     * Get the nilai for this mata pelajaran.
     */
    public function nilai()
    {
        return $this->hasMany(Nilai::class);
    }

    /**
     * Get the guru that teach this mata pelajaran.
     */
    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    /**
     * Get the kelas where this mata pelajaran is taught.
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    /**
     * Scope a query to only include active mata pelajaran.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get nilai statistics for a specific tahun ajaran.
     *
     * @param  int  $tahunAjaranId
     * @return array
     */
    public function getNilaiStatistics($tahunAjaranId)
    {
        $nilai = $this->nilai()->where('tahun_ajaran_id', $tahunAjaranId);
        
        return [
            'average' => $nilai->avg('nilai_rapor'),
            'highest' => $nilai->max('nilai_rapor'),
            'lowest' => $nilai->min('nilai_rapor'),
            'passed' => $nilai->where('nilai_rapor', '>=', $this->kkm)->count(),
            'failed' => $nilai->where('nilai_rapor', '<', $this->kkm)->count(),
        ];
    }
}
