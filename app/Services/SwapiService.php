<?php

namespace App\Services;

use App\Exceptions\SwapiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SwapiService
{
    private const VALORES_DESCONOCIDOS = ['unknown', 'n/a', ''];

    private const MAPA_GENEROS = [
        'male' => 'masculino',
        'female' => 'femenino',
    ];

    public function __construct(
        private readonly string $urlBase,
        private readonly int $timeoutEnSegundos,
    ) {}

    public function obtenerPersonaje(int $swapiId): array
    {
        $datos = $this->consultar("people/{$swapiId}/", $swapiId);

        if (! isset($datos['name'])) {
            throw SwapiException::respuestaInvalida();
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
            'nombre' => Str::limit(trim($datos['name']), 100, ''),
            'altura' => $this->convertirAEntero($datos['height'] ?? null, 300),
            'masa' => $this->convertirAEntero($datos['mass'] ?? null, 500),
            'color_cabello' => $this->limpiarTexto($datos['hair_color'] ?? null),
            'color_ojos' => $this->limpiarTexto($datos['eye_color'] ?? null),
            'genero' => self::MAPA_GENEROS[strtolower(trim($datos['gender'] ?? ''))] ?? 'n/a',
        ];
    }

    private function consultar(string $ruta, int $swapiId): array
    {
        try {
            $respuesta = $this->cliente()->get($ruta);
        } catch (ConnectionException $excepcion) {
            Log::warning('SWAPI no disponible', ['ruta' => $ruta, 'error' => $excepcion->getMessage()]);

            throw SwapiException::sinConexion();
        }

        if ($respuesta->notFound()) {
            throw SwapiException::personajeNoEncontrado($swapiId);
        }

        if ($respuesta->failed()) {
            Log::warning('SWAPI respondió con error', ['ruta' => $ruta, 'status' => $respuesta->status()]);

            throw SwapiException::errorDelServidor($respuesta->status());
        }

        $datos = $respuesta->json();

        if (! is_array($datos)) {
            throw SwapiException::respuestaInvalida();
        }

        return $datos;
    }

    private function obtenerNombrePlaneta(?string $urlPlaneta): ?string
    {
        if (! $urlPlaneta || ! Str::startsWith($urlPlaneta, rtrim($this->urlBase, '/').'/planets/')) {
            return null;
        }

        try {
            $respuesta = $this->cliente()->get($urlPlaneta);
        } catch (ConnectionException) {
            return null;
        }

        return $respuesta->successful() ? $this->limpiarTexto($respuesta->json('name')) : null;
    }

    private function cliente(): PendingRequest
    {
        return Http::baseUrl($this->urlBase)
            ->acceptJson()
            ->timeout($this->timeoutEnSegundos)
            ->retry(2, 300, fn ($excepcion) => $excepcion instanceof ConnectionException, throw: false);
    }

    private function convertirAEntero(?string $valor, int $maximo): ?int
    {
        $valorLimpio = str_replace(',', '', trim((string) $valor));

        if (! is_numeric($valorLimpio)) {
            return null;
        }

        $numero = (int) round((float) $valorLimpio);

        return $numero >= 1 && $numero <= $maximo ? $numero : null;
    }

    private function limpiarTexto(mixed $valor): ?string
    {
        if (! is_string($valor) || in_array(strtolower(trim($valor)), self::VALORES_DESCONOCIDOS, true)) {
            return null;
        }

        return Str::limit(trim($valor), 100, '');
    }
}
