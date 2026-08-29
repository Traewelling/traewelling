<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Api, UserProfileSettingsResource } from '../../../../../types/Api.gen';
import SettingsToggleListRow from '../../SettingsToggleListRow.vue';

defineProps<{
    profile: UserProfileSettingsResource;
}>();
const emits = defineEmits(['profile-updated', 'error']);

const api = new Api({ baseUrl: window.location.origin + '/api' });

function updateLikes(value: boolean) {
    api.settings
        .updateProfileSettings({ likesEnabled: value })
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
        :checked="profile.likesEnabled"
        :title="trans('user.likes-enabled')"
        :description="trans('user.likes-enabled.description')"
        @change="updateLikes"
    />
</template>
