<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { inject, ref } from 'vue';
import { Api, UserProfileSettingsResource } from '../../../../../types/Api.gen';
import SettingsListRow from '../../SettingsListRow.vue';

defineProps<{
    profile: UserProfileSettingsResource;
}>();
const emits = defineEmits<{
    'profile-updated': [profile: UserProfileSettingsResource];
}>();

const notyf = inject('notyf') as Notyf;
const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const modal = ref<HTMLDialogElement>();

const currentPassword = ref('');
const password = ref('');
const passwordConfirmation = ref('');
const loading = ref(false);

async function updatePassword() {
    loading.value = true;
    const response = await api.settings.updatePassword({
        currentPassword: currentPassword.value || undefined,
        password: password.value,
        password_confirmation: passwordConfirmation.value,
    });
    loading.value = false;

    if (response.ok) {
        const data = await response.json();
        notyf.success(trans('controller.user.password-changed-ok'));
        emits('profile-updated', data.data);
        modal.value?.close();
        currentPassword.value = '';
        password.value = '';
        passwordConfirmation.value = '';
    } else {
        const data = await response.json();
        const message = data?.message ?? trans('settings.something-wrong');
        notyf.error(message);
    }
}
</script>

<template>
    <SettingsListRow :title="trans('settings.title-change-password')" @click.prevent="modal?.showModal()" />
    <dialog ref="modal" class="modal">
        <div class="modal-box">
            <form @submit.prevent="updatePassword">
                <h3 class="text-lg font-bold">{{ trans('settings.title-change-password') }}</h3>
                <div class="flex flex-col gap-3 mt-4">
                    <input
                        v-if="profile.password"
                        v-model="currentPassword"
                        type="password"
                        class="input input-bordered w-full"
                        :placeholder="trans('settings.current-password')"
                        autocomplete="current-password"
                        required
                    />
                    <input
                        v-model="password"
                        type="password"
                        class="input input-bordered w-full"
                        :placeholder="trans('settings.new-password')"
                        autocomplete="new-password"
                        minlength="8"
                        required
                    />
                    <input
                        v-model="passwordConfirmation"
                        type="password"
                        class="input input-bordered w-full"
                        :placeholder="trans('settings.confirm-password')"
                        autocomplete="new-password"
                        required
                    />
                </div>
                <div class="modal-action">
                    <form method="dialog">
                        <button class="btn me-2">{{ trans('menu.abort') }}</button>
                    </form>
                    <button class="btn btn-primary" type="submit" :disabled="loading">
                        {{ trans('settings.btn-update') }}
                    </button>
                </div>
            </form>
        </div>
    </dialog>
</template>
