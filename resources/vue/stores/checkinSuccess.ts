import { defineStore } from 'pinia';
import { CheckinSuccessResource } from '../../types/Api.gen';

export const checkinSuccessStore = defineStore('checkinSuccess', {
    persist: true,
    state: () => ({
        checkinResponse: null as CheckinSuccessResource | null,
    }),
    getters: {
        getCheckinSuccessResource(): CheckinSuccessResource | null {
            return this.checkinResponse;
        },
    },
    actions: {
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        setResponse(CheckinSuccessResource: CheckinSuccessResource | any): void {
            this.checkinResponse = CheckinSuccessResource;
        },
        reset(): void {
            this.checkinResponse = null;
        },
    },
});
