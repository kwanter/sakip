<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IndikatorKinerja extends Model
{
    protected $fillable = [
        'kegiatan_id',
        'nama_indikator',
        'definisi',
        'satuan',
        'target',
        'input',
        'realisasi',
        'jenis',
        'formula_perhitungan',
        'status'
    ];

    /**
     * Relasi ke Kegiatan
     */
    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class);
    }

    /**
     * Relasi ke Laporan Kinerja
     */
    public function laporanKinerjas(): HasMany
    {
        return $this->hasMany(LaporanKinerja::class);
    }

    /**
     * Accessor untuk menghitung persentase capaian
     */
    public function getPersentaseCapaianAttribute()
    {
        if ($this->target > 0) {
            return round(($this->realisasi / $this->target) * 100, 2);
        }
        return 0;
    }
}
