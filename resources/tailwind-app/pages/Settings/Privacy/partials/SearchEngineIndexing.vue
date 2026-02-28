<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { ref } from 'vue';
import { Api, UpdateProfileInformationRequest, UserProfileSettingsResource } from '../../../../../types/Api.gen';
import SettingsToggleListRow from '../../SettingsToggleListRow.vue';

const props = defineProps<{
    profile: UserProfileSettingsResource;
}>();
const emits = defineEmits(['profile-updated', 'error']);

const modal = ref<HTMLDialogElement>();
const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

function updateSearchEngine(value: boolean) {
    const data = props.profile as UpdateProfileInformationRequest;
    data.preventIndex = value;
    api.settings
        .updateProfileSettings(data)
        .then((response) => {
            response.json().then((data) => {
                emits('profile-updated', data.data);
            });
            modal.value?.close();
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
