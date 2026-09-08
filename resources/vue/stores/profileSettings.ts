import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

import { Api, UserProfileSettingsResource } from '../../types/Api.gen';

const api = new Api({ baseUrl: window.location.origin + '/api' });

export const useProfileSettingsStore = defineStore(
    'profileSettings',
    () => {
        const settings = ref<UserProfileSettingsResource | null>(null);
        const loading = ref<boolean>(false);
        const error = ref<unknown | null>(null);
        const refreshed = ref<string | null>(null);

        const getDisplayName = computed<string>(() => settings.value?.displayName ?? '');
        const getUsername = computed<string>(() => settings.value?.username ?? '');
        const getProfilePicture = computed<string | null>(() => settings.value?.profilePicture ?? '');
        const isPrivateProfile = computed<boolean>(() => settings.value?.privateProfile ?? false);
        const isPreventIndex = computed<boolean>(() => settings.value?.preventIndex ?? false);
        const getDefaultStatusVisibility = computed<number>(() => settings.value?.defaultStatusVisibility ?? 0);
        const getPrivacyHideDays = computed<number>(() => settings.value?.privacyHideDays ?? 0);
        const getEmail = computed<string | null>(() => settings.value?.email ?? null);
        const isEmailVerified = computed<boolean>(() => settings.value?.emailVerified ?? false);
        const isProfilePictureSet = computed<boolean>(() => settings.value?.profilePictureSet ?? false);
        const getMastodon = computed<string | null>(() => settings.value?.mastodon ?? null);
        const getMastodonVisibility = computed<number>(() => settings.value?.mastodonVisibility ?? 0);

        function updateDefaultStatusVisibility(visibility: number): void {
            if (settings.value) {
                settings.value.defaultStatusVisibility = visibility;
            }
        }

        async function fetchSettings(): Promise<void> {
            // Fetch Data every 15 Minutes
            // ToDo: reduce interval
            // ToDo: invalidate when logging out
            if (refreshed.value && Date.now() - new Date(refreshed.value).getTime() < 60 * 15 * 1000) {
                return;
            }
            loading.value = true;
            try {
                const response = await api.settings.getProfileSettings();
                settings.value = response.data.data;
                refreshed.value = new Date().toString();
            } catch (err) {
                error.value = err;
            } finally {
                loading.value = false;
            }
        }

        return {
            settings,
            loading,
            error,
            refreshed,
            getDisplayName,
            getUsername,
            getProfilePicture,
            isPrivateProfile,
            isPreventIndex,
            getDefaultStatusVisibility,
            getPrivacyHideDays,
            getEmail,
            isEmailVerified,
            isProfilePictureSet,
            getMastodon,
            getMastodonVisibility,
            updateDefaultStatusVisibility,
            fetchSettings,
        };
    },
    { persist: true },
);
