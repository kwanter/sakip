@extends('layouts.app')

@section('title', 'Aksesibilitas')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h1 class="text-2xl font-semibold text-gray-800 mb-6">Pernyataan Aksesibilitas</h1>

                <div class="sakip-card mb-6">
                    <div class="p-6">
                        <p class="text-gray-600">
                            Kami berkomitmen untuk memastikan aksesibilitas digital untuk semua orang, termasuk penyandang disabilitas. Kami terus meningkatkan pengalaman pengguna untuk semua orang dan menerapkan standar aksesibilitas yang relevan.
                        </p>
                    </div>
                </div>

                <div class="sakip-card mb-6">
                    <div class="p-6">
                        <h2 class="text-xl font-semibold text-gray-700 mb-4">Upaya Kami</h2>
                        <p class="text-gray-600">
                            Upaya kami untuk mendukung aksesibilitas meliputi:
                        </p>
                        <ul class="list-disc list-inside text-gray-600">
                            <li>Menyertakan teks alternatif untuk semua gambar.</li>
                            <li>Memastikan semua fungsionalitas dapat diakses melalui keyboard.</li>
                            <li>Menjaga rasio kontras warna yang memadai.</li>
                            <li>Menggunakan markup HTML semantik untuk membantu teknologi pendukung.</li>
                        </ul>
                    </div>
                </div>

                <div class="sakip-card">
                    <div class="p-6">
                        <h2 class="text-xl font-semibold text-gray-700 mb-4">Umpan Balik</h2>
                        <p class="text-gray-600">
                            Kami menyambut umpan balik Anda tentang aksesibilitas platform SAKIP. Jika Anda mengalami hambatan aksesibilitas, silakan hubungi kami melalui halaman <a href="{{ route('feedback') }}" class="text-blue-500 hover:underline">umpan balik</a>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
