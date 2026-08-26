<?php

namespace Tests\Unit;

use App\Services\Export\ExcelExportService;
use PHPUnit\Framework\TestCase;

/**
 * SECURITY: spreadsheet export must neutralize formula injection
 * (=SUM, +cmd, -2+3, @SUM on leading characters).
 */
class ExportFormulaInjectionTest extends TestCase
{
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
}
