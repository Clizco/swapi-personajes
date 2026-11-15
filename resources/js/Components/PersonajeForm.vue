<script setup>
import { useForm } from '@inertiajs/vue3';
import BaseButton from '@/Components/BaseButton.vue';
import CampoFormulario from '@/Components/CampoFormulario.vue';
import { etiquetaGenero } from '@/generos';

const props = defineProps({
    personaje: { type: Object, default: () => ({}) },
    generos: { type: Array, required: true },
    metodo: { type: String, default: 'post', validator: (valor) => ['post', 'put'].includes(valor) },
    url: { type: String, required: true },
    textoBoton: { type: String, required: true },
    textoProcesando: { type: String, default: 'Guardando…' },
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

const CAMPOS_DE_TEXTO = {
    color_cabello: 'El color de cabello',
    color_ojos: 'El color de ojos',
    planeta_origen: 'El planeta de origen',
};

const validarEntero = (campo, etiqueta, maximo) => {
    const valor = form[campo];
    if (valor === '' || valor === null) return;

    if (!Number.isInteger(valor) || valor < 1) {
        form.setError(campo, `${etiqueta} debe ser un número entero positivo.`);
    } else if (valor > maximo) {
        form.setError(campo, `${etiqueta} no debe ser mayor que ${maximo}.`);
    }
};

const validar = () => {
    form.clearErrors();
    const nombre = form.nombre.trim();

    if (!nombre) {
        form.setError('nombre', 'El nombre es obligatorio.');
    } else if (nombre.length < 3) {
        form.setError('nombre', 'El nombre debe tener al menos 3 caracteres.');
    } else if (nombre.length > 100) {
        form.setError('nombre', 'El nombre no debe tener más de 100 caracteres.');
    }

    if (!props.generos.includes(form.genero)) {
        form.setError('genero', 'Selecciona un género.');
    }

    validarEntero('altura', 'La altura', 300);
    validarEntero('masa', 'La masa', 500);

    Object.entries(CAMPOS_DE_TEXTO).forEach(([campo, etiqueta]) => {
        if ((form[campo] ?? '').length > 100) {
            form.setError(campo, `${etiqueta} no debe tener más de 100 caracteres.`);
        }
    });

    return !form.hasErrors;
};

const enviar = () => {
    if (!validar()) return;

    form[props.metodo](props.url, { preserveScroll: true });
};
</script>

<template>
    <form
        class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900"
        novalidate
        @submit.prevent="enviar"
    >
        <div class="grid gap-5 sm:grid-cols-2">
            <CampoFormulario id="nombre" etiqueta="Nombre" requerido :error="form.errors.nombre" class="sm:col-span-2">
                <template #default="{ clasesInput }">
                    <input
                        id="nombre"
                        v-model="form.nombre"
                        type="text"
                        maxlength="100"
                        autocomplete="off"
                        :class="clasesInput"
                        :aria-invalid="Boolean(form.errors.nombre)"
                    />
                </template>
            </CampoFormulario>

            <CampoFormulario id="altura" etiqueta="Altura (cm)" ayuda="Entre 1 y 300" :error="form.errors.altura">
                <template #default="{ clasesInput }">
                    <input
                        id="altura"
                        v-model.number="form.altura"
                        type="number"
                        min="1"
                        max="300"
                        step="1"
                        :class="clasesInput"
                        :aria-invalid="Boolean(form.errors.altura)"
                    />
                </template>
            </CampoFormulario>

            <CampoFormulario id="masa" etiqueta="Masa (kg)" ayuda="Entre 1 y 500" :error="form.errors.masa">
                <template #default="{ clasesInput }">
                    <input
                        id="masa"
                        v-model.number="form.masa"
                        type="number"
                        min="1"
                        max="500"
                        step="1"
                        :class="clasesInput"
                        :aria-invalid="Boolean(form.errors.masa)"
                    />
                </template>
            </CampoFormulario>

            <CampoFormulario id="color_cabello" etiqueta="Color de cabello" :error="form.errors.color_cabello">
                <template #default="{ clasesInput }">
                    <input id="color_cabello" v-model="form.color_cabello" type="text" maxlength="100" :class="clasesInput" />
                </template>
            </CampoFormulario>

            <CampoFormulario id="color_ojos" etiqueta="Color de ojos" :error="form.errors.color_ojos">
                <template #default="{ clasesInput }">
                    <input id="color_ojos" v-model="form.color_ojos" type="text" maxlength="100" :class="clasesInput" />
                </template>
            </CampoFormulario>

            <CampoFormulario id="genero" etiqueta="Género" requerido :error="form.errors.genero">
                <template #default="{ clasesInput }">
                    <select
                        id="genero"
                        v-model="form.genero"
                        :class="clasesInput"
                        :aria-invalid="Boolean(form.errors.genero)"
                    >
                        <option value="" disabled>Selecciona…</option>
                        <option v-for="genero in generos" :key="genero" :value="genero">{{ etiquetaGenero(genero) }}</option>
                    </select>
                </template>
            </CampoFormulario>

            <CampoFormulario id="planeta_origen" etiqueta="Planeta de origen" :error="form.errors.planeta_origen">
                <template #default="{ clasesInput }">
                    <input id="planeta_origen" v-model="form.planeta_origen" type="text" maxlength="100" :class="clasesInput" />
                </template>
            </CampoFormulario>
        </div>

        <div class="mt-6 flex flex-col-reverse gap-2 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end dark:border-slate-800">
            <BaseButton variante="secundario" href="/personajes">Cancelar</BaseButton>
            <BaseButton type="submit" :cargando="form.processing">
                {{ form.processing ? textoProcesando : textoBoton }}
            </BaseButton>
        </div>
    </form>
</template>
