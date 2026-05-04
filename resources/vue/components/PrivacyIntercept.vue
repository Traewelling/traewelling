<script setup lang="ts">
import DOMPurify from 'dompurify';
import { trans } from 'laravel-vue-i18n';
import { marked } from 'marked';
import { computed, onMounted, ref } from 'vue';
import { Api } from '../../types/Api.gen';

const props = defineProps<{
    username: string;
}>();

const isLoggedIn = computed(() => !!props.username);

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const policyId = ref<string | null>(null);
const policyChanged = ref(false);
const hasUserSigned = ref(false);
const policyHtml = ref('');
const loading = ref(true);

const showActions = computed(() => isLoggedIn.value && !hasUserSigned.value);

const modal = ref<HTMLDialogElement>();
const deleteStep = ref<1 | 2>(1);
const confirmation = ref('');
const loadingAccept = ref(false);
const loadingDelete = ref(false);

onMounted(async () => {
    if (!isLoggedIn.value) {
        const policyResponse = await api.privacyPolicies.appHttpControllersApiV1PrivacyPolicyController();
        if (policyResponse.ok) {
            const policy = policyResponse.data.data;
            const locale = document.documentElement.lang ?? 'en';
            const markdown = locale.startsWith('de') ? policy.de : policy.en;
            policyHtml.value = DOMPurify.sanitize(await marked.parse(markdown));
        }
        loading.value = false;
        return;
    }

    const policyResponse = await api.privacyPolicies.appHttpControllersApiV1PrivacyPolicyController();

    if (policyResponse.ok) {
        const policy = policyResponse.data.data;

        policyId.value = policy.id;
        hasUserSigned.value = policy.acceptedAt !== null;
        policyChanged.value = !hasUserSigned.value && policy.hasOldAcceptance;

        const locale = document.documentElement.lang ?? 'en';
        const markdown = locale.startsWith('de') ? policy.de : policy.en;
        policyHtml.value = DOMPurify.sanitize(await marked.parse(markdown));
    }

    loading.value = false;
});

function openDeleteModal() {
    deleteStep.value = 1;
    confirmation.value = '';
    modal.value?.showModal();
}

function closeDeleteModal() {
    modal.value?.close();
    confirmation.value = '';
    deleteStep.value = 1;
}

async function acceptPolicy() {
    if (!policyId.value) {
        return;
    }
    loadingAccept.value = true;
    try {
        const response = await api.privacyPolicies.acceptPrivacyPolicy(policyId.value);
        if (response.ok) {
            window.location.href = '/dashboard';
        } else {
            window.notyf.error(trans('settings.something-wrong'));
        }
    } catch {
        window.notyf.error(trans('settings.something-wrong'));
    } finally {
        loadingAccept.value = false;
    }
}

async function deleteAccount() {
    loadingDelete.value = true;
    try {
        const response = await api.settings.deleteUserAccount({ confirmation: confirmation.value });
        if (response.ok) {
            window.notyf.success(trans('settings.delete-account-completed'));
            window.location.href = '/';
        } else {
            window.notyf.error(trans('settings.something-wrong'));
            closeDeleteModal();
        }
    } catch {
        window.notyf.error(trans('settings.something-wrong'));
        closeDeleteModal();
    } finally {
        loadingDelete.value = false;
    }
}
</script>

<template>
    <!-- Loading skeleton -->
    <div v-if="loading" class="text-center my-5">
        <div class="spinner-border text-secondary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <template v-else>
        <!-- Info card: policy changed or not signed yet -->
        <div v-if="isLoggedIn && policyChanged" class="card mb-3">
            <!-- eslint-disable-next-line vue/no-v-html -->
            <p class="card-body mb-0" v-html="trans('privacy.we-changed')"></p>
        </div>
        <div v-else-if="isLoggedIn && !hasUserSigned" class="card mb-3">
            <!-- eslint-disable-next-line vue/no-v-html -->
            <p class="card-body mb-0" v-html="trans('privacy.not-signed-yet')"></p>
        </div>

        <!-- Action buttons (top) -->
        <div v-if="showActions" class="d-flex justify-content-between align-items-center mb-4">
            <button type="button" class="btn btn-outline-secondary btn-sm" @click="openDeleteModal">
                {{ trans('settings.delete-account.more') }}
            </button>
            <button type="button" class="btn btn-success" :disabled="loadingAccept" @click="acceptPolicy">
                {{ trans('privacy.sign.more') }}
            </button>
        </div>

        <!-- Privacy policy content -->
        <!-- eslint-disable-next-line vue/no-v-html -->
        <div class="privacy" v-html="policyHtml"></div>

        <!-- Action buttons (bottom, repeated for convenience after reading) -->
        <div v-if="showActions" class="d-flex justify-content-between align-items-center mt-4 mb-5">
            <button type="button" class="btn btn-outline-secondary btn-sm" @click="openDeleteModal">
                {{ trans('settings.delete-account.more') }}
            </button>
            <button type="button" class="btn btn-success" :disabled="loadingAccept" @click="acceptPolicy">
                {{ trans('privacy.sign.more') }}
            </button>
        </div>
    </template>

    <!-- Delete account dialog -->
    <dialog
        ref="modal"
        style="z-index: 1100; border: none; border-radius: 0.5rem; padding: 0; max-width: 500px; width: 90%"
    >
        <!-- Step 1: Initial warning -->
        <div v-if="deleteStep === 1" style="padding: 1.5rem">
            <h5 class="mb-2 fw-bold">{{ trans('settings.delete-account') }}</h5>
            <p class="text-muted small">{{ trans('settings.delete-account.detail') }}</p>
            <div class="d-flex justify-content-end gap-2 mt-3">
                <button type="button" class="btn btn-secondary" @click="closeDeleteModal">
                    {{ trans('menu.abort') }}
                </button>
                <button type="button" class="btn btn-danger" @click="deleteStep = 2">
                    {{ trans('settings.delete-account') }}
                </button>
            </div>
        </div>

        <!-- Step 2: Final confirmation with username -->
        <div v-else style="padding: 1.5rem; border: 2px solid #dc3545; border-radius: 0.5rem">
            <h5 class="mb-2 fw-bold text-danger">{{ trans('settings.delete-account') }}</h5>
            <!-- eslint-disable-next-line vue/no-v-html -->
            <p class="small" v-html="trans('settings.delete-account-verify', { appname: 'Träwelling' })"></p>
            <form @submit.prevent="deleteAccount">
                <div class="mt-3">
                    <!-- eslint-disable vue/no-v-html -->
                    <label
                        class="form-label small"
                        v-html="trans('messages.account.please-confirm', { delete: username })"
                    ></label>
                    <!-- eslint-enable vue/no-v-html -->
                    <input
                        v-model="confirmation"
                        type="text"
                        class="form-control is-invalid"
                        :placeholder="username ?? ''"
                        autocomplete="off"
                        required
                    />
                </div>
                <div class="d-flex justify-content-end gap-2 mt-3">
                    <button type="button" class="btn btn-secondary" @click="deleteStep = 1">
                        {{ trans('settings.delete-account-btn-back') }}
                    </button>
                    <button class="btn btn-danger" type="submit" :disabled="loadingDelete || confirmation !== username">
                        {{ trans('settings.delete-account-btn-confirm') }}
                    </button>
                </div>
            </form>
        </div>

        <form method="dialog" style="display: none">
            <button>close</button>
        </form>
    </dialog>
</template>
