<script setup lang="ts">
import { MglMarker, MglPopup } from '@indoorequal/vue-maplibre-gl';
import { trans } from 'laravel-vue-i18n';
import { PropType } from 'vue';
import { EventResource } from '../../../types/Api.gen';
import { DtmRange } from '../../helpers/DateRange';

defineProps({
    event: {
        type: Object as PropType<EventResource>,
        required: true,
    },
});
</script>

<template>
    <mgl-marker
        v-if="event.station?.latitude && event.station?.longitude"
        :coordinates="[event.station.longitude, event.station.latitude]"
        :offset="[0, -18]"
    >
        <template #marker>
            <div class="event-map-marker">
                <svg
                    class="event-map-icon"
                    xmlns="http://www.w3.org/2000/svg"
                    width="13"
                    height="13"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                    <line x1="16" y1="2" x2="16" y2="6" />
                    <line x1="8" y1="2" x2="8" y2="6" />
                    <line x1="3" y1="10" x2="21" y2="10" />
                </svg>
            </div>
        </template>
        <mgl-popup>
            <div class="event-popup-content">
                <strong
                    ><a target="_blank" :href="event.url">{{ event.name }}</a></strong
                ><br />
                <i class="fa fa-user-clock" /> {{ event.host }}<br />
                <i class="fa fa-calendar-day" />
                {{ DtmRange.fromISO(event.begin, event.end).toLocaleDateString() }}<br />
                <a :href="`/event/${event.slug}`">{{ trans('events.show-all-for-event') }}</a>
            </div>
        </mgl-popup>
    </mgl-marker>
</template>

<style scoped>
.maplibregl-popup .event-popup-content,
.maplibregl-popup .event-popup-content a,
.maplibregl-popup .event-popup-content strong {
    color: #000;
}

.event-map-marker {
    width: 26px;
    height: 26px;
    background: #c72730;
    border-radius: 50% 50% 50% 0;
    transform: rotate(-45deg);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.35);
    border: 2px solid rgba(255, 255, 255, 0.9);
    cursor: pointer;
    transition: transform 0.15s ease;
}

.event-map-marker:hover {
    transform: rotate(-45deg) scale(1.15);
}

.event-map-icon {
    transform: rotate(45deg);
    color: #fff;
}
</style>
