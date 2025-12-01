<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Rapor Model
 * 
 * Represents student report cards
 */
class Rapor extends Model
{
    use HasFactory;

    protected $table = 'rapor';

    protected $fillable = [
        'siswa_id',
        'tahun_ajaran_id',
        'kelas_id',
        'tanggal_rapor',
        'status',
        'approved_by',
        'approved_at',
        'file_rapor',
    ];

    protected $casts = [
        'tanggal_rapor' => 'date',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the siswa.
     */
    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    /**
     * Get the tahun ajaran.
     */
    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    /**
     * Get the kelas.
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    /**
     * Get the user who approved the rapor.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope a query to filter by status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include draft rapor.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope a query to only include approved rapor.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include published rapor.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Check if rapor is draft.
     *
     * @return bool
     */
    public function isDraft()
    {
        return $this->status === 'draft';
    }

    /**
     * Check if rapor is approved.
     *
     * @return bool
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * Check if rapor is published.
     *
     * @return bool
     */
    public function isPublished()
    {
        return $this->status === 'published';
    }

    /**
     * Approve the rapor.
     *
     * @param  int  $userId
     * @return bool
     */
    public function approve($userId)
    {
        return $this->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
    }

    /**
     * Publish the rapor.
     *
     * @return bool
     */
    public function publish()
    {
        if (!$this->isApproved()) {
            return false;
        }

        return $this->update(['status' => 'published']);
    }

    /**
     * Get all nilai for this rapor.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getNilaiAttribute()
    {
        return $this->siswa->nilai()
                    ->where('tahun_ajaran_id', $this->tahun_ajaran_id)
                    ->with('mataPelajaran')
                    ->get();
    }

    /**
     * Get kehadiran for this rapor.
     *
     * @return \App\Models\Kehadiran|null
     */
    public function getKehadiranAttribute()
    {
        return $this->siswa->kehadiran()
                    ->where('tahun_ajaran_id', $this->tahun_ajaran_id)
                    ->first();
    }

    /**
     * Get catatan akademik for this rapor.
     *
     * @return \App\Models\CatatanAkademik|null
     */
    public function getCatatanAkademikAttribute()
    {
        return $this->siswa->catatanAkademik()
                    ->where('tahun_ajaran_id', $this->tahun_ajaran_id)
                    ->first();
    }

    /**
     * Check if rapor is complete (all grades entered).
     *
     * @return bool
     */
    public function isComplete()
    {
        // Check if all required data is present
        $enteredGrades = $this->nilai->count();

        return $enteredGrades > 0 && $this->kehadiran && $this->catatan_akademik;
    }
}