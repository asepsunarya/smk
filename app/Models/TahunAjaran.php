<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * TahunAjaran Model
 * 
 * Represents academic year and semester
 */
class TahunAjaran extends Model
{
    use HasFactory;

    protected $table = 'tahun_ajaran';

    protected $fillable = [
        'tahun',
        'semester',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];


    /**
     * Get the nilai for this tahun ajaran.
     */
    public function nilai()
    {
        return $this->hasMany(Nilai::class);
    }

    /**
     * Get the nilai ekstrakurikuler for this tahun ajaran.
     */
    public function nilaiEkstrakurikuler()
    {
        return $this->hasMany(NilaiEkstrakurikuler::class);
    }

    /**
     * Get the PKL for this tahun ajaran.
     */
    public function pkl()
    {
        return $this->hasMany(Pkl::class);
    }

    /**
     * Get the P5 projects for this tahun ajaran.
     */
    public function p5()
    {
        return $this->hasMany(P5::class);
    }

    /**
     * Get the kehadiran records for this tahun ajaran.
     */
    public function kehadiran()
    {
        return $this->hasMany(Kehadiran::class);
    }

    /**
     * Get the UKK records for this tahun ajaran.
     */
    public function ukk()
    {
        return $this->hasMany(Ukk::class);
    }

    /**
     * Get the catatan akademik for this tahun ajaran.
     */
    public function catatanAkademik()
    {
        return $this->hasMany(CatatanAkademik::class);
    }

    /**
     * Get the rapor for this tahun ajaran.
     */
    public function rapor()
    {
        return $this->hasMany(Rapor::class);
    }

    /**
     * Scope a query to only include active tahun ajaran.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the full description of tahun ajaran.
     *
     * @return string
     */
    public function getFullDescriptionAttribute()
    {
        $semesterText = $this->semester == '1' ? 'Ganjil' : 'Genap';
        return "Tahun Ajaran {$this->tahun} - Semester {$semesterText}";
    }
}