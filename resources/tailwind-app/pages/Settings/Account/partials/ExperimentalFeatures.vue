<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { ref } from 'vue';
import { Api, UpdateProfileInformationRequest, UserProfileSettingsResource } from '../../../../../types/Api.gen';
import SettingsToggleListRow from '../../SettingsToggleListRow.vue';

const props = defineProps<{
    profile: UserProfileSettingsResource;
}>();
const emits = defineEmits(['profile-updated']);

const modal = ref<HTMLDialogElement>();
const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

function updateExperimental(value: boolean) {
    const data = props.profile as UpdateProfileInformationRequest;
    data.experimental = value;
    api.settings.updateProfileSettings(data).then((response) => {
        response.json().then((data) => {
            emits('profile-updated', data.data);
        });
        modal.value?.close();
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
