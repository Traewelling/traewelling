<script setup lang="ts">
import { FileText } from '@lucide/vue';
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { Api, type ReportResource } from '../../../types/Api.gen';
import BackendLayout from '../../layouts/BackendLayout.vue';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const route = useRoute();
const reportId = route.params.id as string;

const report = ref<ReportResource | null>(null);
const loading = ref(true);
const error = ref<string | null>(null);

const actionStatus = ref('');
const actionDescription = ref('');
const actionLoading = ref(false);
const actionError = ref<string | null>(null);
const actionSuccess = ref(false);

async function fetchReport(): Promise<void> {
    loading.value = true;
    error.value = null;
    try {
        const res = await api.reports.getReport(reportId);
        report.value = res.data.data;
        actionStatus.value = res.data.data.status;
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Unknown error';
    } finally {
        loading.value = false;
    }
}

async function submitAction(): Promise<void> {
    actionLoading.value = true;
    actionError.value = null;
    actionSuccess.value = false;
    try {
        await api.reports.updateReport(reportId, {
            status: actionStatus.value,
            description: actionDescription.value || undefined,
        });
        actionSuccess.value = true;
        actionDescription.value = '';
        await fetchReport();
    } catch (e) {
        actionError.value = e instanceof Error ? e.message : 'Something went wrong.';
    } finally {
        actionLoading.value = false;
    }
}

function statusBadgeClass(status: string): string {
    return status === 'open' ? 'badge-error' : status === 'waiting' ? 'badge-warning' : 'badge-ghost';
}

function subjectAdminUrl(r: ReportResource): string | null {
    switch (r.subject_type) {
        case 'Status':
            return `/admin/statuses/${r.subject_id}`;
        case 'User':
            return `/admin/users/${r.subject_id}`;
        case 'Event':
            return `/admin/events/${r.subject_id}/edit`;
        case 'Trip':
            return `/admin/trips/${r.subject_id}`;
        default:
            return null;
    }
}

onMounted(fetchReport);
</script>

