<?php

namespace App\Http\Controllers\Api\Sakip;

use App\Http\Controllers\Controller;
use App\Services\SakipDataTableService;
use App\Services\SakipService;
use Illuminate\Http\Request;

class SakipDataTableController extends Controller
{
    protected $sakipService;
    protected $dataTableService;

    public function __construct(
        SakipService $sakipService,
        SakipDataTableService $dataTableService
    ) {
        $this->sakipService = $sakipService;
        $this->dataTableService = $dataTableService;
    }

    /**
     * Get data table configuration
     */
    public function configuration(string $type)
    {
        try {
            $config = $this->dataTableService->getDataTableConfig($type);
            
            return response()->json([
                'success' => true,
                'data' => $config,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch data table configuration',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process indicators data table
     */
    public function indicators(Request $request)
    {
        try {
            $data = $this->dataTableService->processRequest($request, 'indicators');
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process indicators data table',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process programs data table
     */
    public function programs(Request $request)
    {
        try {
            $data = $this->dataTableService->processRequest($request, 'programs');
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process programs data table',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process activities data table
     */
    public function activities(Request $request)
    {
        try {
            $data = $this->dataTableService->processRequest($request, 'activities');
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process activities data table',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process reports data table
     */
    public function reports(Request $request)
    {
        try {
            $data = $this->dataTableService->processRequest($request, 'reports');
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process reports data table',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export data table data
     */
    public function export(Request $request, string $type)
    {
        try {
            // Get all data without pagination for export
            $request->merge(['per_page' => 999999]);
            $data = $this->dataTableService->processRequest($request, $type);
            
            // Format data for export
            $exportData = $this->formatExportData($data['data'], $type);
            
            return response()->json([
                'success' => true,
                'data' => $exportData,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Format data for export
     */
    protected function formatExportData($data, string $type): array
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
     * Get export headers
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