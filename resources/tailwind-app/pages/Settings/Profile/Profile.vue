<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { inject, ref } from 'vue';
import { Api, UserProfileSettingsResource } from '../../../../types/Api.gen';
import { useUserStore } from '../../../../vue/stores/user';
import SettingsLayout from '../../../layouts/SettingsLayout.vue';
import Avatar from './partials/Avatar.vue';
import Bio from './partials/Bio.vue';
import Displayname from './partials/Displayname.vue';
import ProfileLinks from './partials/ProfileLinks.vue';
import Username from './partials/Username.vue';

const api = new Api({ baseUrl: window.location.origin + '/api' });

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

function updateImage() {
    getUserProfile();
    notyf.success(trans('settings.saved'));
}

getUserProfile();
</script>

<template>
    <SettingsLayout>
        <h2 class="text-xl font-bold">{{ trans('settings.title-profile') }}</h2>
        <ul v-if="!loading && profile" class="list bg-base-100 rounded-box shadow-md mt-2">
            <Avatar :profile @image-updated="updateImage" />
            <Displayname :profile @profile-updated="updateProfile" />
            <Username :profile @profile-updated="updateProfile" />
            <Bio :profile @profile-updated="updateProfile" />
            <ProfileLinks :profile @profile-updated="updateProfile" />
        </ul>
    </SettingsLayout>
</template>
