<?php

namespace App\Services;

use App\Exceptions\SwapiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Habla con la API de SWAPI y traduce sus datos (inglés) a nuestros campos (español).
 */
class SwapiService
{
    private const GENEROS = [
        'male' => 'masculino',
        'female' => 'femenino',
    ];

    public function obtenerPersonaje(int $swapiId): array
    {
        $datos = $this->get(config('services.swapi.base_url')."/people/{$swapiId}/", $swapiId);

        if (! isset($datos['name'])) {
            throw new SwapiException('SWAPI devolvió una respuesta con un formato inesperado.');
        }

        return [
            ...$this->mapearPersonaje($datos),
            'planeta_origen' => $this->obtenerNombrePlaneta($datos['homeworld'] ?? null),
            'swapi_id' => $swapiId,
        ];
    }

    public function mapearPersonaje(array $datos): array
    {
        return [
            'nombre' => mb_substr(trim($datos['name']), 0, 100),
            'altura' => $this->aEntero($datos['height'] ?? null, 300),
            'masa' => $this->aEntero($datos['mass'] ?? null, 500),
            'color_cabello' => $this->aTexto($datos['hair_color'] ?? null),
            'color_ojos' => $this->aTexto($datos['eye_color'] ?? null),
            'genero' => self::GENEROS[strtolower($datos['gender'] ?? '')] ?? 'n/a',
        ];
    }

    private function get(string $url, int $swapiId): array
    {
        try {
            $respuesta = Http::acceptJson()->timeout(config('services.swapi.timeout'))->get($url);
        } catch (ConnectionException) {
            throw new SwapiException('No se pudo conectar con SWAPI (tiempo de espera agotado o servicio caído). Intenta nuevamente en unos minutos.');
        }

        if ($respuesta->notFound()) {
            throw new SwapiException("No existe un personaje con el ID {$swapiId} en SWAPI.");
        }

        if ($respuesta->failed()) {
            throw new SwapiException("SWAPI respondió con un error ({$respuesta->status()}). Intenta nuevamente más tarde.");
        }

        return $respuesta->json() ?? [];
    }

    // Si el planeta falla, importamos igual al personaje, solo que sin planeta.
    private function obtenerNombrePlaneta(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        try {
            $respuesta = Http::acceptJson()->timeout(config('services.swapi.timeout'))->get($url);
        } catch (ConnectionException) {
            return null;
        }

        return $respuesta->successful() ? $this->aTexto($respuesta->json('name')) : null;
    }

    // "77" -> 77, "78.2" -> 78, "unknown" -> null, "1,358" -> null (pasa el máximo).
    private function aEntero(?string $valor, int $maximo): ?int
    {
        $valor = str_replace(',', '', (string) $valor);

        if (! is_numeric($valor)) {
            return null;
        }

        $numero = (int) round((float) $valor);

        return $numero >= 1 && $numero <= $maximo ? $numero : null;
    }

    // "unknown", "n/a" y "" se guardan como null.
    private function aTexto(?string $valor): ?string
    {
        $valor = trim((string) $valor);

        if (in_array(strtolower($valor), ['unknown', 'n/a', ''], true)) {
            return null;
        }

        return mb_substr($valor, 0, 100);
    }
}
