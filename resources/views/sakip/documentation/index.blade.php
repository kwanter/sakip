@extends('layouts.app')

@section('title', 'Dokumentasi SAKIP')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h1 class="text-2xl font-semibold text-gray-800 mb-6">Dokumentasi SAKIP</h1>

                <div class="sakip-card mb-6">
                    <div class="p-6">
                        <h2 class="text-xl font-semibold text-gray-700 mb-4">Pengantar</h2>
                        <p class="text-gray-600">
                            Selamat datang di dokumentasi Sistem Akuntabilitas Kinerja Instansi Pemerintah (SAKIP). Dokumen ini akan memandu Anda melalui fitur-fitur utama dan fungsionalitas sistem.
                        </p>
                    </div>
                </div>

                <div class="sakip-card mb-6">
                    <div class="p-6">
                        <h2 class="text-xl font-semibold text-gray-700 mb-4">Panduan Pengguna</h2>
                        <p class="text-gray-600">
                            Panduan ini mencakup cara menggunakan berbagai modul dalam SAKIP, mulai dari input data hingga pembuatan laporan.
                        </p>
                        {{-- Add more detailed user guide content here --}}
                    </div>
                </div>

                <div class="sakip-card mb-6">
                    <div class="p-6">
                        <h2 class="text-xl font-semibold text-gray-700 mb-4">FAQ (Frequently Asked Questions)</h2>
                        <p class="text-gray-600">
                            Temukan jawaban atas pertanyaan yang sering diajukan tentang SAKIP.
                        </p>
                        {{-- Add FAQ content here --}}
                    </div>
                </div>

                <div class="sakip-card">
                    <div class="p-6">
                        <h2 class="text-xl font-semibold text-gray-700 mb-4">Referensi Teknis</h2>
                        <p class="text-gray-600">
                            Informasi teknis untuk developer dan administrator sistem.
                        </p>
                        {{-- Add technical reference content here --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
