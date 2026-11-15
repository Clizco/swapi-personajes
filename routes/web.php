<?php

use App\Http\Controllers\PersonajeController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/personajes');

Route::post('personajes/importar', [PersonajeController::class, 'importar'])
    ->middleware('throttle:30,1')
    ->name('personajes.importar');

Route::resource('personajes', PersonajeController::class)
    ->except('show')
    ->parameters(['personajes' => 'personaje']);
