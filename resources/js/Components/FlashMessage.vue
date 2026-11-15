<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const DURACION_MS = 4500;

const pagina = usePage();
const mensaje = ref(null);
let temporizador = null;

const flash = computed(() => pagina.props.flash ?? {});

const cerrar = () => {
    mensaje.value = null;
    clearTimeout(temporizador);
};

watch(
    flash,
    (nuevo) => {
        const texto = nuevo.success || nuevo.error;
        if (!texto) return;

        mensaje.value = { texto, tipo: nuevo.success ? 'exito' : 'error' };
        clearTimeout(temporizador);
        temporizador = setTimeout(cerrar, DURACION_MS);
    },
    { immediate: true },
);

onBeforeUnmount(() => clearTimeout(temporizador));
</script>

<template>
    <div class="pointer-events-none fixed inset-x-0 top-4 z-50 flex justify-center px-4" aria-live="polite">
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="-translate-y-4 opacity-0"
            leave-active-class="transition duration-200 ease-in"
            leave-to-class="-translate-y-4 opacity-0"
        >
            <div
                v-if="mensaje"
                :class="[
                    'pointer-events-auto flex w-full max-w-md items-start gap-3 rounded-lg border px-4 py-3 text-sm shadow-lg',
                    mensaje.tipo === 'exito'
                        ? 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-200'
                        : 'border-red-200 bg-red-50 text-red-800 dark:border-red-800 dark:bg-red-950 dark:text-red-200',
                ]"
                role="status"
            >
                <span class="flex-1">{{ mensaje.texto }}</span>
                <button
                    type="button"
                    class="-m-1 rounded p-1 opacity-70 hover:opacity-100"
                    aria-label="Cerrar mensaje"
                    @click="cerrar"
                >
                    ✕
                </button>
            </div>
        </Transition>
    </div>
</template>
