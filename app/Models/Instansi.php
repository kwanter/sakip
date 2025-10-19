<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Instansi extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'kode_instansi',
        'nama_instansi',
        'alamat',
        'telepon',
        'email',
        'website',
        'kepala_instansi',
        'nip_kepala',
        'status'
    ];

    public $incrementing = false;
    protected $keyType = 'string';

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
