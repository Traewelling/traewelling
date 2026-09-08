<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { inject, ref } from 'vue';
import { Api } from '../../../../types/Api.gen';

const notyf = inject('notyf') as Notyf;
const props = defineProps<{
    username: string;
}>();

const api = new Api({ baseUrl: window.location.origin + '/api' });

const modal = ref<HTMLDialogElement>();
const deleteStep = ref<1 | 2>(1);
const confirmation = ref('');
const loadingDelete = ref(false);

function open() {
    deleteStep.value = 1;
    confirmation.value = '';
    modal.value?.showModal();
}

function close() {
    modal.value?.close();
    confirmation.value = '';
    deleteStep.value = 1;
}

async function deleteAccount() {
    loadingDelete.value = true;
    try {
        const response = await api.settings.deleteUserAccount({ confirmation: confirmation.value });
        if (response.ok) {
            notyf.success(trans('settings.delete-account-completed'));
            window.location.href = '/';
        } else {
            notyf.error(trans('settings.something-wrong'));
            close();
        }
    } catch {
        notyf.error(trans('settings.something-wrong'));
        close();
    } finally {
        loadingDelete.value = false;
    }
}

defineExpose({ open });
</script>

<template>
    <dialog ref="modal" class="modal">
        <!-- Step 1: Initial warning -->
        <div v-if="deleteStep === 1" class="modal-box">
            <h5 class="mb-2 text-lg font-bold">{{ trans('settings.delete-account') }}</h5>
            <p class="text-muted small">{{ trans('settings.delete-account.detail') }}</p>
            <div class="modal-action">
                <button type="button" class="btn btn-secondary" @click="close">
                    {{ trans('menu.abort') }}
                </button>
                <button type="button" class="btn btn-error btn-outline" @click.prevent="deleteStep = 2">
                    {{ trans('settings.delete-account') }}
                </button>
            </div>
        </div>

        <!-- Step 2: Final confirmation with username -->
        <div v-else class="modal-box border-error border-3">
            <h5 class="mb-2 fw-bold text-danger">{{ trans('settings.delete-account') }}</h5>
            <p class="small">{{ trans('settings.delete-account-verify', { appname: 'Träwelling' }) }}</p>
            <form @submit.prevent="deleteAccount">
                <div class="mt-3">
                    <label for="confirmation" class="form-label small">
                        {{ trans('messages.account.please-confirm', { delete: props.username }) }}
                    </label>
                    <input
                        id="confirmation"
                        v-model="confirmation"
                        type="text"
                        class="input w-full is-invalid"
                        :placeholder="props.username ?? ''"
                        autocomplete="off"
                        required
                    />
                </div>
                <div class="modal-action">
                    <button type="button" class="btn btn-secondary" @click="deleteStep = 1">
                        {{ trans('settings.delete-account-btn-back') }}
                    </button>
                    <button
                        class="btn btn-error"
                        type="submit"
                        :disabled="loadingDelete || confirmation !== props.username"
                    >
                        {{ trans('settings.delete-account-btn-confirm') }}
                    </button>
                </div>
            </form>
        </div>

        <form method="dialog" style="display: none">
            <button>close</button>
        </form>
    </dialog>
</template>
