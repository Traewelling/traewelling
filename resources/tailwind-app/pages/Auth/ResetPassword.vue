<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { ref } from 'vue';
import { useRoute } from 'vue-router';
import { FormErrors, postWebForm } from '../../helpers/webForm';
import AppLayout from '../../layouts/AppLayout.vue';

const route = useRoute();

const email = ref(typeof route.query.email === 'string' ? route.query.email : '');
const password = ref('');
const passwordConfirmation = ref('');
const errors = ref<FormErrors>({});
const loading = ref(false);

async function submit(): Promise<void> {
    loading.value = true;
    errors.value = {};

    const result = await postWebForm('/password/reset', {
        token: route.params.token,
        email: email.value,
        password: password.value,
        password_confirmation: passwordConfirmation.value,
    });

    if (result.ok) {
        window.location.href = '/dashboard';
        return;
    }

    errors.value = result.errors;
    loading.value = false;
}
</script>

<template>
    <AppLayout>
        <div class="card bg-base-100 shadow-sm mx-auto w-full max-w-md">
            <div class="card-body">
                <form class="flex flex-col gap-4" @submit.prevent="submit">
                    <h2 class="text-3xl font-bold self-center">{{ trans('user.header-reset-pw') }}</h2>

                    <div v-if="errors.form || errors.token" class="alert alert-error" role="alert">
                        <span>{{ (errors.form ?? errors.token)[0] }}</span>
                    </div>

                    <fieldset class="fieldset">
                        <label class="label" for="email">{{ trans('user.email') }}</label>
                        <input
                            id="email"
                            v-model="email"
                            type="email"
                            class="input w-full"
                            :class="{ 'input-error': errors.email }"
                            required
                            autocomplete="email"
                        />
                        <p v-if="errors.email" class="text-error" role="alert">{{ errors.email[0] }}</p>
                    </fieldset>

                    <fieldset class="fieldset">
                        <label class="label" for="password">{{ trans('user.password') }}</label>
                        <input
                            id="password"
                            v-model="password"
                            type="password"
                            class="input w-full"
                            :class="{ 'input-error': errors.password }"
                            required
                            minlength="8"
                            autocomplete="new-password"
                            autofocus
                        />
                        <p v-if="errors.password" class="text-error" role="alert">{{ errors.password[0] }}</p>
                    </fieldset>

                    <fieldset class="fieldset">
                        <label class="label" for="password-confirm">{{ trans('settings.confirm-password') }}</label>
                        <input
                            id="password-confirm"
                            v-model="passwordConfirmation"
                            type="password"
                            class="input w-full"
                            required
                            minlength="8"
                            autocomplete="new-password"
                        />
                    </fieldset>

                    <button class="btn btn-primary" type="submit" :disabled="loading">
                        <span v-if="loading" class="loading loading-spinner loading-sm"></span>
                        {{ trans('user.header-reset-pw') }}
                    </button>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
