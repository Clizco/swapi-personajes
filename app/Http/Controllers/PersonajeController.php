<?php

namespace App\Http\Controllers;

use App\Exceptions\SwapiException;
use App\Http\Requests\ImportarPersonajeRequest;
use App\Http\Requests\PersonajeRequest;
use App\Models\Personaje;
use App\Services\PersonajeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PersonajeController extends Controller
{
    public function __construct(private readonly PersonajeService $personajes) {}

    public function index(Request $request): Response
    {
        $filtros = $request->only(['buscar', 'genero', 'orden', 'direccion']);

        return Inertia::render('Personajes/Index', [
            'personajes' => $this->personajes->listar($filtros),
            'filtros' => $filtros,
            'generos' => Personaje::GENEROS,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Personajes/Create', [
            'generos' => Personaje::GENEROS,
        ]);
    }

    public function store(PersonajeRequest $request): RedirectResponse
    {
        $personaje = $this->personajes->crear($request->validated());

        return to_route('personajes.index')
            ->with('success', "Personaje «{$personaje->nombre}» creado correctamente.");
    }

    public function edit(Personaje $personaje): Response
    {
        return Inertia::render('Personajes/Edit', [
            'personaje' => $personaje,
            'generos' => Personaje::GENEROS,
        ]);
    }

    public function update(PersonajeRequest $request, Personaje $personaje): RedirectResponse
    {
        $this->personajes->actualizar($personaje, $request->validated());

        return to_route('personajes.index')
            ->with('success', "Personaje «{$personaje->nombre}» actualizado correctamente.");
    }

    public function destroy(Personaje $personaje): RedirectResponse
    {
        $this->personajes->eliminar($personaje);

        return back()->with('success', "Personaje «{$personaje->nombre}» eliminado.");
    }

    public function importar(ImportarPersonajeRequest $request): RedirectResponse
    {
        try {
            $personaje = $this->personajes->importarDesdeSwapi($request->integer('swapi_id'));
        } catch (SwapiException $excepcion) {
            return back()->withErrors(['swapi_id' => $excepcion->getMessage()]);
        }

        return to_route('personajes.index')
            ->with('success', "Personaje «{$personaje->nombre}» importado desde SWAPI.");
    }
}
