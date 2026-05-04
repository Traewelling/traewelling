<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { ref } from 'vue';
import { Api, UserProfileSettingsResource } from '../../../../../types/Api.gen';
import SettingsListRow from '../../SettingsListRow.vue';

const emits = defineEmits(['mastodon-removed', 'error']);

const modal = ref<HTMLDialogElement>();
const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const input = ref<string>('');

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
