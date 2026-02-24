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
                <i class="fa fa-calendar-day" />
            </div>
        </template>
        <mgl-popup>
            <strong
                ><a target="_blank" :href="event.url">{{ event.name }}</a></strong
            ><br />
            <i class="fa fa-user-clock" /> {{ event.host }}<br />
            <i class="fa fa-calendar-day" />
            {{ DtmRange.fromISO(event.begin, event.end).toLocaleDateString() }}<br />
            <a :href="`/event/${event.slug}`">{{ trans('events.show-all-for-event') }}</a>
        </mgl-popup>
    </mgl-marker>
</template>

<style scoped>
.event-map-marker {
    width: 34px;
    height: 34px;
    background: #c72730;
    border-radius: 50% 50% 50% 0;
    transform: rotate(-45deg);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.45);
    border: 2.5px solid #fff;
    cursor: pointer;
    transition: transform 0.15s ease;
}

.event-map-marker:hover {
    transform: rotate(-45deg) scale(1.15);
}

.event-map-marker i {
    transform: rotate(45deg);
    color: #fff;
    font-size: 14px;
    line-height: 1;
}
</style>
