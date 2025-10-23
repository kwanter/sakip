# Diagnosis: Form "Tambah Indikator Kinerja" - Tidak Berfungsi dengan Baik

**Date**: December 2024  
**Status**: 🔴 CRITICAL - Form memiliki ketidaksesuaian field yang signifikan  
**Priority**: URGENT

---

## 📋 Ringkasan Masalah

Form "Tambah Indikator Kinerja" memiliki banyak field yang tidak sesuai dengan:
1. Database schema (tabel `performance_indicators`)
2. Form Request validation rules (`StorePerformanceIndicatorRequest.php`)
3. Controller yang mengirim data ke view
4. Business logic SAKIP yang sebenarnya

---

## 🔍 Masalah Detail

### 1. ❌ KATEGORI TIDAK SESUAI (CRITICAL)

**Status**: Tidak sesuai antara form dan controller

| Lokasi | Nilai Kategori | Status |
|--------|-----------------|--------|
| Controller (`getCategories()`) | input, output, outcome, impact | ✅ Benar |
| Form View (create.blade.php L125-140) | iku, ikk, ikt, iks | ❌ SALAH |
| StorePerformanceIndicatorRequest | input, output, outcome, impact | ✅ Benar |
| Database Validation | input, output, outcome, impact | ✅ Benar |

**Dampak**: 
- Form validation akan gagal jika user memilih kategori dari dropdown
- Data tidak akan tersimpan dengan benar
- UI menampilkan kategori yang tidak sesuai dengan dokumentasi

**Solusi**: Update dropdown kategori di form untuk menampilkan opsi yang benar

---

### 2. ❌ FIELD YANG TIDAK ADA DI DATABASE (CRITICAL)

**Status**: Overly complex form dengan field yang tidak sesuai schema

#### A. Field `department_id` (Line 113-159)
```blade
<select id="department_id" name="department_id" required>
    <option value="1">Dinas Kesehatan</option>
    <option value="2">Dinas Pendidikan</option>
    ... (hardcoded values)
</select>
```
- ❌ Tidak ada di database schema
- ❌ Tidak ada di StorePerformanceIndicatorRequest
- ❌ Tidak ada di controller
- ⚠️ Tidak ada di PerformanceIndicator model
- 💡 Seharusnya menggunakan `instansi_id` (sudah ada)

**Solusi**: Hapus field ini, gunakan `instansi_id` yang sudah ada

---

#### B. Field `year` (Line 161-175)
```blade
<select id="year" name="year" required>
    <option value="">Pilih Tahun</option>
    @for($year = date('Y') + 1; $year >= date('Y') - 5; $year--)
    ...
</select>
```
- ❌ Tidak ada di database schema `performance_indicators`
- ❌ Tidak ada di StorePerformanceIndicatorRequest
- 💡 Tahun seharusnya ada di `performance_data` table, bukan `performance_indicators`
- 🔗 Indikator bersifat umum, tahun spesifik adalah ketika data dientry

**Solusi**: Hapus field ini. Tahun ditangani di modul "Pengumpulan Data" (performance_data)

---

#### C. Fields Target & Formula (Lines 229-300)
```blade
- target_value (Line 234-245)
- target_type (Line 247-262)
- target_direction (Line 264-280)
- baseline_value (Line 289-299)
- baseline_year (Line 301-312)
```
- ❌ Tidak ada di `performance_indicators` table
- ✅ Ada di `targets` table
- 💡 Targets adalah data terpisah yang di-link ke performance_indicators
- 🔗 Relasi: performance_indicators → targets (one-to-many)

**Solusi**: Hapus dari form create, tangani di "Manage Targets" atau step tambahan

---

#### D. Fields Perhitungan (Lines 314-352)
```blade
- numerator (Line 320-325)
- denominator (Line 327-333)
- calculation_method (Line 335-342)
```
- ⚠️ `numerator` dan `denominator` tidak ada di schema
- ✅ Ada `calculation_formula` di schema (singular)
- ❌ `calculation_method` bukan nama field yang benar

**Solusi**: 
- Gunakan hanya `calculation_formula` (optional)
- Hapus `numerator` dan `denominator`

---

#### E. Fields Validasi Tambahan (Lines 360-380)
```blade
- validation_frequency (Line 365-380)
- responsible_person (Line 382-389)
```
- ❌ Tidak ada di schema
- ⚠️ Ini adalah concern operasional, bukan konfigurasi indikator

**Solusi**: Hapus atau pindahkan ke modul pengelolaan yang berbeda

---

#### F. Fields Strategic Linkage (Lines 192-220)
```blade
- sasaran_strategis_id (Line 202-211)
- program_id (Line 213-222)
```
- ⚠️ Tidak ada di current schema `performance_indicators`
- 🤔 Berguna untuk linking tapi memerlukan relationship baru atau model relasi
- 💾 Seharusnya ada di tabel yang berbeda atau metadata

