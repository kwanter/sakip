@extends('layouts.app')

@section('title', 'Kebijakan Privasi')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h1 class="text-2xl font-semibold text-gray-800 mb-6">Kebijakan Privasi</h1>

                <div class="sakip-card mb-6">
                    <div class="p-6">
                        <p class="text-gray-600">
                            Selamat datang di Kebijakan Privasi SAKIP. Kami menghargai privasi Anda dan berkomitmen untuk melindungi informasi pribadi Anda. Kebijakan ini menjelaskan bagaimana kami mengumpulkan, menggunakan, dan melindungi data Anda.
                        </p>
                    </div>
                </div>

                <div class="sakip-card mb-6">
                    <div class="p-6">
                        <h2 class="text-xl font-semibold text-gray-700 mb-4">Informasi yang Kami Kumpulkan</h2>
                        <p class="text-gray-600">
                            Kami dapat mengumpulkan beberapa jenis informasi, termasuk:
                        </p>
                        <ul class="list-disc list-inside text-gray-600">
                            <li><strong>Informasi Pribadi:</strong> Nama, alamat email, NIP, dan informasi kontak lainnya saat Anda mendaftar atau menggunakan layanan kami.</li>
                            <li><strong>Data Kinerja:</strong> Data terkait indikator kinerja, target, realisasi, dan dokumen pendukung yang Anda unggah.</li>
                            <li><strong>Data Penggunaan:</strong> Informasi tentang bagaimana Anda mengakses dan menggunakan platform, termasuk alamat IP, jenis browser, dan waktu akses.</li>
                        </ul>
                    </div>
                </div>

                <div class="sakip-card mb-6">
                    <div class="p-6">
                        <h2 class="text-xl font-semibold text-gray-700 mb-4">Bagaimana Kami Menggunakan Informasi Anda</h2>
                        <p class="text-gray-600">
                            Informasi yang kami kumpulkan digunakan untuk:
                        </p>
                        <ul class="list-disc list-inside text-gray-600">
                            <li>Menyediakan, mengoperasikan, dan memelihara layanan SAKIP.</li>
                            <li>Meningkatkan, mempersonalisasi, dan memperluas layanan kami.</li>
                            <li>Memahami dan menganalisis bagaimana Anda menggunakan platform kami.</li>
                            <li>Mengembangkan produk, layanan, fitur, dan fungsionalitas baru.</li>
                            <li>Berkomunikasi dengan Anda, baik secara langsung maupun melalui mitra kami, termasuk untuk layanan pelanggan, pembaruan, dan informasi lainnya.</li>
                        </ul>
                    </div>
                </div>

                <div class="sakip-card mb-6">
                    <div class="p-6">
                        <h2 class="text-xl font-semibold text-gray-700 mb-4">Keamanan Data</h2>
                        <p class="text-gray-600">
                            Kami menerapkan berbagai langkah keamanan untuk melindungi informasi Anda. Namun, tidak ada metode transmisi melalui internet atau metode penyimpanan elektronik yang 100% aman. Kami berusaha keras untuk melindungi data Anda, tetapi kami tidak dapat menjamin keamanan mutlak.
                        </p>
                    </div>
                </div>

                <div class="sakip-card">
                    <div class="p-6">
                        <h2 class="text-xl font-semibold text-gray-700 mb-4">Perubahan pada Kebijakan Privasi</h2>
                        <p class="text-gray-600">
                            Kami dapat memperbarui Kebijakan Privasi ini dari waktu ke waktu. Kami akan memberi tahu Anda tentang perubahan apa pun dengan memposting kebijakan baru di halaman ini. Anda disarankan untuk meninjau Kebijakan Privasi ini secara berkala untuk setiap perubahan.
                        </p>
                        <p class="text-sm text-gray-500 mt-4">
                            Terakhir diperbarui: {{ now()->format('d F Y') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
