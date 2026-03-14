<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { DateTime } from 'luxon';
import { onMounted, ref } from 'vue';
import { Api, StatusResource } from '../../../types/Api.gen';
import { getArrivalForStopover, getDepartureForStopover } from '../../helpers/DateTimeHelper';

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

                    const otherDeparturePlanned = s.checkin?.origin?.departurePlanned;
                    const otherArrivalPlanned = s.checkin?.destination?.arrivalPlanned;

                    if (!otherDeparturePlanned || !otherArrivalPlanned) return true;
                    if (!props.departurePlanned || !props.arrivalPlanned) return true;

                    return otherArrivalPlanned > props.departurePlanned && otherDeparturePlanned < props.arrivalPlanned;
                });
                emit('hasCoPassengers', passengers.value.length > 0);
            });
        })
        .catch((error) => {
            console.error('Error fetching co-passengers:', error);
            emit('hasCoPassengers', false);
        });
});

function formatTime(isoString: string | null | undefined): string {
    if (!isoString) return '';
    return DateTime.fromISO(isoString).toLocaleString(DateTime.TIME_SIMPLE);
}
</script>

<template>
    <div v-if="passengers.length > 0" class="card mb-3">
        <div class="card-header">
            {{ trans('trip-info.also-in-this-connection') }}
        </div>

        <div v-for="passenger in passengers" :key="passenger.id" class="card-footer clearfix">
            <a :href="`/@${passenger.user.username}`" class="float-start me-2">
                <img
                    loading="lazy"
                    :src="passenger.user.profilePicture"
                    :alt="passenger.user.displayName"
                    class="co-passenger-avatar"
                />
            </a>
            <div class="d-table-cell">
                <a :href="`/@${passenger.user.username}`" class="fw-semibold text-decoration-none">
                    {{ passenger.user.displayName }}
                </a>
                <br />
                <small class="text-muted">
                    <a :href="`/status/${passenger.id}`" class="text-muted text-decoration-none">
                        {{ passenger.checkin.origin.name }}
                        <span class="text-secondary"
                            >({{
                                formatTime(getDepartureForStopover(passenger.checkin.origin).dateTime.toISO())
                            }})</span
                        >
                        &rarr;
                        {{ passenger.checkin.destination.name }}
                        <span class="text-secondary"
                            >({{
                                formatTime(getArrivalForStopover(passenger.checkin.destination).dateTime.toISO())
                            }})</span
                        >
                    </a>
                </small>
            </div>
        </div>
    </div>
</template>

<style scoped lang="scss">
.co-passenger-avatar {
    height: 2em;
    border-radius: 50%;
}
</style>
