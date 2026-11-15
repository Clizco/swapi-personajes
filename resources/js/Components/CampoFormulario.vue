<script setup>
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
            <span v-if="requerido" class="text-red-500" aria-hidden="true">*</span>
        </label>

        <slot
            :clases-input="[
                'w-full rounded-md border bg-white px-3 py-2 text-sm outline-none transition focus:ring-2 dark:bg-slate-950',
                error
                    ? 'border-red-400 focus:ring-red-300'
                    : 'border-slate-300 focus:ring-amber-300 dark:border-slate-700',
            ]"
        />

        <p v-if="error" class="mt-1 text-sm text-red-600 dark:text-red-400" role="alert">{{ error }}</p>
        <p v-else-if="ayuda" class="mt-1 text-xs text-slate-500">{{ ayuda }}</p>
    </div>
</template>
