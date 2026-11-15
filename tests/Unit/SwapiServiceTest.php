<?php

namespace Tests\Unit;

use App\Services\SwapiService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SwapiServiceTest extends TestCase
{
    private SwapiService $servicio;

    protected function setUp(): void
    {
        parent::setUp();

        $this->servicio = new SwapiService('https://swapi.py4e.com/api', 5);
    }

    private function personaje(array $cambios = []): array
    {
        return [
            'name' => 'Luke Skywalker',
            'height' => '172',
            'mass' => '77',
            'hair_color' => 'blond',
            'eye_color' => 'blue',
            'gender' => 'male',
            ...$cambios,
        ];
    }

    public function test_mapea_los_campos_de_ingles_a_espanol(): void
    {
        $this->assertSame([
            'nombre' => 'Luke Skywalker',
            'altura' => 172,
            'masa' => 77,
            'color_cabello' => 'blond',
            'color_ojos' => 'blue',
            'genero' => 'masculino',
        ], $this->servicio->mapearPersonaje($this->personaje()));
    }

    public static function generos(): array
    {
        return [
            'male' => ['male', 'masculino'],
            'female' => ['female', 'femenino'],
            'n/a' => ['n/a', 'n/a'],
            'hermaphrodite' => ['hermaphrodite', 'n/a'],
            'none' => ['none', 'n/a'],
            'mayúsculas' => ['Female', 'femenino'],
        ];
    }

    #[DataProvider('generos')]
    public function test_mapea_el_genero(string $generoSwapi, string $esperado): void
    {
        $mapeado = $this->servicio->mapearPersonaje($this->personaje(['gender' => $generoSwapi]));

        $this->assertSame($esperado, $mapeado['genero']);
    }

    public static function numeros(): array
    {
        return [
            'entero' => ['77', 77],
            'decimal' => ['78.2', 78],
            'desconocido' => ['unknown', null],
            'con separador de miles fuera de rango' => ['1,358', null],
            'vacío' => ['', null],
        ];
    }

    #[DataProvider('numeros')]
    public function test_convierte_la_masa_a_entero(string $masaSwapi, ?int $esperado): void
    {
        $mapeado = $this->servicio->mapearPersonaje($this->personaje(['mass' => $masaSwapi]));

        $this->assertSame($esperado, $mapeado['masa']);
    }

    public function test_respeta_el_maximo_de_altura(): void
    {
        $this->assertSame(264, $this->servicio->mapearPersonaje($this->personaje(['height' => '264']))['altura']);
        $this->assertNull($this->servicio->mapearPersonaje($this->personaje(['height' => '301']))['altura']);
    }

    public function test_convierte_los_textos_desconocidos_en_null(): void
    {
        $mapeado = $this->servicio->mapearPersonaje($this->personaje([
            'hair_color' => 'n/a',
            'eye_color' => 'unknown',
        ]));

        $this->assertNull($mapeado['color_cabello']);
        $this->assertNull($mapeado['color_ojos']);
    }
}