**Solusi**: 
- Option 1: Hapus untuk MVP
- Option 2: Tambahkan kolom ke database jika diperlukan bisnis

---

### 3. ❌ FIELD YANG DIPERLUKAN TAPI TIDAK ADA (CRITICAL)

#### A. `measurement_unit` (REQUIRED by validation)
- ✅ Diperlukan di StorePerformanceIndicatorRequest
- ❌ **TIDAK ADA DI FORM**
- 💥 Akan gagal validasi setiap kali form disubmit

**Dampak**: Form tidak akan pernah bisa disimpan dengan sukses

**Solusi**: Tambahkan field ini ke Step 1

---

#### B. `frequency` (REQUIRED by validation)
- ✅ Diperlukan di StorePerformanceIndicatorRequest
- ✅ Ada di controller `getFrequencies()`
- ❌ **TIDAK DITAMPILKAN DI FORM**

**Solusi**: Tambahkan field ini ke Step 1 atau Step 2

---

#### C. `collection_method` (REQUIRED by validation)
- ✅ Diperlukan di StorePerformanceIndicatorRequest
- ✅ Ada di controller `getCollectionMethods()`
- ❌ **TIDAK DITAMPILKAN DI FORM**

**Solusi**: Tambahkan field ini ke Step 1 atau Step 2

---

#### D. `weight` (REQUIRED by validation)
- ✅ Diperlukan di StorePerformanceIndicatorRequest
- ❌ **TIDAK ADA DI FORM**

**Solusi**: Tambahkan field ini ke Step 2 (Target & Formula)

---

### 4. ⚠️ FIELD OPTIONAL YANG ADA

| Field | Status | Catatan |
|-------|--------|---------|
| `description` | ✅ Ada | Optional, sudah benar |
| `calculation_formula` | ❌ Ada tapi tidak lengkap | Diberi nama `calculation_method` di form |
| `is_mandatory` | ❌ Tidak ada | Optional di validation |
| `metadata` | ❌ Tidak ada | Optional untuk custom fields |

---

### 5. ❌ JAVASCRIPT FUNCTIONS YANG BERMASALAH

#### `updateCategoryDescription()` (Line 1109-1121)
```javascript
function updateCategoryDescription() {
    const category = document.getElementById('category').value;
    const descriptions = {
        'iku': 'IKU (Indikator Kinerja Utama)',
        'ikk': 'IKK (Indikator Kinerja Kegiatan)',
        'ikt': 'IKT (Indikator Kinerja Turunan)',
        'iks': 'IKS (Indikator Kinerja Strategis)'
    };
    // ...
}
```
- ❌ Menggunakan kategori yang salah (iku, ikk, ikt, iks)
- ✅ Seharusnya menggunakan (input, output, outcome, impact)

**Solusi**: Update descriptions dan mapping

---

#### `updateTargetFields()` (Line 1124-1135)
```javascript
function updateTargetFields() {
    const targetType = document.getElementById('target_type').value;
    // ...
}
```
- ⚠️ Mengontrol field yang seharusnya tidak ada (target_value, baseline_value, dll)

**Solusi**: Hapus fungsi ini atau sesuaikan dengan field yang benar

---

## 📊 Form Fields Mapping: Seharusnya vs Sekarang

### ✅ BENAR (Sesuai dengan requirement)

| Field | Database | Validation | Form | Status |
|-------|----------|------------|------|--------|
| code | ✅ | ✅ | ✅ | BENAR |
| name | ✅ | ✅ | ✅ | BENAR |
| description | ✅ | ✅ | ✅ | BENAR |
| instansi_id | ✅ | ✅ | ✅ | BENAR |
| data_source | ✅ | ✅ | ✅ | BENAR |

### ❌ SALAH/MISSING

| Field | Database | Validation | Form | Status | Aktion |
|-------|----------|------------|------|--------|--------|
| category | ✅ | ✅ | ❌ (wrong options) | SALAH | Update opsi |
| measurement_unit | ✅ | ✅ | ❌ MISSING | MISSING | Tambah field |
| frequency | ✅ | ✅ | ❌ MISSING | MISSING | Tambah field |
| collection_method | ✅ | ✅ | ❌ MISSING | MISSING | Tambah field |
| weight | ✅ | ✅ | ❌ MISSING | MISSING | Tambah field |
| calculation_formula | ✅ | ✅ | ⚠️ wrong name | SALAH | Rename field |
| department_id | ❌ | ❌ | ✅ | SALAH | Hapus |
| year | ❌ | ❌ | ✅ | SALAH | Hapus |
| target_value | ❌ | ❌ | ✅ | SALAH | Hapus |
| target_type | ❌ | ❌ | ✅ | SALAH | Hapus |
| target_direction | ❌ | ❌ | ✅ | SALAH | Hapus |
| baseline_value | ❌ | ❌ | ✅ | SALAH | Hapus |
| baseline_year | ❌ | ❌ | ✅ | SALAH | Hapus |
| numerator | ❌ | ❌ | ✅ | SALAH | Hapus |
| denominator | ❌ | ❌ | ✅ | SALAH | Hapus |
| calculation_method | ❌ | ❌ | ✅ | SALAH | Hapus |
| validation_frequency | ❌ | ❌ | ✅ | SALAH | Hapus |
| responsible_person | ❌ | ❌ | ✅ | SALAH | Hapus |
| sasaran_strategis_id | ⚠️ | ❌ | ✅ | SALAH | Hapus/Discuss |
| program_id | ⚠️ | ❌ | ✅ | SALAH | Hapus/Discuss |

