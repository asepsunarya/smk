<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * CapaianPembelajaran Model
 * 
 * Represents learning outcomes for Kurikulum Merdeka
 */
class CapaianPembelajaran extends Model
{
    use HasFactory;

    protected $table = 'capaian_pembelajaran';

    protected $fillable = [
        'mata_pelajaran_id',
        'kode_cp',
        'target',
        'deskripsi',
        'fase',
        'semester',
        'tingkat',
        'elemen',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the mata pelajaran.
     */
    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    /**
     * Get the tujuan pembelajaran.
     */
    public function tujuanPembelajaran()
    {
        return $this->hasMany(TujuanPembelajaran::class);
    }

    /**
     * Scope a query to filter by fase.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $fase
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFase($query, $fase)
    {
        return $query->where('fase', $fase);
    }

    /**
     * Scope a query to filter by elemen.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $elemen
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeElemen($query, $elemen)
    {
        return $query->where('elemen', $elemen);
    }

    /**
     * Get the count of tujuan pembelajaran.
     *
     * @return int
     */
    public function getTujuanPembelajaranCountAttribute()
    {
        return $this->tujuanPembelajaran()->count();
    }

    /**
     * Get formatted elemen name.
     *
     * @return string
     */
    public function getElemenNameAttribute()
    {
        $elemenNames = [
            'pemahaman' => 'Pemahaman',
            'keterampilan' => 'Keterampilan',
            'sikap' => 'Sikap',
        ];

        return $elemenNames[$this->elemen] ?? $this->elemen;
    }
}