<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { ref } from 'vue';
import { FormErrors, postWebForm } from '../../helpers/webForm';
import AppLayout from '../../layouts/AppLayout.vue';

const email = ref('');
const errors = ref<FormErrors>({});
const status = ref<string | null>(null);
const loading = ref(false);

async function submit(): Promise<void> {
    loading.value = true;
    errors.value = {};
    status.value = null;

    const result = await postWebForm<{ message: string }>('/password/email', { email: email.value });

    if (result.ok) {
        status.value = result.data.message;
    } else {
        errors.value = result.errors;
    }
    loading.value = false;
}
</script>

<template>
    <AppLayout>
        <div class="card bg-base-100 shadow-sm mx-auto w-full max-w-md">
            <div class="card-body">
                <form class="flex flex-col gap-4" @submit.prevent="submit">
                    <h2 class="text-3xl font-bold self-center">{{ trans('user.header-reset-pw') }}</h2>

                    <div v-if="status" class="alert alert-success" role="alert">
                        <span>{{ status }}</span>
                    </div>
                    <div v-if="errors.form" class="alert alert-error" role="alert">
                        <span>{{ errors.form[0] }}</span>
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
                            autofocus
                        />
                        <p v-if="errors.email" class="text-error" role="alert">{{ errors.email[0] }}</p>
                    </fieldset>

                    <button class="btn btn-primary" type="submit" :disabled="loading">
                        <span v-if="loading" class="loading loading-spinner loading-sm"></span>
                        {{ trans('user.reset-pw-link') }}
                    </button>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
