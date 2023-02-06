<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Venta>
 */
class VentaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'usuario_id' => $this->faker->numberBetween(1, 10),
            'cliente_id' => $this->faker->numberBetween(1, 111),
            'fecha' => $this->faker->dateTimeBetween('-7 years', 'now'),
            'observacion' => $this->faker->text(),
            'numero_guia' => $this->faker->text(),       
            'entrega_id' => $this->faker->numberBetween(1, 200),
        ];
    }
}
