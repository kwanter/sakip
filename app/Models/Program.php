<?php

namespace App\Models;

use App\Models\Scopes\ForYearTrait;
use App\Models\Scopes\InstansiScope;
use App\Models\Scopes\RecentScope;
use App\Models\Scopes\SearchScope;
use App\Models\Scopes\WithStatusScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Program extends Model
{
    use ForYearTrait, RecentScope, SearchScope, WithStatusScope;
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'instansi_id',
        'sasaran_strategis_id',
        'kode_program',
        'nama_program',
        'deskripsi',
        'anggaran',
        'tahun',
        'penanggung_jawab',
        'status',
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

    protected static function booted(): void
    {
        static::addGlobalScope(new InstansiScope);
    }
}
