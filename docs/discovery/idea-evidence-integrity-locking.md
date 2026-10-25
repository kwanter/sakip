---
title: "Project Discovery & Idea Assessment: Evidence Integrity Locking (REQ-006)"
status: DRAFT (Phase 0)
date_analyzed: 2026-10-25
target_slug: evidence-integrity-locking
verdict: GO
---

# Project Discovery & Idea Assessment: Evidence Integrity Locking (REQ-006)

## 1. Problem Statement & Business Justification

SAKIP adalah sistem akuntabilitas kinerja pemerintah: `PerformanceData` yang
telah diserahkan/divalidasi adalah **rekam jepret yang diaudit**, dan
`EvidenceDocument` adalah bukti fisiknya. Hari ini alur pengumpul (collector)
dapat **menghapus seluruh bukti dari data yang sudah divalidasi secara diam-diam**:

- `DataCollectionController::update` (`app/Http/Controllers/Sakip/DataCollectionController.php:615-624`)
  menghapus **setiap** dokumen bukti yang tidak ada dalam `existing_files` —
  mengirim `existing_files: []` menghapus **semua** bukti milik data tersebut.
- `deleteEvidenceFile` (`:1008-1020`) menghapus **byte fisik dari disk segera**
  dan baris DB, di dalam transaksi yang sedang berjalan (`DB::beginTransaction`
  di `:579`) — rollback basis data tidak dapat memulihkan file yang telah dihapus.
- Tidak ada **prasyarat status**: status `submitted` dan `validated` tetap dapat
  diedit/dikosongkan buktinya oleh pengumpul (`PerformanceDataPolicy::update`
  hanya memeriksa tenant + izin edit-own + pembuat/pengirim, `app/Policies/PerformanceDataPolicy.php:38-46`).
- Tidak ada **otorisasi per dokumen**: `EvidenceDocumentPolicy::delete` mencabut
  semua orang kecuali `sakip.admin` (`:112-117`), namun penghapusan massal ini
  tidak pernah memanggilnya — kebijakan yang dibayangkan tidak pernah berjalan.
- Dua cacat kontrak turunan di jalur yang sama: `validationService` mengembalikan
  struktur yang dibaca melalui `$validationResult['has_errors']` (`:632`) yang
  dapat tidak selaras dengan hasil aktual, dan hasil validasi **gagal justru
  menetapkan `status='submitted'`** (`:635-638`) — pembalikan dari alur draft→submitted.

Dampak bisnis: integritas audit SAKIP. Pengumpul (atau sesi yang dibajak)
dapat mengosongkan jejak audit setelah validasi tanpa jejak otorisasi. Ini
satu-satunya temuan keamanan run-1 (v1.0 `REQ-006`/`DEC-005`/`DEC-006`) yang
tetap tidak diperbaiki, dan rekonfirmasi langsung ke kode saat ini menunjukkan
semua elemennya masih ada.

## 2. Technology Stack & Infrastructure Grounding

(Merujuk `docs/ARCHITECTURE.md` §2–§6.)

- **Core Framework:** Laravel 12 / PHP 8.3, Eloquent + global scope `InstansiScope`
  (default-deny multi-tenant), Spatie Permission untuk peran/kemampuan.
- **Data Layer:** MySQL (produksi) / SQLite `:memory:` (suite pengujian);
  enum status `performance_data.status` = `draft|submitted|validated|rejected`.
- **Storage:** disk privat `local` (`storage/app/private`) — bukti seharusnya
  tidak pernah diambil dari disk `public` sejak commit `e0af39a`;
  `deleteEvidenceFile` masih membersihkan kedua disk (benar untuk warisan lama).
- **Key Seams:** `DataCollectionController::update` (titik masuk publik),
  `PerformanceDataPolicy` / `EvidenceDocumentPolicy` (otorisasi), kelas
  `DataValidationService` (kontrak hasil), `AuditLog` (tetap wajib).
- **Existing regression kin:** `tests/Feature/EvidenceUpdateIsolationTest.php`
  dan `tests/Feature/EvidenceDownloadLocalOnlyTest.php` menetapkan pola
  fixture (fake storage + byte assertion) yang harus digunakan kembali.

## 3. Current Architecture Assessment & Tech Debt

