<?php

namespace App\Services;

use App\Exceptions\SwapiException;
use App\Models\Personaje;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Lógica de personajes que no es un simple create/update/delete:
 * el listado con filtros y la importación desde SWAPI.
 */
class PersonajeService
{
    public function __construct(private SwapiService $swapi) {}

    public function listar(array $filtros): LengthAwarePaginator
    {
        $buscar = $filtros['buscar'] ?? null;
        $genero = $filtros['genero'] ?? null;

        // Solo dejamos ordenar por columnas conocidas (nunca por texto libre del usuario).
        $orden = in_array($filtros['orden'] ?? null, Personaje::COLUMNAS_ORDENABLES)
            ? $filtros['orden']
            : 'created_at';
        $direccion = ($filtros['direccion'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return Personaje::query()
            ->when($buscar, fn ($query) => $query->where('nombre', 'like', "%{$buscar}%"))
            ->when($genero, fn ($query) => $query->where('genero', $genero))
            ->orderBy($orden, $direccion)
            ->orderBy('id', $direccion)
            ->paginate(10)
            ->withQueryString();
    }

    public function importarDesdeSwapi(int $swapiId): Personaje
    {
        // withTrashed() también encuentra a los eliminados (soft delete).
        $existente = Personaje::withTrashed()->where('swapi_id', $swapiId)->first();

        if ($existente && ! $existente->trashed()) {
            throw new SwapiException('Este personaje de SWAPI ya fue importado.');
        }

        $datos = $this->swapi->obtenerPersonaje($swapiId);

        // Si estaba eliminado, lo restauramos con los datos frescos en vez de crear otro.
        if ($existente) {
            $existente->restore();
            $existente->update($datos);

            return $existente;
        }

        return Personaje::create([...$datos, 'origen_swapi' => true]);
    }
}
