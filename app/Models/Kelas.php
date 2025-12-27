<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Kelas Model
 * 
 * Represents classes in the school
 */
class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';

    protected $fillable = [
        'nama_kelas',
        'tingkat',
        'jurusan_id',
        'kapasitas',
    ];

    protected $casts = [
        'kapasitas' => 'integer',
    ];

    /**
     * Get the jurusan that owns the kelas.
     */
    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class);
    }

    /**
     * Get the wali kelas (homeroom teacher) assignments.
     */
    public function waliKelas()
    {
        return $this->hasMany(WaliKelas::class);
    }

    /**
     * Get the current active wali kelas.
     */
    public function waliKelasAktif()
    {
        return $this->hasOne(WaliKelas::class)->where('is_active', true);
    }

    /**
     * Get the mata pelajaran taught in this kelas.
     */
    public function mataPelajaran()
    {
        return $this->belongsToMany(MataPelajaran::class, 'kelas_mata_pelajaran', 'kelas_id', 'mata_pelajaran_id')
                    ->withTimestamps();
    }

    /**
     * Get the siswa in this kelas.
     */
    public function siswa()
    {
        return $this->hasMany(Siswa::class);
    }


    /**
     * Get the rapor for this kelas.
     */
    public function rapor()
    {
        return $this->hasMany(Rapor::class);
    }

    /**
     * Get active siswa in this kelas.
     */
    public function activeSiswa()
    {
        return $this->siswa()->where('status', 'aktif');
    }

    /**
     * Get the count of active siswa.
     *
     * @return int
     */
    public function getActiveSiswaCountAttribute()
    {
        return $this->activeSiswa()->count();
    }

    /**
     * Check if kelas is full.
     *
     * @return bool
     */
    public function getIsFullAttribute()
    {
        return $this->activeSiswa()->count() >= $this->kapasitas;
    }

    /**
     * Get the available capacity.
     *
     * @return int
     */
    public function getAvailableCapacityAttribute()
    {
        return $this->kapasitas - $this->activeSiswa()->count();
    }

    /**
     * Get the full name of the kelas.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return "{$this->nama_kelas} - {$this->jurusan->nama_jurusan}";
    }
}