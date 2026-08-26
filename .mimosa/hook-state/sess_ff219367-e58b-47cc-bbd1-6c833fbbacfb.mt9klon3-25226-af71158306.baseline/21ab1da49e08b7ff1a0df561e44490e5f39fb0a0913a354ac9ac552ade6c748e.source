<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Kegiatan;
use App\Models\Program;

/** @extends Factory<Kegiatan> */
class KegiatanFactory extends Factory
{
    protected $model = Kegiatan::class;

    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-1 years', 'now');
        $end = (clone $start)->modify('+1 month');
        return [
            'program_id' => Program::factory(),
            'kode_kegiatan' => 'KEG' . $this->faker->unique()->numerify('######'),
            'nama_kegiatan' => $this->faker->sentence(3),
            'deskripsi' => $this->faker->paragraph(),
            'anggaran' => 500000,
            'tanggal_mulai' => $start->format('Y-m-d'),
            'tanggal_selesai' => $end->format('Y-m-d'),
            'penanggung_jawab' => $this->faker->name(),
            'status' => 'berjalan',
        ];
    }
}