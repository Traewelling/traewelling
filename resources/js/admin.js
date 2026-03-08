import * as Popper from '@popperjs/core';
import 'bootstrap';
import 'leaflet';
import 'leaflet/dist/leaflet.js';
import { Notyf } from 'notyf';
import { createApp } from 'vue';
import RouteSegmentDetails from '../vue/components/Admin/RouteSegmentDetails.vue';
import './components/maps';

window.Popper = Popper;

document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('vue-route-segment-details');
    if (el) {
        createApp(RouteSegmentDetails, { segmentId: el.dataset.segmentId }).mount(el);
    }
});

window.notyf = new Notyf({
    duration: 5000,
    position: { x: 'right', y: window.innerWidth > 480 ? 'top' : 'bottom' },
    dismissible: true,
    ripple: true,
    types: [
        {
            type: 'info',
            background: '#0dcaf0',
            icon: {
                className: 'fa-solid fa-circle-info',
                color: 'white',
                tagName: 'i',
            },
        },
        {
            type: 'warning',
            background: '#ffc107',
            icon: {
                className: 'fa-solid fa-triangle-exclamation',
                tagName: 'i',
                color: 'white',
            },
        },
    ],
});
