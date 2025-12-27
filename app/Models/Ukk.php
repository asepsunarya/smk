<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * UKK (Uji Kompetensi Keahlian) Model
 * 
 * Represents vocational competency tests
 */
class Ukk extends Model
{
    use HasFactory;

    protected $table = 'ukk';

    protected $fillable = [
        'siswa_id',
        'jurusan_id',
        'kelas_id',
        'nama_du_di',
        'tanggal_ujian',
        'nilai_teori',
        'nilai_praktek',
        'nilai_akhir',
        'predikat',
        'penguji_internal_id',
        'penguji_eksternal',
        'tahun_ajaran_id',
    ];

    protected $casts = [
        'tanggal_ujian' => 'date',
        'nilai_teori' => 'integer',
        'nilai_praktek' => 'integer',
        'nilai_akhir' => 'decimal:2',
    ];

    /**
     * Get the siswa.
     */
    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    /**
     * Get the jurusan.
     */
    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class);
    }

    /**
     * Get the kelas.
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    /**
     * Get the penguji internal.
     */
    public function pengujiInternal()
    {
        return $this->belongsTo(Guru::class, 'penguji_internal_id');
    }

    /**
     * Get the tahun ajaran.
     */
    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    /**
     * Calculate nilai akhir (30% teori + 70% praktek).
     *
     * @return float
     */
    public function calculateNilaiAkhir()
    {
        if (!$this->nilai_teori || !$this->nilai_praktek) {
            return 0;
        }

        return round(($this->nilai_teori * 0.3) + ($this->nilai_praktek * 0.7), 2);
    }

    /**
     * Determine predikat based on nilai akhir.
     *
     * @return string
     */
    public function determinePredikat()
    {
        $nilai = $this->nilai_akhir ?? $this->calculateNilaiAkhir();

        return $nilai >= 75 ? 'Kompeten' : 'Belum Kompeten';
    }

    /**
     * Check if student is competent.
     *
     * @return bool
     */
    public function isKompeten()
    {
        return $this->predikat === 'Kompeten';
    }

    /**
     * Get test status.
     *
     * @return string
     */
    public function getStatusAttribute()
    {
        if (!$this->nilai_teori || !$this->nilai_praktek) {
            return 'Belum Dinilai';
        }

        return $this->isKompeten() ? 'Lulus' : 'Tidak Lulus';
    }

    /**
     * Boot method to auto-calculate values.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($ukk) {
            if (is_null($ukk->nilai_akhir) && $ukk->nilai_teori && $ukk->nilai_praktek) {
                $ukk->nilai_akhir = $ukk->calculateNilaiAkhir();
            }
            
            if (is_null($ukk->predikat) && $ukk->nilai_akhir) {
                $ukk->predikat = $ukk->determinePredikat();
            }
        });
    }
}