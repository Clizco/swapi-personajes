<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import Spinner from '@/Components/Spinner.vue';

const props = defineProps({
    variante: { type: String, default: 'primario' },
    href: { type: String, default: null },
    type: { type: String, default: 'button' },
    cargando: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
});

const VARIANTES = {
    primario: 'bg-amber-400 text-slate-900 hover:bg-amber-300 focus-visible:outline-amber-500',
    secundario:
        'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800',
    peligro: 'bg-red-600 text-white hover:bg-red-500 focus-visible:outline-red-600',
};

const clases = computed(() => [
    'inline-flex items-center justify-center gap-2 rounded-md px-4 py-2 text-sm font-medium transition',
    'focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:opacity-60',
    VARIANTES[props.variante] ?? VARIANTES.primario,
]);
</script>

<template>
    <Link v-if="href" :href="href" :class="clases">
        <slot />
    </Link>
    <button v-else :type="type" :class="clases" :disabled="disabled || cargando">
        <Spinner v-if="cargando" />
        <slot />
    </button>
</template>
