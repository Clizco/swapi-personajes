<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Personaje extends Model
{
    use HasFactory, SoftDeletes;

    public const GENEROS = ['masculino', 'femenino', 'n/a'];

    public const COLUMNAS_ORDENABLES = ['nombre', 'altura', 'masa', 'genero', 'created_at'];

    protected $table = 'personajes';

    protected $fillable = [
        'nombre',
        'altura',
        'masa',
        'color_cabello',
        'color_ojos',
        'genero',
        'planeta_origen',
        'origen_swapi',
        'swapi_id',
    ];

    protected function casts(): array
    {
        return [
            'altura' => 'integer',
            'masa' => 'integer',
            'origen_swapi' => 'boolean',
            'swapi_id' => 'integer',
        ];
    }
}
