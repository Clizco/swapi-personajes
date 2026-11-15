<?php

namespace App\Services;

use App\Exceptions\SwapiException;
use App\Models\Personaje;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\UniqueConstraintViolationException;

class PersonajeService
{
    public const POR_PAGINA = 10;

    public function __construct(private readonly SwapiService $swapi) {}

    public function listar(array $filtros): LengthAwarePaginator
    {
        $columna = in_array($filtros['orden'] ?? null, Personaje::COLUMNAS_ORDENABLES, true)
            ? $filtros['orden']
            : 'created_at';
        $direccion = ($filtros['direccion'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return Personaje::query()
            ->buscarPorNombre($filtros['buscar'] ?? null)
            ->delGenero($filtros['genero'] ?? null)
            ->orderBy($columna, $direccion)
            ->orderBy('id', $direccion)
            ->paginate(self::POR_PAGINA)
            ->withQueryString();
    }

    public function crear(array $datos): Personaje
    {
        return Personaje::create($datos);
    }

    public function actualizar(Personaje $personaje, array $datos): Personaje
    {
        $personaje->update($datos);

        return $personaje;
    }

    public function eliminar(Personaje $personaje): void
    {
        $personaje->delete();
    }

    public function importarDesdeSwapi(int $swapiId): Personaje
    {
        if (Personaje::where('swapi_id', $swapiId)->exists()) {
            throw SwapiException::personajeYaImportado($swapiId);
        }

        $datos = $this->swapi->obtenerPersonaje($swapiId);

        try {
            $personaje = Personaje::onlyTrashed()->firstOrNew(['swapi_id' => $swapiId]);
            $personaje->fill([...$datos, 'origen_swapi' => true]);
            $personaje->deleted_at = null;
            $personaje->save();
        } catch (UniqueConstraintViolationException) {
            throw SwapiException::personajeYaImportado($swapiId);
        }

        return $personaje;
    }
}
