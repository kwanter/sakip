<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $report->title ?: 'Laporan Kinerja' }}</title>
    <style>
        /* Print document: self-contained styles (Dompdf ignores external CSS) */
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #0a0a0a;
            margin: 0;
            padding: 0;
        }
        .header {
            border-bottom: 2px solid #6366f1;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0 0 4px 0;
            color: #0a0a0a;
        }
        .header .meta { color: #6b6b6b; font-size: 10px; }
        .section { margin-bottom: 18px; }
        .section h2 {
            font-size: 13px;
            color: #4f46e5;
            border-bottom: 1px solid #e8e8ec;
            padding-bottom: 4px;
            margin: 0 0 8px 0;
        }
        table { width: 100%; border-collapse: collapse; }
        th, td {
            border: 1px solid #e8e8ec;
            padding: 5px 7px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background: #fafafa;
            color: #6b6b6b;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .summary-grid { width: 100%; }
        .summary-grid td {
            border: 1px solid #e8e8ec;
            padding: 8px;
            width: 25%;
        }
        .summary-label { color: #6b6b6b; font-size: 9px; text-transform: uppercase; }
        .summary-value { font-size: 16px; font-weight: bold; color: #0a0a0a; }
        .footer {
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px solid #e8e8ec;
            color: #9c9c9c;
            font-size: 9px;
        }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    @php
        $year = \Carbon\Carbon::parse($report->period)->year;
        $summary = $data['summary'] ?? [];
        $instansiName = $report->instansi->nama_instansi ?? '-';
    @endphp

    <div class="header">
        <h1>{{ $report->title ?: 'Laporan Kinerja' }}</h1>
        <div class="meta">
            {{ $instansiName }} &middot; Periode {{ $report->period }} &middot;
            Dibuat {{ now()->translatedFormat('d F Y H:i') }}
        </div>
    </div>

    <div class="section">
        <h2>Ringkasan</h2>
        <table class="summary-grid">
            <tr>
                <td>
                    <div class="summary-label">Total Indikator</div>
                    <div class="summary-value">{{ $summary['total_indicators'] ?? 0 }}</div>
                </td>
                <td>
                    <div class="summary-label">Indikator dengan Data</div>
                    <div class="summary-value">{{ $summary['indicators_with_data'] ?? 0 }}</div>
                </td>
                <td>
                    <div class="summary-label">Rata-rata Capaian</div>
                    <div class="summary-value">{{ $summary['average_performance'] ?? 0 }}%</div>
                </td>
                <td>
                    <div class="summary-label">Capaian Tercapai</div>
                    <div class="summary-value">{{ $summary['achieved_indicators'] ?? 0 }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Rincian Indikator</h2>
        <table>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Indikator</th>
                    <th>Satuan</th>
                    <th class="text-right">Target {{ $year }}</th>
                    <th class="text-right">Realisasi</th>
                    <th class="text-right">Capaian</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report->indicators as $indicator)
                    @php
                        $target = $indicator->targets->firstWhere('year', $year);
                        $performance = $indicator->performanceData
                            ->filter(fn ($item) => str_starts_with((string) $item->period, (string) $year))
                            ->sortByDesc('period')
                            ->first();
                    @endphp
                    <tr>
                        <td>{{ $indicator->code }}</td>
                        <td>{{ $indicator->name }}</td>
                        <td>{{ $indicator->unit ?? '-' }}</td>
                        <td class="text-right">{{ $target->target_value ?? '-' }}</td>
                        <td class="text-right">{{ $performance->actual_value ?? '-' }}</td>
                        <td class="text-right">
                            @php($achievement = $performance?->calculateAchievement())
                            @if($achievement !== null)
                                {{ number_format((float) $achievement, 2, ',', '.') }}%
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #9c9c9c;">Tidak ada indikator pada laporan ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">
        Dokumen ini dihasilkan otomatis oleh sistem SAKIP.
    </div>
</body>
</html>
