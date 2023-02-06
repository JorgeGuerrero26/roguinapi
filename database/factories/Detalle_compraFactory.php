<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Detalle_compra>
 */
class Detalle_compraFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'compra_id' => $this->faker->numberBetween(1, 16452),
            'material_id' => $this->faker->numberBetween(1, 3),
            'cantidad_comprada' => $this->faker->numberBetween(10, 50),
            'precio_unitario' => $this->faker->numberBetween(6, 10),        
        ]; 
    }
}
