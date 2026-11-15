<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import PersonajeForm from '@/Components/PersonajeForm.vue';

defineProps({
    generos: { type: Array, required: true },
});
</script>

<template>
    <AppLayout titulo="Crear personaje">
        <PersonajeForm
            :generos="generos"
            metodo="post"
            url="/personajes"
            texto-boton="Guardar"
            texto-procesando="Guardando…"
        />
    </AppLayout>
</template>
