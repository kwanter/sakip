# Technical Documentation

This document provides technical details of the changes: helper modules, status mapping, and view integrations.

## Helpers Module (`public/js/helpers.js`)

### Exposed API
- `Helpers.toIDCurrency(value: number|string): string`
- `Helpers.stripCurrency(value: string): number`
- `Helpers.initialCurrencyFormat(input: HTMLInputElement): void`
- `Helpers.attachCurrencyInputFormatting(input: HTMLInputElement): void`
- `Helpers.sanitizeCurrencyBeforeSubmit(form: HTMLFormElement, inputIds: string[]): void`
- `Helpers.validateYearRange(startInputId: string, endInputId: string): boolean`
- `Helpers.validateDateRange(startInputId: string, endInputId: string): boolean`
- `Helpers.setStatusOptions(selectEl: HTMLSelectElement, entity: 'kegiatan'|'program'|'instansi'|'indikator'): void`

### Usage Patterns
- On page load, call `initialCurrencyFormat` for existing values and `attachCurrencyInputFormatting` for input events.
- Before form submit, call `sanitizeCurrencyBeforeSubmit` with relevant currency field IDs.
- Date/Year range validation returns `false` if invalid; use to prevent submission.

### Implementation Notes
- IIFE exposes `Helpers` on `window` to be available in Blade templates.
- Formatting uses `toLocaleString('id-ID')` for consistent Indonesian currency grouping.
- Sanitization strips non-digit characters, converting to integer numeric string before submit.

## Status Mapping

### Database Enums
- Program: `draft`, `aktif`, `selesai`
- Kegiatan: `draft`, `berjalan`, `selesai`, `tunda`
- Instansi: `aktif`, `nonaktif`
- Indikator Kinerja: `aktif`, `nonaktif`

### UI Labels
- Program: `Draft`, `Aktif`, `Selesai`
- Kegiatan: `Draft`, `Berjalan`, `Selesai`, `Tunda`
- Instansi: `Aktif`, `Non-aktif`
- Indikator Kinerja: `Aktif`, `Non-aktif`

### Filters
- Program index filters use `aktif`, `selesai`, `draft` values.
- Kegiatan index filters use `berjalan`, `selesai`, `tunda` values.
- Indikator/Instansi filters remain `aktif`/`nonaktif`.

## View Integrations

- `layouts/app.blade.php`: Includes `helpers.js` just before the closing body tag.
- Program and Kegiatan create/edit pages attach currency formatting and pre-submit sanitization.
- Status badges and filters updated to align with enum mappings.

## Validation & UX
- Numeric-only enforcement on currency inputs with graceful formatting.
- Range validation for date/year inputs; error alerts and focus shift for invalid ranges.
- Pre-submit sanitization ensures server receives clean numeric values.

## Risks & Mitigations
- Risk: Inline scripts and helpers could conflict. Mitigation: single `Helpers` namespace and id-based selectors.
- Risk: Enum drift. Mitigation: centralized `setStatusOptions` and documented mappings.

## Future Enhancements
- Extract helpers into ES modules with bundling.
- Add unit tests for helpers using Jest or similar.
- Introduce form-level validation messaging placeholders instead of alerts.