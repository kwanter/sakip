<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kegiatan extends Model
{
    protected $fillable = [
        'program_id',
        'kode_kegiatan',
        'nama_kegiatan',
        'deskripsi',
        'anggaran',
        'tanggal_mulai',
        'tanggal_selesai',
        'penanggung_jawab',
        'status'
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    /**
     * Relasi ke Program
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Relasi ke Indikator Kinerja
     */
    public function indikatorKinerjas(): HasMany
    {
        return $this->hasMany(IndikatorKinerja::class);
    }
}