---

## 🛠️ RENCANA PERBAIKAN

### Phase 1: CRITICAL (Harus dilakukan sekarang)

1. **Fix Kategori Dropdown**
   - Ubah opsi dari (iku, ikk, ikt, iks) ke (input, output, outcome, impact)
   - Update deskripsi kategori di JavaScript

2. **Hapus Field Tidak Perlu (Step 1)**
   - Hapus `department_id` (duplikat dengan `instansi_id`)
   - Hapus `year` (bukan untuk create indikator)
   - Hapus `sasaran_strategis_id` dan `program_id` (untuk fase 2)

3. **Hapus Field Tidak Perlu (Step 2)**
   - Hapus semua target fields (target_value, target_type, etc.)
   - Hapus numerator/denominator
   - Hapus validation_frequency dan responsible_person

4. **Tambah Field Missing**
   - Tambah `measurement_unit` (Step 1)
   - Tambah `frequency` (Step 1 atau 2)
   - Tambah `collection_method` (Step 1 atau 2)
   - Tambah `weight` (Step 2)

5. **Fix Field Names**
   - Ubah `calculation_method` menjadi `calculation_formula`

6. **Update JavaScript**
   - Hapus/update `updateCategoryDescription()` dengan kategori yang benar
   - Hapus `updateTargetFields()` jika field target dihapus

### Phase 2: Enhancements (Fase berikutnya)

1. Target Management
   - Buat form/section terpisah untuk manage targets
   - Implement after indicator created

2. Strategic Linkage
   - Tambah fields untuk link dengan strategic objectives
   - Implement dengan proper relationship design

3. Bulk Import
   - Implementasi fitur bulk import dari Excel

---

## 📝 Struktur Form yang Benar (Proposed)

### Step 1: Informasi Dasar Indikator
- [ ] Kode Indikator (code)
- [ ] Nama Indikator (name)
- [ ] Kategori (category) - FIXED OPTIONS: input, output, outcome, impact
- [ ] Unit Kerja / Instansi (instansi_id)
- [ ] Deskripsi (description)
- [ ] Unit Pengukuran (measurement_unit) - ADDED
- [ ] Frekuensi Pengumpulan (frequency) - ADDED
- [ ] Metode Pengumpulan (collection_method) - ADDED

### Step 2: Formula & Data Source
- [ ] Sumber Data (data_source)
- [ ] Formula Perhitungan (calculation_formula)
- [ ] Bobot/Weight (weight) - ADDED
- [ ] Mandatory? (is_mandatory)

### Step 3: Validasi & Dokumen
- [ ] Dokumen Pendukung
- [ ] Catatan Tambahan

### Step 4: Review & Submit
- [ ] Preview semua data
- [ ] Submit

---

## 🧪 Testing Checklist

Setelah perbaikan, test hal-hal berikut:

- [ ] Form dapat dimuat tanpa error
- [ ] Semua dropdown berisi opsi yang benar
- [ ] Dropdown kategori menampilkan (input, output, outcome, impact)
- [ ] Field `measurement_unit` ada dan required
- [ ] Field `frequency` ada dan required
- [ ] Field `collection_method` ada dan required
- [ ] Field `weight` ada dan required
- [ ] Form dapat disubmit dengan data valid
- [ ] Tidak ada validation error untuk field yang hilang
- [ ] Data tersimpan dengan benar di database
- [ ] Redirect ke detail page setelah sukses
- [ ] Success message ditampilkan
- [ ] Audit log mencatat aksi create

---

## 📚 Referensi

- Database Schema: `migrations/*performance_indicators*`
- Form Request: `app/Http/Requests/Sakip/StorePerformanceIndicatorRequest.php`
- Controller: `app/Http/Controllers/Sakip/PerformanceIndicatorController.php`
- Form View: `resources/views/sakip/indicators/create.blade.php`
- Documentation: `INDIKATOR_KINERJA_FEATURE.md`

---

**Status**: 🔴 CRITICAL - Memerlukan perbaikan mendesak  
**Next Action**: Implementasi Phase 1 fixes