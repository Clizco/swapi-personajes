<script setup>
// Envuelve un input con su etiqueta y su mensaje de error.
defineProps({
    id: { type: String, required: true },
    etiqueta: { type: String, required: true },
    error: { type: String, default: null },
    requerido: { type: Boolean, default: false },
    ayuda: { type: String, default: null },
});
</script>

<template>
    <div>
        <label :for="id" class="mb-1 block text-sm font-medium">
            {{ etiqueta }}
            <span v-if="requerido" class="text-red-500">*</span>
        </label>

        <slot />

        <p v-if="error" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ error }}</p>
        <p v-else-if="ayuda" class="mt-1 text-xs text-slate-500">{{ ayuda }}</p>
    </div>
</template>
