<?php

namespace App\Repositories\Contracts;

use App\Models\Kegiatan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Kegiatan Repository Interface
 *
 * Defines the contract for Kegiatan data access operations.
 * This interface enables dependency injection and makes the codebase more testable.
 *
 * Benefits:
 * - Decouples data access from business logic
 * - Enables easy mocking for unit tests
 * - Provides a clear contract for data operations
 * - Allows swapping implementations (e.g., for caching)
 *
 * @package App\Repositories\Contracts
 */
interface KegiatanRepositoryInterface
{
    /**
     * Find all kegiatan with pagination.
     *
     * @param  int  $perPage Number of items per page
     * @param  array  $filters Optional filters (search, program_id, status)
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function findAllWithPagination(int $perPage, array $filters = []): LengthAwarePaginator;

    /**
     * Find a kegiatan by ID.
     *
     * @param  string  $id The kegiatan UUID
     * @return \App\Models\Kegiatan|null
     */
    public function findById(string $id): ?Kegiatan;

    /**
     * Find a kegiatan by kode_kegiatan.
     *
     * @param  string  $kode The kegiatan code
     * @return \App\Models\Kegiatan|null
     */
    public function findByKode(string $kode): ?Kegiatan;

    /**
     * Find all kegiatan for a specific program.
     *
     * @param  string  $programId The program UUID
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findByProgram(string $programId): Collection;

    /**
     * Find all active kegiatan.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findActive(): Collection;

    /**
     * Create a new kegiatan.
     *
     * @param  array  $data The kegiatan data
     * @return \App\Models\Kegiatan
     */
    public function create(array $data): Kegiatan;

    /**
     * Update an existing kegiatan.
     *
     * @param  string  $id The kegiatan UUID
     * @param  array  $data The updated data
     * @return \App\Models\Kegiatan
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function update(string $id, array $data): Kegiatan;

    /**
     * Delete a kegiatan by ID.
     *
     * @param  string  $id The kegiatan UUID
     * @return bool True if successful, false otherwise
     */
    public function delete(string $id): bool;

    /**
     * Count kegiatan by program.
     *
     * @param  string  $programId The program UUID
     * @return int
     */
    public function countByProgram(string $programId): int;

    /**
     * Search kegiatan by keyword.
     *
     * @param  string  $keyword The search keyword
     * @param  int  $limit Optional result limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function search(string $keyword, int $limit = 20): Collection;
}
