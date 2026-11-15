<script setup>
import { onBeforeUnmount, watch } from 'vue';

const props = defineProps({
    mostrar: { type: Boolean, default: false },
    titulo: { type: String, required: true },
    bloqueado: { type: Boolean, default: false },
});

const emit = defineEmits(['cerrar']);

const cerrar = () => {
    if (!props.bloqueado) emit('cerrar');
};

const alPresionarTecla = (evento) => {
    if (evento.key === 'Escape') cerrar();
};

watch(
    () => props.mostrar,
    (visible) => {
        document.body.classList.toggle('overflow-hidden', visible);
        visible
            ? window.addEventListener('keydown', alPresionarTecla)
            : window.removeEventListener('keydown', alPresionarTecla);
    },
);

onBeforeUnmount(() => {
    document.body.classList.remove('overflow-hidden');
    window.removeEventListener('keydown', alPresionarTecla);
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            leave-active-class="transition duration-150 ease-in"
            leave-to-class="opacity-0"
        >
            <div
                v-if="mostrar"
                class="fixed inset-0 z-40 flex items-end justify-center bg-slate-950/60 p-4 sm:items-center"
                @click.self="cerrar"
            >
                <div
                    class="w-full max-w-md animate-aparecer rounded-xl bg-white shadow-xl dark:bg-slate-900"
                    role="dialog"
                    aria-modal="true"
                    :aria-label="titulo"
                >
                    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                        <h2 class="text-lg font-semibold">{{ titulo }}</h2>
                        <button
                            type="button"
                            class="rounded p-1 text-slate-400 hover:text-slate-600 disabled:opacity-40 dark:hover:text-slate-200"
                            aria-label="Cerrar"
                            :disabled="bloqueado"
                            @click="cerrar"
                        >
                            ✕
                        </button>
                    </div>

                    <div class="px-5 py-4">
                        <slot />
                    </div>

                    <div
                        v-if="$slots.pie"
                        class="flex flex-col-reverse gap-2 border-t border-slate-200 px-5 py-4 sm:flex-row sm:justify-end dark:border-slate-800"
                    >
                        <slot name="pie" />
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
