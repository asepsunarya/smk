<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class P5KelompokSiswa extends Model
{
    use HasFactory;

    protected $table = 'p5_kelompok_siswa';

    protected $fillable = ['p5_kelompok_id', 'siswa_id'];

    public function kelompok()
    {
        return $this->belongsTo(P5Kelompok::class, 'p5_kelompok_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
