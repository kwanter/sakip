<?php

namespace Database\Factories;

use App\Models\Instansi;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Instansi> */
class InstansiFactory extends Factory
{
    protected $model = Instansi::class;

    public function definition(): array
    {
        return [
            'kode_instansi' => 'INS'.$this->faker->unique()->numerify('######'),
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
