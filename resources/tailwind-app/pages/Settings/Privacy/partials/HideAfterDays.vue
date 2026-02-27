<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { ref } from 'vue';
import { Api, UpdateProfileInformationRequest, UserProfileSettingsResource } from '../../../../../types/Api.gen';
import SettingsListRow from '../../SettingsListRow.vue';

const props = defineProps<{
    profile: UserProfileSettingsResource;
}>();
const emits = defineEmits(['profile-updated', 'error']);

const modal = ref<HTMLDialogElement>();
const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const input = ref<number>(props.profile.privacyHideDays);

function updateDays() {
    const data = props.profile as UpdateProfileInformationRequest;
    data.privacyHideDays = input.value;

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
        :title="trans('settings.visibility.hide')"
        :description="trans('settings.visibility.hide.explain')"
        :badge="profile.privacyHideDays > 0 ? profile.privacyHideDays.toString() : undefined"
        @click.prevent="modal?.showModal()"
    />
    <dialog class="modal" ref="modal">
        <div class="modal-box">
            <form @submit.prevent="updateDays">
                <h3 class="text-lg font-bold">{{ trans('settings.visibility.hide') }}</h3>
                <input type="number" v-model.number="input" class="input w-full mt-4" min="0" />
                <span>{{ trans('empty-input-disable-function') }}</span>
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
