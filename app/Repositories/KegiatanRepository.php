<?php

namespace App\Repositories;

use App\Models\Kegiatan;
use App\Repositories\Contracts\KegiatanRepositoryInterface;
use App\Constants\Pagination;
use App\Constants\Status;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Kegiatan Repository Implementation
 *
 * Implements data access operations for Kegiatan model.
 * This class encapsulates all database queries related to Kegiatan,
 * promoting separation of concerns and testability.
 *
 * Usage:
 *   $kegiatan = app()->make(KegiatanRepositoryInterface::class);
 *   $kegiatan = $kegiatanRepo->findAllWithPagination(15);
 *
 * @package App\Repositories
 */
class KegiatanRepository implements KegiatanRepositoryInterface
{
    /**
     * Find all kegiatan with pagination and optional filters.
     *
     * @param  int  $perPage Number of items per page
     * @param  array  $filters Optional filters (search, program_id, status)
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function findAllWithPagination(int $perPage = Pagination::DEFAULT, array $filters = []): LengthAwarePaginator
    {
        $query = $this->buildQueryWithFilters($filters);

        return $query->with(['program', 'program.instansi'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Find a kegiatan by ID.
     *
     * @param  string  $id The kegiatan UUID
     * @return \App\Models\Kegiatan|null
     */
    public function findById(string $id): ?Kegiatan
    {
        return Kegiatan::with(['program', 'program.instansi'])->find($id);
    }

    /**
     * Find a kegiatan by kode_kegiatan.
     *
     * @param  string  $kode The kegiatan code
     * @return \App\Models\Kegiatan|null
     */
    public function findByKode(string $kode): ?Kegiatan
    {
        return Kegiatan::where('kode_kegiatan', $kode)->first();
    }

    /**
     * Find all kegiatan for a specific program.
     *
     * @param  string  $programId The program UUID
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findByProgram(string $programId): Collection
    {
        return Kegiatan::where('program_id', $programId)
            ->orderBy('nama_kegiatan')
            ->get();
    }

    /**
     * Find all active kegiatan.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findActive(): Collection
    {
        return Kegiatan::where('status', Status::ACTIVE)
            ->with(['program', 'program.instansi'])
            ->orderBy('nama_kegiatan')
            ->get();
    }

    /**
     * Create a new kegiatan.
     *
     * @param  array  $data The kegiatan data
     * @return \App\Models\Kegiatan
     */
    public function create(array $data): Kegiatan
    {
        return Kegiatan::create($data);
    }

    /**
     * Update an existing kegiatan.
     *
     * @param  string  $id The kegiatan UUID
     * @param  array  $data The updated data
     * @return \App\Models\Kegiatan
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function update(string $id, array $data): Kegiatan
    {
        $kegiatan = $this->findById($id);

        if (!$kegiatan) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Kegiatan not found");
        }

        $kegiatan->update($data);

        return $kegiatan->fresh();
    }

    /**
     * Delete a kegiatan by ID.
     *
     * @param  string  $id The kegiatan UUID
     * @return bool True if successful, false otherwise
     */
    public function delete(string $id): bool
    {
        $kegiatan = $this->findById($id);

        if (!$kegiatan) {
            return false;
        }

        return $kegiatan->delete();
    }

    /**
     * Count kegiatan by program.
     *
     * @param  string  $programId The program UUID
     * @return int
     */
    public function countByProgram(string $programId): int
    {
        return Kegiatan::where('program_id', $programId)->count();
    }

    /**
     * Search kegiatan by keyword.
     *
     * @param  string  $keyword The search keyword
     * @param  int  $limit Optional result limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function search(string $keyword, int $limit = 20): Collection
    {
        return Kegiatan::where(function ($query) use ($keyword) {
                $query->where('nama_kegiatan', 'like', "%{$keyword}%")
                    ->orWhere('kode_kegiatan', 'like', "%{$keyword}%")
                    ->orWhere('deskripsi', 'like', "%{$keyword}%")
                    ->orWhereHas('program', function ($q) use ($keyword) {
                        $q->where('nama_program', 'like', "%{$keyword}%");
                    });
            })
            ->with(['program', 'program.instansi'])
            ->orderBy('nama_kegiatan')
            ->limit($limit)
            ->get();
    }

    /**
     * Build query with optional filters.
     *
     * @param  array  $filters Optional filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildQueryWithFilters(array $filters): Builder
    {
        $query = Kegiatan::query();

        // Search filter
        if (!empty($filters['search'])) {
            $keyword = $filters['search'];
            $query->where(function ($q) use ($keyword) {
                $q->where('nama_kegiatan', 'like', "%{$keyword}%")
                    ->orWhere('kode_kegiatan', 'like', "%{$keyword}%")
                    ->orWhere('deskripsi', 'like', "%{$keyword}%")
                    ->orWhereHas('program', function ($q) use ($keyword) {
                        $q->where('nama_program', 'like', "%{$keyword}%");
                    });
            });
        }

        // Program filter
        if (!empty($filters['program_id'])) {
            $query->where('program_id', $filters['program_id']);
        }

        // Status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query;
    }

    /**
     * Get kegiatan statistics.
     *
     * @param  string|null  $programId Optional program ID for filtering
     * @return array
     */
    public function getStatistics(?string $programId = null): array
    {
        $query = Kegiatan::query();

        if ($programId) {
            $query->where('program_id', $programId);
        }

        return [
            'total' => $query->count(),
            'active' => (clone $query)->where('status', Status::ACTIVE)->count(),
            'draft' => (clone $query)->where('status', Status::DRAFT)->count(),
            'completed' => (clone $query)->where('status', Status::COMPLETED)->count(),
        ];
    }
}
