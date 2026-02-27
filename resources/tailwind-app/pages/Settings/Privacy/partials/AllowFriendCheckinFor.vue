<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { ref } from 'vue';
import {
    Api,
    FriendCheckinSetting,
    UpdateProfileInformationRequest,
    UserProfileSettingsResource,
} from '../../../../../types/Api.gen';
import SettingsListRow from '../../SettingsListRow.vue';

const props = defineProps<{
    profile: UserProfileSettingsResource;
}>();
const emits = defineEmits(['profile-updated', 'error']);

const modal = ref<HTMLDialogElement>();
const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const input = ref<FriendCheckinSetting>(props.profile.friendCheckin);

const options = [
    {
        value: FriendCheckinSetting.Forbidden,
        label: 'settings.friend_checkin.forbidden',
    },
    {
        value: FriendCheckinSetting.Friends,
        label: 'settings.friend_checkin.friends',
    },
    {
        value: FriendCheckinSetting.List,
        label: 'settings.friend_checkin.list',
    },
];

function getLabel(visibility: FriendCheckinSetting): string {
    const option = options.find((v) => v.value === visibility);
    return option ? trans(option.label) : '';
}

function updateVisibility() {
    const data = props.profile as UpdateProfileInformationRequest;
    data.friendCheckin = input.value;

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
            modal.value?.close();
        });
}
</script>

<template>
    <SettingsListRow
        :title="trans('settings.allow_friend_checkin_for')"
        :badge="getLabel(profile.friendCheckin)"
        @click.prevent="modal?.showModal()"
    />
    <dialog ref="modal" class="modal">
        <div class="modal-box">
            <form @submit.prevent="updateVisibility">
                <h3 class="text-lg font-bold">{{ trans('settings.allow_friend_checkin_for') }}</h3>
                <select v-model="input" class="select w-full">
                    <option v-for="option in options" :key="option.value" :value="option.value">
                        {{ trans(option.label) }}
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
