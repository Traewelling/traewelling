import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { Api, type StationResource, type UserAuthResource } from '../../types/Api.gen';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

export const useUserStore = defineStore(
    'user',
    () => {
        const user = ref<UserAuthResource | null>(null);
        const authenticated = ref<boolean | null>(null);
        const loading = ref<boolean>(false);
        const error = ref<unknown | null>(null);
        const refreshed = ref<string>('2021-08-01T12:00:00Z');

        const getId = computed<number | null>(() => user.value?.id ?? null);
        const getDisplayName = computed<string>(() => user.value?.displayName ?? '');
        const getUsername = computed<string>(() => user.value?.username ?? '');
        const getProfilePicture = computed<string>(() => user.value?.profilePicture ?? '');
        const getTotalDistance = computed<number>(() => user.value?.totalDistance ?? 0);
        const getTotalDuration = computed<number>(() => user.value?.totalDuration ?? 0);
        const getPoints = computed<number>(() => user.value?.points ?? 0);
        const getMastodonUrl = computed<string | null>(() => user.value?.mastodonUrl ?? null);
        const isPrivateProfile = computed<boolean>(() => user.value?.privateProfile ?? false);
        const isPreventIndex = computed<boolean>(() => user.value?.preventIndex ?? false);
        const getRoles = computed<string[]>(() => user.value?.roles ?? []);
        const getHome = computed<StationResource | null>(() => user.value?.home ?? null);
        const getLanguage = computed<string>(() => user.value?.language ?? '');
        const hasBeta = computed<boolean>(() => user.value?.roles?.includes('open-beta') ?? false);
        const isAdmin = computed<boolean>(() => user.value?.roles?.includes('admin') ?? false);
        const isEventModerator = computed<boolean>(() => user.value?.roles?.includes('event-moderator') ?? false);
        const isClosedBeta = computed<boolean>(() => user.value?.roles?.includes('closed-beta') ?? false);
        const isAuthenticated = computed<boolean>(() => authenticated.value === true);

        async function setHome(home: StationResource): Promise<void> {
            const curStation = user.value?.home ?? null;

            api.station
                .setHomeStation(home.id)
                .then((response) => {
                    if (user.value && response.data.data) {
                        const newStation = response.data.data as StationResource;
                        newStation.areas = [];

                        user.value.home = newStation;
                    }
                })
                .catch((err: unknown) => {
                    if (curStation && user.value) {
                        user.value.home = curStation;
                    }
                    return err;
                });
        }

        function invalidateUser(): void {
            user.value = null;
            authenticated.value = false;
            refreshed.value = new Date().toString();
            error.value = null;
            loading.value = false;
        }

        async function fetchSettings(force: boolean = false, authRefresh: boolean = false): Promise<void> {
            // Fetch Data every 5 Minutes
            // ToDo: refresh with settings update
            if (
                force ||
                (!authenticated.value && authRefresh) ||
                (refreshed.value && Date.now() - new Date(refreshed.value).getTime() > 60 * 5 * 1000)
            ) {
                loading.value = true;
                api.auth
                    .getAuthenticatedUser()
                    .then((response) => {
                        user.value = response.data.data || null;
                        authenticated.value = !!user.value;

                        loading.value = false;
                        refreshed.value = new Date().toString();
                    })
                    .catch((err: unknown) => {
                        error.value = err;
                        authenticated.value = false;

                        loading.value = false;
                        refreshed.value = new Date().toString();
                    });
            }
        }

        return {
            user,
            authenticated,
            loading,
            error,
            refreshed,
            getId,
            getDisplayName,
            getUsername,
            getProfilePicture,
            getTotalDistance,
            getTotalDuration,
            getPoints,
            getMastodonUrl,
            isPrivateProfile,
            isPreventIndex,
            getRoles,
            getHome,
            getLanguage,
            hasBeta,
            isAdmin,
            isEventModerator,
            isClosedBeta,
            isAuthenticated,
            setHome,
            invalidateUser,
            fetchSettings,
        };
    },
    {
        // Exclude `refreshed` from persistence so it always resets to the default
        // stale date on page load, guaranteeing fetchSettings runs a fresh API call
        // after every login — even if a different user logged in on the same device.
        persist: { omit: ['refreshed'] },
    },
);
