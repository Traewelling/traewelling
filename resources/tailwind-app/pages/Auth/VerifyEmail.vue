<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { inject } from 'vue';
import { Api } from '../../../types/Api.gen';
import AppLayout from '../../layouts/AppLayout.vue';

const api = new Api({ baseUrl: window.location.origin + '/api' });
const notyf = inject('notyf') as Notyf;

function resend(): void {
    api.settings
        .resendVerificationEmail()
        .then(() => notyf.success(trans('user.fresh-link')))
        .catch(() => notyf.error(trans('email.verification.too-many-requests')));
}
</script>

<template>
    <AppLayout>
        <div class="card bg-base-100 shadow-sm mx-auto w-full max-w-md">
            <div class="card-body gap-4">
                <h2 class="text-3xl font-bold self-center">{{ trans('user.email-verify') }}</h2>
                <p>{{ trans('user.please-check') }}</p>
                <p>
                    {{ trans('user.not-received-before') }}
                    <button type="button" class="link link-primary" @click="resend">
                        {{ trans('user.not-received-link') }}
                    </button>
                </p>
            </div>
        </div>
    </AppLayout>
</template>
