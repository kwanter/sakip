# SAKIP — Domain Language

The ubiquitous language of SAKIP (Sistem Akuntabilitas Kinerja Instansi Pemerintah), the Indonesian government performance-accountability system. This glossary keeps product
documents, code, tests, and user-facing copy naming one concept with one name. Terms enter here only once their meaning has been explicitly resolved.

## Language

### Cakupan & Akses

**Cakupan Instansi**:
The agency coverage of a figure or screen — either exactly one agency, or every agency in the system.
_Avoid_: tenant, tenant scope, scope instansi, wilayah

**Semua Instansi**:
The coverage label shown to a viewer entitled to figures that are not limited to any single agency.
_Avoid_: all tenants, global scope, lintas instansi, semua unit

**Instansi Belum Ditetapkan**:
The state of a viewer account that carries no agency assignment and is therefore entitled to no agency-scoped data.
_Avoid_: semua instansi, instansi kosong, instansi null, instansi global

### Periode

**Periode Pelaporan**:
The annual, quarterly, or monthly window that scopes a triage figure. A figure not limited to such a window must state that limitation on screen.
_Avoid_: periode, periode data, bulan berjalan, date range, rentang waktu

### Antrean

**Antrean Verifikasi**:
The performance-data records awaiting a decision at the verification step of the reporting workflow.
_Avoid_: inbox, pending list, tugas saya, daftar tunggu

**Antrean Asesmen**:
The assessments awaiting their first decision, under the same agency coverage as the performance data they assess.
_Avoid_: antrean lain, menunggu, pending asesmen

**Antrean Laporan**:
The reports awaiting HQ review before publication.
_Avoid_: antrean lain, menunggu, pending laporan

### Bukti & Integritas

**Bukti Terkunci**:
Evidence attached to a submitted or validated performance record; collectors cannot mutate or remove it — only a genuine validation transition (or sakip.admin authority) may change it.
_Avoid_: frozen evidence, readonly evidence, bukti beku

**Status Dapat Edit**:
The record statuses draft and rejected — the only ones in which an owning collector may still edit values and evidence attachments.
_Avoid_: open status, status terbuka

**Cleanup Setelah Commit**:
Physical file deletion scheduled only after the owning database transaction commits; a failed transaction preserves bytes and rows in a diagnosable state.
_Avoid_: deferred delete, async delete
