<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { CircleCheck } from 'lucide-vue-next';
import { Notyf } from 'notyf';
import { inject, ref } from 'vue';
import { Api } from '../../../../../types/Api.gen';
import SettingsListRow from '../../SettingsListRow.vue';

const emits = defineEmits(['token-added', 'error']);
const notyf = inject('notyf') as Notyf;

const modal = ref<HTMLDialogElement>();
const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const loading = ref(false);
const token = ref<string>('');

function createToken() {
    loading.value = true;
    api.security
        .createToken()
        .then((response) => {
            loading.value = false;
            response.json().then((data) => {
                token.value = data.data.token;
            });
            emits('token-added');
        })
        .catch((error) => {
            loading.value = false;
            emits('error', error.error.message);
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
    modal.value?.close();
}

function openModal() {
    createToken();
    modal.value?.showModal();
}
</script>

<template>
    <SettingsListRow
        :title="trans('settings.create-ics-token')"
        :description="trans('access-token-is-private')"
        @click.prevent="openModal()"
    />
    <dialog ref="modal" class="modal">
        <div class="modal-box">
            <form @submit.prevent="createToken">
                <h3 class="text-lg font-bold">{{ trans('settings.create-ics-token') }}</h3>
                <div class="flex justify-center">
                    <span v-if="loading" class="my-4 loading loading-spinner text-primary"></span>
                </div>
                <div v-if="token" class="my-4">
                    <div role="alert" class="alert alert-success">
                        <CircleCheck class="w-6 h-6" />
                        <span>{{ trans('access-token-generated-success') }}</span>
                    </div>
                    <div class="join w-full mt-4">
                        <input type="text" class="input join-item input-bordered w-full" :value="token" readonly />
                        <button class="btn join-item" @click.prevent="copyToClipboard()">
                            {{ trans('menu.copy') }}
                        </button>
                    </div>
                </div>
                <p class="text-error">{{ trans('your-access-token.ask') }}</p>

                <div class="modal-action">
                    <form method="dialog">
                        <button class="btn me-2" @click="closeModal()">
                            {{ trans('menu.close') }}
                        </button>
                    </form>
                    <button v-if="!token" class="btn btn-primary" type="submit">
                        {{ trans('modals.edit-confirm') }}
                    </button>
                </div>
            </form>
        </div>
    </dialog>
</template>
