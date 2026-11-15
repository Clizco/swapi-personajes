<script setup>
import { onBeforeUnmount, reactive, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import BaseButton from '@/Components/BaseButton.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import ImportModal from '@/Components/ImportModal.vue';
import Pagination from '@/Components/Pagination.vue';
import PersonajeTable from '@/Components/PersonajeTable.vue';
import Spinner from '@/Components/Spinner.vue';
import { etiquetaGenero } from '@/generos';

const ESPERA_BUSQUEDA_MS = 350;

const props = defineProps({
    personajes: { type: Object, required: true },
    filtros: { type: Object, default: () => ({}) },
    generos: { type: Array, required: true },
});

const filtros = reactive({
    buscar: props.filtros.buscar ?? '',
    genero: props.filtros.genero ?? '',
    orden: props.filtros.orden ?? null,
    direccion: props.filtros.direccion ?? 'desc',
});

const cargando = ref(false);
const mostrarImportar = ref(false);
const personajeAEliminar = ref(null);
const eliminando = ref(false);
let temporizadorBusqueda = null;

const parametrosActivos = () => {
    const { buscar, genero, orden, direccion } = filtros;
    const parametros = { buscar, genero, orden, direccion: orden ? direccion : null };

    return Object.fromEntries(Object.entries(parametros).filter(([, valor]) => valor !== '' && valor !== null));
};

const aplicarFiltros = () => {
    router.get('/personajes', parametrosActivos(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onStart: () => (cargando.value = true),
        onFinish: () => (cargando.value = false),
    });
};

const buscarConEspera = () => {
    clearTimeout(temporizadorBusqueda);
    temporizadorBusqueda = setTimeout(aplicarFiltros, ESPERA_BUSQUEDA_MS);
};

const ordenarPor = (columna) => {
    const mismaColumna = filtros.orden === columna;
    filtros.direccion = mismaColumna && filtros.direccion === 'asc' ? 'desc' : 'asc';
    filtros.orden = columna;
    aplicarFiltros();
};

const limpiarFiltros = () => {
    clearTimeout(temporizadorBusqueda);
    Object.assign(filtros, { buscar: '', genero: '', orden: null, direccion: 'desc' });
    aplicarFiltros();
};

const confirmarEliminacion = () => {
    const personaje = personajeAEliminar.value;
    const quedaPaginaVacia = props.personajes.data.length === 1 && props.personajes.current_page > 1;

    router.delete(`/personajes/${personaje.id}`, {
        preserveScroll: true,
        onStart: () => (eliminando.value = true),
        onSuccess: () => {
            personajeAEliminar.value = null;
            if (quedaPaginaVacia) {
                router.get('/personajes', { ...parametrosActivos(), page: props.personajes.current_page - 1 }, { preserveState: true });
            }
        },
        onFinish: () => (eliminando.value = false),
    });
};

onBeforeUnmount(() => clearTimeout(temporizadorBusqueda));
</script>

<template>
    <AppLayout titulo="Personajes">
        <template #acciones>
            <BaseButton variante="secundario" @click="mostrarImportar = true">Importar desde SWAPI</BaseButton>
            <BaseButton href="/personajes/crear">+ Crear nuevo</BaseButton>
        </template>

        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative flex-1">
                <label for="buscar" class="sr-only">Buscar por nombre</label>
                <input
                    id="buscar"
                    v-model="filtros.buscar"
                    type="search"
                    @input="buscarConEspera"
                    placeholder="Buscar por nombre…"
                    autocomplete="off"
                    class="w-full rounded-md border border-slate-300 bg-white py-2 pr-9 pl-3 text-sm outline-none focus:ring-2 focus:ring-amber-300 dark:border-slate-700 dark:bg-slate-900"
                />
                <Spinner v-if="cargando" class="absolute top-2.5 right-3 text-amber-500" />
            </div>

            <label for="genero" class="sr-only">Filtrar por género</label>
            <select
                id="genero"
                v-model="filtros.genero"
                @change="aplicarFiltros"
                class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-amber-300 sm:w-48 dark:border-slate-700 dark:bg-slate-900"
            >
                <option value="">Todos los géneros</option>
                <option v-for="genero in generos" :key="genero" :value="genero">{{ etiquetaGenero(genero) }}</option>
            </select>

            <button
                v-if="filtros.buscar || filtros.genero || filtros.orden"
                type="button"
                class="text-sm text-slate-500 underline-offset-2 hover:underline"
                @click="limpiarFiltros"
            >
                Limpiar filtros
            </button>
        </div>

        <div :class="['transition-opacity', cargando ? 'pointer-events-none opacity-60' : '']">
            <PersonajeTable
                v-if="personajes.data.length"
                :personajes="personajes.data"
                :orden="filtros.orden"
                :direccion="filtros.direccion"
                :eliminando-id="eliminando ? personajeAEliminar?.id : null"
                @ordenar="ordenarPor"
                @eliminar="personajeAEliminar = $event"
            />

            <div
                v-else
                class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center dark:border-slate-700 dark:bg-slate-900"
            >
                <p class="font-medium">
                    {{ filtros.buscar || filtros.genero ? 'Ningún personaje coincide con los filtros.' : 'Aún no hay personajes.' }}
                </p>
                <p class="mt-1 text-sm text-slate-500">Crea uno manualmente o impórtalo desde SWAPI.</p>
            </div>
        </div>

        <Pagination :paginador="personajes" />

        <ImportModal :mostrar="mostrarImportar" @cerrar="mostrarImportar = false" />

        <ConfirmDialog
            :mostrar="Boolean(personajeAEliminar)"
            titulo="Eliminar personaje"
            :mensaje="`¿Seguro que deseas eliminar a «${personajeAEliminar?.nombre ?? ''}»? Esta acción no se puede deshacer desde la aplicación.`"
            texto-confirmar="Eliminar"
            :procesando="eliminando"
            @confirmar="confirmarEliminacion"
            @cancelar="personajeAEliminar = null"
        />
    </AppLayout>
</template>
