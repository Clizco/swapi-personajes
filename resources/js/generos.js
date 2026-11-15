const ETIQUETAS = {
    masculino: 'Masculino',
    femenino: 'Femenino',
    'n/a': 'N/A',
};

export const etiquetaGenero = (genero) => ETIQUETAS[genero] ?? genero;
