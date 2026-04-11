import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import type { CheckinSuccessResource } from '../../types/Api.gen';

export const checkinSuccessStore = defineStore(
    'checkinSuccess',
    () => {
        const checkinResponse = ref<CheckinSuccessResource | null>(null);

        const getCheckinSuccessResource = computed<CheckinSuccessResource | null>(() => checkinResponse.value);

        function setResponse(response: CheckinSuccessResource | null): void {
            checkinResponse.value = response;
        }

        function reset(): void {
            checkinResponse.value = null;
        }

        return {
            checkinResponse,
            getCheckinSuccessResource,
            setResponse,
            reset,
        };
    },
    { persist: true },
);
