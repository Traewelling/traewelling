<script setup lang="ts">
import { PencilLine, Plus, Trash2 } from '@lucide/vue';
import { onMounted, ref } from 'vue';
import { type AlertResource, Api } from '../../../types/Api.gen';
import BackendLayout from '../../layouts/BackendLayout.vue';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const alerts = ref<AlertResource[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);
const deletingIds = ref<Set<string>>(new Set());
const nextCursor = ref<string | null>(null);
const prevCursor = ref<string | null>(null);

async function fetchAlerts(cursor?: string): Promise<void> {
    loading.value = true;
    error.value = null;
    try {
        const res = await api.alerts.getAlerts({ all: true, cursor });
        alerts.value = res.data.data ?? [];
        const meta = (res.data as { meta?: { next_cursor?: string; prev_cursor?: string } }).meta;
        nextCursor.value = meta?.next_cursor ?? null;
        prevCursor.value = meta?.prev_cursor ?? null;
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Unknown error';
    } finally {
        loading.value = false;
    }
}

async function deleteAlert(alert: AlertResource): Promise<void> {
    if (!confirm(`Delete alert "${translationFor(alert, 'en')?.title ?? alert.id.slice(0, 8)}"?`)) return;

    deletingIds.value = new Set([...deletingIds.value, alert.id]);
    try {
        await api.alerts.deleteAlert(alert.id);
        await fetchAlerts();
    } catch (e) {
        window.alert(`Delete failed: ${e instanceof Error ? e.message : 'Unknown error'}`);
    } finally {
        const next = new Set(deletingIds.value);
        next.delete(alert.id);
        deletingIds.value = next;
    }
}

function translationFor(alert: AlertResource, locale: string) {
    return alert.translations.find((t) => t.locale === locale);
}

function isActive(alert: AlertResource): boolean {
    const now = new Date();
    const from = new Date(alert.active_from);
    const until = alert.active_until ? new Date(alert.active_until) : null;
    return from <= now && (until === null || until >= now);
}

function typeBadgeClass(type: string): string {
    switch (type) {
        case 'danger':
            return 'badge-error';
        case 'warning':
            return 'badge-warning';
        case 'success':
            return 'badge-success';
        default:
            return 'badge-info';
    }
}

onMounted(() => fetchAlerts());
</script>

<template>
    <BackendLayout>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">Alerts</h1>
            <router-link to="/admin/alerts/create" class="btn btn-primary btn-sm gap-1">
                <Plus class="w-4 h-4" />
                New Alert
            </router-link>
        </div>

        <div v-if="loading" class="flex justify-center py-16">
            <span class="loading loading-spinner loading-lg" />
        </div>

        <div v-else-if="error" role="alert" class="alert alert-error">
            <span>{{ error }}</span>
        </div>

        <div v-else class="card bg-base-100 shadow">
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Active</th>
                            <th>Type</th>
                            <th>Title (DE)</th>
                            <th>Title (EN)</th>
                            <th>From</th>
                            <th>Until</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!alerts.length">
                            <td colspan="7" class="text-center text-base-content/50 py-8">No alerts found.</td>
                        </tr>
                        <tr v-for="alert in alerts" :key="alert.id" class="hover">
                            <td>
                                <span class="badge badge-sm" :class="isActive(alert) ? 'badge-success' : 'badge-ghost'">
                                    {{ isActive(alert) ? 'active' : 'inactive' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-sm" :class="typeBadgeClass(alert.type)">
                                    {{ alert.type }}
                                </span>
                            </td>
                            <td class="max-w-48 truncate text-sm">
                                {{ translationFor(alert, 'de')?.title ?? '—' }}
                            </td>
                            <td class="max-w-48 truncate text-sm">
                                {{ translationFor(alert, 'en')?.title ?? '—' }}
                            </td>
                            <td class="text-xs text-base-content/60 whitespace-nowrap">
                                {{ new Date(alert.active_from).toLocaleDateString() }}
                            </td>
                            <td class="text-xs text-base-content/60 whitespace-nowrap">
                                {{ alert.active_until ? new Date(alert.active_until).toLocaleDateString() : '∞' }}
                            </td>
                            <td class="text-right">
                                <div class="flex gap-1 justify-end">
                                    <router-link :to="`/admin/alerts/${alert.id}/edit`" class="btn btn-xs btn-primary">
                                        <PencilLine class="w-3 h-3" />
                                        Edit
                                    </router-link>
                                    <button
                                        class="btn btn-xs btn-outline btn-error"
                                        :disabled="deletingIds.has(alert.id)"
                                        @click="deleteAlert(alert)"
                                    >
                                        <span
                                            v-if="deletingIds.has(alert.id)"
                                            class="loading loading-spinner loading-xs"
                                        />
                                        <Trash2 v-else class="w-3 h-3" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="prevCursor || nextCursor" class="flex justify-center gap-2 mt-4">
            <button class="btn btn-sm btn-ghost" :disabled="!prevCursor" @click="fetchAlerts(prevCursor ?? undefined)">
                ← Previous
            </button>
            <button class="btn btn-sm btn-ghost" :disabled="!nextCursor" @click="fetchAlerts(nextCursor ?? undefined)">
                Next →
            </button>
        </div>
    </BackendLayout>
</template>
