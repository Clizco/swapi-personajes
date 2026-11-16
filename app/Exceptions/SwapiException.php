<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Error al importar desde SWAPI. Su mensaje se muestra tal cual al usuario.
 */
class SwapiException extends RuntimeException {}
