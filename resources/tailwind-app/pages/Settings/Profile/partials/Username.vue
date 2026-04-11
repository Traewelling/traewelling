<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { ref } from 'vue';
import { Api, UpdateProfileInformationRequest, UserProfileSettingsResource } from '../../../../../types/Api.gen';
import SettingsListRow from '../../SettingsListRow.vue';

const props = defineProps<{
    profile: UserProfileSettingsResource;
}>();
const emits = defineEmits(['profile-updated']);

const modal = ref<HTMLDialogElement>();
const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const input = ref<string>(props.profile.username);

function updateUsername() {
    const data = props.profile as UpdateProfileInformationRequest;
    data.username = input.value;
    api.settings.updateProfileSettings(data).then((response) => {
        response.json().then((data) => {
            emits('profile-updated', data.data);
        });
        modal.value?.close();
    });
}
</script>

<template>
    <SettingsListRow
        :title="trans('user.username')"
        :description="trans('user.username.description')"
        :badge="`@${profile.username}`"
        @click="modal?.showModal()"
    />
    <dialog ref="modal" class="modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold">{{ trans('user.username') }}</h3>
            <label class="input input-bordered w-full mt-4">
                <span class="label">@</span>
                <input v-model="input" type="text" />
            </label>
            <div class="modal-action">
                <form method="dialog">
                    <button class="btn me-2">{{ trans('menu.abort') }}</button>
                    <button class="btn btn-primary" @click.prevent="updateUsername()">
                        {{ trans('modals.edit-confirm') }}
                    </button>
                </form>
            </div>
        </div>
    </dialog>
</template>
