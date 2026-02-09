<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\WithStatusScope;
use App\Models\Scopes\RecentScope;
use App\Models\Scopes\SearchScope;

class Instansi extends Model
{
    use HasUuids, HasFactory, SoftDeletes;
    use WithStatusScope, RecentScope, SearchScope;

    protected $fillable = [
        "kode_instansi",
        "nama_instansi",
        "alamat",
        "telepon",
        "email",
        "website",
        "kepala_instansi",
        "nip_kepala",
        "status",
    ];

    public $incrementing = false;
    protected $keyType = "string";

    /**
     * Relasi ke Sasaran Strategis
     */
    public function sasaranStrategis(): HasMany
    {
        return $this->hasMany(SasaranStrategis::class);
    }

    /**
     * Relasi ke Program
     */
    public function programs(): HasMany
    {
        return $this->hasMany(Program::class);
    }

    /**
     * Relasi ke PerformanceIndicator (indikator kinerja)
     */
    public function performanceIndicators(): HasMany
    {
        return $this->hasMany(PerformanceIndicator::class);
    }
}
