<script setup>
// Modal para importar un personaje de SWAPI por su ID.
// La validación (1-83, duplicados, errores de SWAPI) la hace Laravel.
import { useForm } from '@inertiajs/vue3';
import BaseModal from '@/Components/BaseModal.vue';
import BaseButton from '@/Components/BaseButton.vue';

defineProps({
    mostrar: { type: Boolean, default: false },
});

const emit = defineEmits(['cerrar']);

const form = useForm({ swapi_id: '' });

const cerrar = () => {
    form.reset();
    form.clearErrors();
    emit('cerrar');
};

const importar = () => {
    form.post('/personajes/importar', {
        preserveScroll: true,
        onSuccess: cerrar,
    });
};
</script>

<template>
    <BaseModal :mostrar="mostrar" titulo="Importar desde SWAPI" :bloqueado="form.processing" @cerrar="cerrar">
        <form id="form-importar" @submit.prevent="importar">
            <label for="swapi_id" class="mb-1 block text-sm font-medium">ID del personaje en SWAPI</label>
            <input
                id="swapi_id"
                v-model="form.swapi_id"
                type="number"
                placeholder="Ej: 1 (Luke Skywalker)"
                :disabled="form.processing"
                class="input"
            />

            <p v-if="form.errors.swapi_id" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ form.errors.swapi_id }}</p>
            <p v-else class="mt-2 text-xs text-slate-500">Valores válidos: 1 a 83.</p>

            <p v-if="form.processing" class="mt-3 text-sm text-amber-700 dark:text-amber-300">
                Consultando SWAPI, esto puede tardar unos segundos…
            </p>
        </form>

        <template #pie>
            <BaseButton variante="secundario" :disabled="form.processing" @click="cerrar">Cancelar</BaseButton>
            <BaseButton type="submit" form="form-importar" :cargando="form.processing">
                {{ form.processing ? 'Importando…' : 'Importar' }}
            </BaseButton>
        </template>
    </BaseModal>
</template>
