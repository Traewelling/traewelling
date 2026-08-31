<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { inject, ref } from 'vue';
import { Api, UserProfileSettingsResource } from '../../../../../types/Api.gen';
import SettingsListRow from '../../SettingsListRow.vue';

const emits = defineEmits(['mastodon-removed', 'profile-picture-updated']);

const modal = ref<HTMLDialogElement>();
const api = new Api({ baseUrl: window.location.origin + '/api' });
const notyf = inject('notyf') as Notyf;
const input = ref<string>('');
const importingPicture = ref(false);

const props = defineProps<{
    profile: UserProfileSettingsResource;
}>();

function linkMastodon() {
    if (props.profile.mastodon) {
        api.security.deleteSocialProvider({ provider: 'mastodon' }).then(() => {
            emits('mastodon-removed');
            closeModal();
        });
    } else {
        window.location.href =
            window.location.origin + '/auth/redirect/mastodon?domain=' + encodeURIComponent(input.value);
    }
}

function importProfilePicture() {
    importingPicture.value = true;
    api.settings
        .importProfilePictureFromMastodon()
        .then(() => {
            notyf.success(trans('settings.saved'));
            emits('profile-picture-updated');
            closeModal();
        })
        .catch(() => {
            notyf.error(trans('messages.exception.general'));
        })
        .finally(() => {
            importingPicture.value = false;
        });
}

function closeModal() {
    input.value = '';
    modal.value?.close();
}
</script>

<template>
    <SettingsListRow
        :title="profile.mastodon ? 'Mastodon' : trans('settings.connect-mastodon')"
        :description="trans('settings.mastodon.description')"
        :badge="profile.mastodon"
        @click.prevent="modal?.showModal()"
    />
    <dialog ref="modal" class="modal">
        <div class="modal-box">
            <form @submit.prevent="linkMastodon">
                <h3 class="text-lg font-bold">
                    {{ profile.mastodon ? trans('settings.disconnect') : trans('settings.connect-mastodon') }}
                </h3>
                <input
                    v-if="!profile.mastodon"
                    v-model="input"
                    type="text"
                    class="input input-bordered w-full mt-4"
                    :placeholder="trans('user.mastodon-instance-url')"
                    required
                />
                <div v-if="profile.mastodon" class="mt-4">
                    <button
                        type="button"
                        class="btn btn-outline btn-sm"
                        :disabled="importingPicture"
                        @click.prevent="importProfilePicture"
                    >
                        {{ trans('settings.mastodon.import-profile-picture') }}
                    </button>
                </div>
                <div class="modal-action">
                    <form method="dialog">
                        <button class="btn me-2" @click="closeModal()">{{ trans('menu.close') }}</button>
                    </form>
                    <button class="btn btn-primary" type="submit">
                        {{ profile.mastodon ? trans('settings.disconnect') : trans('settings.connect') }}
                    </button>
                </div>
            </form>
        </div>
    </dialog>
</template>
