<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Program;
use App\Models\Instansi;

/** @extends Factory<Program> */
class ProgramFactory extends Factory
{
    protected $model = Program::class;

    public function definition(): array
    {
        return [
            'instansi_id' => Instansi::factory(),
            'kode_program' => 'PROG' . $this->faker->unique()->numerify('######'),
            'nama_program' => $this->faker->sentence(3),
            'deskripsi' => $this->faker->paragraph(),
            'anggaran' => 1000000,
            'tahun' => 2024,
            'penanggung_jawab' => $this->faker->name(),
            'status' => 'aktif',
        ];
    }
}