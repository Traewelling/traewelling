import { defineStore } from 'pinia';
import { ref } from 'vue';
import type { StatusResource, StopoverResource } from '../../types/Api.gen';
import { Api } from '../../types/Api.gen';

const api = new Api({ baseUrl: window.location.origin + '/api' });

export const useActiveCheckin = defineStore(
    'activeStatus',
    () => {
        const status = ref<StatusResource | null>(null);
        const stopovers = ref<StopoverResource[] | null>(null);
        const loading = ref<boolean>(false);
        const error = ref<unknown | null>(null);
        const refreshed = ref<string>('2021-08-01T12:00:00Z');

        function reset(): void {
            status.value = null;
            refreshed.value = '2021-08-01T12:00:00Z';
            stopovers.value = null;
        }

        async function fetchStopovers(trip: number): Promise<void> {
            const response = await api.stopovers.getStopOvers(trip.toString());
            if (Object.prototype.hasOwnProperty.call(response.data.data, trip)) {
                // @ts-expect-error: the api documentation is not correct
                stopovers.value = response.data.data[trip];
            }
        }

        async function fetchActiveStatus(): Promise<void> {
            // remove status if it arrived more than 2 Minutes ago
            const time =
                status.value?.checkin.manualArrival ??
                status.value?.checkin.destination?.arrivalReal ??
                status.value?.checkin.destination?.arrivalPlanned ??
                status.value?.checkin.destination?.departureReal ??
                status.value?.checkin.destination?.departurePlanned ??
                null;
            if (time && Date.parse(time) < Date.now() - 60 * 2 * 1000) {
                status.value = null;
            }

            // Fetch Data every 2 Minutes
            // ToDo: invalidate when logging out
            if (refreshed.value && new Date().getTime() - new Date(refreshed.value).getTime() < 60 * 5 * 1000) {
                return;
            }
            loading.value = true;
            try {
                const response = await api.user.userState();
                const payload = response.data;
                status.value = payload.data && 'id' in payload.data && payload.data.id ? payload.data : null;

                if (status.value?.checkin.trip) {
                    await fetchStopovers(status.value.checkin.trip);
                }
                refreshed.value = new Date().toString();
            } catch (err) {
                error.value = err;
            } finally {
                loading.value = false;
            }
        }

        return {
            status,
            stopovers,
            loading,
            error,
            refreshed,
            reset,
            fetchStopovers,
            fetchActiveStatus,
        };
    },
    { persist: true },
);
