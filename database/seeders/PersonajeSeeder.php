<?php

namespace Database\Seeders;

use App\Models\Personaje;
use Illuminate\Database\Seeder;

class PersonajeSeeder extends Seeder
{
    public function run(): void
    {
        if (Personaje::withTrashed()->exists()) {
            $this->command?->info('La tabla personajes ya tiene datos; no se generan datos de prueba.');

            return;
        }

        Personaje::factory()->count(25)->create();
    }
}
