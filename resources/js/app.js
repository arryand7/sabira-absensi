import './bootstrap';
import 'flowbite';
import '@fortawesome/fontawesome-free/css/all.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';
import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';
import collapse from '@alpinejs/collapse';

window.Alpine = Alpine;

Alpine.plugin(focus);
Alpine.plugin(collapse);
Alpine.start();
