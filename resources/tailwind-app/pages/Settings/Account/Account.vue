<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { inject, ref } from 'vue';
import { Api, UserProfileSettingsResource } from '../../../../types/Api.gen';
import { useUserStore } from '../../../../vue/stores/user';
import SettingsLayout from '../../../layouts/SettingsLayout.vue';
import DeleteAccount from './partials/DeleteAccount.vue';
import Email from './partials/Email.vue';
import ExperimentalFeatures from './partials/ExperimentalFeatures.vue';
import HomeStation from './partials/HomeStation.vue';
import MapProvider from './partials/MapProvider.vue';
import Password from './partials/Password.vue';
import Timezone from './partials/Timezone.vue';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const user = useUserStore();
const profile = ref<UserProfileSettingsResource | null>(null);
const loading = ref(true);

const notyf = inject('notyf') as Notyf;

const params = new URLSearchParams(window.location.search);
const emailVerified = params.has('verified');
const emailAlreadyVerified = params.has('already_verified');
if (emailVerified || emailAlreadyVerified) {
    window.history.replaceState({}, '', window.location.pathname);
}

function getUserProfile() {
    api.settings.getProfileSettings().then((response) => {
        response.json().then((data) => {
            profile.value = data.data;
            loading.value = false;
        });
    });
}

function updateProfile(updatedProfile: UserProfileSettingsResource) {
    profile.value = updatedProfile;
    notyf.success(trans('settings.saved'));
    user.fetchSettings(true);
}

function error(message: string): void {
    notyf.error(message);
}

getUserProfile();
</script>

<template>
    <SettingsLayout>
        <h2 class="text-xl font-bold">{{ trans('settings.heading.account') }}</h2>
        <div v-if="emailVerified" class="alert alert-success mt-2">
            <span>{{ trans('email.verification.success') }}</span>
        </div>
        <div v-if="emailAlreadyVerified" class="alert alert-warning mt-2">
            <span>{{ trans('email.verification.already-verified') }}</span>
        </div>
        <ul v-if="!loading && profile" class="list bg-base-100 rounded-box shadow-md mt-2">
            <Email :profile="profile" @profile-updated="updateProfile" @error="error" />
            <Password :profile="profile" @profile-updated="updateProfile" />
            <HomeStation />
            <MapProvider :profile="profile" @profile-updated="updateProfile" />
            <ExperimentalFeatures :profile="profile" @profile-updated="updateProfile" />
            <Timezone :profile @profile-updated="updateProfile" />
        </ul>
        <DeleteAccount v-if="!loading && profile" :profile="profile" />
    </SettingsLayout>
</template>
