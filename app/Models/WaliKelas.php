<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * WaliKelas Model
 * 
 * Represents homeroom teacher assignments to classes
 */
class WaliKelas extends Model
{
    use HasFactory;

    protected $table = 'wali_kelas';

    protected $fillable = [
        'guru_id',
        'kelas_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_active',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the guru (teacher).
     */
    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    /**
     * Get the kelas (class).
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    /**
     * Scope a query to only include active wali kelas.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by kelas.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $kelasId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForKelas($query, $kelasId)
    {
        return $query->where('kelas_id', $kelasId);
    }

    /**
     * Scope a query to filter by guru.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $guruId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForGuru($query, $guruId)
    {
        return $query->where('guru_id', $guruId);
    }

    /**
     * Check if assignment is currently active.
     *
     * @return bool
     */
    public function getIsCurrentlyActiveAttribute()
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        
        if ($this->tanggal_mulai && $this->tanggal_mulai->gt($now)) {
            return false;
        }

        if ($this->tanggal_selesai && $this->tanggal_selesai->lt($now)) {
            return false;
        }

        return true;
    }
}

