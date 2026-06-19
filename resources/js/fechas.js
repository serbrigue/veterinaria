import moment from 'moment';
import 'moment/locale/es';

moment.locale('es');

export { moment };

export function formatoFecha(valor, patron = 'DD/MM/YYYY') {
    if (!valor) return '—';
    const m = moment(valor);
    return m.isValid() ? m.format(patron) : '—';
}

export function fechaInput(valor) {
    if (!valor) return '';
    const m = moment(valor);
    return m.isValid() ? m.format('YYYY-MM-DD') : '';
}

export function edadDesde(valor) {
    if (!valor) return '';
    const m = moment(valor);
    return m.isValid() ? m.fromNow(true) : '';
}

export function fechaHoraInput(valor) {
    if (!valor) return '';
    const m = moment(valor);
    return m.isValid() ? m.format('YYYY-MM-DDTHH:mm') : '';
}
