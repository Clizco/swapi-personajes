<script setup>
import { Head, Link } from '@inertiajs/vue3';
import FlashMessage from '@/Components/FlashMessage.vue';
import { useTema } from '@/Composables/useTema';

defineProps({
    titulo: { type: String, required: true },
});

const { esOscuro, alternarTema } = useTema();
</script>

<template>
    <Head :title="titulo" />

    <div class="min-h-screen">
        <header class="border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
            <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-4 sm:px-6">
                <Link href="/personajes" class="flex items-center gap-2 font-semibold tracking-tight">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-400 text-slate-900">★</span>
                    <span>Personajes Star Wars</span>
                </Link>

                <button
                    type="button"
                    class="rounded-md border border-slate-200 px-3 py-1.5 text-sm transition hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800"
                    :aria-label="esOscuro ? 'Activar modo claro' : 'Activar modo oscuro'"
                    @click="alternarTema"
                >
                    {{ esOscuro ? '☀ Claro' : '☾ Oscuro' }}
                </button>
            </div>
        </header>

        <FlashMessage />

        <main class="mx-auto max-w-6xl animate-aparecer px-4 py-6 sm:px-6 sm:py-8">
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <h1 class="text-2xl font-semibold tracking-tight">{{ titulo }}</h1>
                <div class="flex flex-wrap gap-2">
                    <slot name="acciones" />
                </div>
            </div>

            <slot />
        </main>
    </div>
</template>