<template>
    <BackendLayout>
        <div v-if="loading" class="flex justify-center py-16">
            <span class="loading loading-spinner loading-lg" />
        </div>

        <div v-else-if="error" role="alert" class="alert alert-error">
            <span>{{ error }}</span>
        </div>

        <template v-else-if="report">
            <!-- Header -->
            <div class="flex items-center gap-3 mb-6">
                <router-link to="/admin/reports" class="btn btn-ghost btn-sm">← Reports</router-link>
                <h1 class="text-2xl font-bold">Report</h1>
                <span class="badge" :class="statusBadgeClass(report.status)">{{ report.status }}</span>
                <span class="font-mono text-sm text-base-content/50">{{ report.id }}</span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left: Report details -->
                <div class="space-y-4">
                    <div class="card bg-base-100 shadow">
                        <div class="card-body gap-3">
                            <h2 class="card-title text-base">
                                <FileText class="w-4 h-4" />
                                Details
                            </h2>

                            <dl class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-2 text-sm">
                                <dt class="text-base-content/50 font-medium">Reporter</dt>
                                <dd>
                                    <template v-if="report.reporter">
                                        <a :href="`/admin/users/${report.reporter.id}`" class="link link-hover">
                                            {{ report.reporter.displayName }}
                                        </a>
                                        <div class="text-xs text-base-content/50">@{{ report.reporter.username }}</div>
                                    </template>
                                    <span v-else class="text-base-content/30">—</span>
                                </dd>

                                <dt class="text-base-content/50 font-medium">Subject</dt>
                                <dd>
                                    <a
                                        v-if="subjectAdminUrl(report)"
                                        :href="subjectAdminUrl(report)!"
                                        class="link link-hover"
                                    >
                                        {{ report.subject_type }} #{{ report.subject_id }}
                                    </a>
                                    <span v-else>{{ report.subject_type }} #{{ report.subject_id }}</span>
                                </dd>

                                <dt class="text-base-content/50 font-medium">Reason</dt>
                                <dd>
                                    <span class="badge badge-ghost badge-sm">{{ report.reason ?? '—' }}</span>
                                </dd>

                                <dt class="text-base-content/50 font-medium">Description</dt>
                                <dd class="text-sm">{{ report.description }}</dd>

                                <dt class="text-base-content/50 font-medium">Created</dt>
                                <dd class="text-xs text-base-content/70">
                                    {{ new Date(report.created_at!).toLocaleString() }}
                                </dd>
                            </dl>
                        </div>
                    </div>

                    <!-- Action form -->
                    <div class="card bg-base-100 shadow">
                        <div class="card-body">
                            <h2 class="card-title text-base">Action</h2>

                            <div v-if="actionError" role="alert" class="alert alert-error alert-sm text-sm py-2">
                                {{ actionError }}
                            </div>
                            <div v-if="actionSuccess" role="alert" class="alert alert-success alert-sm text-sm py-2">
                                Saved.
                            </div>

                            <form class="space-y-3" @submit.prevent="submitAction">
                                <fieldset class="fieldset">
                                    <legend class="fieldset-legend text-xs">New Status</legend>
                                    <select v-model="actionStatus" class="select select-sm w-full">
                                        <option value="open">open</option>
                                        <option value="waiting">waiting</option>
                                        <option value="closed">closed</option>
                                    </select>
                                </fieldset>

                                <fieldset class="fieldset">
                                    <legend class="fieldset-legend text-xs">Note</legend>
                                    <textarea
                                        v-model="actionDescription"
                                        class="textarea textarea-sm w-full"
                                        rows="3"
                                    />
                                </fieldset>

                                <button type="submit" class="btn btn-primary btn-sm w-full" :disabled="actionLoading">
                                    <span v-if="actionLoading" class="loading loading-spinner loading-xs" />
                                    Save
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Right: Activity log -->
                <div class="lg:col-span-2">
                    <div class="card bg-base-100 shadow">
                        <div class="card-body">
                            <h2 class="card-title text-base">Activity Log</h2>

                            <div class="overflow-x-auto">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>By</th>
                                            <th>Note</th>
                                            <th>Change</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Original report submission -->
                                        <tr class="bg-base-200/40">
                                            <td class="text-xs text-base-content/60 whitespace-nowrap">
                                                {{ new Date(report.created_at!).toLocaleString() }}
                                            </td>
                                            <td>
                                                <template v-if="report.reporter">
                                                    <a
                                                        :href="`/admin/users/${report.reporter.id}`"
                                                        class="link link-hover text-sm"
                                                    >
                                                        @{{ report.reporter.username }}
                                                    </a>
                                                </template>
                                                <span v-else class="text-base-content/30">—</span>
                                            </td>
                                            <td class="text-sm italic text-base-content/70">
                                                {{ report.description }}
                                            </td>
                                            <td>
                                                <span class="badge badge-sm badge-ghost">reported</span>
                                            </td>
                                        </tr>

                                        <!-- Admin activity entries -->
                                        <tr v-for="activity in report.activities" :key="activity.id" class="hover">
                                            <td class="text-xs text-base-content/60 whitespace-nowrap">
                                                {{ new Date(activity.created_at).toLocaleString() }}
                                            </td>
                                            <td>
                                                <template v-if="activity.causer">
                                                    <a
                                                        :href="`/admin/users/${activity.causer.id}`"
                                                        class="link link-hover text-sm"
                                                    >
                                                        @{{ activity.causer.username }}
                                                    </a>
                                                </template>
                                                <span v-else class="text-base-content/30">—</span>
                                            </td>
                                            <td class="text-sm">{{ activity.description }}</td>
                                            <td>
                                                <template v-if="activity.properties?.old">
                                                    <span class="badge badge-sm badge-ghost">
                                                        {{ activity.properties.old.status }}
                                                    </span>
                                                    →
                                                    <span class="badge badge-sm badge-primary">
                                                        {{ activity.properties.attributes?.status }}
                                                    </span>
                                                </template>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </BackendLayout>
</template>
