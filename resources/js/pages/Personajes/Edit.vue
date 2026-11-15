<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import PersonajeForm from '@/Components/PersonajeForm.vue';

defineProps({
    personaje: { type: Object, required: true },
    generos: { type: Array, required: true },
});
</script>

<template>
    <AppLayout :titulo="`Editar: ${personaje.nombre}`">
        <p
            v-if="personaje.origen_swapi"
            class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950/50 dark:text-amber-200"
        >
            Este personaje fue importado desde SWAPI (ID {{ personaje.swapi_id }}). Puedes editar sus datos; el origen se conserva.
        </p>

        <PersonajeForm
            :personaje="personaje"
            :generos="generos"
            metodo="put"
            :url="`/personajes/${personaje.id}`"
            texto-boton="Actualizar"
            texto-procesando="Actualizando…"
        />
    </AppLayout>
</template>
