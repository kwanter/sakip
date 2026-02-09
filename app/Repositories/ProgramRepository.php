<?php

namespace App\Repositories;

use App\Models\Program;
use App\Repositories\Contracts\ProgramRepositoryInterface;
use App\Constants\Pagination;
use App\Constants\Status;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Program Repository Implementation
 *
 * Implements data access operations for Program model.
 *
 * @package App\Repositories
 */
class ProgramRepository implements ProgramRepositoryInterface
{
    /**
     * Find all programs with pagination and optional filters.
     *
     * @param  int  $perPage Number of items per page
     * @param  array  $filters Optional filters
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function findAllWithPagination(int $perPage = Pagination::DEFAULT, array $filters = []): LengthAwarePaginator
    {
        $query = $this->buildQueryWithFilters($filters);

        return $query->with(['instansi', 'sasaranStrategis'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Find a program by ID.
     *
     * @param  string  $id The program UUID
     * @return \App\Models\Program|null
     */
    public function findById(string $id): ?Program
    {
        return Program::with(['instansi', 'sasaranStrategis'])->find($id);
    }

    /**
     * Find a program by kode_program.
     *
     * @param  string  $kode The program code
     * @return \App\Models\Program|null
     */
    public function findByKode(string $kode): ?Program
    {
        return Program::where('kode_program', $kode)->first();
    }

    /**
     * Find all programs for a specific instansi.
     *
     * @param  string  $instansiId The instansi UUID
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findByInstansi(string $instansiId): Collection
    {
        return Program::where('instansi_id', $instansiId)
            ->orderBy('nama_program')
            ->get();
    }

    /**
     * Find all programs for a specific sasaran strategis.
     *
     * @param  string  $sasaranStrategisId The sasaran strategis UUID
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findBySasaranStrategis(string $sasaranStrategisId): Collection
    {
        return Program::where('sasaran_strategis_id', $sasaranStrategisId)
            ->orderBy('nama_program')
            ->get();
    }

    /**
     * Find all active programs.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findActive(): Collection
    {
        return Program::where('status', Status::ACTIVE)
            ->with(['instansi', 'sasaranStrategis'])
            ->orderBy('nama_program')
            ->get();
    }

    /**
     * Create a new program.
     *
     * @param  array  $data The program data
     * @return \App\Models\Program
     */
    public function create(array $data): Program
    {
        return Program::create($data);
    }

    /**
     * Update an existing program.
     *
     * @param  string  $id The program UUID
     * @param  array  $data The updated data
     * @return \App\Models\Program
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function update(string $id, array $data): Program
    {
        $program = $this->findById($id);

        if (!$program) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Program not found");
        }

        $program->update($data);

        return $program->fresh();
    }

    /**
     * Delete a program by ID.
     *
     * @param  string  $id The program UUID
     * @return bool True if successful, false otherwise
     */
    public function delete(string $id): bool
    {
        $program = $this->findById($id);

        if (!$program) {
            return false;
        }

        return $program->delete();
    }

    /**
     * Count programs by instansi.
     *
     * @param  string  $instansiId The instansi UUID
     * @return int
     */
    public function countByInstansi(string $instansiId): int
    {
        return Program::where('instansi_id', $instansiId)->count();
    }

    /**
     * Search programs by keyword.
     *
     * @param  string  $keyword The search keyword
     * @param  int  $limit Optional result limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function search(string $keyword, int $limit = 20): Collection
    {
        return Program::where(function ($query) use ($keyword) {
                $query->where('nama_program', 'like', "%{$keyword}%")
                    ->orWhere('kode_program', 'like', "%{$keyword}%")
                    ->orWhere('deskripsi', 'like', "%{$keyword}%")
                    ->orWhereHas('instansi', function ($q) use ($keyword) {
                        $q->where('nama_instansi', 'like', "%{$keyword}%");
                    });
            })
            ->with(['instansi', 'sasaranStrategis'])
            ->orderBy('nama_program')
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
        $query = Program::query();

        // Search filter
        if (!empty($filters['search'])) {
            $keyword = $filters['search'];
            $query->where(function ($q) use ($keyword) {
                $q->where('nama_program', 'like', "%{$keyword}%")
                    ->orWhere('kode_program', 'like', "%{$keyword}%")
                    ->orWhereHas('instansi', function ($q) use ($keyword) {
                        $q->where('nama_instansi', 'like', "%{$keyword}%");
                    });
            });
        }

        // Instansi filter
        if (!empty($filters['instansi_id'])) {
            $query->where('instansi_id', $filters['instansi_id']);
        }

        // Sasaran Strategis filter
        if (!empty($filters['sasaran_strategis_id'])) {
            $query->where('sasaran_strategis_id', $filters['sasaran_strategis_id']);
        }

        // Status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Year filter
        if (!empty($filters['tahun'])) {
            $query->where('tahun', $filters['tahun']);
        }

        return $query;
    }

    /**
     * Get program statistics.
     *
     * @param  string|null  $instansiId Optional instansi ID for filtering
     * @return array
     */
    public function getStatistics(?string $instansiId = null): array
    {
        $query = Program::query();

        if ($instansiId) {
            $query->where('instansi_id', $instansiId);
        }

        return [
            'total' => $query->count(),
            'active' => (clone $query)->where('status', Status::ACTIVE)->count(),
            'draft' => (clone $query)->where('status', Status::DRAFT)->count(),
            'completed' => (clone $query)->where('status', Status::COMPLETED)->count(),
        ];
    }
}
