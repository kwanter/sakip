@extends('sakip.layouts.app')

@section('title', 'Dashboard Pengumpul Data - SAKIP')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Dashboard Pengumpul Data</h1>
                    <p class="mt-2 text-gray-600">Kelola dan input data kinerja anda</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('sakip.data-collection.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-800 text-white text-sm font-medium rounded-md hover:bg-blue-900 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Tambah Data
                    </a>
                </div>
            </div>
        </div>

        <!-- Alert Notifications -->
        <div class="mb-6">
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Informasi</h3>
                        <div class="mt-1 text-sm text-blue-700">
                            <p>Anda memiliki 5 indikator yang belum diisi data untuk periode ini.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Data</p>
                        <p class="text-2xl font-semibold text-gray-900">24</p>
                        <p class="text-sm text-gray-600">Data terinput</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Tervalidasi</p>
                        <p class="text-2xl font-semibold text-gray-900">18</p>
                        <p class="text-sm text-green-600">75% dari total</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-100 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Menunggu</p>
                        <p class="text-2xl font-semibold text-gray-900">4</p>
                        <p class="text-sm text-yellow-600">Perlu validasi</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-red-100 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Perlu Revisi</p>
                        <p class="text-2xl font-semibold text-gray-900">2</p>
                        <p class="text-sm text-red-600">Butuh perbaikan</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Data Collection -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Indikator Belum Diisi</h3>
                <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">5 perlu diisi</span>
            </div>
            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                    <div class="flex items-center">
                        <div class="w-2 h-2 bg-red-500 rounded-full mr-3"></div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">IK.01 - Persentase pelayanan publik tervalidasi</p>
                            <p class="text-xs text-gray-500">Periode: Triwulan IV 2024 | Tenggat: 30 Des 2024</p>
                        </div>
                    </div>
                    <a href="{{ route('sakip.data-collection.create') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Input Data</a>
                </div>
                
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                    <div class="flex items-center">
                        <div class="w-2 h-2 bg-red-500 rounded-full mr-3"></div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">IK.02 - Jumlah program unggulan terlaksana</p>
                            <p class="text-xs text-gray-500">Periode: Triwulan IV 2024 | Tenggat: 30 Des 2024</p>
                        </div>
                    </div>
                    <a href="{{ route('sakip.data-collection.create') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Input Data</a>
                </div>
                
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                    <div class="flex items-center">
                        <div class="w-2 h-2 bg-red-500 rounded-full mr-3"></div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">IK.03 - Tingkat kepuasan masyarakat</p>
                            <p class="text-xs text-gray-500">Periode: Triwulan IV 2024 | Tenggat: 30 Des 2024</p>
                        </div>
                    </div>
                    <a href="{{ route('sakip.data-collection.create') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Input Data</a>
                </div>
            </div>
        </div>

        <!-- Recent Data Collection -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Data Terakhir Diinput</h3>
                <a href="{{ route('sakip.data-collection.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Lihat Semua</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Indikator</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">IK.15</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Persentase kehadiran pegawai</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">95.2%</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Tervalidasi</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2 jam lalu</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="#" class="text-blue-600 hover:text-blue-800">Detail</a>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">IK.14</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Jumlah kegiatan yang dilaksanakan</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">24</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">1 hari lalu</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="#" class="text-blue-600 hover:text-blue-800">Detail</a>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">IK.13</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Angka kepuasan masyarakat</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">4.2</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Perlu Revisi</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">3 hari lalu</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="#" class="text-blue-600 hover:text-blue-800">Revisi</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Aksi Cepat</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <a href="{{ route('sakip.data-collection.create') }}" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-blue-900">Input Data Baru</p>
                        <p class="text-xs text-blue-600">Tambah data kinerja</p>
                    </div>
                </a>
                
                <a href="#" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-900">Impor Excel</p>
                        <p class="text-xs text-green-600">Upload data massal</p>
                    </div>
                </a>
                
                <a href="#" class="flex items-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-orange-900">Ekspor Data</p>
                        <p class="text-xs text-orange-600">Unduh laporan</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@stop

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add any specific data collector dashboard functionality here
    console.log('Data collector dashboard loaded');
});
</script>
@stop