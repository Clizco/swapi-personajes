<?php

namespace App\Http\Controllers;

use App\Exceptions\SwapiException;
use App\Http\Requests\ImportarPersonajeRequest;
use App\Http\Requests\PersonajeRequest;
use App\Models\Personaje;
use App\Services\PersonajeService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PersonajeController extends Controller
{
    public function __construct(private PersonajeService $personajes) {}

    public function index(Request $request)
    {
        $filtros = $request->only(['buscar', 'genero', 'orden', 'direccion']);

        return Inertia::render('Personajes/Index', [
            'personajes' => $this->personajes->listar($filtros),
            'filtros' => $filtros,
            'generos' => Personaje::GENEROS,
        ]);
    }

    public function create()
    {
        return Inertia::render('Personajes/Create', [
            'generos' => Personaje::GENEROS,
        ]);
    }

    public function store(PersonajeRequest $request)
    {
        $personaje = Personaje::create($request->validated());

        return to_route('personajes.index')->with('success', "Personaje «{$personaje->nombre}» creado correctamente.");
    }

    public function edit(Personaje $personaje)
    {
        return Inertia::render('Personajes/Edit', [
            'personaje' => $personaje,
            'generos' => Personaje::GENEROS,
        ]);
    }

    public function update(PersonajeRequest $request, Personaje $personaje)
    {
        $personaje->update($request->validated());

        return to_route('personajes.index')->with('success', "Personaje «{$personaje->nombre}» actualizado correctamente.");
    }

    public function destroy(Personaje $personaje)
    {
        $personaje->delete();

        return back()->with('success', "Personaje «{$personaje->nombre}» eliminado.");
    }

    public function importar(ImportarPersonajeRequest $request)
    {
        try {
            $personaje = $this->personajes->importarDesdeSwapi($request->integer('swapi_id'));
        } catch (SwapiException $error) {
            return back()->withErrors(['swapi_id' => $error->getMessage()]);
        }

        return to_route('personajes.index')->with('success', "Personaje «{$personaje->nombre}» importado desde SWAPI.");
    }
}
