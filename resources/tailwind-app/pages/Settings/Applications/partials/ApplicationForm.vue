<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { ref, watch } from 'vue';
import { Api, OAuthClientResource } from '../../../../../types/Api.gen';

const props = defineProps<{
    app: OAuthClientResource | null;
    open: boolean;
}>();

const emit = defineEmits<{
    close: [];
    saved: [app: OAuthClientResource, plainSecret: string | null | undefined];
}>();

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const form = ref({
    name: '',
    redirect: '',
    confidential: true,
    webhooksEnabled: false,
    authorizedWebhookUrl: '',
    privacyPolicyUrl: '',
});

const saving = ref(false);
const errors = ref<Record<string, string[]>>({});

function resetForm() {
    errors.value = {};
    form.value = props.app
        ? {
              name: props.app.name,
              redirect: props.app.redirect,
              confidential: props.app.confidential,
              webhooksEnabled: props.app.webhooksEnabled,
              authorizedWebhookUrl: props.app.authorizedWebhookUrl ?? '',
              privacyPolicyUrl: props.app.privacyPolicyUrl ?? '',
          }
        : {
              name: '',
              redirect: '',
              confidential: true,
              webhooksEnabled: false,
              authorizedWebhookUrl: '',
              privacyPolicyUrl: '',
          };
}

watch(
    () => props.open,
    (open) => {
        if (open) resetForm();
    },
);

async function submit() {
    saving.value = true;
    errors.value = {};

    const payload = {
        name: form.value.name,
        redirect: form.value.redirect,
        confidential: form.value.confidential,
        webhooksEnabled: form.value.webhooksEnabled,
        authorizedWebhookUrl: form.value.authorizedWebhookUrl || null,
        privacyPolicyUrl: form.value.privacyPolicyUrl || null,
    };

    try {
        const response = props.app
            ? await api.applications.updateApplication(props.app.id, payload)
            : await api.applications.createApplication(payload);

        emit('saved', response.data.data, response.data.data?.plainSecret);
    } catch (e) {
        errors.value = (e as { error?: { errors?: Record<string, string[]> } })?.error?.errors ?? {};
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <dialog :open="open" class="modal modal-bottom sm:modal-middle">
        <div class="modal-box">
            <h3 class="text-lg font-bold mb-4">
                {{ app ? trans('edit-app') : trans('create-app') }}
            </h3>

            <form class="flex flex-col gap-4" @submit.prevent="submit">
                <!-- Name -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ trans('name') }}</legend>
                    <input
                        v-model="form.name"
                        type="text"
                        class="input w-full"
                        :class="{ 'input-error': errors.name }"
                        required
                        placeholder="My Application"
                    />
                    <p v-if="errors.name" class="fieldset-label text-error">{{ errors.name[0] }}</p>
                </fieldset>

                <!-- Redirect URL -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ trans('oauth.redirect') }}</legend>
                    <input
                        v-model="form.redirect"
                        type="url"
                        class="input w-full"
                        :class="{ 'input-error': errors.redirect }"
                        required
                        placeholder="https://example.com/callback"
                    />
                    <p v-if="errors.redirect" class="fieldset-label text-error">{{ errors.redirect[0] }}</p>
                </fieldset>

                <!-- Privacy Policy URL -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">
                        {{ trans('oauth.privacy-policy') }}
                        <span class="text-base-content/40 font-normal">— {{ trans('optional') }}</span>
                    </legend>
                    <input
                        v-model="form.privacyPolicyUrl"
                        type="url"
                        class="input w-full"
                        :class="{ 'input-error': errors.privacyPolicyUrl }"
                        placeholder="https://example.com/privacy"
                    />
                </fieldset>

                <div class="divider my-0"></div>

                <!-- Confidential -->
                <fieldset class="fieldset">
                    <label class="fieldset-label cursor-pointer gap-3">
                        <input v-model="form.confidential" type="checkbox" class="checkbox checkbox-sm" />
                        <span>{{ trans('oauth.confidential') }}</span>
                    </label>
                    <p v-if="app" class="fieldset-label text-warning mt-1 ms-7">
                        {{ trans('oauth.confidential-change-warning') }}
                    </p>
                </fieldset>

                <!-- Webhooks -->
                <fieldset class="fieldset">
                    <label class="fieldset-label cursor-pointer gap-3">
                        <input
                            v-model="form.webhooksEnabled"
                            type="checkbox"
                            class="checkbox checkbox-sm"
                            :disabled="app?.hasWebhooks"
                        />
                        <span>{{ trans('oauth.enable-webhooks') }}</span>
                    </label>

                    <!-- Authorized Webhook URL -->
                    <div v-if="form.webhooksEnabled || app?.hasWebhooks" class="mt-3 ms-7">
                        <p class="fieldset-legend mb-1">
                            {{ trans('oauth.authorized-webhook-url') }}
                            <span class="text-base-content/40 font-normal">— {{ trans('optional') }}</span>
                        </p>
                        <input
                            v-model="form.authorizedWebhookUrl"
                            type="url"
                            class="input w-full"
                            placeholder="https://example.com/webhook"
                        />
                    </div>
                </fieldset>

                <div class="modal-action mt-0">
                    <button type="button" class="btn btn-ghost" @click="emit('close')">
                        {{ trans('cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary" :disabled="saving">
                        <span v-if="saving" class="loading loading-spinner loading-sm"></span>
                        {{ app ? trans('save') : trans('create-app') }}
                    </button>
                </div>
            </form>
        </div>
        <div class="modal-backdrop" @click="emit('close')"></div>
    </dialog>
</template>
