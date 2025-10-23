<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Program extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $fillable = [
        "instansi_id",
        "sasaran_strategis_id",
        "kode_program",
        "nama_program",
        "deskripsi",
        "anggaran",
        "tahun",
        "penanggung_jawab",
        "status",
    ];

    public $incrementing = false;
    protected $keyType = "string";

    /**
     * Relasi ke Instansi
     */
    public function instansi(): BelongsTo
    {
        return $this->belongsTo(Instansi::class);
    }

    /**
     * Relasi ke Sasaran Strategis
     */
    public function sasaranStrategis(): BelongsTo
    {
        return $this->belongsTo(SasaranStrategis::class);
    }

    /**
     * Relasi ke Kegiatan
     */
    public function kegiatans(): HasMany
    {
        return $this->hasMany(Kegiatan::class);
    }

    /**
     * Relasi ke Performance Indicator
     */
    public function performanceIndicators(): HasMany
    {
        return $this->hasMany(PerformanceIndicator::class);
    }
}
