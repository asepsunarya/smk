<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Guru Model
 *
 * Represents teacher data
 */
class Guru extends Model
{
    use HasFactory;

    protected $table = 'guru';

    protected $fillable = [
        'user_id',
        'nuptk',
        'nama_lengkap',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'agama',
        'alamat',
        'no_hp',
        'pendidikan_terakhir',
        'bidang_studi',
        'tanggal_masuk',
        'status',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_masuk' => 'date',
    ];

    /**
     * Get nuptk; tampilkan "-" ketika null atau kosong.
     */
    public function getNuptkAttribute($value)
    {
        $v = $value ?? '';
        return ($v === '' || trim($v) === '') ? '-' : $v;
    }

    /**
     * Get the user account.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    /**
     * Get the nilai that this guru inputs.
     */
    public function nilai()
    {
        return $this->hasMany(Nilai::class);
    }

    /**
     * Get ekstrakurikuler where this guru is pembina.
     */
    public function ekstrakurikuler()
    {
        return $this->hasMany(Ekstrakurikuler::class, 'pembina_id');
    }

    /**
     * Get PKL where this guru is pembimbing.
     */
    public function pklAsPembimbing()
    {
        return $this->hasMany(Pkl::class, 'pembimbing_sekolah_id');
    }

    /**
     * Get P5 where this guru is koordinator.
     */
    public function p5AsKoordinator()
    {
        return $this->hasMany(P5::class, 'koordinator_id');
    }

    /**
     * Get UKK where this guru is penguji internal.
     */
    public function ukkAsPenguji()
    {
        return $this->hasMany(Ukk::class, 'penguji_internal_id');
    }

    /**
     * Scope a query to only include active guru.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'aktif');
    }


    /**
     * Get the mata pelajaran that this guru teaches.
     */
    public function mataPelajaran()
    {
        return $this->hasMany(MataPelajaran::class);
    }

    /**
     * Get the wali kelas assignments.
     */
    public function waliKelas()
    {
        return $this->hasMany(WaliKelas::class);
    }

    /**
     * Get active wali kelas assignments.
     */
    public function waliKelasAktif()
    {
        return $this->hasMany(WaliKelas::class)->where('is_active', true);
    }

    /**
     * Check if guru is wali kelas.
     *
     * @return bool
     */
    public function isWaliKelas()
    {
        return $this->waliKelasAktif()->exists();
    }

    /**
     * Get the kelas where this guru is currently wali kelas.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getKelasAsWaliAttribute()
    {
        return $this->waliKelasAktif()->with('kelas')->get()->pluck('kelas');
    }

    /**
     * Get years of service.
     *
     * @return int
     */
    public function getYearsOfServiceAttribute()
    {
        return $this->tanggal_masuk->diffInYears(now());
    }

    /**
     * Get kelas IDs that this guru teaches (wali kelas + kelas dari mata pelajaran yang diajar).
     *
     * @return \Illuminate\Support\Collection<int>
     */
    public function getKelasIdsYangDiajarAttribute()
    {
        try {
            $kelasIds = collect();

            // Kelas dimana guru jadi wali kelas
            $kelasIds = $kelasIds->merge(
                $this->waliKelasAktif()->pluck('kelas_id')
            );

            // Kelas yang punya mata pelajaran yang diajar guru ini
            $mapelQuery = $this->mataPelajaran();
            if (\Illuminate\Support\Facades\Schema::hasColumn('mata_pelajaran', 'is_active')) {
                $mapelQuery = $mapelQuery->where('is_active', true);
            }
            $kelasIds = $kelasIds->merge(
                $mapelQuery->get()->flatMap(fn ($m) => $m->kelas()->pluck('id'))
            );

            $result = $kelasIds->unique()->filter()->values();
            return $result instanceof \Illuminate\Support\Collection ? $result : collect();
        } catch (\Throwable $e) {
            report($e);
            return collect();
        }
    }
}
