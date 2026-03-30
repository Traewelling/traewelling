<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { Api, type ReportResource } from '../../../types/Api.gen';
import BackendLayout from '../../layouts/BackendLayout.vue';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const reports = ref<ReportResource[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);
const nextCursor = ref<string | null>(null);
const prevCursor = ref<string | null>(null);
const closingIds = ref<Set<string>>(new Set());

async function fetchReports(cursor?: string): Promise<void> {
    loading.value = true;
    error.value = null;
    try {
        const res = await api.reports.listReports({ cursor });
        reports.value = res.data.data ?? [];
        nextCursor.value = res.data.meta?.next_cursor ?? null;
        prevCursor.value = res.data.meta?.prev_cursor ?? null;
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Unknown error';
    } finally {
        loading.value = false;
    }
}

async function closeReport(report: ReportResource): Promise<void> {
    if (!confirm(`Close report ${report.id.slice(0, 8)}…?`)) return;

    closingIds.value = new Set([...closingIds.value, report.id]);
    try {
        await api.reports.updateReport(report.id, { status: 'closed', description: 'Closed by admin' });
        await fetchReports(prevCursor.value ?? undefined);
    } catch (e) {
        alert(e instanceof Error ? e.message : 'Something went wrong.');
    } finally {
        const next = new Set(closingIds.value);
        next.delete(report.id);
        closingIds.value = next;
    }
}

function statusBadgeClass(status: string): string {
    return status === 'open' ? 'badge-error' : status === 'waiting' ? 'badge-warning' : 'badge-ghost';
}

function reasonBadgeClass(reason: string | null | undefined, status: string): string {
    if (status === 'closed') return 'badge-ghost';
    switch (reason) {
        case 'illegal':
            return 'badge-error';
        case 'inappropriate':
            return 'badge-warning';
        case 'spam':
            return 'badge-info';
        case 'implausible':
            return 'badge-secondary';
        default:
            return 'badge-ghost';
    }
}

onMounted(() => fetchReports(undefined));
</script>

<template>
    <BackendLayout>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">Reports</h1>
        </div>

        <div v-if="loading && !reports.length" class="flex justify-center py-16">
            <span class="loading loading-spinner loading-lg" />
        </div>

        <div v-else-if="error" role="alert" class="alert alert-error">
            <span>{{ error }}</span>
        </div>

        <template v-else>
            <div class="card bg-base-100 shadow">
                <div class="overflow-x-auto">
                    <table class="table table-zebra table-sm">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>ID</th>
                                <th>Reporter</th>
                                <th>Subject</th>
                                <th>Reason</th>
                                <th>Description</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="!reports.length">
                                <td colspan="7" class="text-center text-base-content/50 py-8">No reports found.</td>
                            </tr>
                            <tr
                                v-for="report in reports"
                                :key="report.id"
                                class="hover cursor-pointer"
                                @click="$router.push(`/admin/reports/${report.id}`)"
                            >
                                <td>
                                    <span class="badge badge-sm" :class="statusBadgeClass(report.status)">
                                        {{ report.status }}
                                    </span>
                                </td>
                                <td>
                                    <span class="font-mono text-xs text-base-content/60">
                                        {{ report.id.slice(0, 8) }}…
                                    </span>
                                </td>
                                <td>
                                    <template v-if="report.reporter">
                                        <a
                                            :href="`/admin/users/${report.reporter.id}`"
                                            class="link link-hover"
                                            @click.stop
                                        >
                                            {{ report.reporter.displayName }}
                                        </a>
                                        <div class="text-xs text-base-content/50">@{{ report.reporter.username }}</div>
                                    </template>
                                    <span v-else class="text-base-content/30">—</span>
                                </td>
                                <td class="text-sm">
                                    {{ report.subject_type }}
                                    <span class="font-mono text-base-content/60">#{{ report.subject_id }}</span>
                                </td>
                                <td>
                                    <span
                                        class="badge badge-sm"
                                        :class="reasonBadgeClass(report.reason, report.status)"
                                        >{{ report.reason }}</span
                                    >
                                </td>
                                <td class="max-w-48 truncate text-sm text-base-content/70">
                                    {{ report.description }}
                                </td>
                                <td class="text-right">
                                    <div class="flex gap-1 justify-end">
                                        <button
                                            v-if="report.status !== 'closed'"
                                            class="btn btn-xs btn-outline"
                                            :disabled="closingIds.has(report.id)"
                                            @click.stop="closeReport(report)"
                                        >
                                            <span
                                                v-if="closingIds.has(report.id)"
                                                class="loading loading-spinner loading-xs"
                                            />
                                            <span v-else>Close</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="prevCursor || nextCursor" class="flex justify-center gap-2 mt-4">
                <button
                    class="btn btn-sm btn-ghost"
                    :disabled="!prevCursor"
                    @click="fetchReports(prevCursor ?? undefined)"
                >
                    ← Previous
                </button>
                <button
                    class="btn btn-sm btn-ghost"
                    :disabled="!nextCursor"
                    @click="fetchReports(nextCursor ?? undefined)"
                >
                    Next →
                </button>
            </div>
        </template>
    </BackendLayout>
</template>
