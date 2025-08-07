<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanKinerja extends Model
{
    protected $fillable = [
        'indikator_kinerja_id',
        'tahun',
        'periode',
        'nilai_realisasi',
        'input',
        'persentase_capaian',
        'kendala',
        'tindak_lanjut',
        'file_pendukung',
        'status_verifikasi',
        'catatan_verifikasi'
    ];

    /**
     * Relasi ke Indikator Kinerja
     */
    public function indikatorKinerja(): BelongsTo
    {
        return $this->belongsTo(IndikatorKinerja::class);
    }

    /**
     * Get readable periode name
     */
    public function getPeriodeNameAttribute()
    {
        $periodeNames = [
            'januari' => 'Januari',
            'februari' => 'Februari',
            'maret' => 'Maret',
            'april' => 'April',
            'mei' => 'Mei',
            'juni' => 'Juni',
            'juli' => 'Juli',
            'agustus' => 'Agustus',
            'september' => 'September',
            'oktober' => 'Oktober',
            'november' => 'November',
            'desember' => 'Desember',
            'triwulan1' => 'Triwulan I',
            'triwulan2' => 'Triwulan II',
            'triwulan3' => 'Triwulan III',
            'triwulan4' => 'Triwulan IV',
            'tahunan' => 'Tahunan'
        ];

        return $periodeNames[$this->periode] ?? $this->periode;
    }

    /**
     * Get quarter from month
     */
    public static function getQuarterFromMonth($month)
    {
        $quarters = [
            'januari' => 'triwulan1', 'februari' => 'triwulan1', 'maret' => 'triwulan1',
            'april' => 'triwulan2', 'mei' => 'triwulan2', 'juni' => 'triwulan2',
            'juli' => 'triwulan3', 'agustus' => 'triwulan3', 'september' => 'triwulan3',
            'oktober' => 'triwulan4', 'november' => 'triwulan4', 'desember' => 'triwulan4'
        ];

        return $quarters[$month] ?? null;
    }

    /**
     * Get months in quarter
     */
    public static function getMonthsInQuarter($quarter)
    {
        $months = [
            'triwulan1' => ['januari', 'februari', 'maret'],
            'triwulan2' => ['april', 'mei', 'juni'],
            'triwulan3' => ['juli', 'agustus', 'september'],
            'triwulan4' => ['oktober', 'november', 'desember']
        ];

        return $months[$quarter] ?? [];
    }

    /**
     * Check if periode is monthly
     */
    public function isMonthly()
    {
        return in_array($this->periode, [
            'januari', 'februari', 'maret', 'april', 'mei', 'juni',
            'juli', 'agustus', 'september', 'oktober', 'november', 'desember'
        ]);
    }

    /**
     * Check if periode is quarterly
     */
    public function isQuarterly()
    {
        return in_array($this->periode, ['triwulan1', 'triwulan2', 'triwulan3', 'triwulan4']);
    }

    /**
     * Scope for monthly reports
     */
    public function scopeMonthly($query)
    {
        return $query->whereIn('periode', [
            'januari', 'februari', 'maret', 'april', 'mei', 'juni',
            'juli', 'agustus', 'september', 'oktober', 'november', 'desember'
        ]);
    }

    /**
     * Scope for quarterly reports
     */
    public function scopeQuarterly($query)
    {
        return $query->whereIn('periode', ['triwulan1', 'triwulan2', 'triwulan3', 'triwulan4']);
    }
}
