<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * User Model
 * 
 * Handles authentication and user roles
 */
class User extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'nip',
        'nis',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the guru profile associated with the user.
     */
    public function guru()
    {
        return $this->hasOne(Guru::class);
    }

    /**
     * Get the siswa profile associated with the user.
     */
    public function siswa()
    {
        return $this->hasOne(Siswa::class);
    }

    /**
     * Get the classes where user is wali kelas (through guru).
     */
    public function kelasAsWali()
    {
        if (!$this->guru) {
            return collect();
        }
        
        return $this->guru->waliKelasAktif()->with('kelas')->get()->pluck('kelas');
    }

    /**
     * Get the catatan akademik written by this wali kelas.
     */
    public function catatanAkademik()
    {
        return $this->hasMany(CatatanAkademik::class, 'wali_kelas_id');
    }

    /**
     * Get the rapor approved by this user (kepala sekolah).
     */
    public function approvedRapor()
    {
        return $this->hasMany(Rapor::class, 'approved_by');
    }

    /**
     * Check if user has a specific role.
     *
     * @param  string  $role
     * @return bool
     */
    public function hasRole($role)
    {
        return $this->role === $role;
    }

    /**
     * Check if user is admin/operator.
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is guru.
     *
     * @return bool
     */
    public function isGuru()
    {
        return $this->role === 'guru';
    }

    /**
     * Check if user is wali kelas.
     *
     * @return bool
     */
    public function isWaliKelas()
    {
        return $this->role === 'wali_kelas';
    }

    /**
     * Check if user is kepala sekolah.
     *
     * @return bool
     */
    public function isKepalaSekolah()
    {
        return $this->role === 'kepala_sekolah';
    }

    /**
     * Check if user is siswa.
     *
     * @return bool
     */
    public function isSiswa()
    {
        return $this->role === 'siswa';
    }
}