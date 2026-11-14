<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { ref } from 'vue';
import { ConfigurationFeatureEnum } from '../../../types/Api.gen';
import { useConfigurationStore } from '../../../vue/stores/configuration';
import { FormErrors, postWebForm } from '../../helpers/webForm';
import Mastodon from '../Icons/Mastodon.vue';
import MastodonLoginModal from './MastodonLoginModal.vue';

const config = useConfigurationStore();
config.fetchData();

const login = ref('');
const password = ref('');
const remember = ref(false);
const errors = ref<FormErrors>({});
const loading = ref(false);
const mastodonModal = ref<InstanceType<typeof MastodonLoginModal> | null>(null);

async function submit(): Promise<void> {
    loading.value = true;
    errors.value = {};

    const result = await postWebForm<{ redirect: string }>('/login', {
        login: login.value,
        password: password.value,
        remember: remember.value,
    });

    if (result.ok) {
        window.location.href = result.data.redirect;
        return;
    }

    errors.value = result.errors;
    loading.value = false;
}
</script>

<template>
    <div>
        <form class="flex flex-col gap-4" @submit.prevent="submit">
            <h2 class="text-3xl font-bold self-center">{{ trans('user.login') }}</h2>

            <span v-if="config.isFeatureEnabled(ConfigurationFeatureEnum.UserRegistration)" class="self-center">
                {{ trans('user.no-account') }}
                <router-link :to="{ name: 'register' }" class="link link-primary">
                    {{ trans('user.register') }}
                </router-link>
            </span>

            <button class="btn btn-neutral" type="button" @click="mastodonModal?.open()">
                <Mastodon class="size-4 fill-current" aria-hidden="true" />
                {{ trans('user.login.mastodon') }}
            </button>

            <div class="divider">{{ trans('user.login.or') }}</div>

            <div v-if="errors.login_error || errors.form" class="alert alert-error" role="alert">
                <span>{{ (errors.login_error ?? errors.form)[0] }}</span>
            </div>

            <fieldset class="fieldset">
                <label class="label" for="login">{{ trans('user.login-credentials') }}</label>
                <input
                    id="login"
                    v-model="login"
                    type="text"
                    class="input w-full"
                    :class="{ 'input-error': errors.login }"
                    required
                    autocomplete="username"
                    autocapitalize="none"
                    autofocus
                />
                <p v-if="errors.login" class="text-error" role="alert">{{ errors.login[0] }}</p>
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
                    autocomplete="current-password"
                />
                <p v-if="errors.password" class="text-error" role="alert">{{ errors.password[0] }}</p>
                <router-link :to="{ name: 'password-request' }" class="link link-primary self-end">
                    {{ trans('user.forgot-password') }}
                </router-link>
            </fieldset>

            <label class="label cursor-pointer self-start gap-2" for="remember">
                <input id="remember" v-model="remember" type="checkbox" class="checkbox" />
                {{ trans('user.remember-me') }}
            </label>

            <button class="btn btn-primary" type="submit" :disabled="loading">
                <span v-if="loading" class="loading loading-spinner loading-sm"></span>
                {{ trans('user.login') }}
            </button>
        </form>

        <MastodonLoginModal ref="mastodonModal" />
    </div>
</template>
