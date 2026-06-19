import Swal from 'sweetalert2';

export function alertaExito(titulo, texto = '') {
    return Swal.fire({
        icon: 'success',
        title: titulo,
        text: texto,
        confirmButtonText: 'OK',
    });
}

export function alertaError(titulo, texto = '') {
    return Swal.fire({
        icon: 'error',
        title: titulo,
        text: texto,
        confirmButtonText: 'OK',
    });
}

export function confirmar(titulo, texto = '') {
    return Swal.fire({
        icon: 'question',
        title: titulo,
        text: texto,
        showCancelButton: true,
        confirmButtonText: 'Sí',
        cancelButtonText: 'No',
    });
}

export function alertaValidacion(errors) {
    const lista = Object.values(errors || {}).flat();
    return alertaError('Datos incorrectos', lista[0] || 'Revisa el formulario.');
}
