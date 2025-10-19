@extends('layouts.app')

@section('title', 'Pusat Bantuan')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Pusat Bantuan</h4>
                </div>
                <div class="card-body">
                    <h5>Selamat datang di Pusat Bantuan SAKIP</h5>
                    <p>Halaman ini berisi informasi bantuan untuk menggunakan aplikasi SAKIP.</p>

                    <div class="mt-4">
                        <h6>Panduan Penggunaan</h6>
                        <ul>
                            <li>Dashboard - Melihat ringkasan kinerja dan statistik</li>
                            <li>Indikator Kinerja - Mengelola indikator kinerja</li>
                            <li>Data Kinerja - Mengelola data kinerja</li>
                            <li>Laporan - Melihat dan mengunduh laporan</li>
                        </ul>
                    </div>

                    <div class="mt-4">
                        <h6>Kontak Dukungan</h6>
                        <p>Jika Anda memerlukan bantuan lebih lanjut, silakan hubungi tim dukungan kami:</p>
                        <ul>
                            <li>Email: support@sakip.example.com</li>
                            <li>Telepon: (021) 123-4567</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
