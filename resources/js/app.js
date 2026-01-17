// eslint-disable vue/one-component-per-file
import 'awesomplete/awesomplete';
import 'bootstrap';
import { i18nVue } from 'laravel-vue-i18n';
import 'leaflet/dist/leaflet.js';
import 'maplibre-gl/dist/maplibre-gl.css';
import { Notyf } from 'notyf';
import { createPinia } from 'pinia';
import piniaPluginPersistedsState from 'pinia-plugin-persistedstate';
import { createApp } from 'vue';
import ActiveJourneyMap from '../vue/components/ActiveJourneyMap.vue';
import ApiAlerts from '../vue/components/ApiAlerts.vue';
import CheckinSuccessHelper from '../vue/components/CheckinSuccessHelper.vue';
import Request from '../vue/components/Events/Request.vue';
import UserDropdown from '../vue/components/Navbar/UserDropdown.vue';
import NotificationBell from '../vue/components/NotificationBell.vue';
import FriendCheckinSettings from '../vue/components/Settings/FriendCheckinSettings.vue';
import ProfileSettings from '../vue/components/Settings/ProfileSettings.vue';
import WebhookSettings from '../vue/components/Settings/Webhooks.vue';
import StationAutocomplete from '../vue/components/StationAutocomplete/StationAutocomplete.vue';
import Stationboard from '../vue/components/Stationboard.vue';
import StatsDashboard from '../vue/components/Stats/StatsDashboard.vue';
import TagHelper from '../vue/components/TagHelper.vue';
import TripCreationForm from '../vue/components/TripCreation/TripCreationForm.vue';
import ActiveJourneys from '../vue/views/ActiveJourneys.vue';
import Dashboard from '../vue/views/Dashboard.vue';
import StationMap from '../vue/views/Debug/StationMap.vue';
import EventPage from '../vue/views/Event.vue';
import Profile from '../vue/views/Profile.vue';
import SingleStatus from '../vue/views/SingleStatus.vue';
import StatsDaily from '../vue/views/Stats/Daily.vue';
import './api/api';
import './components/maps';

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

document.addEventListener('DOMContentLoaded', function () {
    // get language query parameter
    let fallbackLang = 'en';
    const urlParams = new URLSearchParams(window.location.search);
    const lang = urlParams.get('language');
    const pinia = createPinia();
    pinia.use(piniaPluginPersistedsState);

    if (lang && lang.startsWith('de_')) {
        fallbackLang = 'de';
    }

    // TODO: As we add more vue components here, we should consider embedding them in a better way

    const i18nOptions = {
        fallbackLang: fallbackLang,
        fallbackMissingTranslations: true,
        resolve: (lang) => import(`../../lang/${lang}.json`),
    };

    if (document.getElementById('nav-main')) {
        const app = createApp({});
        app.component('NotificationBell', NotificationBell);
        app.component('VueDropdown', UserDropdown);
        app.use(pinia);
        app.use(i18nVue, i18nOptions);
        app.mount('#nav-main');
    }

    if (document.getElementById('activeJourneys')) {
        const app2 = createApp({});
        app2.component('ActiveJourneyMap', ActiveJourneyMap);
        app2.use(pinia);
        app2.use(i18nVue, i18nOptions);
        app2.mount('#activeJourneys');
    }

    if (document.getElementById('station-board-new')) {
        const app3 = createApp({});
        app3.component('Stationboard', Stationboard);
        app3.component('Stationautocomplete', StationAutocomplete);
        app3.component('Apialerts', ApiAlerts);
        app3.use(pinia);
        app3.use(i18nVue, i18nOptions);
        app3.mount('#station-board-new');
    }

    if (document.getElementById('checkin-success-helper')) {
        const app4 = createApp({});
        app4.component('CheckinSuccessHelper', CheckinSuccessHelper);
        app4.use(i18nVue, i18nOptions);
        app4.mount('#checkin-success-helper');
    }

    if (document.getElementById('tag-helper')) {
        const app5 = createApp({});
        app5.component('TagHelper', TagHelper);
        app5.use(i18nVue, i18nOptions);
        app5.mount('#tag-helper');
    }

    if (document.getElementById('settings-friend-checkin')) {
        const app6 = createApp({});
        app6.component('FriendCheckinSettings', FriendCheckinSettings);
        app6.use(i18nVue, i18nOptions);
        app6.use(pinia);
        app6.mount('#settings-friend-checkin');
    }

    if (document.getElementById('vue-request-events')) {
        const app7 = createApp({});
        app7.component('Request', Request);
        app7.use(i18nVue, i18nOptions);
        app7.use(pinia);
        app7.mount('#vue-request-events');
    }

    if (document.getElementById('settings-profile')) {
        const app8 = createApp({});
        app8.component('ProfileSettings', ProfileSettings);
        app8.use(i18nVue, i18nOptions);
        app8.use(pinia);
        app8.mount('#settings-profile');
    }

    if (document.getElementById('vue-user-profile')) {
        const app9 = createApp({});
        app9.component('Profile', Profile);
        app9.use(i18nVue, i18nOptions);
        app9.use(pinia);
        app9.mount('#vue-user-profile');
    }

    if (document.getElementById('vue-event')) {
        const app10 = createApp({});
        app10.component('Event', EventPage);
        app10.use(i18nVue, i18nOptions);
        app10.use(pinia);
        app10.mount('#vue-event');
    }

    if (document.getElementById('vue-stats-daily')) {
        const app11 = createApp({});
        app11.component('StatsDaily', StatsDaily);
        app11.use(i18nVue, i18nOptions);
        app11.use(pinia);
        app11.mount('#vue-stats-daily');
    }

    // All components that fully use the blade content slot should be mounted here.
    if (document.getElementById('vue-content')) {
        const contentApp = createApp({});
        contentApp.component('VueDashboard', Dashboard);
        contentApp.component('StatsDashboard', StatsDashboard);
        contentApp.component('Webhooks', WebhookSettings);
        contentApp.component('TripCreationForm', TripCreationForm);
        contentApp.component('SingleStatus', SingleStatus);
        contentApp.component('ActiveJourneys', ActiveJourneys);
        contentApp.component('StationMap', StationMap);
        contentApp.use(i18nVue, i18nOptions);
        contentApp.use(pinia);
        contentApp.mount('#vue-content');
    }
});

/**
 * Once the page is loaded, we can load our frontend components.
 */
window.addEventListener('load', () => {
    import('./components/DarkModeToggle');
    import('./components/progressbar');
    import('./components/settings');
    import('./components/station-autocomplete');
    import('./api/Status');
    import('./components/export');
    import('./components/business-check-in');
    import('./appControls');
    import('bootstrap-cookie-alert/cookiealert');
    import('./components/tooltips');
});
