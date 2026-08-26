<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Instansi;

/** @extends Factory<Instansi> */
class InstansiFactory extends Factory
{
    protected $model = Instansi::class;

    public function definition(): array
    {
        return [
            'kode_instansi' => 'INS' . $this->faker->unique()->numerify('######'),
            'nama_instansi' => $this->faker->company(),
            'alamat' => $this->faker->address(),
            'telepon' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'website' => $this->faker->url(),
            'kepala_instansi' => $this->faker->name(),
            'nip_kepala' => $this->faker->numerify('################'),
            'status' => 'aktif',
        ];
    }
}