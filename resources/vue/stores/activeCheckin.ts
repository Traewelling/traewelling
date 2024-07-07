import {defineStore} from "pinia";
import {StatusResource, StopoverResource} from "../../types/Api";

export const useActiveCheckin = defineStore("activeStatus", {
    // because of the persist option. This option is defined in the pinia persisted state plugin
    // @types-ignore
    persist: true,
    state: () => ({
        status: null as StatusResource | null,
        stopovers: null as StopoverResource[] | null,
        loading: false,
        error: null as unknown | null,
        refreshed: "2021-08-01T12:00:00Z"
    }),
    getters: {},
    actions: {
        reset(): void {
            this.status = null;
            this.refreshed = "2021-08-01T12:00:00Z";
            this.stopovers = null;
        },
        async fetchStopovers(trip: number): Promise<void> {
            await fetch("/api/v1/stopovers/" + trip)
                .then((response: { json: () => any; }) => response.json())
                .then((data: { data: any; }) => {
                    if (data.data.hasOwnProperty(trip)) {
                        this.stopovers = data.data[trip];
                    }
                });
        },
        async fetchActiveStatus() : Promise<void>{
            // remove status if it arrived more than 2 Minutes ago

            const time = this.status?.train?.destination?.arrival ?? this.status?.train?.destination?.departure ?? null;
            if (time && Date.parse(time) < Date.now() - 60 * 2 * 1000) {
                this.status = null;
            }

            // Fetch Data every 2 Minutes
            // ToDo: invalidate when logging out
            if (this.refreshed && (new Date().getTime() - new Date(this.refreshed).getTime()) < 60 * 5 * 1000) {
                return;
            }
            this.loading = true;
            try {
                this.status = await fetch("/api/v1/user/statuses/active")
                    .then((response: { json: () => any; }) => response.json())
                    .then((data: { data: any; }) => {
                        if (data.data.id) {
                            return data.data;
                        }
                        return null;
                    });
                if (this.status?.train?.trip) {
                    await this.fetchStopovers(this.status.train.trip);
                }
                this.refreshed = new Date().toString();
            } catch (error) {
                this.error = error;
            } finally {
                this.loading = false;
            }
        }
    }
});
