<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { DateTime } from 'luxon';
import { computed } from 'vue';
import { StatusResource, StopoverResource, UserResource } from '../../../../types/Api.gen';
import LoadingSkeletonRows from '../../../components/Loader/LoadingSkeletonRows.vue';
import StatusCard from '../../../components/Status/StatusCard.vue';
import { getDepartureForStatus } from '../../../helpers/DateTimeHelper';
import { useUserStore } from '../../../stores/user';

const authUser = useUserStore();
const props = defineProps<{
    statuses: StatusResource[];
    stopovers: Record<string, StopoverResource[]>;
    userData: UserResource;
    loadingStatuses: boolean;
    showMore: boolean;
    lastPage: number | null;
    currentPage: number;
}>();
const emit = defineEmits(['fetch-more-statuses']);

function isNewDay(index: number): boolean {
    if (index === 0) return true;
    const prevDt = getDepartureForStatus(props.statuses[index - 1]).dateTime;
    const currDt = getDepartureForStatus(props.statuses[index]).dateTime;
    return !currDt.hasSame(prevDt, 'day');
}

function dateTitleFor(s: StatusResource): string {
    return getDepartureForStatus(s).dateTime.toLocaleString(DateTime.DATE_HUGE);
}

function getStopoverForTrip(tripId: string) {
    return props.stopovers[tripId];
}

function statsDailyHref(s: StatusResource): string {
    const dt = getDepartureForStatus(s).dateTime;
    return `/statistics/daily/${dt.toISODate()}`;
}

const isOwnProfile = computed(() => {
    return authUser && authUser.getId === props.userData.id;
});
</script>

<template>
    <h1 v-if="statuses.length" class="fs-3">{{ trans('profile.last-journeys-of') }} {{ userData.displayName }}:</h1>

    <template v-for="(s, i) in statuses" :key="s.id">
        <h2 v-if="isNewDay(i)" class="mb-2 fs-5">
            {{ dateTitleFor(s) }}
            <a v-if="isOwnProfile" :href="statsDailyHref(s)" class="text-trwl" aria-label="Tägliche Fahrten">
                <i class="fa-solid fa-map-location-dot" aria-hidden="true" />
            </a>
        </h2>

        <StatusCard
            :status="s"
            :authenticated-user="authUser.user"
            :stopovers="getStopoverForTrip(s.checkin.trip.toString())"
        />
    </template>

    <template v-if="loadingStatuses">
        <LoadingSkeletonRows class="text-center" :row-height="30" :columns="1" :rows="1" />
        <LoadingSkeletonRows class="text-center" :row-height="15" :columns="1" :rows="1" />
        <LoadingSkeletonRows class="text-center mb-4" :row-height="206" :columns="1" :rows="5" />
    </template>

    <div v-if="!loadingStatuses && showMore" class="text-center my-4">
        <button class="btn btn-primary" :disabled="loadingStatuses" @click="emit('fetch-more-statuses')">
            <i class="fa-solid fa-arrow-down" />
        </button>
        <div v-if="lastPage !== null" class="small text-muted mt-2">{{ currentPage }} / {{ lastPage }}</div>
    </div>

    <div v-if="!loadingStatuses && !showMore && statuses.length" class="text-center my-4">
        <p class="text-muted">Final stop. All change, please!</p>
    </div>

    <div v-if="!loadingStatuses && !statuses.length && userData" class="text-center my-4">
        <span class="text-danger fs-3">
            <template v-if="(userData?.totalDistance ?? 0) > 0">
                {{ trans('profile.no-visible-statuses', { username: userData.displayName }) }}
            </template>
            <template v-else>
                {{ trans('profile.no-statuses', { username: userData.displayName }) }}
            </template>
        </span>
    </div>
</template>
