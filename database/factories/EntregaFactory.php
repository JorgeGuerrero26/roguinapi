<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Entrega>
 */
class EntregaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'zona_entrega' => $this->faker->word,
            'direccion_entrega' => $this->faker->address(),
            'cliente_id' => $this->faker->numberBetween(1, 100),
        ];
    }
}
