<script setup lang="ts">
import { ChartBar, ExternalLink, Info, Pencil, Plus, Trash2 } from '@lucide/vue';
import { trans, transChoice } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { inject, ref } from 'vue';
import { Api, OAuthClientResource } from '../../../../types/Api.gen';
import SettingsLayout from '../../../layouts/SettingsLayout.vue';
import ApplicationForm from './partials/ApplicationForm.vue';
import PersonalAccessToken from './partials/PersonalAccessToken.vue';

const api = new Api({ baseUrl: window.location.origin + '/api' });
const notyf = inject('notyf') as Notyf;

const apps = ref<OAuthClientResource[]>([]);
const loading = ref(true);

const formOpen = ref(false);
const editingApp = ref<OAuthClientResource | null>(null);

const deletingApp = ref<OAuthClientResource | null>(null);
const deleteConfirmOpen = ref(false);
const deleting = ref(false);

const newSecret = ref<string | null>(null);
const secretAppName = ref('');

async function fetchApps() {
    loading.value = true;
    const response = await api.applications.getApplications();
    if (response.ok) {
        const data = await response.json();
        apps.value = data.data ?? [];
    }
    loading.value = false;
}

function openCreate() {
    editingApp.value = null;
    formOpen.value = true;
}

function openEdit(app: OAuthClientResource) {
    editingApp.value = app;
    formOpen.value = true;
}

function openDelete(app: OAuthClientResource) {
    deletingApp.value = app;
    deleteConfirmOpen.value = true;
}

function onSaved(app: OAuthClientResource, plainSecret: string | null | undefined) {
    formOpen.value = false;
    fetchApps();

    if (plainSecret) {
        secretAppName.value = app.name;
        newSecret.value = plainSecret;
    } else {
        notyf.success(trans('settings.saved'));
    }
}

async function confirmDelete() {
    if (!deletingApp.value) return;
    deleting.value = true;
    const response = await api.applications.deleteApplication(deletingApp.value.id);
    deleting.value = false;
    deleteConfirmOpen.value = false;

    if (response.status === 204 || response.ok) {
        apps.value = apps.value.filter((a) => a.id !== deletingApp.value!.id);
        notyf.success(trans('settings.saved'));
    } else {
        notyf.error(trans('error'));
    }
    deletingApp.value = null;
}

function copySecret() {
    if (!newSecret.value) return;
    navigator.clipboard.writeText(newSecret.value);
    notyf.success(trans('access-token-copied'));
}

fetchApps();
</script>

