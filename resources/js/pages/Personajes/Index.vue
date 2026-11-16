<script setup>
import { reactive, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import BaseButton from '@/Components/BaseButton.vue';
import ImportModal from '@/Components/ImportModal.vue';
import Pagination from '@/Components/Pagination.vue';
import PersonajeTable from '@/Components/PersonajeTable.vue';
import Spinner from '@/Components/Spinner.vue';
import { etiquetaGenero } from '@/generos';

const props = defineProps({
    personajes: { type: Object, required: true }, // paginador de Laravel: data, links, total...
    filtros: { type: Object, default: () => ({}) },
    generos: { type: Array, required: true },
});

// Copia local de los filtros que vienen en la URL.
const filtros = reactive({
    buscar: props.filtros.buscar ?? '',
    genero: props.filtros.genero ?? '',
    orden: props.filtros.orden ?? '',
    direccion: props.filtros.direccion ?? '',
});

const cargando = ref(false);
const mostrarImportar = ref(false);

// Pide la página otra vez al servidor con los filtros en la URL (?buscar=luke&genero=...).
const aplicarFiltros = () => {
    const parametros = Object.fromEntries(Object.entries(filtros).filter(([, valor]) => valor)); // quita los vacíos

    router.get('/personajes', parametros, {
        preserveState: true, // no reinicia los inputs mientras escribes
        replace: true,
        onStart: () => (cargando.value = true),
        onFinish: () => (cargando.value = false),
    });
};

// Busca 350 ms después de la última tecla para no hacer una petición por cada letra.
let temporizador = null;
const buscarConEspera = () => {
    clearTimeout(temporizador);
    temporizador = setTimeout(aplicarFiltros, 350);
};

// Primer clic en una columna: ascendente. Segundo clic: descendente.
const ordenarPor = (columna) => {
    filtros.direccion = filtros.orden === columna && filtros.direccion === 'asc' ? 'desc' : 'asc';
    filtros.orden = columna;
    aplicarFiltros();
};

const limpiarFiltros = () => {
    Object.assign(filtros, { buscar: '', genero: '', orden: '', direccion: '' });
    aplicarFiltros();
};

const eliminar = (personaje) => {
    if (!confirm(`¿Seguro que deseas eliminar a «${personaje.nombre}»?`)) return;

    router.delete(`/personajes/${personaje.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            // Si borramos el último de la página, volvemos a la anterior.
            if (props.personajes.data.length === 0 && props.personajes.prev_page_url) {
                router.get(props.personajes.prev_page_url);
            }
        },
    });
};
</script>

<template>
    <AppLayout titulo="Personajes">
        <template #acciones>
            <BaseButton variante="secundario" @click="mostrarImportar = true">Importar desde SWAPI</BaseButton>
            <BaseButton href="/personajes/crear">+ Crear nuevo</BaseButton>
        </template>

        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative flex-1">
                <input v-model="filtros.buscar" type="search" placeholder="Buscar por nombre…" class="input" @input="buscarConEspera" />
                <Spinner v-if="cargando" class="absolute top-2.5 right-3 text-amber-500" />
            </div>

            <select v-model="filtros.genero" class="input sm:w-48" @change="aplicarFiltros">
                <option value="">Todos los géneros</option>
                <option v-for="genero in generos" :key="genero" :value="genero">{{ etiquetaGenero(genero) }}</option>
            </select>

            <button
                v-if="filtros.buscar || filtros.genero || filtros.orden"
                type="button"
                class="text-sm text-slate-500 hover:underline"
                @click="limpiarFiltros"
            >
                Limpiar filtros
            </button>
        </div>

        <PersonajeTable
            v-if="personajes.data.length"
            :personajes="personajes.data"
            :orden="filtros.orden"
            :direccion="filtros.direccion"
            @ordenar="ordenarPor"
            @eliminar="eliminar"
        />

        <div v-else class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center dark:border-slate-700 dark:bg-slate-900">
            <p class="font-medium">No hay personajes para mostrar.</p>
            <p class="mt-1 text-sm text-slate-500">Crea uno manualmente o impórtalo desde SWAPI.</p>
        </div>

        <Pagination :paginador="personajes" />

        <ImportModal :mostrar="mostrarImportar" @cerrar="mostrarImportar = false" />
    </AppLayout>
</template>
