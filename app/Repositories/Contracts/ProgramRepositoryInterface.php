<?php

namespace App\Repositories\Contracts;

use App\Models\Program;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Program Repository Interface
 *
 * Defines the contract for Program data access operations.
 *
 * @package App\Repositories\Contracts
 */
interface ProgramRepositoryInterface
{
    /**
     * Find all programs with pagination and optional filters.
     *
     * @param  int  $perPage Number of items per page
     * @param  array  $filters Optional filters (search, instansi_id, status, tahun)
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function findAllWithPagination(int $perPage, array $filters = []): LengthAwarePaginator;

    /**
     * Find a program by ID.
     *
     * @param  string  $id The program UUID
     * @return \App\Models\Program|null
     */
    public function findById(string $id): ?Program;

    /**
     * Find a program by kode_program.
     *
     * @param  string  $kode The program code
     * @return \App\Models\Program|null
     */
    public function findByKode(string $kode): ?Program;

    /**
     * Find all programs for a specific instansi.
     *
     * @param  string  $instansiId The instansi UUID
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findByInstansi(string $instansiId): Collection;

    /**
     * Find all programs for a specific sasaran strategis.
     *
     * @param  string  $sasaranStrategisId The sasaran strategis UUID
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findBySasaranStrategis(string $sasaranStrategisId): Collection;

    /**
     * Find all active programs.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findActive(): Collection;

    /**
     * Create a new program.
     *
     * @param  array  $data The program data
     * @return \App\Models\Program
     */
    public function create(array $data): Program;

    /**
     * Update an existing program.
     *
     * @param  string  $id The program UUID
     * @param  array  $data The updated data
     * @return \App\Models\Program
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function update(string $id, array $data): Program;

    /**
     * Delete a program by ID.
     *
     * @param  string  $id The program UUID
     * @return bool True if successful, false otherwise
     */
    public function delete(string $id): bool;

    /**
     * Count programs by instansi.
     *
     * @param  string  $instansiId The instansi UUID
     * @return int
     */
    public function countByInstansi(string $instansiId): int;

    /**
     * Search programs by keyword.
     *
     * @param  string  $keyword The search keyword
     * @param  int  $limit Optional result limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function search(string $keyword, int $limit = 20): Collection;
}
