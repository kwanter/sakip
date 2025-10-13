<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Program extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'instansi_id',
        'kode_program',
        'nama_program',
        'deskripsi',
        'anggaran',
        'tahun',
        'penanggung_jawab',
        'status'
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Relasi ke Instansi
     */
    public function instansi(): BelongsTo
    {
        return $this->belongsTo(Instansi::class);
    }

    /**
     * Relasi ke Kegiatan
     */
    public function kegiatans(): HasMany
    {
        return $this->hasMany(Kegiatan::class);
    }
}
