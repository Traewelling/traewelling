<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { inject, ref } from 'vue';
import { Api, UserProfileSettingsResource } from '../../../../types/Api.gen';
import { useUserStore } from '../../../../vue/stores/user';
import SettingsLayout from '../../../layouts/SettingsLayout.vue';
import ShowLikes from '../Privacy/partials/ShowLikes.vue';
import ShowPoints from '../Privacy/partials/ShowPoints.vue';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const user = useUserStore();
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
    user.fetchSettings(true);
}

function error(message: string): void {
    notyf.error(message);
}

getUserProfile();
</script>

<template>
    <SettingsLayout>
        <h2 class="text-xl font-bold">{{ trans('settings.tab.wellbeing') }}</h2>
        <ul v-if="!loading && profile" class="list bg-base-100 rounded-box shadow-md mt-2">
            <ShowLikes :profile="profile" @profile-updated="updateProfile" @error="error" />
            <ShowPoints :profile="profile" @profile-updated="updateProfile" @error="error" />
        </ul>
    </SettingsLayout>
</template>
