<?php

namespace App\Console\Commands;

use App\Exceptions\SwapiException;
use App\Http\Requests\ImportarPersonajeRequest;
use App\Services\PersonajeService;
use Illuminate\Console\Command;

class ImportarPersonajesSwapi extends Command
{
    protected $signature = 'swapi:importar
                            {ids?* : IDs de SWAPI a importar (ej: 1 4 10)}
                            {--desde= : Primer ID de un rango}
                            {--hasta= : Último ID de un rango}';

    protected $description = 'Importa uno o varios personajes desde SWAPI';

    public function handle(PersonajeService $personajes): int
    {
        $ids = $this->idsSolicitados();

        if ($ids === []) {
            $this->error('Indica IDs (ej: swapi:importar 1 2 3) o un rango (--desde=1 --hasta=10).');

            return self::INVALID;
        }

        $importados = 0;

        foreach ($ids as $swapiId) {
            if ($swapiId < ImportarPersonajeRequest::SWAPI_ID_MINIMO || $swapiId > ImportarPersonajeRequest::SWAPI_ID_MAXIMO) {
                $this->warn("[{$swapiId}] Fuera de rango (1-83), se omite.");

                continue;
            }

            try {
                $personaje = $personajes->importarDesdeSwapi($swapiId);
                $this->info("[{$swapiId}] {$personaje->nombre} importado.");
                $importados++;
            } catch (SwapiException $excepcion) {
                $this->warn("[{$swapiId}] {$excepcion->getMessage()}");
            }
        }

        $this->newLine();
        $this->line("Importados: {$importados} de ".count($ids).'.');

        return self::SUCCESS;
    }

    private function idsSolicitados(): array
    {
        $ids = array_map('intval', $this->argument('ids'));

        if ($this->option('desde') !== null && $this->option('hasta') !== null) {
            $ids = [...$ids, ...range((int) $this->option('desde'), (int) $this->option('hasta'))];
        }

        return array_values(array_unique($ids));
    }
}
