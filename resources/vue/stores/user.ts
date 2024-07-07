import {defineStore} from "pinia";
import {ShortStation} from "../../types/Station";
import {StationResource, UserAuthResource} from "../../types/Api";

export const useUserStore = defineStore("user", {
    persist: true,
    state: () => ({
        user: null as UserAuthResource | null,
        loading: false,
        error: null as unknown | null,
        refreshed: "2021-08-01T12:00:00Z"
    }),
    getters: {
        getDisplayName(): string {
            return this.user?.displayName ?? "";
        },
        getUsername(): string {
            return this.user?.username ?? "";
        },
        getProfilePicture(): string {
            return this.user?.profilePicture ?? "";
        },
        getTotalDistance(): number {
            return this.user?.totalDistance ?? 0;
        },
        getTotalDuration(): number {
            return this.user?.totalDuration ?? 0;
        },
        getPoints(): number {
            return this.user?.points ?? 0;
        },
        getMastodonUrl(): string | null {
            return this.user?.mastodonUrl ?? null;
        },
        isPrivateProfile(): boolean {
            return this.user?.privateProfile ?? false;
        },
        isPreventIndex(): boolean {
            return this.user?.preventIndex ?? false;
        },
        getRoles(): string[] {
            return this.user?.roles ?? [];
        },
        getHome(): StationResource | null {
            return this.user?.home ?? null;
        },
        getLanguage(): string {
            return this.user?.language ?? "";
        },
        hasBeta(): boolean {
            return this.user?.roles?.includes("open-beta") ?? false;
        }
    },
    actions: {
        async setHome(home: ShortStation | any): Promise<void> {
            console.log(home);
            const curStation = this.user?.home;
            if (this.user) {
                this.user.home = home;
            }

            fetch(`/api/v1/station/${home.id}/home`, {
                method: "PUT"
            })
                .then(response => response.json())
                .then((data) => {
                    if (this.user) {
                        this.user.home = data.data;
                    }

                })
                .catch((error) => {
                    if (this.user) {
                        this.user.home = curStation;
                    }

                    return error;
                })
        },
        async fetchSettings(): Promise<void> {
            // Fetch Data every 15 Minutes
            // ToDo: reduce interval
            // ToDo: refresh with settings update
            // ToDo: invalidate when logging out
            if (this.refreshed && (new Date().getTime() - new Date(this.refreshed).getTime()) < 60 * 15 * 1000) {
                return;
            }
            this.loading = true;
            try {
                this.user = await fetch("/api/v1/auth/user")
                    .then((response: { json: () => any; }) => response.json())
                    .then((data: { data: any; }) => data.data);
                this.refreshed = new Date().toString();
            } catch (error) {
                this.error = error;
            } finally {
                this.loading = false;
            }
        }
    }
});