- **Strengths:** kebijakan berbasis tenant dengan default-deny; `InstansiScope`
  dihormati di jalur pembaruan; suite pengujian memiliki pola fake-disk yang
  terbukti; kewajiban audit-log mapan di controller.
- **Tech Debt & Coupling Risks:**
  - **Fat Controller.** `update()` mencampur validasi + mutasi status +
    penyimpanan file + penghapusan fisik + audit log dalam ~130 baris; logika
    bisnis "bolehkah bukti ini hilang?" tidak memiliki satu pemilik kebijakan —
    itulah sebabnya REQ-006 bertahan: tidak ada celah pengujian terisolasi
    untuk menguncinya.
  - **Cleanup sebelum commit.** Penghapusan fisik di dalam transaksi basis data
    membuat DB-rollback tidak konsisten dengan storage (RBCK-004).
  - **Kontrak hasil validasi rapuh.** `has_errors` + penulisan status terbalik
    adalah bug terpisah yang menempel di jalur yang sama (dicakup di sini,
    karena memperbaiki penguncian tanpa memperbaiki status yang salah akan
    mengunci keadaan yang salah).
  - **Di luar cakupan (jangan digabung):** jalur `store()` masih menulis kolom
    yang tidak ada pada `performance_data` (`indicator_id`/`target_value`/
    `performance_percentage`) — kerusakan fungsional yang diketahui
    (rencana v2.0 RISK-808); memperbaikinya di sini akan merusak bukti RED
    slice ini dengan 500 yang tidak berhubungan.

## 4. Solution Shaping & Trade-Offs

- **Option A (Recommended) — State-gated update + authorized removal +
  after-commit cleanup, extracted to one service seam.**
  `PerformanceDataPolicy::update` menambahkan prasyarat status (`draft`/
  `rejected` dapat diedit; `submitted`/`validated` terkunci — penguncian
  menolak dengan 403 + pesan Bahasa Indonesia, dicabut hanya melalui siklus
  validasi yang sebenarnya). Penghapusan bukti menjadi metode kecil yang
  diotorisasi per dokumen (kebijakan `delete` yang ada, atau kemampuan
  eksplisit yang didelegasikan `remove draft evidence` untuk pembuat draf).
  Pembersihan file fisik terdaftar melalui `DB::afterCommit` sehingga
  kegagalan transaksi meninggalkan byte + baris yang dapat didiagnosis, dan
  kesuksesan membersihkan hanya file yang diotorisasi. Kontrak `has_errors`
  diperbaiki + status hanya maju ke `submitted` ketika validasi bersih.
  **Pro:** satu pemilik (service/kebijakan) yang dapat diuji, ukuran perbaikan
  terkecil yang menutup kelima elemen temuan, cocok untuk TDD murni
  (kebijakan + service + HTTP seams). **Con:** ekstraksi service baru (penerimaan:
  ini adalah pemisahan yang dimandatkan oleh temuan Arsitektur Bersih, bukan
  pengaturan ulang spekulatif).
- **Option B — Controller-only guards (no extraction).** Tambahkan pemeriksaan
  status + cek kebijakan inline di `update()`. **Ditolak:** memperbesar fat
  controller, meninggalkan logika bisnis tanpa celah publik terisolasi, dan
  penghapusan setelah commit inline akan menduplikasi asing yang telah
  dihapus oleh Opsi A.
- **Option C — Full workflow state-machine refactor** (mesin status formal untuk
  `PerformanceData`, termasuk memperbaiki jalur penulisan `store()` yang rusak).
  **Ditolak untuk siklus ini:** memaksimalkan nilai tetapi memperluas cakupan
  ke keputusan arsitektur alur kerja produk + kerusakan fungsional yang belum
  dipetakan (RISK-808); REQ-006 adalah perbaikan integritas yang harus
  dikirim secara sempit dan terverifikasi (pola v1.0 CON-005).

## 5. TDD Verification Hypothesis

> **Hypothesis:** Jika pengeditan bukti dibatasi status dan penghapusan
> diotorisasi + hanya-setelah-commit, maka suite dapat membuktikan bahwa data
> yang divalidasi kehilangan **nol byte bukti** melalui jalur pengumpul, dan
> bahwa penghapusan draf yang diotorisasi menghapus byte hanya setelah commit.

