<script setup>
import { Link } from '@inertiajs/vue3';
import { etiquetaGenero } from '@/generos';

defineProps({
    personajes: { type: Array, required: true },
    orden: { type: String, default: null },
    direccion: { type: String, default: 'desc' },
    eliminandoId: { type: Number, default: null },
});

defineEmits(['ordenar', 'eliminar']);

const COLUMNAS = [
    { clave: 'nombre', etiqueta: 'Nombre', ordenable: true },
    { clave: 'altura', etiqueta: 'Altura', ordenable: true },
    { clave: 'masa', etiqueta: 'Masa', ordenable: true },
    { clave: 'genero', etiqueta: 'Género', ordenable: true },
    { clave: 'origen', etiqueta: 'Origen', ordenable: false },
];

const conUnidad = (valor, unidad) => (valor === null || valor === undefined ? '—' : `${valor} ${unidad}`);
</script>

<template>
    <div>
        <div class="hidden overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm md:block dark:border-slate-800 dark:bg-slate-900">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800/50 dark:text-slate-400">
                    <tr>
                        <th v-for="columna in COLUMNAS" :key="columna.clave" scope="col" class="px-4 py-3 font-medium">
                            <button
                                v-if="columna.ordenable"
                                type="button"
                                class="inline-flex items-center gap-1 uppercase hover:text-slate-800 dark:hover:text-slate-200"
                                :aria-sort="orden === columna.clave ? (direccion === 'asc' ? 'ascending' : 'descending') : 'none'"
                                @click="$emit('ordenar', columna.clave)"
                            >
                                {{ columna.etiqueta }}
                                <span :class="orden === columna.clave ? 'text-amber-500' : 'opacity-30'">
                                    {{ orden === columna.clave && direccion === 'asc' ? '▲' : '▼' }}
                                </span>
                            </button>
                            <span v-else>{{ columna.etiqueta }}</span>
                        </th>
                        <th scope="col" class="px-4 py-3 text-right font-medium">Acciones</th>
                    </tr>
                </thead>
                <TransitionGroup tag="tbody" class="divide-y divide-slate-100 dark:divide-slate-800" leave-active-class="transition duration-200" leave-to-class="opacity-0">
                    <tr
                        v-for="personaje in personajes"
                        :key="personaje.id"
                        class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40"
                    >
                        <td class="px-4 py-3 font-medium">{{ personaje.nombre }}</td>
                        <td class="px-4 py-3 tabular-nums">{{ conUnidad(personaje.altura, 'cm') }}</td>
                        <td class="px-4 py-3 tabular-nums">{{ conUnidad(personaje.masa, 'kg') }}</td>
                        <td class="px-4 py-3">{{ etiquetaGenero(personaje.genero) }}</td>
                        <td class="px-4 py-3">
                            <span
                                :class="[
                                    'rounded-full px-2 py-0.5 text-xs font-medium',
                                    personaje.origen_swapi
                                        ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300'
                                        : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
                                ]"
                            >
                                {{ personaje.origen_swapi ? `SWAPI #${personaje.swapi_id}` : 'Manual' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <Link
                                    :href="`/personajes/${personaje.id}/editar`"
                                    class="rounded-md px-2.5 py-1 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800"
                                >
                                    Editar
                                </Link>
                                <button
                                    type="button"
                                    class="rounded-md px-2.5 py-1 text-sm font-medium text-red-600 hover:bg-red-50 disabled:opacity-50 dark:text-red-400 dark:hover:bg-red-950"
                                    :disabled="eliminandoId === personaje.id"
                                    @click="$emit('eliminar', personaje)"
                                >
                                    Eliminar
                                </button>
                            </div>
                        </td>
                    </tr>
                </TransitionGroup>
            </table>
        </div>

        <ul class="grid gap-3 md:hidden">
            <li
                v-for="personaje in personajes"
                :key="personaje.id"
                class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="flex items-start justify-between gap-3">
                    <h3 class="font-medium">{{ personaje.nombre }}</h3>
                    <span
                        :class="[
                            'shrink-0 rounded-full px-2 py-0.5 text-xs font-medium',
                            personaje.origen_swapi
                                ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300'
                                : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
                        ]"
                    >
                        {{ personaje.origen_swapi ? 'SWAPI' : 'Manual' }}
                    </span>
                </div>
                <dl class="mt-3 grid grid-cols-3 gap-2 text-sm">
                    <div>
                        <dt class="text-xs text-slate-500">Altura</dt>
                        <dd class="tabular-nums">{{ conUnidad(personaje.altura, 'cm') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Masa</dt>
                        <dd class="tabular-nums">{{ conUnidad(personaje.masa, 'kg') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Género</dt>
                        <dd>{{ etiquetaGenero(personaje.genero) }}</dd>
                    </div>
                </dl>
                <div class="mt-4 grid grid-cols-2 gap-2">
                    <Link
                        :href="`/personajes/${personaje.id}/editar`"
                        class="rounded-md border border-slate-300 py-2 text-center text-sm font-medium dark:border-slate-700"
                    >
                        Editar
                    </Link>
                    <button
                        type="button"
                        class="rounded-md border border-red-200 py-2 text-sm font-medium text-red-600 disabled:opacity-50 dark:border-red-900 dark:text-red-400"
                        :disabled="eliminandoId === personaje.id"
                        @click="$emit('eliminar', personaje)"
                    >
                        Eliminar
                    </button>
                </div>
            </li>
        </ul>
    </div>
</template>
