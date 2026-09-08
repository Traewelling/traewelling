<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Api, UserProfileSettingsResource } from '../../../../../types/Api.gen';
import SettingsToggleListRow from '../../SettingsToggleListRow.vue';

defineProps<{
    profile: UserProfileSettingsResource;
}>();
const emits = defineEmits(['profile-updated', 'error']);

const api = new Api({ baseUrl: window.location.origin + '/api' });

function updatePoints(value: boolean) {
    api.settings
        .updateProfileSettings({ pointsEnabled: value })
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
        :checked="profile.pointsEnabled"
        :title="trans('user.points-enabled')"
        :description="trans('user.points-enabled.description')"
        @change="updatePoints"
    />
</template>
