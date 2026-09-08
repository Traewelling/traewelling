<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { type ActivityLogResource, Api } from '../../../types/Api.gen';
import BackendLayout from '../../layouts/BackendLayout.vue';

const api = new Api({ baseUrl: window.location.origin + '/api' });
const route = useRoute();
const router = useRouter();

const activities = ref<ActivityLogResource[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);
const nextCursor = ref<string | null>(null);
const prevCursor = ref<string | null>(null);

const filterSubjectType = ref<string | null>((route.query.subjectType as string) ?? null);
const filterSubjectId = ref<number | null>(route.query.subjectId ? Number(route.query.subjectId) : null);

async function fetchActivities(cursor?: string): Promise<void> {
    loading.value = true;
    error.value = null;
    try {
        const params: Record<string, string | number | undefined> = { cursor };
        if (filterSubjectType.value && filterSubjectId.value !== null) {
            params.subjectType = filterSubjectType.value;
            params.subjectId = filterSubjectId.value;
        }
        const res = await api.admin.getAdminActivity(params as Parameters<typeof api.admin.getAdminActivity>[0]);
        activities.value = res.data.data ?? [];
        const meta = (res.data as { meta?: { next_cursor?: string; prev_cursor?: string } }).meta;
        nextCursor.value = meta?.next_cursor ?? null;
        prevCursor.value = meta?.prev_cursor ?? null;
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Unknown error';
    } finally {
        loading.value = false;
    }
}

function applyFilter(subjectFullType: string | null, subjectId: number | null): void {
    filterSubjectType.value = subjectFullType;
    filterSubjectId.value = subjectId;
    router.replace({
        query:
            subjectFullType && subjectId !== null ? { subjectType: subjectFullType, subjectId: String(subjectId) } : {},
    });
}

function clearFilter(): void {
    applyFilter(null, null);
}

function formatDate(iso: string): string {
    return new Date(iso).toLocaleString(undefined, {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

watch([filterSubjectType, filterSubjectId], () => fetchActivities());
onMounted(() => fetchActivities());
</script>

<template>
    <BackendLayout>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">Activity Log</h1>
        </div>

        <div v-if="filterSubjectType" role="alert" class="alert alert-info py-2 mb-4 flex items-center gap-2">
            <span class="text-sm">
                Filtered by
                <span class="font-mono font-semibold">{{ filterSubjectType }} #{{ filterSubjectId }}</span>
            </span>
            <button class="btn btn-xs btn-ghost ml-auto" @click="clearFilter">✕ Clear filter</button>
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
                            <th class="w-36">Causer</th>
                            <th class="w-24">Description</th>
                            <th class="w-36">Object</th>
                            <th>Attributes</th>
                            <th class="w-36">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!activities.length">
                            <td colspan="5" class="text-center text-base-content/50 py-8">No activities found.</td>
                        </tr>
                        <tr v-for="activity in activities" :key="activity.id" class="align-top">
                            <td class="text-sm">{{ activity.causer?.name ?? '—' }}</td>
                            <td>
                                <span
                                    class="badge badge-sm"
                                    :class="{
                                        'badge-success': activity.description === 'created',
                                        'badge-warning': activity.description === 'updated',
                                        'badge-error': activity.description === 'deleted',
                                        'badge-ghost': !['created', 'updated', 'deleted'].includes(
                                            activity.description,
                                        ),
                                    }"
                                >
                                    {{ activity.description }}
                                </span>
                            </td>
                            <td class="text-sm">
                                <button
                                    v-if="activity.subjectType && activity.subjectId"
                                    class="link link-hover font-mono text-xs"
                                    @click="applyFilter(activity.subjectFullType, activity.subjectId)"
                                >
                                    {{ activity.subjectType }} {{ activity.subjectId }}
                                </button>
                            </td>
                            <td class="text-xs font-mono leading-relaxed max-w-xl">
                                <template v-if="Object.keys(activity.changes.old).length">
                                    <div
                                        v-for="(oldVal, key) in activity.changes.old"
                                        :key="String(key)"
                                        class="whitespace-nowrap"
                                    >
                                        <span class="font-semibold">{{ activity.subjectType }}.{{ key }}</span
                                        >:
                                        <span class="text-base-content/50">"{{ oldVal }}"</span>
                                        →
                                        <span class="text-info">"{{ activity.changes.attributes[key] ?? '???' }}"</span>
                                    </div>
                                </template>
                                <template v-else-if="Object.keys(activity.changes.attributes).length">
                                    <div v-for="(val, key) in activity.changes.attributes" :key="String(key)">
                                        <template v-if="val !== null && val !== ''">
                                            <span class="font-semibold">{{ activity.subjectType }}.{{ key }}</span
                                            >:
                                            <span
                                                :class="
                                                    activity.description === 'created' ? 'text-success' : 'text-error'
                                                "
                                            >
                                                "{{ val }}"
                                            </span>
                                        </template>
                                    </div>
                                </template>
                            </td>
                            <td class="text-xs text-base-content/60 whitespace-nowrap">
                                {{ formatDate(activity.createdAt) }}
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
                @click="fetchActivities(prevCursor ?? undefined)"
            >
                ← Previous
            </button>
            <button
                class="btn btn-sm btn-ghost"
                :disabled="!nextCursor"
                @click="fetchActivities(nextCursor ?? undefined)"
            >
                Next →
            </button>
        </div>
    </BackendLayout>
</template>
