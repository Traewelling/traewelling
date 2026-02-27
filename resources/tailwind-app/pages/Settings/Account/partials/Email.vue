<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { inject, ref } from 'vue';
import { Api, UserProfileSettingsResource } from '../../../../../types/Api.gen';
import SettingsListRow from '../../SettingsListRow.vue';

const props = defineProps<{
    profile: UserProfileSettingsResource;
}>();
const emits = defineEmits(['profile-updated', 'error']);
const notyf = inject('notyf') as Notyf;

const modal = ref<HTMLDialogElement>();
const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const input = ref<string>(props.profile.email);
const password = ref<string>('');

function updateEmail() {
    api.settings
        .updateEmail({
            email: input.value,
            password: password.value,
        })
        .then((response) => {
            response.json().then((data) => {
                notyf.success(trans('user.fresh-link'));
                emits('profile-updated', data.data);
            });
            modal.value?.close();
        })
        .catch((error) => {
            emits('error', error.error.message);
            modal.value?.close();
        });
    password.value = '';
}

function resendMail() {
    api.settings
        .resendVerificationEmail()
        .then((response) => {
            if (response.ok) {
                notyf.success(trans('user.fresh-link'));
            } else {
                notyf.error(trans('email.verification.too-many-requests'));
            }
        })
        .catch((error) => {
            notyf.error(error);
        });
}
</script>

<template>
    <SettingsListRow
        :title="trans('user.email')"
        @click.prevent="modal?.showModal()"
        :badge="profile.email"
        :badge-class="!profile.emailVerified ? 'badge-warning' : ''"
    >
        <div v-if="!profile.emailVerified">
            {{ trans('user.email-verify') }}
            <a href="#" @click.prevent="resendMail" class="link">
                {{ trans('controller.status.email-resend-mail') }}
            </a>
        </div>
    </SettingsListRow>
    <dialog class="modal" ref="modal">
        <div class="modal-box">
            <form @submit.prevent="updateEmail">
                <h3 class="text-lg font-bold">{{ trans('user.email') }}</h3>
                <input
                    type="password"
                    class="input input-bordered w-full mt-4"
                    v-model="password"
                    :placeholder="trans('settings.current-password')"
                />
                <input type="email" class="input input-bordered w-full mt-4" v-model="input" />
                <div class="modal-action">
                    <form method="dialog">
                        <button class="btn me-2">{{ trans('menu.abort') }}</button>
                    </form>
                    <button class="btn btn-primary" type="submit">
                        {{ trans('modals.edit-confirm') }}
                    </button>
                </div>
            </form>
        </div>
    </dialog>
</template>
