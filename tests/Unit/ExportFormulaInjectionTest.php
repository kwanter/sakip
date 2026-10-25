<?php

namespace Tests\Unit;

use App\Models\PerformanceIndicator;
use App\Models\Report;
use App\Services\Export\ExcelExportService;
use App\Services\ReportGenerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

/**
 * SECURITY: spreadsheet export must neutralize formula injection
 * (=SUM, +cmd, -2+3, @SUM on leading characters).
 */
class ExportFormulaInjectionTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = false;

    private function sanitize(array $rows): array
    {
        // Both services implement the same protected sanitizeCells(array): array.
        $m = new \ReflectionMethod(ExcelExportService::class, 'sanitizeCells');

        return $m->invoke(app(ExcelExportService::class), $rows);
    }

    public function test_formula_prefixes_are_neutralized()
    {
        $rows = $this->sanitize([
            ['name' => '=HYPERLINK("http://evil")', 'value' => '100'],
            ['name' => '+cmd|2+5', 'value' => '@SUM(A1)'],
            ['name' => '-2+3', 'value' => 'normal'],
        ]);

        $this->assertSame(chr(39).'=HYPERLINK("http://evil")', $rows[0]['name']);
        $this->assertSame('100', $rows[0]['value']);
        $this->assertSame(chr(39).'+cmd|2+5', $rows[1]['name']);
        $this->assertSame(chr(39).'@SUM(A1)', $rows[1]['value']);
        $this->assertSame(chr(39).'-2+3', $rows[2]['name']);
    }

    public function test_normal_text_untouched()
    {
        $rows = $this->sanitize([['name' => 'achievement', 'value' => '50%']]);
        $this->assertSame('achievement', $rows[0]['name']);
        $this->assertSame('50%', $rows[0]['value']);
    }

    /**
     * SECURITY (review STD-11, OWASP CSV-injection guidance): spreadsheet
     * parsers strip leading whitespace before formula evaluation, so a
     * tab/CR-prefixed payload must be neutralized like a bare formula.
     */
    public function test_tab_and_carriage_return_prefixes_are_neutralized()
    {
        $rows = $this->sanitize([
            ['name' => "\t=HYPERLINK(\"http://evil\")", 'value' => "\r=1+1"],
        ]);

        $this->assertSame(chr(39)."\t=HYPERLINK(\"http://evil\")", $rows[0]['name']);
        $this->assertSame(chr(39)."\r=1+1", $rows[0]['value']);
    }

    /**
     * SECURITY (audit run-2, ReportGenerationService formula cells): the
     * report Excel export writes the user-controlled report title and
     * indicator code/name; they must be stored as literal strings, never
     * as active formulas in the generated workbook.
     */
    public function test_report_generation_neutralizes_user_controlled_cells()
    {
        Storage::fake('local');

        // Minimal manual rows: ReportFactory/PerformanceIndicatorFactory carry
        // drifted columns absent from the current schema.
        $report = Report::create([
            'instansi_id' => \App\Models\Instansi::factory()->create()->id,
            'generated_by' => \App\Models\User::factory()->create()->id,
            'report_type' => 'annual',
            'period' => '2026',
            'status' => 'completed',
            'title' => '=1+1',
        ]);
        $indicator = PerformanceIndicator::create([
            'instansi_id' => $report->instansi_id,
            'code' => '=SUM(A1:A9)',
            'name' => '=HYPERLINK("http://audit.invalid")',
            'measurement_unit' => 'unit',
            'frequency' => 'annual',
            'category' => 'output',
            'created_by' => null,
            'updated_by' => null,
        ]);
        $report->indicators()->attach($indicator->id);

        $path = app(ReportGenerationService::class)
            ->generateExcelFile($report->fresh(), ['summary' => []]);

        $reloaded = IOFactory::load(Storage::path($path));
        $sheet = $reloaded->getActiveSheet();

        $this->assertSame(DataType::TYPE_STRING, $sheet->getCell('A1')->getDataType(), 'report title must be literal text');
        $this->assertSame(DataType::TYPE_STRING, $sheet->getCell('A12')->getDataType(), 'indicator code must be literal text');
        $this->assertSame(DataType::TYPE_STRING, $sheet->getCell('B12')->getDataType(), 'indicator name must be literal text');
    }
}
