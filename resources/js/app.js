import './bootstrap';
import jQuery from 'jquery';
window.$ = jQuery;
window.jQuery = jQuery;
import('bootstrap').then((bootstrap) => {
    window.bootstrap = bootstrap;
});
import { Toast } from 'bootstrap';
import * as datepicker from 'bootstrap-datepicker';
window.datepicker = datepicker;
import flatpickr from "flatpickr";
window.flatpickr = flatpickr;
import selectize from '@selectize/selectize';
window.selectize = selectize;
import "@fontsource-variable/montserrat";
import 'bootstrap-icons/font/bootstrap-icons.css';

document.addEventListener('DOMContentLoaded', () => {
    // Cari semua elemen dengan class .toast
    const toastElList = document.querySelectorAll('.toast');

    // Loop dan inisialisasi setiap toast yang ditemukan
    const toastList = [...toastElList].map(toastEl => {
        // Opsi: autohide true/false, delay, dll
        const toast = new Toast(toastEl, {
            autohide: true,
            delay: 5000
        });
        toast.show();
        return toast;
    });
});
