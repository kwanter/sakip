<?php

namespace App\Http\Controllers\Api\Sakip;

use App\Http\Controllers\Controller;
use App\Services\SakipDataTableService;
use App\Services\SakipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * SAKIP Data Table Controller
 *
 * Handles DataTable server-side processing for SAKIP module with a unified
 * error handling approach to eliminate code duplication.
 */
class SakipDataTableController extends Controller
{
    protected $sakipService;
    protected $dataTableService;

    /**
     * Allowed data table types for validation.
     */
    private const ALLOWED_TYPES = ['indicators', 'programs', 'activities', 'reports'];

    public function __construct(
        SakipService $sakipService,
        SakipDataTableService $dataTableService
    ) {
        $this->sakipService = $sakipService;
        $this->dataTableService = $dataTableService;
    }

    /**
     * Get data table configuration.
     *
     * @param string $type The data table type
     * @return \Illuminate\Http\JsonResponse
     */
    public function configuration(string $type): \Illuminate\Http\JsonResponse
    {
        return $this->handleDataTableRequest(
            fn() => $this->dataTableService->getDataTableConfig($type),
            'data',
            'Failed to fetch data table configuration'
        );
    }

    /**
     * Process indicators data table.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function indicators(Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->processDataTable($request, 'indicators');
    }

    /**
     * Process programs data table.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function programs(Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->processDataTable($request, 'programs');
    }

    /**
     * Process activities data table.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function activities(Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->processDataTable($request, 'activities');
    }

    /**
     * Process reports data table.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reports(Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->processDataTable($request, 'reports');
    }

    /**
     * Export data table data.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $type The data table type
     * @return \Illuminate\Http\JsonResponse
     */
    public function export(Request $request, string $type): \Illuminate\Http\JsonResponse
    {
        if (!in_array($type, self::ALLOWED_TYPES, true)) {
            return $this->errorResponse('Invalid data table type.', 400);
        }

        try {
            // Cap export size (never dump entire tables)
            $request->merge(['per_page' => 100, 'page' => 1]);
            $data = $this->dataTableService->processRequest($request, $type);

            $exportData = $this->formatExportData($data['data'], $type);

            return $this->successResponse('Data exported successfully', [
                'data' => $exportData,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::error('Data table export failed', [
                'type' => $type,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return $this->errorResponse('Failed to export data');
        }
    }

    /**
     * Process a unified data table request.
     *
     * This method eliminates code duplication by providing a single
     * implementation for all data table types.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $type The data table type
     * @return \Illuminate\Http\JsonResponse
     */
    protected function processDataTable(Request $request, string $type): \Illuminate\Http\JsonResponse
    {
        if (!in_array($type, self::ALLOWED_TYPES, true)) {
            return $this->errorResponse('Invalid data table type.', 400);
        }

        return $this->handleDataTableRequest(
            fn() => $this->dataTableService->processRequest($request, $type),
            'data',
            "Failed to process {$type} data table"
        );
    }

    /**
     * Handle data table requests with unified error handling.
     *
     * @param callable $callback The operation to execute
     * @param string $dataKey The key for the data in the response
     * @param string $errorMessage Error message for failures
     * @return \Illuminate\Http\JsonResponse
     */
    protected function handleDataTableRequest(
        callable $callback,
        string $dataKey,
        string $errorMessage
    ): \Illuminate\Http\JsonResponse {
        try {
            $result = $callback();

            return response()->json([
                'success' => true,
                $dataKey => $result,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::error('Data table request failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
            ], 500);
        }
    }

    /**
     * Format data for export.
     *
     * @param array $data The raw data to format
     * @param string $type The data table type
     * @return array Formatted data with headers
     */
    protected function formatExportData(array $data, string $type): array
    {
        $headers = $this->getExportHeaders($type);
        $formattedData = [];

        foreach ($data as $item) {
            $row = [];
            foreach ($headers as $key => $header) {
                $row[$header] = $item[$key] ?? '';
            }
            $formattedData[] = $row;
        }

        return [
            'headers' => array_values($headers),
            'data' => $formattedData,
        ];
    }

    /**
     * Get export headers for a specific data table type.
     *
     * @param string $type The data table type
     * @return array The export headers mapping
     */
    protected function getExportHeaders(string $type): array
    {
        $headers = [
            'indicators' => [
                'kode' => 'Kode',
                'nama' => 'Nama Indikator',
                'kategori' => 'Kategori',
                'satuan' => 'Satuan',
                'target' => 'Target',
                'realisasi' => 'Realisasi',
                'capaian' => 'Capaian',
                'status' => 'Status',
                'instansi' => 'Instansi',
                'created_at' => 'Dibuat Pada',
            ],
            'programs' => [
                'kode' => 'Kode Program',
                'nama' => 'Nama Program',
                'instansi' => 'Instansi',
                'anggaran' => 'Anggaran',
                'realisasi_anggaran' => 'Realisasi Anggaran',
                'capaian_anggaran' => 'Capaian Anggaran',
                'status' => 'Status',
                'created_at' => 'Dibuat Pada',
            ],
            'activities' => [
                'kode' => 'Kode Kegiatan',
                'nama' => 'Nama Kegiatan',
                'program' => 'Program',
                'instansi' => 'Instansi',
                'anggaran' => 'Anggaran',
                'target' => 'Target',
                'realisasi' => 'Realisasi',
                'status' => 'Status',
                'created_at' => 'Dibuat Pada',
            ],
            'reports' => [
                'nomor' => 'Nomor Laporan',
                'judul' => 'Judul Laporan',
                'jenis' => 'Jenis',
                'periode' => 'Periode',
                'instansi' => 'Instansi',
                'tanggal' => 'Tanggal',
                'status' => 'Status',
                'creator' => 'Dibuat Oleh',
                'created_at' => 'Dibuat Pada',
            ],
        ];

        return $headers[$type] ?? $headers['indicators'];
    }
}