<template>
    <SettingsLayout>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold">{{ trans('your-apps') }}</h2>
            <div class="flex gap-2">
                <a href="/api/documentation" target="_blank" class="btn btn-ghost btn-sm">
                    <ExternalLink class="size-4" />
                    API Docs
                </a>
                <button class="btn btn-primary btn-sm" @click="openCreate">
                    <Plus class="size-4" />
                    {{ trans('create-app') }}
                </button>
            </div>
        </div>

        <!-- New secret alert -->
        <div v-if="newSecret" role="alert" class="alert alert-error mb-4 flex-col items-start gap-2">
            <p class="font-semibold">{{ trans('oauth.secret-once', { name: secretAppName }) }}</p>
            <div class="flex gap-2 w-full items-center">
                <code class="bg-base-100/20 rounded px-3 py-2 text-xs flex-1 break-all select-all">{{
                    newSecret
                }}</code>
                <button class="btn btn-sm btn-ghost text-error-content" @click="copySecret">
                    {{ trans('copy') }}
                </button>
            </div>
            <button class="btn btn-sm btn-outline self-end" @click="newSecret = null">
                {{ trans('oauth.secret-confirmed') }}
            </button>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex justify-center py-12">
            <span class="loading loading-spinner loading-lg"></span>
        </div>

        <!-- Empty state -->
        <div v-else-if="apps.length === 0" class="text-center py-12 text-base-content/50">
            <p>{{ trans('no-own-apps') }}</p>
            <button class="btn btn-primary btn-sm mt-4" @click="openCreate">
                {{ trans('create-app') }}
            </button>
        </div>

        <!-- App list -->
        <div v-else class="flex flex-col gap-3 mb-6">
            <div
                v-for="app in apps"
                :key="app.id"
                class="bg-base-100 rounded-box shadow p-4 flex flex-col sm:flex-row sm:items-center gap-3"
            >
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-semibold">{{ app.name }}</span>
                        <span class="badge badge-sm badge-ghost">ID {{ app.id }}</span>
                        <span v-if="app.confidential" class="badge badge-sm badge-neutral">
                            {{ trans('oauth.confidential') }}
                        </span>
                        <span v-if="app.webhooksEnabled" class="badge badge-sm badge-info"> Webhooks </span>
                    </div>
                    <p class="text-xs text-base-content/50 mt-1 truncate">{{ app.redirect }}</p>
                    <p class="text-xs text-base-content/40 mt-0.5">
                        {{
                            transChoice('active-tokens-count', app.activeTokensCount, { count: app.activeTokensCount })
                        }}
                    </p>
                </div>

                <div class="flex gap-2 shrink-0 flex-wrap">
                    <RouterLink
                        :to="{
                            name: 'settings-application-webhook-stats',
                            params: {
                                clientId: app.id,
                            },
                        }"
                        class="btn btn-sm btn-ghost"
                        :title="trans('webhook-stats.title')"
                    >
                        <ChartBar class="size-4" />
                        <span class="hidden sm:inline">{{ trans('webhook-stats.title') }}</span>
                    </RouterLink>
                    <button class="btn btn-sm btn-ghost" @click="openEdit(app)">
                        <Pencil class="size-4" />
                        <span class="hidden sm:inline">{{ trans('edit') }}</span>
                    </button>
                    <button class="btn btn-sm btn-ghost text-error" @click="openDelete(app)">
                        <Trash2 class="size-4" />
                        <span class="hidden sm:inline">{{ trans('delete') }}</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Personal access token -->
        <PersonalAccessToken />

        <!-- API usage guidelines -->
        <div class="bg-base-200 rounded-box p-4 mt-6 text-sm">
            <div class="flex items-center gap-2 font-semibold mb-3">
                <Info class="size-4 shrink-0 text-info" />
                {{ trans('api-usage.title') }}
            </div>
            <ul class="list-disc list-inside space-y-2 text-base-content/80">
                <li>{{ trans('api-usage.prefer-applications') }}</li>
                <li>{{ trans('api-usage.no-token-sharing') }}</li>
                <li>{{ trans('api-usage.rate-limit') }}</li>
                <li>
                    {{ trans('api-usage.user-agent') }}
                    <br />
                    <code class="text-xs opacity-60 ml-4">{{ trans('api-usage.user-agent-example') }}</code>
                </li>
                <li>{{ trans('api-usage.legal') }}</li>
                <li>
                    {{ trans('api-usage.changes') }}
                    <a
                        href="https://github.com/Traewelling/traewelling/blob/develop/API_CHANGELOG.md"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="link"
                        >{{ trans('api-usage.changelog-link') }}</a
                    >
                    <span class="opacity-40 mx-1">|</span>
                    <a
                        href="https://github.com/Traewelling/traewelling/discussions/2619"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="link"
                        >{{ trans('api-usage.discussion-link') }}</a
                    >
                </li>
                <li>{{ trans('api-usage.liability') }}</li>
                <li>{{ trans('api-usage.abuse') }}</li>
            </ul>
        </div>

        <!-- Create/Edit Modal -->
        <ApplicationForm :app="editingApp" :open="formOpen" @close="formOpen = false" @saved="onSaved" />

        <!-- Delete confirmation modal -->
        <dialog :open="deleteConfirmOpen" class="modal modal-bottom sm:modal-middle">
            <div class="modal-box">
                <h3 class="text-lg font-bold">{{ trans('delete') }} "{{ deletingApp?.name }}"</h3>
                <p class="py-4 text-sm text-base-content/70">
                    {{ trans('oauth.delete-confirm', { name: deletingApp?.name ?? '' }) }}
                </p>
                <div class="modal-action">
                    <button class="btn btn-ghost" @click="deleteConfirmOpen = false">
                        {{ trans('cancel') }}
                    </button>
                    <button class="btn btn-error" :disabled="deleting" @click="confirmDelete">
                        <span v-if="deleting" class="loading loading-spinner loading-sm"></span>
                        {{ trans('delete') }}
                    </button>
                </div>
            </div>
            <div class="modal-backdrop" @click="deleteConfirmOpen = false"></div>
        </dialog>
    </SettingsLayout>
</template>
