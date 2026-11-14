<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { ref } from 'vue';
import { ConfigurationFeatureEnum } from '../../../types/Api.gen';
import { useConfigurationStore } from '../../../vue/stores/configuration';
import MastodonLoginModal from '../../components/Auth/MastodonLoginModal.vue';
import Mastodon from '../../components/Icons/Mastodon.vue';
import { FormErrors, postWebForm } from '../../helpers/webForm';
import AppLayout from '../../layouts/AppLayout.vue';

const config = useConfigurationStore();

const form = ref({
    username: '',
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});
const errors = ref<FormErrors>({});
const loading = ref(false);
const mastodonModal = ref<InstanceType<typeof MastodonLoginModal> | null>(null);

async function submit(): Promise<void> {
    loading.value = true;
    errors.value = {};

    const result = await postWebForm('/register', form.value);

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
                <div
                    v-if="!config.isFeatureEnabled(ConfigurationFeatureEnum.UserRegistration)"
                    class="alert alert-info text-center"
                    role="alert"
                >
                    {{ trans('user.registration-disabled') }}
                </div>

                <form v-else class="flex flex-col gap-4" @submit.prevent="submit">
                    <h2 class="text-3xl font-bold self-center">{{ trans('user.register') }}</h2>

                    <span class="self-center">
                        {{ trans('user.has-account') }}
                        <router-link :to="{ name: 'login' }" class="link link-primary">
                            {{ trans('user.login') }}
                        </router-link>
                    </span>

                    <button class="btn btn-neutral" type="button" @click="mastodonModal?.open()">
                        <Mastodon class="size-4 fill-current" aria-hidden="true" />
                        {{ trans('user.login.mastodon') }}
                    </button>

                    <div class="divider">{{ trans('user.login.or') }}</div>

                    <div v-if="errors.form" class="alert alert-error" role="alert">
                        <span>{{ errors.form[0] }}</span>
                    </div>

                    <fieldset class="fieldset">
                        <label class="label" for="username">{{ trans('user.username') }}</label>
                        <label class="input w-full" :class="{ 'input-error': errors.username }">
                            @
                            <input
                                id="username"
                                v-model="form.username"
                                type="text"
                                class="grow"
                                required
                                autocomplete="username"
                                autocapitalize="none"
                                autofocus
                            />
                        </label>
                        <p v-if="errors.username" class="text-error" role="alert">{{ errors.username[0] }}</p>
                    </fieldset>

                    <fieldset class="fieldset">
                        <label class="label" for="name">{{ trans('user.displayname') }}</label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="input w-full"
                            :class="{ 'input-error': errors.name }"
                            required
                            autocomplete="name"
                        />
                        <p v-if="errors.name" class="text-error" role="alert">{{ errors.name[0] }}</p>
                    </fieldset>

                    <fieldset class="fieldset">
                        <label class="label" for="email">{{ trans('user.email') }}</label>
                        <input
                            id="email"
                            v-model="form.email"
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
                            v-model="form.password"
                            type="password"
                            class="input w-full"
                            :class="{ 'input-error': errors.password }"
                            required
                            minlength="8"
                            autocomplete="new-password"
                        />
                        <p v-if="errors.password" class="text-error" role="alert">{{ errors.password[0] }}</p>
                    </fieldset>

                    <fieldset class="fieldset">
                        <label class="label" for="password-confirm">{{ trans('settings.confirm-password') }}</label>
                        <input
                            id="password-confirm"
                            v-model="form.password_confirmation"
                            type="password"
                            class="input w-full"
                            required
                            minlength="8"
                            autocomplete="new-password"
                        />
                    </fieldset>

                    <button class="btn btn-primary" type="submit" :disabled="loading">
                        <span v-if="loading" class="loading loading-spinner loading-sm"></span>
                        {{ trans('user.register') }}
                    </button>
                </form>
            </div>
        </div>

        <MastodonLoginModal ref="mastodonModal" />
    </AppLayout>
</template>
