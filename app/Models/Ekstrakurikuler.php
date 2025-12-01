<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Ekstrakurikuler Model
 *
 * Represents extracurricular activities
 */
class Ekstrakurikuler extends Model
{
    use HasFactory;

    protected $table = 'ekstrakurikuler';

    protected $fillable = [
        'nama',
        'deskripsi',
        'pembina_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the pembina (supervisor).
     */
    public function pembina()
    {
        return $this->belongsTo(Guru::class, 'pembina_id');
    }

    /**
     * Get the nilai ekstrakurikuler.
     */
    public function nilaiEkstrakurikuler()
    {
        return $this->hasMany(NilaiEkstrakurikuler::class);
    }

    /**
     * Get siswa enrolled in this ekstrakurikuler.
     */
    public function siswa()
    {
        return $this->belongsToMany(Siswa::class, 'nilai_ekstrakurikuler')
                    ->withPivot(['predikat', 'keterangan', 'tahun_ajaran_id'])
                    ->withTimestamps();
    }

    /**
     * Scope a query to only include active ekstrakurikuler.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }


    /**
     * Get count of active members for specific tahun ajaran.
     *
     * @param  int  $tahunAjaranId
     * @return int
     */
    public function getMemberCount($tahunAjaranId)
    {
        return $this->nilaiEkstrakurikuler()
                    ->where('tahun_ajaran_id', $tahunAjaranId)
                    ->count();
    }
}