- **Core BDD Verification Scenario:**
  - **Given** sebuah `PerformanceData` berstatus `validated` dengan dua
    `EvidenceDocument` (byte nyata di disk privat), milik pengumpul yang
    terverifikasi dengan `edit-own-data-submissions`
  - **When** pengumpul mensubmit `PUT sakip.data-collection.update` dengan
    `existing_files: []` (dan nilai skalar yang valid)
  - **Then** respons adalah **403** (atau 422 kesalahan status) dengan pesan
    Bahasa Indonesia; **kedua byte file dan kedua baris DB tetap utuh**;
    tidak ada baris audit-log mutasi yang berhasil ditulis
  - **Dan (kontrol)** data yang sama dengan status `draft` milik pembuatnya,
    ketika satu dokumen dihapus dari `existing_files`, menghapus byte dokumen
    itu **setelah** commit; pengumpul lain dari instansi yang sama tanpa
    kepemilikan tetap ditolak.
- **Target Public Test Seam:** `tests/Feature/EvidenceUpdateIsolationTest.php`
  (diperluas — permintaan HTTP nyata, `Storage::fake('local')`, asersi byte,
  asersi baris DB mentah, pola guard-sebelum-validasi P-01);
  kebijakan yang diuji di `PerformanceDataPolicy`/`EvidenceDocumentPolicy`
  melalui rute HTTP yang sama, per rancangan v1.0 CON-004.

## 6. Domain Vocabulary (for `CONTEXT.md` — candidates, propose at PRD)

- **Bukti Terkunci** {Locked Evidence}: bukti yang melekat pada data
  `submitted`/`validated`, yang tidak dapat diubah atau dihapus oleh pengumpul;
  hanya transisi validasi (atau administrator dengan `sakip.admin`) yang dapat
  mengubahnya. (`_Avoid_: frozen evidence, readonly evidence`)
- **Status Dapat Edit** {Editable Status}: `draft` dan `rejected` — satu-satunya
  status di mana pengumpul yang memiliki hak masih dapat mengubah nilai dan
  lampiran bukti. (`_Avoid_: open status`)
- **Cleanup Setelah Commit** {Cleanup After Commit}: penghapusan file fisik
  dijadwalkan hanya setelah transaksi basis data yang memiliki berhasil
  commit; kegagalan menyimpan byte + baris yang dapat didiagnosis.
  (`_Avoid_: deferred delete, async delete`)

## 7. Handoff Notes for Product Manager (`/tdd-prd`)

- **Keputusan warisan yang disetujui (rencana v1.0, tetap mengikat):**
  DEC-005 (pengeditan hanya untuk `draft`/`rejected`), DEC-006 (pertahankan
  aturan `delete` restriktif; pembersihan hanya setelah commit; kegagalan
  harus dapat didiagnosis). PRD tidak boleh melemahkannya; menyesuaikan
  keduanya adalah perubahan produk yang membutuhkan persetujuan eksplisit.
- **Jaminan penguncian pengumpul ≠ jaminan pembacaan** — bukti `validated`
  tetap dapat diunduh oleh kepemilikan yang diotorisasi (seam unduhan sudah
  dikeraskan); PRD harus menyatakan penguncian pada **mutasi**, bukan visibilitas.
- **Jangan menambahkan enum `approved`** ke `performance_data.status`
  (skema saat ini tidak memilikinya; v1.0 CON-005 melarang memunculkan enum
  hanya demi fixture). Status `validated` adalah status kebingkaian (locking).
- **Peluang bahasa:** pesan penolakan dalam Bahasa Indonesia untuk status
  terkunci (misal, "Data telah divalidasi; bukti terkunci.") — konvensi UI
  umum proyek.
- **Risiko peluncuran (RBCK):** setelah penguncian aktif, pengumpul yang saat
  ini mengedit ulang data `submitted` akan mulai menerima 403 — inventaris
  status baca-saja terlebih dahulu jika diperlukan; tidak ada perubahan skema
  yang diperlukan untuk perbaikan ini (murni kode + kebijakan).
- **Cakupan tegasan fixture pengujian:** suite pengujian membagi koneksi
  `:memory:` (KB P-05) — asersi byte harus menggunakan konten sentinel
  unik per pengujian, dan asersi baris harus menggunakan nama sentinel.
