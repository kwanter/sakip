@extends('sakip.layouts.app')

@section('title', 'Edit Indikator Kinerja')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center">
                <a href="{{ route('sakip.indicators.index') }}" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h1 class="ml-4 text-3xl font-bold text-gray-900">Edit Indikator Kinerja</h1>
            </div>
            <p class="mt-2 text-gray-600">Perbarui informasi indikator kinerja</p>
        </div>

        <!-- Indicator Form -->
        <form action="{{ route('sakip.indicators.update', $indicator) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <!-- Basic Information -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Dasar</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 mb-2">Kode Indikator *</label>
                        <input type="text" name="code" id="code" value="{{ old('code', $indicator->code) }}" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama Indikator *</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $indicator->name) }}" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Kategori *</label>
                        <select name="category" id="category" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Pilih Kategori</option>
                            <option value="kinerja_utama" {{ old('category', $indicator->category) == 'kinerja_utama' ? 'selected' : '' }}>Kinerja Utama</option>
                            <option value="kinerja_tambahan" {{ old('category', $indicator->category) == 'kinerja_tambahan' ? 'selected' : '' }}>Kinerja Tambahan</option>
                            <option value="keuangan" {{ old('category', $indicator->category) == 'keuangan' ? 'selected' : '' }}>Keuangan</option>
                            <option value="pelayanan_publik" {{ old('category', $indicator->category) == 'pelayanan_publik' ? 'selected' : '' }}>Pelayanan Publik</option>
                            <option value="sumber_daya_manusia" {{ old('category', $indicator->category) == 'sumber_daya_manusia' ? 'selected' : '' }}>Sumber Daya Manusia</option>
                            <option value="inovasi" {{ old('category', $indicator->category) == 'inovasi' ? 'selected' : '' }}>Inovasi</option>
                        </select>
                        @error('category')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="unit" class="block text-sm font-medium text-gray-700 mb-2">Satuan *</label>
                        <input type="text" name="unit" id="unit" value="{{ old('unit', $indicator->unit) }}" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('unit')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="instansi_id" class="block text-sm font-medium text-gray-700 mb-2">Instansi *</label>
                        <select name="instansi_id" id="instansi_id" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Pilih Instansi</option>
                            @foreach($instansis as $instansi)
                                <option value="{{ $instansi->id }}" {{ old('instansi_id', $indicator->instansi_id) == $instansi->id ? 'selected' : '' }}>{{ $instansi->name }}</option>
                            @endforeach
                        </select>
                        @error('instansi_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="frequency" class="block text-sm font-medium text-gray-700 mb-2">Frekuensi Pengukuran *</label>
                        <select name="frequency" id="frequency" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Pilih Frekuensi</option>
                            <option value="bulanan" {{ old('frequency', $indicator->frequency) == 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                            <option value="triwulan" {{ old('frequency', $indicator->frequency) == 'triwulan' ? 'selected' : '' }}>Triwulan</option>
                            <option value="semester" {{ old('frequency', $indicator->frequency) == 'semester' ? 'selected' : '' }}>Semester</option>
                            <option value="tahunan" {{ old('frequency', $indicator->frequency) == 'tahunan' ? 'selected' : '' }}>Tahunan</option>
                        </select>
                        @error('frequency')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <div class="mt-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                    <textarea name="description" id="description" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description', $indicator->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Target Settings -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Pengaturan Target</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="target_value" class="block text-sm font-medium text-gray-700 mb-2">Nilai Target *</label>
                        <input type="number" name="target_value" id="target_value" step="0.01" value="{{ old('target_value', $indicator->target_value) }}" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('target_value')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="target_year" class="block text-sm font-medium text-gray-700 mb-2">Tahun Target *</label>
                        <select name="target_year" id="target_year" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @for($year = date('Y') - 2; $year <= date('Y') + 2; $year++)
                                <option value="{{ $year }}" {{ old('target_year', $indicator->target_year) == $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endfor
                        </select>
                        @error('target_year')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Assessment Criteria -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Kriteria Penilaian</h3>
                
                <div id="criteria-container">
                    @foreach($indicator->criteria as $index => $criterion)
                    <div class="criteria-item border border-gray-200 rounded-md p-4 mb-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Kriteria</label>
                                <input type="text" name="criteria[{{ $index }}][name]" value="{{ $criterion->name }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Nama kriteria">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Bobot (%)</label>
                                <input type="number" name="criteria[{{ $index }}][weight]" value="{{ $criterion->weight }}" min="0" max="100" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="0-100">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                                <input type="text" name="criteria[{{ $index }}][description]" value="{{ $criterion->description }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Deskripsi kriteria">
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <button type="button" id="add-criteria" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Tambah Kriteria
                </button>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between">
                <a href="{{ route('sakip.indicators.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Batal
                </a>
                
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-800 text-white text-sm font-medium rounded-md hover:bg-blue-900 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Perbarui Indikator
                </button>
            </div>
        </form>
    </div>
</div>
@stop

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let criteriaCount = {{ $indicator->criteria->count() }};
    
    document.getElementById('add-criteria').addEventListener('click', function() {
        const container = document.getElementById('criteria-container');
        const newCriteria = document.createElement('div');
        newCriteria.className = 'criteria-item border border-gray-200 rounded-md p-4 mb-4';
        newCriteria.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Kriteria</label>
                    <input type="text" name="criteria[${criteriaCount}][name]" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Nama kriteria">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bobot (%)</label>
                    <input type="number" name="criteria[${criteriaCount}][weight]" min="0" max="100" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="0-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                    <input type="text" name="criteria[${criteriaCount}][description]" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Deskripsi kriteria">
                </div>
            </div>
        `;
        container.appendChild(newCriteria);
        criteriaCount++;
    });
});
</script>
@stop