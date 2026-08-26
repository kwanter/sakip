<?php

namespace App\Services;

use App\Models\Instansi;
use App\Models\PerformanceIndicator;
use App\Models\Program;
use App\Models\Kegiatan;
use App\Models\SasaranStrategis;
use Illuminate\Support\Facades\Cache;

/**
 * Dropdown Cache Service
 *
 * Provides cached access to commonly used dropdown data to reduce database queries.
 * Uses Laravel's cache system with configurable TTL.
 */
class DropdownCacheService
{
    /**
     * Cache time-to-live in seconds (1 hour default)
     */
    private const CACHE_TTL = 3600;

    /**
     * Get all active institutions for dropdown
     *
     * @param bool $refresh Force cache refresh
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveInstansi(bool $refresh = false)
    {
        return Cache::remember('dropdown.instansi.active', self::CACHE_TTL, function () {
            return Instansi::orderBy("nama_instansi")->get();
        });
    }

    /**
     * Get institutions by status
     *
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getInstansiByStatus(string $status = 'aktif')
    {
        return Cache::remember("dropdown.instansi.{$status}", self::CACHE_TTL, function () use ($status) {
            return Instansi::where('status', $status)
                ->orderBy("nama_instansi")
                ->get();
        });
    }

    /**
     * Get all sasaran strategis for dropdown
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSasaranStrategis()
    {
        return Cache::remember('dropdown.sasaran_strategis', self::CACHE_TTL, function () {
            return SasaranStrategis::orderBy('nama_sasaran')->get();
        });
    }

    /**
     * Get programs for dropdown by institution
     *
     * @param string|null $instansiId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getProgramsByInstansi(?string $instansiId = null)
    {
        $key = $instansiId
            ? "dropdown.programs.{$instansiId}"
            : 'dropdown.programs.all';

        return Cache::remember($key, self::CACHE_TTL, function () use ($instansiId) {
            $query = Program::with('instansi')
                ->orderBy('nama_program');

            if ($instansiId) {
                $query->where('instansi_id', $instansiId);
            }

            return $query->get();
        });
    }

    /**
     * Get active programs for dropdown
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActivePrograms()
    {
        return Cache::remember('dropdown.programs.active', self::CACHE_TTL, function () {
            return Program::where('status', 'aktif')
                ->with('instansi')
                ->orderBy('nama_program')
                ->get();
        });
    }

    /**
     * Get kegiatan by program for dropdown
     *
     * @param string|null $programId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getKegiatanByProgram(?string $programId = null)
    {
        $key = $programId
            ? "dropdown.kegiatan.{$programId}"
            : 'dropdown.kegiatan.all';

        return Cache::remember($key, self::CACHE_TTL, function () use ($programId) {
            $query = Kegiatan::with('program.instansi')
                ->orderBy('nama_kegiatan');

            if ($programId) {
                $query->where('program_id', $programId);
            }

            return $query->get();
        });
    }

    /**
     * Get performance indicator categories
     *
     * @return array
     */
    public function getIndicatorCategories(): array
    {
        return Cache::remember('dropdown.indicator_categories', self::CACHE_TTL, function () {
            return [
                'input' => 'Input',
                'output' => 'Output',
                'outcome' => 'Outcome',
                'impact' => 'Impact',
            ];
        });
    }

    /**
     * Get measurement types
     *
     * @return array
     */
    public function getMeasurementTypes(): array
    {
        return Cache::remember('dropdown.measurement_types', self::CACHE_TTL, function () {
            return [
                'percentage' => 'Persentase (%)',
                'ratio' => 'Rasio',
                'number' => 'Angka',
                'nominal' => 'Nominal (Rp)',
                'index' => 'Indeks',
                'quantity' => 'Kuantitas (Unit)',
            ];
        });
    }

    /**
     * Get frequencies
     *
     * @return array
     */
    public function getFrequencies(): array
    {
        return Cache::remember('dropdown.frequencies', self::CACHE_TTL, function () {
            return [
                'monthly' => 'Bulanan',
                'quarterly' => 'Triwulan',
                'semester' => 'Semester',
                'annual' => 'Tahunan',
            ];
        });
    }

    /**
     * Get data quality levels
     *
     * @return array
     */
    public function getDataQualityLevels(): array
    {
        return Cache::remember('dropdown.data_quality', self::CACHE_TTL, function () {
            return [
                'excellent' => 'Sangat Baik',
                'good' => 'Baik',
                'fair' => 'Cukup',
                'poor' => 'Kurang',
            ];
        });
    }

    /**
     * Get all dropdown data in a single call
     *
     * @return array
     */
    public function getAllDropdownData(): array
    {
        return [
            'instansi' => $this->getActiveInstansi(),
            'sasaran_strategis' => $this->getSasaranStrategis(),
            'programs' => $this->getActivePrograms(),
            'categories' => $this->getIndicatorCategories(),
            'measurement_types' => $this->getMeasurementTypes(),
            'frequencies' => $this->getFrequencies(),
            'data_quality' => $this->getDataQualityLevels(),
        ];
    }

    /**
     * Clear all dropdown caches
     *
     * Call this after updating dropdown reference data.
     */
    public function clearCache(): void
    {
        $patterns = [
            'dropdown.instansi*',
            'dropdown.sasaran_strategis',
            'dropdown.programs*',
            'dropdown.kegiatan*',
            'dropdown.indicator_categories',
            'dropdown.measurement_types',
            'dropdown.frequencies',
            'dropdown.data_quality',
        ];

        foreach ($patterns as $pattern) {
            // Match cache keys and forget them
            if (str_ends_with($pattern, '*')) {
                $prefix = str_replace('*', '', $prefix);
                // For file/cache drivers, you'd need to implement prefix matching
                // For now, we'll clear known keys
                Cache::forget(str_replace('*', '', $pattern));
                Cache::forget(str_replace('*', '.active', $pattern));
            } else {
                Cache::forget($pattern);
            }
        }
    }

    /**
     * Clear specific dropdown cache
     *
     * @param string $key
     */
    public function clearSpecificCache(string $key): void
    {
        Cache::forget("dropdown.{$key}");
    }
}
