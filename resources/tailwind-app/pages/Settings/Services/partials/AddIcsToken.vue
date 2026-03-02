<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { inject, ref } from 'vue';
import { Api } from '../../../../../types/Api.gen';
import SettingsListRow from '../../SettingsListRow.vue';

const emits = defineEmits(['ics-added', 'error']);
const notyf = inject('notyf') as Notyf;

const modal = ref<HTMLDialogElement>();
const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const input = ref<string>('');
const token = ref<string>('');

function createToken() {
    api.icsTokens
        .createIcsToken({ name: input.value })
        .then((response) => {
            response.json().then((data) => {
                token.value = data.data.url;
            });
            emits('ics-added');
            input.value = '';
        })
        .catch((error) => {
            emits('error', error.error.message);
            input.value = '';
            modal.value?.close();
        });
}

function copyToClipboard() {
    navigator.clipboard.writeText(token.value).then(
        () => {
            notyf.success(trans('menu.share.clipboard.success'));
        },
        () => {},
    );
}

function closeModal() {
    token.value = '';
    input.value = '';
    modal.value?.close();
}
</script>

<template>
    <SettingsListRow :title="trans('settings.create-ics-token')" @click.prevent="modal?.showModal()" />
    <dialog ref="modal" class="modal">
        <div class="modal-box">
            <form @submit.prevent="createToken">
                <h3 class="text-lg font-bold">{{ trans('settings.create-ics-token') }}</h3>
                <input
                    v-if="!token"
                    v-model="input"
                    type="text"
                    class="input input-bordered w-full mt-4"
                    :placeholder="trans('settings.ics.name-placeholder')"
                />
                <div v-if="token" class="join w-full mt-4">
                    <input type="text" class="input join-item input-bordered w-full" :value="token" readonly />
                    <button class="btn join-item" @click.prevent="copyToClipboard()">
                        {{ trans('menu.copy-link') }}
                    </button>
                </div>
                <div class="modal-action">
                    <form method="dialog">
                        <button class="btn me-2" @click="closeModal()">{{ trans('menu.close') }}</button>
                    </form>
                    <button v-if="!token" class="btn btn-primary" type="submit">
                        {{ trans('modals.edit-confirm') }}
                    </button>
                </div>
            </form>
        </div>
    </dialog>
</template>
