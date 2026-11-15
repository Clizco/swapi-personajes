<?php

namespace Database\Factories;

use App\Models\Personaje;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Personaje>
 */
class PersonajeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => fake()->firstName().' '.fake()->lastName(),
            'altura' => fake()->optional(0.9)->numberBetween(60, 250),
            'masa' => fake()->optional(0.8)->numberBetween(20, 200),
            'color_cabello' => fake()->optional()->randomElement(['negro', 'castaño', 'rubio', 'blanco', 'ninguno']),
            'color_ojos' => fake()->optional()->randomElement(['azul', 'marrón', 'verde', 'amarillo', 'rojo']),
            'genero' => fake()->randomElement(Personaje::GENEROS),
            'planeta_origen' => fake()->optional()->randomElement(['Tatooine', 'Naboo', 'Alderaan', 'Coruscant', 'Kashyyyk']),
            'origen_swapi' => false,
            'swapi_id' => null,
        ];
    }

    public function desdeSwapi(int $swapiId): static
    {
        return $this->state(fn () => [
            'origen_swapi' => true,
            'swapi_id' => $swapiId,
        ]);
    }
}
