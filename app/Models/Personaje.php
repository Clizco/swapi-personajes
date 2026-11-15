<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    public function scopeBuscarPorNombre(Builder $query, ?string $texto): Builder
    {
        return $query->when(
            filled($texto),
            fn (Builder $query) => $query->where('nombre', 'like', '%'.trim($texto).'%')
        );
    }

    public function scopeDelGenero(Builder $query, ?string $genero): Builder
    {
        return $query->when(
            in_array($genero, self::GENEROS, true),
            fn (Builder $query) => $query->where('genero', $genero)
        );
    }
}
