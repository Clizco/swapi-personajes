<script setup>
// Tabla de personajes. No hace peticiones: avisa al padre con eventos
// ("ordenar" y "eliminar") y el padre decide qué hacer.
import { Link } from '@inertiajs/vue3';
import { etiquetaGenero } from '@/generos';

defineProps({
    personajes: { type: Array, required: true },
    orden: { type: String, default: null },
    direccion: { type: String, default: null },
});

defineEmits(['ordenar', 'eliminar']);

const COLUMNAS_ORDENABLES = [
    { clave: 'nombre', etiqueta: 'Nombre' },
    { clave: 'altura', etiqueta: 'Altura' },
    { clave: 'masa', etiqueta: 'Masa' },
    { clave: 'genero', etiqueta: 'Género' },
];

const conUnidad = (valor, unidad) => (valor === null ? '—' : `${valor} ${unidad}`);
</script>

<template>
    <!-- overflow-x-auto: en el celular la tabla se desliza de lado en vez de romper la página -->
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <table class="w-full text-left text-sm whitespace-nowrap">
            <thead class="bg-slate-50 text-xs text-slate-500 uppercase dark:bg-slate-800/50 dark:text-slate-400">
                <tr>
                    <th v-for="columna in COLUMNAS_ORDENABLES" :key="columna.clave" class="px-4 py-3 font-medium">
                        <button type="button" class="uppercase hover:text-slate-800 dark:hover:text-slate-200" @click="$emit('ordenar', columna.clave)">
                            {{ columna.etiqueta }}
                            <span :class="orden === columna.clave ? 'text-amber-500' : 'opacity-30'">
                                {{ orden === columna.clave && direccion === 'asc' ? '▲' : '▼' }}
                            </span>
                        </button>
                    </th>
                    <th class="px-4 py-3 font-medium">Origen</th>
                    <th class="px-4 py-3 text-right font-medium">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <tr v-for="personaje in personajes" :key="personaje.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                    <td class="px-4 py-3 font-medium">{{ personaje.nombre }}</td>
                    <td class="px-4 py-3">{{ conUnidad(personaje.altura, 'cm') }}</td>
                    <td class="px-4 py-3">{{ conUnidad(personaje.masa, 'kg') }}</td>
                    <td class="px-4 py-3">{{ etiquetaGenero(personaje.genero) }}</td>
                    <td class="px-4 py-3">
                        <span
                            v-if="personaje.origen_swapi"
                            class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900/40 dark:text-amber-300"
                        >
                            SWAPI #{{ personaje.swapi_id }}
                        </span>
                        <span v-else class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                            Manual
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <Link
                            :href="`/personajes/${personaje.id}/editar`"
                            class="rounded-md px-2.5 py-1 font-medium text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800"
                        >
                            Editar
                        </Link>
                        <button
                            type="button"
                            class="rounded-md px-2.5 py-1 font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950"
                            @click="$emit('eliminar', personaje)"
                        >
                            Eliminar
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
