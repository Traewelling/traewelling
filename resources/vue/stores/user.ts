import { defineStore } from 'pinia';
import { Api, StationResource, UserAuthResource } from '../../types/Api.gen';
import { ShortStation } from '../../types/Station';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

export const useUserStore = defineStore('user', {
    persist: true,
    state: () => ({
        user: null as UserAuthResource | null,
        authenticated: null as boolean | null,
        loading: false,
        error: null as unknown | null,
        refreshed: '2021-08-01T12:00:00Z',
    }),
    getters: {
        getId(): number | null {
            return this.user?.id ?? null;
        },
        getDisplayName(): string {
            return this.user?.displayName ?? '';
        },
        getUsername(): string {
            return this.user?.username ?? '';
        },
        getProfilePicture(): string {
            return this.user?.profilePicture ?? '';
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
            return this.user?.language ?? '';
        },
        hasBeta(): boolean {
            return this.user?.roles?.includes('open-beta') ?? false;
        },
        isAdmin(): boolean {
            return this.user?.roles?.includes('admin') ?? false;
        },
        isEventModerator(): boolean {
            return this.user?.roles?.includes('event-moderator') ?? false;
        },
        isClosedBeta(): boolean {
            return this.user?.roles?.includes('closed-beta') ?? false;
        },
        isAuthenticated(): boolean {
            return this.authenticated === true;
        },
    },
    actions: {
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        async setHome(home: ShortStation | any): Promise<void> {
            const curStation = this.user?.home;

            api.station
                .setHomeStation(home.id)
                .then((response) => {
                    if (this.user && response.data.data) {
                        const newStation = response.data.data as StationResource;
                        newStation.areas = [];

                        this.user.home = newStation;
                    }
                })
                .catch((error) => {
                    if (curStation && this.user) {
                        this.user.home = curStation;
                    }
                    return error;
                });
        },
        invalidateUser(): void {
            this.user = null;
            this.authenticated = false;
            this.refreshed = new Date().toString();
            this.error = null;
            this.loading = false;
        },
        async fetchSettings(force: boolean = false, authRefresh: boolean = false): Promise<void> {
            // Fetch Data every 5 Minutes
            // ToDo: refresh with settings update
            if (
                force ||
                (!this.authenticated && authRefresh) ||
                (this.refreshed && new Date().getTime() - new Date(this.refreshed).getTime() > 60 * 5 * 1000)
            ) {
                this.loading = true;
                api.auth
                    .getAuthenticatedUser()
                    .then((response) => {
                        this.user = response.data.data || null;
                        this.authenticated = !!this.user;

                        this.loading = false;
                        this.refreshed = new Date().toString();
                    })
                    .catch((error) => {
                        this.error = error;
                        this.authenticated = false;

                        this.loading = false;
                        this.refreshed = new Date().toString();
                    });
            }
        },
    },
});
