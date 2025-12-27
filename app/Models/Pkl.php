<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * PKL (Praktik Kerja Lapangan) Model
 * 
 * Represents internship/work practice data
 */
class Pkl extends Model
{
    use HasFactory;

    protected $table = 'pkl';

    protected $fillable = [
        'nama_perusahaan',
        'alamat_perusahaan',
        'pembimbing_perusahaan',
        'pembimbing_sekolah_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'tahun_ajaran_id',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];


    /**
     * Get the tahun ajaran.
     */
    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    /**
     * Get the pembimbing sekolah (guru).
     */
    public function pembimbingSekolah()
    {
        return $this->belongsTo(Guru::class, 'pembimbing_sekolah_id');
    }

    /**
     * Get the duration in days.
     *
     * @return int
     */
    public function getDurationInDaysAttribute()
    {
        return $this->tanggal_mulai->diffInDays($this->tanggal_selesai);
    }

    /**
     * Get the duration in months.
     *
     * @return float
     */
    public function getDurationInMonthsAttribute()
    {
        return round($this->duration_in_days / 30, 1);
    }

    /**
     * Get status of PKL.
     *
     * @return string
     */
    public function getStatusAttribute()
    {
        $today = now();

        if ($today->lt($this->tanggal_mulai)) {
            return 'Belum Mulai';
        } elseif ($today->between($this->tanggal_mulai, $this->tanggal_selesai)) {
            return 'Sedang Berlangsung';
        } else {
            return 'Selesai';
        }
    }
}