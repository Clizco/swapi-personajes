<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    paginador: { type: Object, required: true },
});

const paginas = computed(() => props.paginador.links.slice(1, -1));

const claseEnlace = (activo) => [
    'min-w-9 rounded-md px-3 py-1.5 text-center text-sm transition',
    activo
        ? 'bg-amber-400 font-semibold text-slate-900'
        : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800',
];
</script>

<template>
    <nav
        v-if="paginador.total > 0"
        class="mt-6 flex flex-col items-center justify-between gap-3 sm:flex-row"
        aria-label="Paginación"
    >
        <p class="text-sm text-slate-500">
            Mostrando <strong>{{ paginador.from }}</strong>–<strong>{{ paginador.to }}</strong> de
            <strong>{{ paginador.total }}</strong>
        </p>

        <div v-if="paginador.last_page > 1" class="flex flex-wrap items-center justify-center gap-1">
            <Link
                v-if="paginador.prev_page_url"
                :href="paginador.prev_page_url"
                preserve-scroll
                :class="claseEnlace(false)"
                aria-label="Página anterior"
            >
                ‹ Anterior
            </Link>

            <template v-for="(pagina, indice) in paginas" :key="indice">
                <span v-if="!pagina.url" class="px-2 text-slate-400">{{ pagina.label }}</span>
                <Link
                    v-else
                    :href="pagina.url"
                    preserve-scroll
                    :class="[claseEnlace(pagina.active), 'hidden sm:inline-block']"
                    :aria-current="pagina.active ? 'page' : undefined"
                >
                    {{ pagina.label }}
                </Link>
            </template>

            <span class="px-2 text-sm text-slate-500 sm:hidden">
                {{ paginador.current_page }} / {{ paginador.last_page }}
            </span>

            <Link
                v-if="paginador.next_page_url"
                :href="paginador.next_page_url"
                preserve-scroll
                :class="claseEnlace(false)"
                aria-label="Página siguiente"
            >
                Siguiente ›
            </Link>
        </div>
    </nav>
</template>
