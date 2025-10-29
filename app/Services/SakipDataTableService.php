<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class SakipDataTableService
{
    protected $sakipService;

    public function __construct(SakipService $sakipService)
    {
        $this->sakipService = $sakipService;
    }

    /**
     * Render data table
     */
    public function render(array $config = []): string
    {
        $defaultConfig = [
            "id" => "sakip-data-table-" . uniqid(),
            "columns" => [],
            "data_source" => null,
            "options" => [],
        ];

        $config = array_merge($defaultConfig, $config);

        return View::make("sakip.components.data-table", $config)->render();
    }

    /**
     * Process data table request
     */
    public function processRequest(Request $request, string $type): array
    {
        $method = "process" . ucfirst($type) . "Table";

        if (!method_exists($this, $method)) {
            throw new \InvalidArgumentException(
                "Data table type '{$type}' not supported",
            );
        }

        return $this->$method($request);
    }

    /**
     * Process indicators data table
     */
    protected function processIndicatorsTable(Request $request): array
    {
        $query = DB::table("performance_indicators")
            ->join(
                "instansis",
                "performance_indicators.instansi_id",
                "=",
                "instansis.id",
            )
            ->select(
                "performance_indicators.*",
                "instansis.nama_instansi as instansi_name",
            )
            ->whereNull("performance_indicators.deleted_at");

        // Apply filters
        if ($request->has("search")) {
            $search = $request->input("search");
            $query->where(function ($q) use ($search) {
                $q->where("performance_indicators.name", "like", "%{$search}%")
                    ->orWhere(
                        "performance_indicators.code",
                        "like",
                        "%{$search}%",
                    )
                    ->orWhere("instansis.nama_instansi", "like", "%{$search}%");
            });
        }

        if ($request->has("category")) {
            $query->where(
                "performance_indicators.category",
                $request->input("category"),
            );
        }

        if ($request->has("instansi_id")) {
            $query->where(
                "performance_indicators.instansi_id",
                $request->input("instansi_id"),
            );
        }

        if ($request->has("frequency")) {
            $query->where(
                "performance_indicators.frequency",
                $request->input("frequency"),
            );
        }

        // Apply sorting
        if ($request->has("sort_by")) {
            $sortBy = $request->input("sort_by");
            $sortOrder = $request->input("sort_order", "asc");
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy("performance_indicators.created_at", "desc");
        }

        // Get total count
        $totalRecords = $query->count();

        // Apply pagination
        $page = $request->input("page", 1);
        $perPage = $request->input("per_page", 10);
        $offset = ($page - 1) * $perPage;

        $data = $query->offset($offset)->limit($perPage)->get();

        // Format data
        $formattedData = $data->map(function ($item) {
            return [
                "id" => $item->id,
                "code" => $item->code,
                "name" => $item->name,
                "category" => ucfirst($item->category),
                "measurement_unit" => $item->measurement_unit,
                "frequency" => ucfirst($item->frequency),
                "weight" => number_format($item->weight, 2) . "%",
                "is_mandatory" => $item->is_mandatory ? "Ya" : "Tidak",
                "instansi" => $item->instansi_name,
                "created_at" => $item->created_at,
                "updated_at" => $item->updated_at,
            ];
        });

        return [
            "data" => $formattedData,
            "total" => $totalRecords,
            "page" => $page,
            "per_page" => $perPage,
            "total_pages" => ceil($totalRecords / $perPage),
        ];
    }

    /**
     * Process program data table
     */
    protected function processProgramTable(Request $request): array
    {
        $query = DB::table("program")
            ->join("instansi", "program.instansi_id", "=", "instansi.id")
            ->select("program.*", "instansi.nama as instansi_name");

        // Apply filters
        if ($request->has("search")) {
            $search = $request->input("search");
            $query->where(function ($q) use ($search) {
                $q->where("program.nama", "like", "%{$search}%")
                    ->orWhere("program.kode", "like", "%{$search}%")
                    ->orWhere("instansi.nama", "like", "%{$search}%");
            });
        }

        if ($request->has("instansi_id")) {
            $query->where(
                "program.instansi_id",
                $request->input("instansi_id"),
            );
        }

        if ($request->has("status")) {
            $query->where("program.status", $request->input("status"));
        }

        // Apply sorting
        if ($request->has("sort_by")) {
            $sortBy = $request->input("sort_by");
            $sortOrder = $request->input("sort_order", "asc");
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy("program.created_at", "desc");
        }

        // Get total count
        $totalRecords = $query->count();

        // Apply pagination
        $page = $request->input("page", 1);
        $perPage = $request->input("per_page", 10);
        $offset = ($page - 1) * $perPage;

        $data = $query->offset($offset)->limit($perPage)->get();

        // Format data
        $formattedData = $data->map(function ($item) {
            return [
                "id" => $item->id,
                "kode" => $item->kode,
                "nama" => $item->nama,
                "instansi" => $item->instansi_name,
                "anggaran" => $this->sakipService->formatCurrency(
                    $item->anggaran,
                ),
                "realisasi_anggaran" => $this->sakipService->formatCurrency(
                    $item->realisasi_anggaran,
                ),
                "capaian_anggaran" => $this->sakipService->formatPercentage(
                    $item->capaian_anggaran,
                ),
                "status" => $this->getStatusBadge($item->status),
                "created_at" => $item->created_at,
                "updated_at" => $item->updated_at,
            ];
        });

        return [
            "data" => $formattedData,
            "total" => $totalRecords,
            "page" => $page,
            "per_page" => $perPage,
            "total_pages" => ceil($totalRecords / $perPage),
        ];
    }

    /**
     * Process kegiatan data table
     */
    protected function processKegiatanTable(Request $request): array
    {
        $query = DB::table("kegiatan")
            ->join("program", "kegiatan.program_id", "=", "program.id")
            ->join("instansi", "kegiatan.instansi_id", "=", "instansi.id")
            ->select(
                "kegiatan.*",
                "program.nama as program_name",
                "instansi.nama as instansi_name",
            );

        // Apply filters
        if ($request->has("search")) {
            $search = $request->input("search");
            $query->where(function ($q) use ($search) {
                $q->where("kegiatan.nama", "like", "%{$search}%")
                    ->orWhere("kegiatan.kode", "like", "%{$search}%")
                    ->orWhere("program.nama", "like", "%{$search}%")
                    ->orWhere("instansi.nama", "like", "%{$search}%");
            });
        }

        if ($request->has("program_id")) {
            $query->where("kegiatan.program_id", $request->input("program_id"));
        }

        if ($request->has("instansi_id")) {
            $query->where(
                "kegiatan.instansi_id",
                $request->input("instansi_id"),
            );
        }

        if ($request->has("status")) {
            $query->where("kegiatan.status", $request->input("status"));
        }

        // Apply sorting
        if ($request->has("sort_by")) {
            $sortBy = $request->input("sort_by");
            $sortOrder = $request->input("sort_order", "asc");
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy("kegiatan.created_at", "desc");
        }

        // Get total count
        $totalRecords = $query->count();

        // Apply pagination
        $page = $request->input("page", 1);
        $perPage = $request->input("per_page", 10);
        $offset = ($page - 1) * $perPage;

        $data = $query->offset($offset)->limit($perPage)->get();

        // Format data
        $formattedData = $data->map(function ($item) {
            return [
                "id" => $item->id,
                "kode" => $item->kode,
                "nama" => $item->nama,
                "program" => $item->program_name,
                "instansi" => $item->instansi_name,
                "anggaran" => $this->sakipService->formatCurrency(
                    $item->anggaran,
                ),
                "target" => $this->sakipService->formatNumber($item->target),
                "realisasi" => $this->sakipService->formatNumber(
                    $item->realisasi,
                ),
                "status" => $this->getStatusBadge($item->status),
                "created_at" => $item->created_at,
                "updated_at" => $item->updated_at,
            ];
        });

        return [
            "data" => $formattedData,
            "total" => $totalRecords,
            "page" => $page,
            "per_page" => $perPage,
            "total_pages" => ceil($totalRecords / $perPage),
        ];
    }

    /**
     * Process laporan data table
     */
    protected function processLaporanTable(Request $request): array
    {
        $query = DB::table("laporan_kinerja")
            ->join(
                "instansi",
                "laporan_kinerja.instansi_id",
                "=",
                "instansi.id",
            )
            ->join("users", "laporan_kinerja.created_by", "=", "users.id")
            ->select(
                "laporan_kinerja.*",
                "instansi.nama as instansi_name",
                "users.name as creator_name",
            );

        // Apply filters
        if ($request->has("search")) {
            $search = $request->input("search");
            $query->where(function ($q) use ($search) {
                $q->where("laporan_kinerja.judul", "like", "%{$search}%")
                    ->orWhere("laporan_kinerja.nomor", "like", "%{$search}%")
                    ->orWhere("instansi.nama", "like", "%{$search}%");
            });
        }

        if ($request->has("jenis")) {
            $query->where("laporan_kinerja.jenis", $request->input("jenis"));
        }

        if ($request->has("instansi_id")) {
            $query->where(
                "laporan_kinerja.instansi_id",
                $request->input("instansi_id"),
            );
        }

        if ($request->has("status")) {
            $query->where("laporan_kinerja.status", $request->input("status"));
        }

        if ($request->has("periode")) {
            $query->where(
                "laporan_kinerja.periode",
                $request->input("periode"),
            );
        }

        // Apply sorting
        if ($request->has("sort_by")) {
            $sortBy = $request->input("sort_by");
            $sortOrder = $request->input("sort_order", "asc");
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy("laporan_kinerja.created_at", "desc");
        }

        // Get total count
        $totalRecords = $query->count();

        // Apply pagination
        $page = $request->input("page", 1);
        $perPage = $request->input("per_page", 10);
        $offset = ($page - 1) * $perPage;

        $data = $query->offset($offset)->limit($perPage)->get();

        // Format data
        $formattedData = $data->map(function ($item) {
            return [
                "id" => $item->id,
                "nomor" => $item->nomor,
                "judul" => $item->judul,
                "jenis" => $item->jenis,
                "periode" => $item->periode,
                "instansi" => $item->instansi_name,
                "tanggal" => \Carbon\Carbon::parse($item->tanggal)->format(
                    "d M Y",
                ),
                "status" => $this->getStatusBadge($item->status),
                "creator" => $item->creator_name,
                "created_at" => $item->created_at,
                "updated_at" => $item->updated_at,
            ];
        });

        return [
            "data" => $formattedData,
            "total" => $totalRecords,
            "page" => $page,
            "per_page" => $perPage,
            "total_pages" => ceil($totalRecords / $perPage),
        ];
    }

    /**
     * Get status badge HTML
     */
    protected function getStatusBadge(string $status): string
    {
        $statusClasses = [
            "active" => "sakip-status-success",
            "inactive" => "sakip-status-neutral",
            "pending" => "sakip-status-warning",
            "approved" => "sakip-status-success",
            "rejected" => "sakip-status-error",
            "draft" => "sakip-status-info",
        ];

        $statusLabels = [
            "active" => "Active",
            "inactive" => "Inactive",
            "pending" => "Pending",
            "approved" => "Approved",
            "rejected" => "Rejected",
            "draft" => "Draft",
        ];

        $class = $statusClasses[$status] ?? "sakip-status-neutral";
        $label = $statusLabels[$status] ?? ucfirst($status);

        return '<span class="sakip-status-badge ' .
            $class .
            '">' .
            $label .
            "</span>";
    }

    /**
     * Get data table configuration
     */
    public function getDataTableConfig(string $type): array
    {
        $configs = [
            "indicators" => [
                "columns" => [
                    ["field" => "kode", "title" => "Kode", "sortable" => true],
                    [
                        "field" => "nama",
                        "title" => "Nama Indikator",
                        "sortable" => true,
                    ],
                    [
                        "field" => "kategori",
                        "title" => "Kategori",
                        "sortable" => true,
                    ],
                    [
                        "field" => "satuan",
                        "title" => "Satuan",
                        "sortable" => true,
                    ],
                    [
                        "field" => "target",
                        "title" => "Target",
                        "sortable" => true,
                    ],
                    [
                        "field" => "realisasi",
                        "title" => "Realisasi",
                        "sortable" => true,
                    ],
                    [
                        "field" => "capaian",
                        "title" => "Capaian",
                        "sortable" => true,
                    ],
                    [
                        "field" => "status",
                        "title" => "Status",
                        "sortable" => true,
                    ],
                    [
                        "field" => "instansi",
                        "title" => "Instansi",
                        "sortable" => true,
                    ],
                ],
                "filters" => [
                    "kategori" => $this->sakipService->getIndicatorCategories(),
                    "status" => ["active", "inactive", "pending"],
                ],
            ],
            "programs" => [
                "columns" => [
                    [
                        "field" => "kode",
                        "title" => "Kode Program",
                        "sortable" => true,
                    ],
                    [
                        "field" => "nama",
                        "title" => "Nama Program",
                        "sortable" => true,
                    ],
                    [
                        "field" => "instansi",
                        "title" => "Instansi",
                        "sortable" => true,
                    ],
                    [
                        "field" => "anggaran",
                        "title" => "Anggaran",
                        "sortable" => true,
                    ],
                    [
                        "field" => "realisasi_anggaran",
                        "title" => "Realisasi",
                        "sortable" => true,
                    ],
                    [
                        "field" => "capaian_anggaran",
                        "title" => "Capaian",
                        "sortable" => true,
                    ],
                    [
                        "field" => "status",
                        "title" => "Status",
                        "sortable" => true,
                    ],
                ],
                "filters" => [
                    "status" => ["active", "inactive", "pending"],
                ],
            ],
            "activities" => [
                "columns" => [
                    [
                        "field" => "kode",
                        "title" => "Kode Kegiatan",
                        "sortable" => true,
                    ],
                    [
                        "field" => "nama",
                        "title" => "Nama Kegiatan",
                        "sortable" => true,
                    ],
                    [
                        "field" => "program",
                        "title" => "Program",
                        "sortable" => true,
                    ],
                    [
                        "field" => "instansi",
                        "title" => "Instansi",
                        "sortable" => true,
                    ],
                    [
                        "field" => "anggaran",
                        "title" => "Anggaran",
                        "sortable" => true,
                    ],
                    [
                        "field" => "target",
                        "title" => "Target",
                        "sortable" => true,
                    ],
                    [
                        "field" => "realisasi",
                        "title" => "Realisasi",
                        "sortable" => true,
                    ],
                    [
                        "field" => "status",
                        "title" => "Status",
                        "sortable" => true,
                    ],
                ],
                "filters" => [
                    "status" => ["active", "inactive", "pending"],
                ],
            ],
            "reports" => [
                "columns" => [
                    [
                        "field" => "nomor",
                        "title" => "Nomor Laporan",
                        "sortable" => true,
                    ],
                    [
                        "field" => "judul",
                        "title" => "Judul Laporan",
                        "sortable" => true,
                    ],
                    [
                        "field" => "jenis",
                        "title" => "Jenis",
                        "sortable" => true,
                    ],
                    [
                        "field" => "periode",
                        "title" => "Periode",
                        "sortable" => true,
                    ],
                    [
                        "field" => "instansi",
                        "title" => "Instansi",
                        "sortable" => true,
                    ],
                    [
                        "field" => "tanggal",
                        "title" => "Tanggal",
                        "sortable" => true,
                    ],
                    [
                        "field" => "status",
                        "title" => "Status",
                        "sortable" => true,
                    ],
                    [
                        "field" => "creator",
                        "title" => "Created By",
                        "sortable" => true,
                    ],
                ],
                "filters" => [
                    "jenis" => $this->sakipService->getReportTypes(),
                    "status" => ["draft", "pending", "approved", "rejected"],
                ],
            ],
        ];

        return $configs[$type] ?? $configs["indicators"];
    }
}
