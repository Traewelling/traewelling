<script setup lang="ts">
import { computed } from 'vue';
import {
    Business,
    HafasTravelType,
    MotisCategory,
    StatusResource,
    StatusVisibility,
    StopoverResource,
} from '../../../types/Api.gen';
import StatusCard from '../Status/StatusCard.vue';

const props = defineProps<{
    label: string;
    body: string;
}>();

const DEMO_ORIGIN = 'Karlsruhe Hbf';
const DEMO_DESTINATION = 'Hamburg Hbf';

const departure = new Date(Date.now() - 180 * 60 * 1000);
const arrival = new Date(Date.now() + 150 * 60 * 1000);
const arrivalReal = new Date(arrival.getTime() + 6 * 60 * 1000);

function stopover(id: number, name: string, times: Partial<StopoverResource>): StopoverResource {
    return {
        id,
        uuid: null,
        stopoverId: id,
        station: {
            id,
            uuid: null,
            name,
            latitude: 0,
            longitude: 0,
            ibnr: null,
            rilIdentifier: null,
            areas: [],
            identifiers: [],
            time_offset: null,
            created_at: null,
        },
        name,
        identifiers: [],
        rilIdentifier: null,
        evaIdentifier: null,
        arrival: null,
        arrivalPlanned: null,
        arrivalReal: null,
        arrivalPlatformPlanned: null,
        arrivalPlatformReal: null,
        departure: null,
        departurePlanned: null,
        departureReal: null,
        departurePlatformPlanned: null,
        departurePlatformReal: null,
        platform: null,
        isArrivalDelayed: false,
        isDepartureDelayed: false,
        cancelled: false,
        ...times,
    };
}

const demoStatus = computed<StatusResource>(() => ({
    id: 0,
    body: props.body,
    bodyMentions: [],
    business: Business.Value0,
    visibility: StatusVisibility.Value0,
    likes: 23,
    liked: false,
    isLikable: false,
    client: null,
    event: null,
    createdBy: null,
    tags: [],
    ticket: null,
    moderation_notes: null,
    lock_visibility: null,
    hide_body: null,
    createdAt: departure.toISOString(),
    user: {
        id: 0,
        uuid: '00000000-0000-0000-0000-000000000000',
        displayName: 'Träwelling',
        username: 'traewelling',
        profilePicture: '/images/icons/logo512.png',
        mastodon: {},
        preventIndex: false,
    },
    checkin: {
        trip: 0,
        tripUuid: null,
        hafasId: '',
        category: HafasTravelType.NationalExpress,
        mode: MotisCategory.HIGHSPEED_RAIL,
        number: 'ICE 74',
        lineName: 'ICE 74',
        routeColor: null,
        routeTextColor: null,
        journeyNumber: 74,
        manualJourneyNumber: null,
        distance: 640_000,
        points: 0,
        duration: 330,
        manualDeparture: null,
        manualArrival: null,
        operator: null,
        dataSource: null,
        origin: stopover(1, DEMO_ORIGIN, {
            departurePlanned: departure.toISOString(),
            departureReal: departure.toISOString(),
            departure: departure.toISOString(),
            platform: '2',
        }),
        destination: stopover(2, DEMO_DESTINATION, {
            arrivalPlanned: arrival.toISOString(),
            arrivalReal: arrivalReal.toISOString(),
            arrival: arrivalReal.toISOString(),
            isArrivalDelayed: true,
            platform: '1',
        }),
    },
}));

const label = computed(() => props.label);
</script>

<template>
    <div inert role="img" :aria-label="label" class="select-none">
        <StatusCard :status="demoStatus" />
    </div>
</template>
