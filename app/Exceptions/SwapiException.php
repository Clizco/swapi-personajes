<?php

namespace App\Exceptions;

use RuntimeException;

class SwapiException extends RuntimeException
{
    public static function personajeNoEncontrado(int $swapiId): self
    {
        return new self("No existe un personaje con el ID {$swapiId} en SWAPI.");
    }

    public static function personajeYaImportado(int $swapiId): self
    {
        return new self("El personaje con ID {$swapiId} de SWAPI ya fue importado.");
    }

    public static function sinConexion(): self
    {
        return new self('No se pudo conectar con SWAPI (tiempo de espera agotado o servicio caído). Intenta nuevamente en unos minutos.');
    }

    public static function errorDelServidor(int $codigoHttp): self
    {
        return new self("SWAPI respondió con un error ({$codigoHttp}). Intenta nuevamente más tarde.");
    }

    public static function respuestaInvalida(): self
    {
        return new self('SWAPI devolvió una respuesta con un formato inesperado.');
    }
}
