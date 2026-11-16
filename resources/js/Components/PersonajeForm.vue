<script setup>
// Formulario compartido por Crear y Editar. La validación la hace Laravel
// (PersonajeRequest) y los errores llegan solos a form.errors.
import { useForm } from '@inertiajs/vue3';
import BaseButton from '@/Components/BaseButton.vue';
import CampoFormulario from '@/Components/CampoFormulario.vue';
import { etiquetaGenero } from '@/generos';

const props = defineProps({
    personaje: { type: Object, default: () => ({}) },
    generos: { type: Array, required: true },
    metodo: { type: String, default: 'post' }, // 'post' para crear, 'put' para editar
    url: { type: String, required: true },
    textoBoton: { type: String, required: true },
});

const form = useForm({
    nombre: props.personaje.nombre ?? '',
    altura: props.personaje.altura ?? '',
    masa: props.personaje.masa ?? '',
    color_cabello: props.personaje.color_cabello ?? '',
    color_ojos: props.personaje.color_ojos ?? '',
    genero: props.personaje.genero ?? '',
    planeta_origen: props.personaje.planeta_origen ?? '',
});

const enviar = () => {
    form.submit(props.metodo, props.url, { preserveScroll: true });
};
</script>

<template>
    <form
        class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900"
        @submit.prevent="enviar"
    >
        <div class="grid gap-5 sm:grid-cols-2">
            <CampoFormulario id="nombre" etiqueta="Nombre" requerido :error="form.errors.nombre" class="sm:col-span-2">
                <input id="nombre" v-model="form.nombre" type="text" class="input" />
            </CampoFormulario>

            <CampoFormulario id="altura" etiqueta="Altura (cm)" ayuda="Entre 1 y 300" :error="form.errors.altura">
                <input id="altura" v-model="form.altura" type="number" class="input" />
            </CampoFormulario>

            <CampoFormulario id="masa" etiqueta="Masa (kg)" ayuda="Entre 1 y 500" :error="form.errors.masa">
                <input id="masa" v-model="form.masa" type="number" class="input" />
            </CampoFormulario>

            <CampoFormulario id="color_cabello" etiqueta="Color de cabello" :error="form.errors.color_cabello">
                <input id="color_cabello" v-model="form.color_cabello" type="text" class="input" />
            </CampoFormulario>

            <CampoFormulario id="color_ojos" etiqueta="Color de ojos" :error="form.errors.color_ojos">
                <input id="color_ojos" v-model="form.color_ojos" type="text" class="input" />
            </CampoFormulario>

            <CampoFormulario id="genero" etiqueta="Género" requerido :error="form.errors.genero">
                <select id="genero" v-model="form.genero" class="input">
                    <option value="" disabled>Selecciona…</option>
                    <option v-for="genero in generos" :key="genero" :value="genero">{{ etiquetaGenero(genero) }}</option>
                </select>
            </CampoFormulario>

            <CampoFormulario id="planeta_origen" etiqueta="Planeta de origen" :error="form.errors.planeta_origen">
                <input id="planeta_origen" v-model="form.planeta_origen" type="text" class="input" />
            </CampoFormulario>
        </div>

        <div class="mt-6 flex flex-col-reverse gap-2 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end dark:border-slate-800">
            <BaseButton variante="secundario" href="/personajes">Cancelar</BaseButton>
            <BaseButton type="submit" :cargando="form.processing">
                {{ form.processing ? 'Guardando…' : textoBoton }}
            </BaseButton>
        </div>
    </form>
</template>
