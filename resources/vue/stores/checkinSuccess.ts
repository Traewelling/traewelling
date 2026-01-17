import { defineStore } from 'pinia';
import { CheckinResponse } from '../../types/Api.gen';

export const checkinSuccessStore = defineStore('checkinSuccess', {
    persist: true,
    state: () => ({
        checkinResponse: null as CheckinResponse | null,
    }),
    getters: {
        getCheckinResponse(): CheckinResponse | null {
            return this.checkinResponse;
        },
    },
    actions: {
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        setResponse(checkinResponse: CheckinResponse | any): void {
            this.checkinResponse = checkinResponse;
        },
        reset(): void {
            this.checkinResponse = null;
        },
    },
});
