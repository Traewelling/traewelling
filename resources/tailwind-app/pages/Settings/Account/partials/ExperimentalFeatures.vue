<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Api, UserProfileSettingsResource } from '../../../../../types/Api.gen';
import SettingsToggleListRow from '../../SettingsToggleListRow.vue';

defineProps<{
    profile: UserProfileSettingsResource;
}>();
const emits = defineEmits(['profile-updated']);

const api = new Api({ baseUrl: window.location.origin + '/api' });

function updateExperimental(value: boolean) {
    api.settings.updateProfileSettings({ experimental: value }).then((response) => {
        response.json().then((data) => {
            emits('profile-updated', data.data);
        });
    });
}
</script>

<template>
    <SettingsToggleListRow
        :checked="profile.experimental"
        :title="trans('settings.experimental')"
        :description="trans('settings.experimental.description')"
        @change="updateExperimental"
    />
</template>
