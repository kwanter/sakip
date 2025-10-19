@extends('layouts.app')

@section('title', 'Disclaimer')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h1 class="text-2xl font-semibold text-gray-800 mb-6">Disclaimer</h1>

                <div class="sakip-card mb-6">
                    <div class="p-6">
                        <p class="text-gray-600">
                            Informasi yang disediakan oleh SAKIP di platform ini hanya untuk tujuan informasi umum. Semua informasi di situs ini disediakan dengan itikad baik, namun kami tidak membuat pernyataan atau jaminan apa pun, baik tersurat maupun tersirat, mengenai keakuratan, kecukupan, validitas, keandalan, ketersediaan, atau kelengkapan informasi apa pun di situs ini.
                        </p>
                    </div>
                </div>

                <div class="sakip-card mb-6">
                    <div class="p-6">
                        <p class="text-gray-600">
                            Dalam keadaan apa pun kami tidak akan memiliki kewajiban apa pun kepada Anda atas kehilangan atau kerusakan apa pun yang terjadi sebagai akibat dari penggunaan situs atau ketergantungan pada informasi apa pun yang disediakan di situs. Penggunaan Anda atas situs dan ketergantungan Anda pada informasi apa pun di situs sepenuhnya merupakan risiko Anda sendiri.
                        </p>
                    </div>
                </div>

                <div class="sakip-card">
                    <div class="p-6">
                        <p class="text-gray-600">
                            Platform ini mungkin berisi tautan ke situs web lain atau konten milik atau berasal dari pihak ketiga. Tautan eksternal semacam itu tidak diselidiki, dipantau, atau diperiksa keakuratannya oleh kami.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
