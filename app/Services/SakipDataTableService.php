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
     * Map public type aliases to internal method stems.
     */
    private const TYPE_MAP = [
        "indicators" => "Indicators",
        "programs" => "Program",
        "activities" => "Kegiatan",
        "reports" => "Laporan",
        "program" => "Program",
        "kegiatan" => "Kegiatan",
        "laporan" => "Laporan",
    ];

    private const MAX_PER_PAGE = 100;

    private const ALLOWED_SORT = [
        "indicators" => [
            "performance_indicators.created_at",
            "performance_indicators.name",
            "performance_indicators.code",
            "performance_indicators.category",
            "performance_indicators.frequency",
            "instansis.nama_instansi",
        ],
        "programs" => ["program.created_at", "program.nama", "program.kode"],
        "activities" => ["kegiatan.created_at", "kegiatan.nama", "kegiatan.kode"],
        "reports" => ["laporan_kinerja.created_at", "laporan_kinerja.judul"],
    ];

    /**
     * Process data table request
     */
    public function processRequest(Request $request, string $type): array
    {
        $stem = self::TYPE_MAP[$type] ?? ucfirst($type);
        $method = "process" . $stem . "Table";

        if (!method_exists($this, $method)) {
            throw new \InvalidArgumentException(
                "Data table type '{$type}' not supported",
            );
        }

        return $this->$method($request);
    }

    /**
     * Force tenant scope for non-super-admin users.
     * Client-supplied instansi_id is ignored for tenant users.
     */
    protected function applyTenantScope($query, string $column): void
    {
        $user = auth()->user();
        if (!$user) {
            $query->whereRaw("1 = 0");
            return;
        }

        if ($user->hasRole("Super Admin")) {
            return;
        }

        if ($user->instansi_id) {
            $query->where($column, $user->instansi_id);
            return;
        }

        // Global roles without instansi still must not dump all tenants unless Super Admin
        if (!$user->hasAnyRole(["Executive", "Auditor"])) {
            $query->whereRaw("1 = 0");
        }
    }

    /**
     * Safe pagination inputs.
     *
     * @return array{0:int,1:int}
     */
    protected function pagination(Request $request): array
    {
        $page = max(1, (int) $request->input("page", 1));
        $perPage = (int) $request->input("per_page", 10);
        $perPage = max(1, min(self::MAX_PER_PAGE, $perPage));

        return [$page, $perPage];
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

        $this->applyTenantScope($query, "performance_indicators.instansi_id");

        // Apply filters
        if ($request->filled("search")) {
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

        if ($request->filled("category")) {
            $query->where(
                "performance_indicators.category",
                $request->input("category"),
            );
        }

        // Super Admin may filter by instansi_id; tenant users cannot override scope
        if (
            auth()->user()?->hasRole("Super Admin") &&
            $request->filled("instansi_id")
        ) {
            $query->where(
                "performance_indicators.instansi_id",
                $request->input("instansi_id"),
            );
        }

        if ($request->filled("frequency")) {
            $query->where(
                "performance_indicators.frequency",
                $request->input("frequency"),
            );
        }

        // Apply sorting (allowlisted)
        $allowed = self::ALLOWED_SORT["indicators"];
        $sortBy = $request->input("sort_by", "performance_indicators.created_at");
        if (!in_array($sortBy, $allowed, true)) {
            $sortBy = "performance_indicators.created_at";
        }
        $sortOrder = strtolower((string) $request->input("sort_order", "desc")) === "asc" ? "asc" : "desc";
        $query->orderBy($sortBy, $sortOrder);

        // Get total count
        $totalRecords = $query->count();

        // Apply pagination
        [$page, $perPage] = $this->pagination($request);
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
        // Use actual table names from migrations
        $query = DB::table("programs")
            ->join("instansis", "programs.instansi_id", "=", "instansis.id")
            ->select("programs.*", "instansis.nama_instansi as instansi_name");

        $this->applyTenantScope($query, "programs.instansi_id");

        // Apply filters
        if ($request->filled("search")) {
            $search = $request->input("search");
            $query->where(function ($q) use ($search) {
                $q->where("programs.nama_program", "like", "%{$search}%")
                    ->orWhere("programs.kode_program", "like", "%{$search}%")
                    ->orWhere("instansis.nama_instansi", "like", "%{$search}%");
            });
        }

        if (
            auth()->user()?->hasRole("Super Admin") &&
            $request->filled("instansi_id")
        ) {
            $query->where("programs.instansi_id", $request->input("instansi_id"));
        }

        if ($request->filled("status")) {
            $query->where("programs.status", $request->input("status"));
        }

        $sortBy = $request->input("sort_by", "programs.created_at");
        if (
            !in_array(
                $sortBy,
                ["programs.created_at", "programs.nama_program", "programs.kode_program"],
                true,
            )
        ) {
            $sortBy = "programs.created_at";
        }
        $sortOrder =
            strtolower((string) $request->input("sort_order", "desc")) === "asc"
                ? "asc"
                : "desc";
        $query->orderBy($sortBy, $sortOrder);

        // Get total count
        $totalRecords = $query->count();

        // Apply pagination
        [$page, $perPage] = $this->pagination($request);
        $offset = ($page - 1) * $perPage;

        $data = $query->offset($offset)->limit($perPage)->get();

        // Format data
        $formattedData = $data->map(function ($item) {
            return [
                "id" => $item->id,
                "kode" => $item->kode_program ?? $item->kode ?? null,
                "nama" => $item->nama_program ?? $item->nama ?? null,
                "instansi" => $item->instansi_name,
                "status" => $this->getStatusBadge($item->status ?? null),
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
        $query = DB::table("kegiatans")
            ->join("programs", "kegiatans.program_id", "=", "programs.id")
            ->join("instansis", "programs.instansi_id", "=", "instansis.id")
            ->select(
                "kegiatans.*",
                "programs.nama_program as program_name",
                "instansis.nama_instansi as instansi_name",
            );

        $this->applyTenantScope($query, "programs.instansi_id");

        // Apply filters
        if ($request->filled("search")) {
            $search = $request->input("search");
            $query->where(function ($q) use ($search) {
                $q->where("kegiatans.nama_kegiatan", "like", "%{$search}%")
                    ->orWhere("kegiatans.kode_kegiatan", "like", "%{$search}%")
                    ->orWhere("programs.nama_program", "like", "%{$search}%")
                    ->orWhere("instansis.nama_instansi", "like", "%{$search}%");
            });
        }

        if ($request->filled("program_id")) {
            $query->where("kegiatans.program_id", $request->input("program_id"));
        }

        if (
            auth()->user()?->hasRole("Super Admin") &&
            $request->filled("instansi_id")
        ) {
            $query->where("programs.instansi_id", $request->input("instansi_id"));
        }

        if ($request->filled("status")) {
            $query->where("kegiatans.status", $request->input("status"));
        }

        $sortBy = $request->input("sort_by", "kegiatans.created_at");
        if (
            !in_array(
                $sortBy,
                ["kegiatans.created_at", "kegiatans.nama_kegiatan", "kegiatans.kode_kegiatan"],
                true,
            )
        ) {
            $sortBy = "kegiatans.created_at";
        }
        $sortOrder =
            strtolower((string) $request->input("sort_order", "desc")) === "asc"
                ? "asc"
                : "desc";
        $query->orderBy($sortBy, $sortOrder);

        // Get total count
        $totalRecords = $query->count();

        // Apply pagination
        [$page, $perPage] = $this->pagination($request);
        $offset = ($page - 1) * $perPage;

        $data = $query->offset($offset)->limit($perPage)->get();

        // Format data
        $formattedData = $data->map(function ($item) {
            return [
                "id" => $item->id,
                "kode" => $item->kode_kegiatan ?? $item->kode ?? null,
                "nama" => $item->nama_kegiatan ?? $item->nama ?? null,
                "program" => $item->program_name,
                "instansi" => $item->instansi_name,
                "status" => $this->getStatusBadge($item->status ?? null),
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
        // Prefer reports table used by current SAKIP module
        $table = \Illuminate\Support\Facades\Schema::hasTable("reports")
            ? "reports"
            : "laporan_kinerjas";

        $query = DB::table($table)
            ->leftJoin("instansis", "{$table}.instansi_id", "=", "instansis.id")
            ->leftJoin("users", "{$table}.generated_by", "=", "users.id")
            ->select(
                "{$table}.*",
                "instansis.nama_instansi as instansi_name",
                "users.name as creator_name",
            );

        $this->applyTenantScope($query, "{$table}.instansi_id");

        // Apply filters
        if ($request->filled("search")) {
            $search = $request->input("search");
            $query->where(function ($q) use ($search, $table) {
                $q->where("{$table}.title", "like", "%{$search}%")
                    ->orWhere("{$table}.report_type", "like", "%{$search}%")
                    ->orWhere("instansis.nama_instansi", "like", "%{$search}%");
            });
        }

        if ($request->filled("jenis") || $request->filled("report_type")) {
            $query->where(
                "{$table}.report_type",
                $request->input("report_type", $request->input("jenis")),
            );
        }

        if (
            auth()->user()?->hasRole("Super Admin") &&
            $request->filled("instansi_id")
        ) {
            $query->where("{$table}.instansi_id", $request->input("instansi_id"));
        }

        if ($request->filled("status")) {
            $query->where("{$table}.status", $request->input("status"));
        }

        if ($request->filled("periode") || $request->filled("period")) {
            $query->where(
                "{$table}.period",
                $request->input("period", $request->input("periode")),
            );
        }

        $sortBy = $request->input("sort_by", "{$table}.created_at");
        if (
            !in_array(
                $sortBy,
                ["{$table}.created_at", "{$table}.title", "{$table}.status", "{$table}.period"],
                true,
            )
        ) {
            $sortBy = "{$table}.created_at";
        }
        $sortOrder =
            strtolower((string) $request->input("sort_order", "desc")) === "asc"
                ? "asc"
                : "desc";
        $query->orderBy($sortBy, $sortOrder);

        // Get total count
        $totalRecords = $query->count();

        // Apply pagination
        [$page, $perPage] = $this->pagination($request);
        $offset = ($page - 1) * $perPage;

        $data = $query->offset($offset)->limit($perPage)->get();

        // Format data
        $formattedData = $data->map(function ($item) {
            return [
                "id" => $item->id,
                "judul" => $item->title ?? $item->judul ?? null,
                "jenis" => $item->report_type ?? $item->jenis ?? null,
                "periode" => $item->period ?? $item->periode ?? null,
                "instansi" => $item->instansi_name,
                "status" => $this->getStatusBadge($item->status ?? null),
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
    protected function getStatusBadge(?string $status): string
    {
        $status = $status ?? "unknown";
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
