<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { inject, ref } from 'vue';
import { Api, UserProfileSettingsResource } from '../../../../../types/Api.gen';

defineProps<{
    profile: UserProfileSettingsResource;
}>();

const notyf = inject('notyf') as Notyf;
const api = new Api({ baseUrl: window.location.origin + '/api' });
const modal = ref<HTMLDialogElement>();
const step = ref<1 | 2>(1);
const confirmation = ref('');
const loading = ref(false);

function openModal() {
    step.value = 1;
    confirmation.value = '';
    modal.value?.showModal();
}

async function deleteAccount() {
    loading.value = true;
    const response = await api.settings.deleteUserAccount({ confirmation: confirmation.value });
    loading.value = false;

    if (response.ok) {
        notyf.success(trans('settings.delete-account-completed'));
        window.location.href = '/';
    } else {
        notyf.error(trans('settings.something-wrong'));
        modal.value?.close();
        confirmation.value = '';
    }
}
</script>

<template>
    <div class="mt-4 text-center">
        <button
            class="btn btn-ghost btn-sm text-base-content/40 hover:text-error hover:bg-transparent"
            @click="openModal"
        >
            {{ trans('settings.delete-account') }}
        </button>
    </div>

    <dialog ref="modal" class="modal">
        <!-- Step 1: First check -->
        <div v-if="step === 1" class="modal-box">
            <h3 class="text-lg font-bold">{{ trans('settings.delete-account') }}</h3>
            <p class="mt-2 text-sm opacity-70">{{ trans('settings.delete-account.detail') }}</p>
            <div class="modal-action">
                <form method="dialog">
                    <button class="btn me-2">{{ trans('menu.abort') }}</button>
                </form>
                <button class="btn btn-error" @click="step = 2">
                    {{ trans('settings.delete-account') }}
                </button>
            </div>
        </div>

        <!-- Step 2: Final confirmation with username -->
        <div v-else class="modal-box border-2 border-error">
            <h3 class="text-lg font-bold text-error">{{ trans('settings.delete-account') }}</h3>
            <p class="mt-2 text-sm">{{ trans('settings.delete-account-verify', { appname: 'Träwelling' }) }}</p>
            <form @submit.prevent="deleteAccount">
                <div class="mt-4">
                    <label class="text-sm">
                        {{ trans('messages.account.please-confirm', { delete: profile.username }) }}
                    </label>
                    <input
                        v-model="confirmation"
                        type="text"
                        class="input input-bordered input-error w-full mt-2"
                        :placeholder="profile.username"
                        autocomplete="off"
                        required
                    />
                </div>
                <div class="modal-action">
                    <button type="button" class="btn me-2" @click="step = 1">
                        {{ trans('settings.delete-account-btn-back') }}
                    </button>
                    <button
                        class="btn btn-error"
                        type="submit"
                        :disabled="loading || confirmation !== profile.username"
                    >
                        {{ trans('settings.delete-account-btn-confirm') }}
                    </button>
                </div>
            </form>
        </div>

        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>
</template>
