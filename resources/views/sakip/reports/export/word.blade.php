<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:w="urn:schemas-microsoft-com:office:word"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="utf-8">
    <title>{{ $report->title ?: 'Laporan Kinerja' }}</title>
    <!--[if gte mso 9]>
    <xml>
        <w:WordDocument>
            <w:View>Print</w:View>
            <w:Zoom>100</w:Zoom>
        </w:WordDocument>
    </xml>
    <![endif]-->
    <style>
        body { font-family: Calibri, Arial, sans-serif; font-size: 11pt; color: #0a0a0a; }
        h1 { font-size: 16pt; margin: 0 0 4pt 0; }
        h2 { font-size: 12pt; color: #4f46e5; border-bottom: 1px solid #e8e8ec; padding-bottom: 2pt; }
        .meta { color: #6b6b6b; font-size: 9pt; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #e8e8ec; padding: 4pt 6pt; font-size: 10pt; text-align: left; }
        th { background: #fafafa; color: #6b6b6b; text-transform: uppercase; font-size: 8pt; }
        .text-right { text-align: right; }
        .summary-label { color: #6b6b6b; font-size: 8pt; text-transform: uppercase; }
        .summary-value { font-size: 14pt; font-weight: bold; }
        .footer { margin-top: 16pt; color: #9c9c9c; font-size: 8pt; }
    </style>
</head>
<body>
    @php
        $year = \Carbon\Carbon::parse($report->period)->year;
        $summary = $data['summary'] ?? [];
        $instansiName = $report->instansi->nama_instansi ?? '-';
    @endphp

    <h1>{{ $report->title ?: 'Laporan Kinerja' }}</h1>
    <p class="meta">
        {{ $instansiName }} &middot; Periode {{ $report->period }} &middot;
        Dibuat {{ now()->translatedFormat('d F Y H:i') }}
    </p>

    <h2>Ringkasan</h2>
    <table>
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

    <p class="footer">Dokumen ini dihasilkan otomatis oleh sistem SAKIP.</p>
</body>
</html>
