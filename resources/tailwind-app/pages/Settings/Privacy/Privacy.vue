<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { inject, ref } from 'vue';
import { Api, TrustedUserResource, UserProfileSettingsResource } from '../../../../types/Api.gen';
import { useUserStore } from '../../../../vue/stores/user';
import SettingsLayout from '../../../layouts/SettingsLayout.vue';
import AllowFriendCheckinFor from './partials/AllowFriendCheckinFor.vue';
import DefaultVisibility from './partials/DefaultVisibility.vue';
import HideAfterDays from './partials/HideAfterDays.vue';
import ManageFriends from './partials/ManageFriends.vue';
import MastodonVisibility from './partials/MastodonVisibility.vue';
import PrivateProfile from './partials/PrivateProfile.vue';
import SearchEngineIndexing from './partials/SearchEngineIndexing.vue';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const user = useUserStore();
const profile = ref<UserProfileSettingsResource | null>(null);
const trustedUsers = ref<TrustedUserResource[]>([]);
const loading = ref(true);

const notyf = inject('notyf') as Notyf;

function getUserProfile() {
    api.settings.getProfileSettings().then((response) => {
        response.json().then((data) => {
            profile.value = data.data;
            loading.value = false;
        });
    });
}

function getTrustedUsers() {
    api.user
        .trustedUserIndex('self')
        .then((data) => {
            if (!data.ok || data.status === 404) {
                trustedUsers.value = [];
                return;
            }
            data.json().then((data) => {
                trustedUsers.value = data.data;
            });
        })
        .catch((error) => {
            notyf.error(error.error.message);
            trustedUsers.value = [];
        });
}

function updateProfile(updatedProfile: UserProfileSettingsResource) {
    profile.value = updatedProfile;
    notyf.success(trans('settings.saved'));
    user.fetchSettings(true);
}

function updateFriends(updatedFriends: TrustedUserResource[]) {
    trustedUsers.value = updatedFriends;
    notyf.success(trans('settings.saved'));
}

function error(message: string): void {
    notyf.error(message);
}

getUserProfile();
getTrustedUsers();
</script>

<template>
    <SettingsLayout>
        <h2 class="text-xl font-bold">{{ trans('settings.title-privacy') }}</h2>
        <ul v-if="!loading && profile" class="list bg-base-100 rounded-box shadow-md mt-2">
            <PrivateProfile :profile="profile" @profile-updated="updateProfile" @error="error" />
            <SearchEngineIndexing :profile="profile" @profile-updated="updateProfile" @error="error" />
            <DefaultVisibility :profile="profile" @profile-updated="updateProfile" @error="error" />
            <MastodonVisibility
                v-if="profile.mastodon"
                :profile="profile"
                @profile-updated="updateProfile"
                @error="error"
            />
            <HideAfterDays :profile="profile" @profile-updated="updateProfile" @error="error" />
        </ul>

        <h2 class="text-xl font-bold mt-4">
            {{ trans('settings.friend_checkin') }}
        </h2>
        <ul v-if="!loading && profile" class="list bg-base-100 rounded-box shadow-md mt-2">
            <AllowFriendCheckinFor :profile="profile" @profile-updated="updateProfile" @error="error" />
            <ManageFriends
                :profile="profile"
                :trusted-users="trustedUsers"
                @profile-updated="updateProfile"
                @friends-updated="updateFriends"
                @error="error"
            />
        </ul>
    </SettingsLayout>
</template>
