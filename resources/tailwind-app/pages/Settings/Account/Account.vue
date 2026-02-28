<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { inject, ref } from 'vue';
import { Api, UserProfileSettingsResource } from '../../../../types/Api.gen';
import SettingsLayout from '../../../layouts/SettingsLayout.vue';
import Email from './partials/Email.vue';
import ExperimentalFeatures from './partials/ExperimentalFeatures.vue';
import MapProvider from './partials/MapProvider.vue';
import Timezone from './partials/Timezone.vue';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const profile = ref<UserProfileSettingsResource | null>(null);
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

function updateProfile(updatedProfile: UserProfileSettingsResource) {
    profile.value = updatedProfile;
    notyf.success(trans('settings.saved'));
}

function error(message: string): void {
    notyf.error(message);
}

getUserProfile();
</script>

<template>
    <SettingsLayout>
        <h2 class="text-xl font-bold">{{ trans('settings.heading.account') }}</h2>
        <ul v-if="!loading && profile" class="list bg-base-100 rounded-box shadow-md mt-2">
            <Timezone :profile @profile-updated="updateProfile" />
            <Email :profile="profile" @profile-updated="updateProfile" @error="error" />
            <MapProvider :profile="profile" @profile-updated="updateProfile" />
            <ExperimentalFeatures :profile="profile" @profile-updated="updateProfile" />
            <!-- todo: password -->
            <!-- todo: delete account -->
        </ul>
    </SettingsLayout>
</template>
