import {defineStore} from "pinia";
import {User} from "../../types/User";
import {ShortStation} from "../../types/Station";

export const useUserStore = defineStore('user', {
    persist: true,
    state: () => ({
        user: null as User | null,
        loading: false,
        error: null as unknown | null,
        refreshed: "2021-08-01T12:00:00Z"
    }),
    getters: {
        getDisplayName(): string {
            return this.user ? this.user.displayName : '';
        },
        getUsername(): string {
            return this.user ? this.user.username : '';
        },
        getProfilePicture(): string {
            return this.user ? this.user.profilePicture : '';
        },
        getTrainDistance(): number {
            return this.user ? this.user.trainDistance : 0;
        },
        getTrainDuration(): number {
            return this.user ? this.user.trainDuration : 0;
        },
        getPoints(): number {
            return this.user ? this.user.points : 0;
        },
        getMastodonUrl(): string | null {
            return this.user ? this.user.mastodonUrl : null;
        },
        isPrivateProfile(): boolean {
            return this.user ? this.user.privateProfile : false;
        },
        getPrivacyHideDays(): number {
            return this.user ? this.user.privacyHideDays : 0;
        },
        isPreventIndex(): boolean {
            return this.user ? this.user.preventIndex : false;
        },
        getRole(): number {
            return this.user ? this.user.role : 0;
        },
        getHome(): ShortStation | null {
            return this.user ? this.user.home : null;
        },
        getLanguage(): string {
            return this.user ? this.user.language : '';
        }
    },
    actions: {
        async setHome(home: ShortStation|any): Promise<void> {
            console.log(home);
            const curStation = this.user?.home;
            if (this.user) {
                this.user.home = home;
            }

            fetch(`/api/v1/station/${home.id}/home`, {
                method: 'PUT'
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
                this.user = await fetch('/api/v1/auth/user')
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
