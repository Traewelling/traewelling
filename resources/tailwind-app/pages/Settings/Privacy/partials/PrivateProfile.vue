<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Api, UserProfileSettingsResource } from '../../../../../types/Api.gen';
import SettingsToggleListRow from '../../SettingsToggleListRow.vue';

defineProps<{
    profile: UserProfileSettingsResource;
}>();
const emits = defineEmits(['profile-updated', 'error']);

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

function updatePrivate(value: boolean) {
    api.settings
        .updateProfileSettings({ privateProfile: value })
        .then((response) => {
            response.json().then((data) => {
                emits('profile-updated', data.data);
            });
        })
        .catch((error) => {
            emits('error', error.error.message);
        });
}
</script>

<template>
    <SettingsToggleListRow
        :checked="profile.privateProfile"
        :title="trans('user.private-profile')"
        :description="trans('user.private-profile.description')"
        @change="updatePrivate"
    />
</template>
