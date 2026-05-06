<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Api, UserProfileSettingsResource } from '../../../../../types/Api.gen';
import SettingsToggleListRow from '../../SettingsToggleListRow.vue';

defineProps<{
    profile: UserProfileSettingsResource;
}>();
const emits = defineEmits(['profile-updated', 'error']);

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

function updateSearchEngine(value: boolean) {
    api.settings
        .updateProfileSettings({ preventIndex: value })
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
        :checked="profile.preventIndex"
        :title="trans('settings.prevent-indexing')"
        :description="trans('settings.search-engines.description')"
        @change="updateSearchEngine"
    />
</template>
