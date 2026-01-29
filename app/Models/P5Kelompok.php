<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class P5Kelompok extends Model
{
    use HasFactory;

    protected $table = 'p5_kelompok';

    protected $fillable = ['p5_id', 'guru_id'];

    public function p5()
    {
        return $this->belongsTo(P5::class);
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function siswa()
    {
        return $this->belongsToMany(Siswa::class, 'p5_kelompok_siswa', 'p5_kelompok_id', 'siswa_id')
            ->withTimestamps();
    }
}
