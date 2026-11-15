<?php

namespace Tests\Feature;

use App\Models\Personaje;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PersonajeCrudTest extends TestCase
{
    use RefreshDatabase;

    private function datosValidos(array $cambios = []): array
    {
        return [
            'nombre' => 'Ahsoka Tano',
            'altura' => 170,
            'masa' => 55,
            'color_cabello' => 'ninguno',
            'color_ojos' => 'azul',
            'genero' => 'femenino',
            'planeta_origen' => 'Shili',
            ...$cambios,
        ];
    }

    public function test_la_raiz_redirige_al_listado(): void
    {
        $this->get('/')->assertRedirect('/personajes');
    }

    public function test_el_listado_pagina_de_10_en_10(): void
    {
        Personaje::factory()->count(15)->create();

        $this->get('/personajes')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $pagina) => $pagina
                ->component('Personajes/Index')
                ->has('personajes.data', 10)
                ->where('personajes.total', 15)
                ->where('personajes.last_page', 2));

        $this->get('/personajes?page=2')
            ->assertInertia(fn (AssertableInertia $pagina) => $pagina->has('personajes.data', 5));
    }

    public function test_el_listado_filtra_por_nombre_y_genero(): void
    {
        Personaje::factory()->create(['nombre' => 'Luke Skywalker', 'genero' => 'masculino']);
        Personaje::factory()->create(['nombre' => 'Leia Organa', 'genero' => 'femenino']);
        Personaje::factory()->create(['nombre' => 'Anakin Skywalker', 'genero' => 'masculino']);

        $this->get('/personajes?buscar=skywalker')
            ->assertInertia(fn (AssertableInertia $pagina) => $pagina->has('personajes.data', 2));

        $this->get('/personajes?genero=femenino')
            ->assertInertia(fn (AssertableInertia $pagina) => $pagina
                ->has('personajes.data', 1)
                ->where('personajes.data.0.nombre', 'Leia Organa'));
    }

    public function test_el_listado_ordena_por_columna_permitida_e_ignora_las_demas(): void
    {
        Personaje::factory()->create(['nombre' => 'Bravo', 'altura' => 200]);
        Personaje::factory()->create(['nombre' => 'Alfa', 'altura' => 100]);

        $this->get('/personajes?orden=altura&direccion=asc')
            ->assertInertia(fn (AssertableInertia $pagina) => $pagina->where('personajes.data.0.nombre', 'Alfa'));

        $this->get('/personajes?orden=id;drop&direccion=asc')->assertOk();
    }

    public function test_muestra_el_formulario_de_creacion(): void
    {
        $this->get('/personajes/crear')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $pagina) => $pagina
                ->component('Personajes/Create')
                ->where('generos', Personaje::GENEROS));
    }

    public function test_guarda_un_personaje_valido(): void
    {
        $this->post('/personajes', $this->datosValidos())
            ->assertRedirect('/personajes')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('personajes', [
            'nombre' => 'Ahsoka Tano',
            'origen_swapi' => false,
            'swapi_id' => null,
        ]);
    }

    public function test_los_campos_opcionales_pueden_quedar_vacios(): void
    {
        $this->post('/personajes', [
            'nombre' => 'R2-D2',
            'genero' => 'n/a',
            'altura' => null,
            'masa' => null,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('personajes', ['nombre' => 'R2-D2', 'altura' => null]);
    }

    public static function datosInvalidos(): array
    {
        return [
            'nombre vacío' => [['nombre' => ''], 'nombre'],
            'nombre corto' => [['nombre' => 'Yo'], 'nombre'],
            'nombre largo' => [['nombre' => str_repeat('a', 101)], 'nombre'],
            'altura negativa' => [['altura' => -5], 'altura'],
            'altura mayor a 300' => [['altura' => 301], 'altura'],
            'altura decimal' => [['altura' => 1.5], 'altura'],
            'masa cero' => [['masa' => 0], 'masa'],
            'masa mayor a 500' => [['masa' => 501], 'masa'],
            'género vacío' => [['genero' => ''], 'genero'],
            'género inválido' => [['genero' => 'otro'], 'genero'],
            'color de ojos largo' => [['color_ojos' => str_repeat('a', 101)], 'color_ojos'],
            'planeta largo' => [['planeta_origen' => str_repeat('a', 101)], 'planeta_origen'],
        ];
    }

    #[DataProvider('datosInvalidos')]
    public function test_rechaza_datos_invalidos(array $cambios, string $campoConError): void
    {
        $this->from('/personajes/crear')
            ->post('/personajes', $this->datosValidos($cambios))
            ->assertRedirect('/personajes/crear')
            ->assertSessionHasErrors($campoConError);

        $this->assertDatabaseCount('personajes', 0);
    }

    public function test_muestra_el_formulario_de_edicion_con_los_datos(): void
    {
        $personaje = Personaje::factory()->create();

        $this->get("/personajes/{$personaje->id}/editar")
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $pagina) => $pagina
                ->component('Personajes/Edit')
                ->where('personaje.id', $personaje->id)
                ->where('personaje.nombre', $personaje->nombre));
    }

    public function test_actualiza_un_personaje(): void
    {
        $personaje = Personaje::factory()->create();

        $this->put("/personajes/{$personaje->id}", $this->datosValidos(['nombre' => 'Ahsoka']))
            ->assertRedirect('/personajes')
            ->assertSessionHas('success');

        $this->assertSame('Ahsoka', $personaje->fresh()->nombre);
    }

    public function test_actualizar_no_cambia_el_origen_swapi(): void
    {
        $personaje = Personaje::factory()->desdeSwapi(1)->create();

        $this->put("/personajes/{$personaje->id}", $this->datosValidos([
            'origen_swapi' => false,
            'swapi_id' => 99,
        ]));

        $this->assertTrue($personaje->fresh()->origen_swapi);
        $this->assertSame(1, $personaje->fresh()->swapi_id);
    }

    public function test_elimina_un_personaje_con_soft_delete(): void
    {
        $personaje = Personaje::factory()->create();

        $this->from('/personajes')
            ->delete("/personajes/{$personaje->id}")
            ->assertRedirect('/personajes')
            ->assertSessionHas('success');

        $this->assertSoftDeleted($personaje);
    }

    public function test_devuelve_404_si_el_personaje_no_existe(): void
    {
        $this->get('/personajes/999/editar')->assertNotFound();
        $this->put('/personajes/999', $this->datosValidos())->assertNotFound();
        $this->delete('/personajes/999')->assertNotFound();
    }
}
