<script setup>
import BaseModal from '@/Components/BaseModal.vue';
import BaseButton from '@/Components/BaseButton.vue';

defineProps({
    mostrar: { type: Boolean, default: false },
    titulo: { type: String, default: '¿Estás seguro?' },
    mensaje: { type: String, required: true },
    textoConfirmar: { type: String, default: 'Confirmar' },
    procesando: { type: Boolean, default: false },
});

defineEmits(['confirmar', 'cancelar']);
</script>

<template>
    <BaseModal :mostrar="mostrar" :titulo="titulo" :bloqueado="procesando" @cerrar="$emit('cancelar')">
        <p class="text-sm text-slate-600 dark:text-slate-300">{{ mensaje }}</p>

        <template #pie>
            <BaseButton variante="secundario" :disabled="procesando" @click="$emit('cancelar')">Cancelar</BaseButton>
            <BaseButton variante="peligro" :cargando="procesando" @click="$emit('confirmar')">
                {{ textoConfirmar }}
            </BaseButton>
        </template>
    </BaseModal>
</template>
