<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Compra>
 */
class CompraFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'fecha' => $this->faker->dateTimeBetween('-7 years', 'now'),
            'observacion' => $this->faker->text,           
            'proveedor_id' => $this->faker->numberBetween(1, 252),
            'usuario_id' => $this->faker->numberBetween(1, 10),                 
        ];
    }
}
