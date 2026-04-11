<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { ref } from 'vue';
import {
    Api,
    StatusVisibility,
    UpdateProfileInformationRequest,
    UserProfileSettingsResource,
} from '../../../../../types/Api.gen';
import { useProfileSettingsStore } from '../../../../../vue/stores/profileSettings';
import SettingsListRow from '../../SettingsListRow.vue';

const props = defineProps<{
    profile: UserProfileSettingsResource;
}>();
const emits = defineEmits(['profile-updated', 'error']);

const modal = ref<HTMLDialogElement>();
const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const profileStore = useProfileSettingsStore();
const input = ref<StatusVisibility>(props.profile.defaultStatusVisibility);

const visibilities = [
    { value: StatusVisibility.Value0, label: 'status.visibility.0', description: 'status.visibility.0.detail' },
    { value: StatusVisibility.Value1, label: 'status.visibility.1', description: 'status.visibility.1.detail' },
    { value: StatusVisibility.Value2, label: 'status.visibility.2', description: 'status.visibility.2.detail' },
    { value: StatusVisibility.Value3, label: 'status.visibility.3', description: 'status.visibility.3.detail' },
    { value: StatusVisibility.Value4, label: 'status.visibility.4', description: 'status.visibility.4.detail' },
    { value: StatusVisibility.Value5, label: 'status.visibility.5', description: 'status.visibility.5.detail' },
];

function getLabel(visibility: StatusVisibility): string {
    const option = visibilities.find((v) => v.value === visibility);
    return option ? trans(option.label) : '';
}

function updateVisibility() {
    const data = props.profile as UpdateProfileInformationRequest;
    data.defaultStatusVisibility = input.value;

    api.settings
        .updateProfileSettings(data)
        .then((response) => {
            response.json().then((data) => {
                profileStore.updateDefaultStatusVisibility(input.value);
                emits('profile-updated', data.data);
            });
            modal.value?.close();
        })
        .catch((error) => {
            emits('error', error.error.message);
            modal.value?.close();
        });
}
</script>

<template>
    <SettingsListRow
        :title="trans('settings.visibility.default')"
        :description="trans('settings.visibility.default.description')"
        :badge="getLabel(profile.defaultStatusVisibility)"
        @click.prevent="modal?.showModal()"
    />
    <dialog ref="modal" class="modal">
        <div class="modal-box">
            <form @submit.prevent="updateVisibility">
                <h3 class="text-lg font-bold">{{ trans('settings.visibility.default') }}</h3>
                <select v-model="input" class="select w-full">
                    <option v-for="option in visibilities" :key="option.value" :value="option.value">
                        {{ trans(option.label) }} ({{ trans(option.description) }})
                    </option>
                </select>
                <div class="modal-action">
                    <form method="dialog">
                        <button class="btn me-2">{{ trans('menu.abort') }}</button>
                    </form>
                    <button class="btn btn-primary" type="submit">
                        {{ trans('modals.edit-confirm') }}
                    </button>
                </div>
            </form>
        </div>
    </dialog>
</template>
