<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';
import { Api, type ReportResource } from '../../../types/Api.gen';
import ReportSubjectSummary from './ReportSubjectSummary.vue';

type ReportQuery = NonNullable<Parameters<Api<unknown>['reports']['listReports']>[0]>;

const props = defineProps<{ query: ReportQuery; excludeId?: string }>();

const api = new Api({ baseUrl: window.location.origin + '/api' });

const reports = ref<ReportResource[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);
const nextCursor = ref<string | null>(null);
const prevCursor = ref<string | null>(null);

async function fetchReports(cursor?: string): Promise<void> {
    loading.value = true;
    error.value = null;
    try {
        const res = await api.reports.listReports({ ...props.query, cursor });
        const page = res.data as { data?: ReportResource[]; meta?: { next_cursor?: string; prev_cursor?: string } };
        reports.value = (page.data ?? []).filter((report) => report.id !== props.excludeId);
        nextCursor.value = page.meta?.next_cursor ?? null;
        prevCursor.value = page.meta?.prev_cursor ?? null;
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Unknown error';
    } finally {
        loading.value = false;
    }
}

function statusBadgeClass(status: string): string {
    return status === 'open' ? 'badge-error' : status === 'waiting' ? 'badge-warning' : 'badge-ghost';
}

onMounted(() => fetchReports());
watch(
    () => props.query,
    () => fetchReports(),
    { deep: true },
);
</script>

<template>
    <div>
        <div v-if="loading && !reports.length" class="flex justify-center py-4">
            <span class="loading loading-spinner" />
        </div>
        <div v-else-if="error" role="alert" class="alert alert-error text-sm py-2">{{ error }}</div>
        <p v-else-if="!reports.length" class="text-sm text-base-content/50">No reports.</p>
        <div v-else class="overflow-x-auto">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Subject</th>
                        <th>Reason</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="report in reports"
                        :key="report.id"
                        class="hover cursor-pointer"
                        @click="$router.push(`/admin/reports/${report.id}`)"
                    >
                        <td class="text-xs text-base-content/60 whitespace-nowrap">
                            {{ new Date(report.created_at).toLocaleString() }}
                        </td>
                        <td>
                            <span class="badge badge-sm" :class="statusBadgeClass(report.status)">
                                {{ report.status }}
                            </span>
                        </td>
                        <td><ReportSubjectSummary :report="report" compact /></td>
                        <td>
                            <span class="badge badge-sm badge-ghost">{{ report.reason }}</span>
                        </td>
                        <td class="max-w-48 truncate text-sm text-base-content/70">{{ report.description }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-if="prevCursor || nextCursor" class="flex justify-center gap-2 mt-2">
            <button class="btn btn-xs btn-ghost" :disabled="!prevCursor" @click="fetchReports(prevCursor ?? undefined)">
                ← Previous
            </button>
            <button class="btn btn-xs btn-ghost" :disabled="!nextCursor" @click="fetchReports(nextCursor ?? undefined)">
                Next →
            </button>
        </div>
    </div>
</template>
