<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * NilaiP5 Model
 * 
 * Represents P5 assessment scores
 */
class NilaiP5 extends Model
{
    use HasFactory;

    protected $table = 'nilai_p5';

    protected $fillable = [
        'siswa_id',
        'p5_id',
        'dimensi_id',
        'sub_elemen',
        'nilai',
        'catatan',
    ];

    /**
     * Get the siswa.
     */
    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    /**
     * Get the P5 project.
     */
    public function p5()
    {
        return $this->belongsTo(P5::class);
    }

    /**
     * Get the dimensi.
     */
    public function dimensi()
    {
        return $this->belongsTo(DimensiP5::class, 'dimensi_id');
    }

    /**
     * Get nilai description.
     *
     * @return string
     */
    public function getNilaiDescriptionAttribute()
    {
        $descriptions = [
            'MB' => 'Mulai Berkembang',
            'SB' => 'Sedang Berkembang',
            'BSH' => 'Berkembang Sesuai Harapan',
            'SAB' => 'Sangat Berkembang',
        ];

        return $descriptions[$this->nilai] ?? $this->nilai;
    }

    /**
     * Get numeric value for calculations.
     *
     * @return int
     */
    public function getNumericValueAttribute()
    {
        $values = [
            'MB' => 1,
            'SB' => 2,
            'BSH' => 3,
            'SAB' => 4,
        ];

        return $values[$this->nilai] ?? 0;
    }

    /**
     * Scope a query to filter by nilai category.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $nilai
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByNilai($query, $nilai)
    {
        return $query->where('nilai', $nilai);
    }
}