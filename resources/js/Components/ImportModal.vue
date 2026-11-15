<script setup>
import { nextTick, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import BaseModal from '@/Components/BaseModal.vue';
import BaseButton from '@/Components/BaseButton.vue';

const ID_MINIMO = 1;
const ID_MAXIMO = 83;

const props = defineProps({
    mostrar: { type: Boolean, default: false },
});

const emit = defineEmits(['cerrar']);

const campoId = ref(null);
const form = useForm({ swapi_id: '' });

const cerrar = () => {
    form.reset();
    form.clearErrors();
    emit('cerrar');
};

const importar = () => {
    form.clearErrors();
    const id = Number(form.swapi_id);

    if (!Number.isInteger(id) || id < ID_MINIMO || id > ID_MAXIMO) {
        form.setError('swapi_id', `Ingresa un número entero entre ${ID_MINIMO} y ${ID_MAXIMO}.`);
        return;
    }

    form.post('/personajes/importar', {
        preserveScroll: true,
        onSuccess: cerrar,
        onError: () => campoId.value?.select(),
    });
};

watch(
    () => props.mostrar,
    async (visible) => {
        if (!visible) return;
        await nextTick();
        campoId.value?.focus();
    },
);
</script>

<template>
    <BaseModal :mostrar="mostrar" titulo="Importar desde SWAPI" :bloqueado="form.processing" @cerrar="cerrar">
        <form id="form-importar" novalidate @submit.prevent="importar">
            <label for="swapi_id" class="mb-1 block text-sm font-medium">ID del personaje en SWAPI</label>
            <input
                id="swapi_id"
                ref="campoId"
                v-model="form.swapi_id"
                type="number"
                inputmode="numeric"
                :min="ID_MINIMO"
                :max="ID_MAXIMO"
                placeholder="Ej: 1 (Luke Skywalker)"
                :disabled="form.processing"
                :aria-invalid="Boolean(form.errors.swapi_id)"
                aria-describedby="swapi_id_ayuda"
                :class="[
                    'w-full rounded-md border bg-white px-3 py-2 text-sm outline-none focus:ring-2 dark:bg-slate-950',
                    form.errors.swapi_id
                        ? 'border-red-400 focus:ring-red-300'
                        : 'border-slate-300 focus:ring-amber-300 dark:border-slate-700',
                ]"
            />
            <p v-if="form.errors.swapi_id" class="mt-2 text-sm text-red-600 dark:text-red-400" role="alert">
                {{ form.errors.swapi_id }}
            </p>
            <p v-else id="swapi_id_ayuda" class="mt-2 text-xs text-slate-500">
                Valores válidos: {{ ID_MINIMO }} a {{ ID_MAXIMO }}. Se traerá nombre, altura, masa, colores, género y planeta.
            </p>

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
