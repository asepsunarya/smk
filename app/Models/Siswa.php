<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Siswa Model
 * 
 * Represents student data
 */
class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswa';

    protected $fillable = [
        'user_id',
        'nis',
        'nisn',
        'nama_lengkap',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'agama',
        'anak_ke',
        'sekolah_asal',
        'alamat',
        'no_hp',
        'nama_ayah',
        'nama_ibu',
        'pekerjaan_ayah',
        'pekerjaan_ibu',
        'no_hp_ortu',
        'kelas_id',
        'tanggal_masuk',
        'status',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_masuk' => 'date',
    ];

    /**
     * Get the user account.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the kelas.
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    /**
     * Get the nilai for the siswa.
     */
    public function nilai()
    {
        return $this->hasMany(Nilai::class);
    }

    /**
     * Get the nilai ekstrakurikuler.
     */
    public function nilaiEkstrakurikuler()
    {
        return $this->hasMany(NilaiEkstrakurikuler::class);
    }

    /**
     * Get the PKL records.
     */
    public function pkl()
    {
        return $this->hasMany(Pkl::class);
    }

    /**
     * Get the nilai P5.
     */
    public function nilaiP5()
    {
        return $this->hasMany(NilaiP5::class);
    }

    /**
     * Get the kehadiran records.
     */
    public function kehadiran()
    {
        return $this->hasMany(Kehadiran::class);
    }

    /**
     * Get the UKK records.
     */
    public function ukk()
    {
        return $this->hasMany(Ukk::class);
    }

    /**
     * Get the catatan akademik.
     */
    public function catatanAkademik()
    {
        return $this->hasMany(CatatanAkademik::class);
    }

    /**
     * Get the rapor.
     */
    public function rapor()
    {
        return $this->hasMany(Rapor::class);
    }

    /**
     * Scope a query to only include active siswa.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Get the age of the siswa.
     *
     * @return int
     */
    public function getAgeAttribute()
    {
        return $this->tanggal_lahir->age;
    }

    /**
     * Get the full address with parent contact.
     *
     * @return array
     */
    public function getContactInfoAttribute()
    {
        return [
            'alamat' => $this->alamat,
            'no_hp_siswa' => $this->no_hp,
            'no_hp_ortu' => $this->no_hp_ortu,
            'nama_ortu' => "{$this->nama_ayah} / {$this->nama_ibu}",
        ];
    }

    /**
     * Get nilai for specific tahun ajaran.
     *
     * @param  int  $tahunAjaranId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function nilaiByTahunAjaran($tahunAjaranId)
    {
        return $this->nilai()->where('tahun_ajaran_id', $tahunAjaranId)->get();
    }

    /**
     * Get rapor for specific tahun ajaran.
     *
     * @param  int  $tahunAjaranId
     * @return \App\Models\Rapor|null
     */
    public function raporByTahunAjaran($tahunAjaranId)
    {
        return $this->rapor()->where('tahun_ajaran_id', $tahunAjaranId)->first();
    }
}