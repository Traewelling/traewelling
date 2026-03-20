<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import BackendLayout from '../../../../tailwind-app/layouts/BackendLayout.vue';
import { Api, type AlertResource } from '../../../../types/Api.gen';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const route = useRoute();
const router = useRouter();

const alertId = route.params.id as string | undefined;
const isEdit = !!alertId;

const loading = ref(isEdit);
const saving = ref(false);
const error = ref<string | null>(null);

const form = ref({
    type: 'info' as 'info' | 'warning' | 'danger' | 'success',
    active_from: '',
    active_until: '',
    title_de: '',
    content_de: '',
    url_de: '',
    title_en: '',
    content_en: '',
    url_en: '',
    url: '',
});

function fillForm(alert: AlertResource): void {
    const de = alert.translations.find((t) => t.locale === 'de');
    const en = alert.translations.find((t) => t.locale === 'en');
    form.value = {
        type: alert.type as typeof form.value.type,
        active_from: alert.active_from ? alert.active_from.slice(0, 10) : '',
        active_until: alert.active_until ? alert.active_until.slice(0, 10) : '',
        title_de: de?.title ?? '',
        content_de: de?.content ?? '',
        url_de: de?.url ?? '',
        title_en: en?.title ?? '',
        content_en: en?.content ?? '',
        url_en: en?.url ?? '',
        url: alert.url ?? '',
    };
}

async function fetchAlert(): Promise<void> {
    try {
        const res = await api.alerts.getAlert(alertId!);
        fillForm(res.data.data!);
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Unknown error';
    } finally {
        loading.value = false;
    }
}

async function submit(): Promise<void> {
    saving.value = true;
    error.value = null;
    const payload = {
        ...form.value,
        active_until: form.value.active_until || null,
        url_de: form.value.url_de || null,
        url_en: form.value.url_en || null,
        url: form.value.url || null,
    };
    try {
        if (isEdit) {
            await api.alerts.updateAlert(alertId!, payload);
        } else {
            await api.alerts.createAlert(payload);
        }
        await router.push('/admin/alerts');
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Something went wrong.';
    } finally {
        saving.value = false;
    }
}

onMounted(() => {
    if (isEdit) fetchAlert();
});
</script>

<template>
    <BackendLayout>
        <div class="flex items-center gap-3 mb-6">
            <router-link to="/admin/alerts" class="btn btn-ghost btn-sm">← Alerts</router-link>
            <h1 class="text-2xl font-bold">{{ isEdit ? 'Edit Alert' : 'New Alert' }}</h1>
        </div>

        <div v-if="loading" class="flex justify-center py-16">
            <span class="loading loading-spinner loading-lg" />
        </div>

        <form v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6" @submit.prevent="submit">
            <div v-if="error" role="alert" class="alert alert-error lg:col-span-2">
                <span>{{ error }}</span>
            </div>

            <!-- Meta -->
            <div class="card bg-base-100 shadow lg:col-span-2">
                <div class="card-body">
                    <h2 class="card-title text-base">General</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <fieldset class="fieldset">
                            <legend class="fieldset-legend text-xs">Type</legend>
                            <select v-model="form.type" class="select select-sm w-full" required>
                                <option value="info">info</option>
                                <option value="warning">warning</option>
                                <option value="danger">danger</option>
                                <option value="success">success</option>
                            </select>
                        </fieldset>
                        <fieldset class="fieldset">
                            <legend class="fieldset-legend text-xs">Active From</legend>
                            <input v-model="form.active_from" type="date" class="input input-sm w-full" required />
                        </fieldset>
                        <fieldset class="fieldset">
                            <legend class="fieldset-legend text-xs">Active Until</legend>
                            <input v-model="form.active_until" type="date" class="input input-sm w-full" />
                        </fieldset>
                    </div>
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend text-xs">Default URL (fallback)</legend>
                        <input v-model="form.url" type="url" class="input input-sm w-full" placeholder="https://…" />
                    </fieldset>
                </div>
            </div>

            <!-- German -->
            <div class="card bg-base-100 shadow">
                <div class="card-body gap-3">
                    <h2 class="card-title text-base">🇩🇪 German</h2>
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend text-xs">Title</legend>
                        <input v-model="form.title_de" type="text" class="input input-sm w-full" required />
                    </fieldset>
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend text-xs">Content</legend>
                        <textarea v-model="form.content_de" class="textarea textarea-sm w-full" rows="3" required />
                    </fieldset>
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend text-xs">URL</legend>
                        <input v-model="form.url_de" type="url" class="input input-sm w-full" placeholder="https://…" />
                    </fieldset>
                </div>
            </div>

            <!-- English -->
            <div class="card bg-base-100 shadow">
                <div class="card-body gap-3">
                    <h2 class="card-title text-base">🇬🇧 English</h2>
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend text-xs">Title</legend>
                        <input v-model="form.title_en" type="text" class="input input-sm w-full" required />
                    </fieldset>
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend text-xs">Content</legend>
                        <textarea v-model="form.content_en" class="textarea textarea-sm w-full" rows="3" required />
                    </fieldset>
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend text-xs">URL</legend>
                        <input v-model="form.url_en" type="url" class="input input-sm w-full" placeholder="https://…" />
                    </fieldset>
                </div>
            </div>

            <!-- Submit -->
            <div class="lg:col-span-2 flex justify-end gap-2">
                <router-link to="/admin/alerts" class="btn btn-ghost">Cancel</router-link>
                <button type="submit" class="btn btn-primary" :disabled="saving">
                    <span v-if="saving" class="loading loading-spinner loading-sm" />
                    {{ isEdit ? 'Save Changes' : 'Create Alert' }}
                </button>
            </div>
        </form>
    </BackendLayout>
</template>
