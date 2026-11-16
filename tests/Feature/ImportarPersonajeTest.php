<?php

namespace Tests\Feature;

use App\Models\Personaje;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImportarPersonajeTest extends TestCase
{
    use RefreshDatabase;

    private const URL_PERSONA = 'swapi.py4e.com/api/people/*';

    private const URL_PLANETA = 'swapi.py4e.com/api/planets/*';

    private function respuestaLuke(): array
    {
        return [
            'name' => 'Luke Skywalker',
            'height' => '172',
            'mass' => '77',
            'hair_color' => 'blond',
            'eye_color' => 'blue',
            'gender' => 'male',
            'homeworld' => 'https://swapi.py4e.com/api/planets/1/',
        ];
    }

    private function simularSwapiFuncionando(): void
    {
        Http::fake([
            self::URL_PERSONA => Http::response($this->respuestaLuke()),
            self::URL_PLANETA => Http::response(['name' => 'Tatooine']),
        ]);
    }

    public function test_importa_y_traduce_un_personaje(): void
    {
        $this->simularSwapiFuncionando();

        $this->post('/personajes/importar', ['swapi_id' => 1])
            ->assertRedirect('/personajes')
            ->assertSessionHas('success')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('personajes', [
            'nombre' => 'Luke Skywalker',
            'altura' => 172,
            'masa' => 77,
            'color_cabello' => 'blond',
            'color_ojos' => 'blue',
            'genero' => 'masculino',
            'planeta_origen' => 'Tatooine',
            'origen_swapi' => true,
            'swapi_id' => 1,
        ]);

        Http::assertSent(fn ($request) => $request->url() === 'https://swapi.py4e.com/api/people/1/');
    }

    public function test_no_permite_importar_dos_veces_el_mismo_personaje(): void
    {
        $this->simularSwapiFuncionando();
        Personaje::factory()->desdeSwapi(1)->create();

        $this->from('/personajes')
            ->post('/personajes/importar', ['swapi_id' => 1])
            ->assertSessionHasErrors(['swapi_id' => 'Este personaje de SWAPI ya fue importado.']);

        $this->assertDatabaseCount('personajes', 1);
        Http::assertNothingSent();
    }

    public function test_permite_reimportar_un_personaje_eliminado(): void
    {
        $this->simularSwapiFuncionando();
        $eliminado = Personaje::factory()->desdeSwapi(1)->create(['nombre' => 'Viejo']);
        $eliminado->delete();

        $this->post('/personajes/importar', ['swapi_id' => 1])->assertSessionHasNoErrors();

        $this->assertNotSoftDeleted($eliminado);
        $this->assertSame('Luke Skywalker', $eliminado->fresh()->nombre);
        $this->assertSame(1, Personaje::withTrashed()->count());
    }

    public function test_valida_que_el_id_este_entre_1_y_83(): void
    {
        Http::fake();

        foreach ([0, 84, 'abc', null] as $idInvalido) {
            $this->from('/personajes')
                ->post('/personajes/importar', ['swapi_id' => $idInvalido])
                ->assertSessionHasErrors('swapi_id');
        }

        Http::assertNothingSent();
    }

    public function test_informa_cuando_el_personaje_no_existe_en_swapi(): void
    {
        Http::fake([self::URL_PERSONA => Http::response(['detail' => 'Not found'], 404)]);

        $this->from('/personajes')
            ->post('/personajes/importar', ['swapi_id' => 17])
            ->assertRedirect('/personajes')
            ->assertSessionHasErrors(['swapi_id' => 'No existe un personaje con el ID 17 en SWAPI.']);

        $this->assertDatabaseCount('personajes', 0);
    }

    public function test_informa_cuando_swapi_no_responde(): void
    {
        Http::fake(fn () => throw new ConnectionException('cURL error 28: Operation timed out'));

        $this->from('/personajes')
            ->post('/personajes/importar', ['swapi_id' => 1])
            ->assertSessionHasErrors('swapi_id');

        $this->assertStringContainsString('No se pudo conectar con SWAPI', session('errors')->first('swapi_id'));
        $this->assertDatabaseCount('personajes', 0);
    }

    public function test_informa_cuando_swapi_responde_con_error_del_servidor(): void
    {
        Http::fake([self::URL_PERSONA => Http::response('Bad Gateway', 502)]);

        $this->from('/personajes')
            ->post('/personajes/importar', ['swapi_id' => 1])
            ->assertSessionHasErrors(['swapi_id' => 'SWAPI respondió con un error (502). Intenta nuevamente más tarde.']);
    }

    public function test_informa_cuando_la_respuesta_no_tiene_el_formato_esperado(): void
    {
        Http::fake([self::URL_PERSONA => Http::response(['foo' => 'bar'])]);

        $this->from('/personajes')
            ->post('/personajes/importar', ['swapi_id' => 1])
            ->assertSessionHasErrors(['swapi_id' => 'SWAPI devolvió una respuesta con un formato inesperado.']);
    }

    public function test_importa_aunque_falle_la_consulta_del_planeta(): void
    {
        Http::fake([
            self::URL_PERSONA => Http::response($this->respuestaLuke()),
            self::URL_PLANETA => Http::response(null, 500),
        ]);

        $this->post('/personajes/importar', ['swapi_id' => 1])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('personajes', ['swapi_id' => 1, 'planeta_origen' => null]);
    }
}
