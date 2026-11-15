import { ref } from 'vue';

const esOscuro = ref(
    typeof document !== 'undefined' && document.documentElement.classList.contains('dark'),
);

export function useTema() {
    const alternarTema = () => {
        esOscuro.value = !esOscuro.value;
        document.documentElement.classList.toggle('dark', esOscuro.value);

        try {
            localStorage.setItem('tema', esOscuro.value ? 'oscuro' : 'claro');
        } catch {
            return;
        }
    };

    return { esOscuro, alternarTema };
}
