<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { ref } from 'vue';
import { Api } from '../../../types/Api.gen';

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
            window.notyf.success(trans('settings.delete-account-completed'));
            window.location.href = '/';
        } else {
            window.notyf.error(trans('settings.something-wrong'));
            close();
        }
    } catch {
        window.notyf.error(trans('settings.something-wrong'));
        close();
    } finally {
        loadingDelete.value = false;
    }
}

defineExpose({ open });
</script>

<template>
    <dialog
        ref="modal"
        style="z-index: 1100; border: none; border-radius: 0.5rem; padding: 0; max-width: 500px; width: 90%"
    >
        <!-- Step 1: Initial warning -->
        <div v-if="deleteStep === 1" style="padding: 1.5rem">
            <h5 class="mb-2 fw-bold">{{ trans('settings.delete-account') }}</h5>
            <p class="text-muted small">{{ trans('settings.delete-account.detail') }}</p>
            <div class="d-flex justify-content-end gap-2 mt-3">
                <button type="button" class="btn btn-secondary" @click="close">
                    {{ trans('menu.abort') }}
                </button>
                <button type="button" class="btn btn-danger" @click="deleteStep = 2">
                    {{ trans('settings.delete-account') }}
                </button>
            </div>
        </div>

        <!-- Step 2: Final confirmation with username -->
        <div v-else style="padding: 1.5rem; border: 2px solid #dc3545; border-radius: 0.5rem">
            <h5 class="mb-2 fw-bold text-danger">{{ trans('settings.delete-account') }}</h5>
            <p class="small">{{ trans('settings.delete-account-verify', { appname: 'Träwelling' }) }}</p>
            <form @submit.prevent="deleteAccount">
                <div class="mt-3">
                    <label class="form-label small">
                        {{ trans('messages.account.please-confirm', { delete: props.username }) }}
                    </label>
                    <input
                        v-model="confirmation"
                        type="text"
                        class="form-control is-invalid"
                        :placeholder="props.username ?? ''"
                        autocomplete="off"
                        required
                    />
                </div>
                <div class="d-flex justify-content-end gap-2 mt-3">
                    <button type="button" class="btn btn-secondary" @click="deleteStep = 1">
                        {{ trans('settings.delete-account-btn-back') }}
                    </button>
                    <button
                        class="btn btn-danger"
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
