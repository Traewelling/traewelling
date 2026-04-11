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
const input = ref<string>(props.profile.bio || '');

function updateBio() {
    const data = props.profile as UpdateProfileInformationRequest;
    data.bio = input.value;
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
        :title="trans('profile.bio')"
        :description="trans('profile.bio.description')"
        @click="modal?.showModal()"
    />
    <dialog ref="modal" class="modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold">{{ trans('profile.bio') }}</h3>
            <textarea v-model="input" class="textarea w-full" rows="3"></textarea>
            <div class="modal-action">
                <form method="dialog">
                    <button class="btn me-2">{{ trans('menu.abort') }}</button>
                    <button class="btn btn-primary" @click.prevent="updateBio()">
                        {{ trans('modals.edit-confirm') }}
                    </button>
                </form>
            </div>
        </div>
    </dialog>
</template>
