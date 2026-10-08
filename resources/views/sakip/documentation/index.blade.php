@extends('layouts.modern')

@section('title', 'Dokumentasi SAKIP')

@section('page-title', 'Dokumentasi SAKIP')

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-layout">
            <div>
                <h1 class="page-header-title">Dokumentasi SAKIP</h1>
                <p class="page-header-subtitle">Panduan penggunaan Sistem Akuntabilitas Kinerja Instansi Pemerintah</p>
            </div>
        </div>
    </div>

    <!-- Pengantar -->
    <div class="card mb-4">
        <div class="card-body">
            <h2 class="h5 fw-bold mb-3">Pengantar</h2>
            <p class="mb-0">
                Selamat datang di dokumentasi Sistem Akuntabilitas Kinerja Instansi Pemerintah (SAKIP). Dokumen ini akan memandu Anda melalui fitur-fitur utama dan fungsionalitas sistem.
            </p>
        </div>
    </div>

    <!-- Panduan Pengguna -->
    <div class="card mb-4">
        <div class="card-body">
            <h2 class="h5 fw-bold mb-3">Panduan Pengguna</h2>
            <p class="mb-0">
                Panduan ini mencakup cara menggunakan berbagai modul dalam SAKIP, mulai dari input data hingga pembuatan laporan.
            </p>
            {{-- Add more detailed user guide content here --}}
        </div>
    </div>

    <!-- FAQ -->
    <div class="card mb-4">
        <div class="card-body">
            <h2 class="h5 fw-bold mb-3">FAQ (Frequently Asked Questions)</h2>
            <p class="mb-0">
                Temukan jawaban atas pertanyaan yang sering diajukan tentang SAKIP.
            </p>
            {{-- Add FAQ content here --}}
        </div>
    </div>

    <!-- Referensi Teknis -->
    <div class="card">
        <div class="card-body">
            <h2 class="h5 fw-bold mb-3">Referensi Teknis</h2>
            <p class="mb-0">
                Informasi teknis untuk developer dan administrator sistem.
            </p>
            {{-- Add technical reference content here --}}
        </div>
    </div>
</div>
@endsection
