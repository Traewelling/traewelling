<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { computed, inject, ref } from 'vue';
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
const confirmationTouched = ref(false);
const loading = ref(false);

const meetsMinLength = computed(() => password.value.length >= 8);
const passwordsMatch = computed(() => password.value === passwordConfirmation.value);
const confirmationError = computed(
    () => confirmationTouched.value && passwordConfirmation.value !== '' && !passwordsMatch.value,
);

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
    <SettingsListRow
        :title="profile.password ? trans('settings.title-change-password') : trans('settings.title-set-password')"
        :description="!profile.password ? trans('settings.warning-no-password') : undefined"
        :warning="!profile.password"
        @click.prevent="modal?.showModal()"
    />
    <dialog ref="modal" class="modal">
        <div class="modal-box">
            <form @submit.prevent="updatePassword">
                <h3 class="text-lg font-bold">
                    {{
                        profile.password
                            ? trans('settings.title-change-password')
                            : trans('settings.title-set-password')
                    }}
                </h3>
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
                    <div>
                        <input
                            v-model="password"
                            type="password"
                            class="input input-bordered w-full"
                            :placeholder="trans('settings.new-password')"
                            autocomplete="new-password"
                            minlength="8"
                            required
                        />
                        <ul class="mt-1 text-xs flex flex-col gap-0.5">
                            <li :class="meetsMinLength ? 'text-success' : 'text-base-content/50'">
                                {{ meetsMinLength ? '✓' : '·' }} {{ trans('settings.password-min-length', { min: 8 }) }}
                            </li>
                        </ul>
                    </div>
                    <div>
                        <input
                            v-model="passwordConfirmation"
                            type="password"
                            class="input input-bordered w-full"
                            :class="{ 'input-error': confirmationError }"
                            :placeholder="trans('settings.confirm-password')"
                            autocomplete="new-password"
                            required
                            @input="confirmationTouched = true"
                        />
                        <p v-if="confirmationError" class="mt-1 text-xs text-error">
                            {{ trans('settings.password-mismatch') }}
                        </p>
                    </div>
                </div>
                <div class="modal-action">
                    <form method="dialog">
                        <button class="btn me-2">{{ trans('menu.abort') }}</button>
                    </form>
                    <button
                        class="btn btn-primary"
                        type="submit"
                        :disabled="loading || !meetsMinLength || !passwordsMatch"
                    >
                        {{ trans('settings.btn-update') }}
                    </button>
                </div>
            </form>
        </div>
    </dialog>
</template>
