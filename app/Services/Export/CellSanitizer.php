<?php

namespace App\Services\Export;

/**
 * Single owner of the spreadsheet formula-injection invariant: a cell value
 * whose first character is = + - @ is interpreted as a formula by spreadsheet
 * engines (Excel/Calc). Prefix a single quote so it renders as literal text.
 *
 * Pinned by tests/Unit/ExportFormulaInjectionTest.php.
 */
final class CellSanitizer
{
    public static function neutralize(mixed $value): mixed
    {
        // OWASP CSV-injection guidance: spreadsheet parsers strip leading
        // whitespace before formula evaluation, so a tab/CR prefix must be
        // treated like a bare formula trigger.
        if (is_string($value) && $value !== '' && str_contains("=+-@\t\r", $value[0])) {
            return "'".$value;
        }

        return $value;
    }
}
