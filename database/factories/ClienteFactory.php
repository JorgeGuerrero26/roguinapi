<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cliente>
 */
class ClienteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'nombre' => $this->faker->name,
            'documento' => $this->faker->unique()->numberBetween(100000000, 999999999),
            'saldo_botellon' => $this->faker->numberBetween(0, 100),
            'forma_pago' => $this->faker->randomElement(['EFECTIVO', 'CUENTA']),
        ];
    }
}
