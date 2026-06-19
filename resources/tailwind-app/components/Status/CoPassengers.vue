<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { DateTime } from 'luxon';
import { onMounted, ref } from 'vue';
import { Api, StatusResource } from '../../../types/Api.gen';
import { getArrivalForStopover, getDepartureForStopover } from '../../../vue/helpers/DateTimeHelper';

const props = defineProps<{
    tripId: number;
    currentStatusId: number;
    departurePlanned: string | null;
    arrivalPlanned: string | null;
}>();

const emit = defineEmits<{
    hasCoPassengers: [value: boolean];
}>();

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const passengers = ref<StatusResource[]>([]);

onMounted(() => {
    api.trips
        .getTripStatuses(props.tripId)
        .then((response) => {
            response.json().then((data) => {
                passengers.value = (data.data as StatusResource[]).filter((s) => {
                    if (s.id === props.currentStatusId) return false;

                    const otherDep = s.checkin?.origin?.departurePlanned;
                    const otherArr = s.checkin?.destination?.arrivalPlanned;

                    if (!otherDep || !otherArr) return true;
                    if (!props.departurePlanned || !props.arrivalPlanned) return true;

                    return otherArr > props.departurePlanned && otherDep < props.arrivalPlanned;
                });
                emit('hasCoPassengers', passengers.value.length > 0);
            });
        })
        .catch(() => {
            emit('hasCoPassengers', false);
        });
});

function formatTime(isoString: string | null | undefined): string {
    if (!isoString) return '';
    return DateTime.fromISO(isoString).toLocaleString(DateTime.TIME_SIMPLE);
}
</script>

<template>
    <div v-if="passengers.length > 0" class="card bg-base-100 shadow-sm">
        <div class="card-body p-0">
            <div class="px-4 py-3 border-b border-base-200 font-semibold text-sm">
                {{ trans('trip-info.also-in-this-connection') }}
            </div>
            <div
                v-for="passenger in passengers"
                :key="passenger.id"
                class="flex items-center gap-3 px-4 py-3 border-b border-base-200 last:border-0"
            >
                <router-link
                    :to="{ name: 'user-profile', params: { username: passenger.user.username } }"
                    class="shrink-0"
                >
                    <img
                        loading="lazy"
                        :src="passenger.user.profilePicture"
                        :alt="passenger.user.displayName"
                        class="w-8 h-8 rounded-full object-cover"
                    />
                </router-link>
                <div class="text-sm min-w-0">
                    <router-link
                        :to="{ name: 'user-profile', params: { username: passenger.user.username } }"
                        class="link link-hover font-medium block truncate"
                    >
                        {{ passenger.user.displayName }}
                    </router-link>
                    <router-link
                        :to="{ name: 'single-status', params: { id: passenger.id } }"
                        class="text-base-content/50 hover:text-base-content transition-colors block"
                    >
                        <span class="block truncate">
                            {{ passenger.checkin.origin.name }}
                            <span class="text-base-content/40">
                                ({{ formatTime(getDepartureForStopover(passenger.checkin.origin).dateTime.toISO()) }})
                            </span>
                        </span>
                        <span class="block truncate">
                            &rarr;&nbsp;{{ passenger.checkin.destination.name }}
                            <span class="text-base-content/40">
                                ({{
                                    formatTime(getArrivalForStopover(passenger.checkin.destination).dateTime.toISO())
                                }})
                            </span>
                        </span>
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>
