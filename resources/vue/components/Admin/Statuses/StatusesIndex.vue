<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';
import BackendLayout from '../../../../tailwind-app/layouts/BackendLayout.vue';
import { Api, type AdminStatusResource } from '../../../../types/Api.gen';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const statuses = ref<AdminStatusResource[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);
const userQuery = ref('');
const nextCursor = ref<string | null>(null);
const prevCursor = ref<string | null>(null);

let debounceTimer: ReturnType<typeof setTimeout> | null = null;

async function fetchStatuses(cursor?: string): Promise<void> {
    loading.value = true;
    error.value = null;
    try {
        const res = await api.admin.getAdminStatuses({ userQuery: userQuery.value || undefined, cursor });
        statuses.value = res.data.data ?? [];
        const meta = (res.data as { meta?: { next_cursor?: string; prev_cursor?: string } }).meta;
        nextCursor.value = meta?.next_cursor ?? null;
        prevCursor.value = meta?.prev_cursor ?? null;
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Unknown error';
    } finally {
        loading.value = false;
    }
}

watch(userQuery, () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => fetchStatuses(), 300);
});

function visibilityLabel(v: number | undefined): string {
    switch (v) {
        case 0:
            return 'Public';
        case 1:
            return 'Unlisted';
        case 2:
            return 'Followers';
        case 3:
            return 'Private';
        case 4:
            return 'Auth';
        case 5:
            return 'Trusted';
        default:
            return '?';
    }
}

function businessLabel(b: number | undefined): string {
    switch (b) {
        case 0:
            return 'Private';
        case 1:
            return 'Business';
        case 2:
            return 'Commute';
        default:
            return '?';
    }
}

function formatDate(iso: string | null | undefined): string {
    if (!iso) return '—';
    return new Date(iso).toLocaleString();
}

onMounted(() => fetchStatuses());
</script>

<template>
    <BackendLayout>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">Statuses</h1>
        </div>

        <div class="mb-4">
            <input
                v-model="userQuery"
                type="text"
                placeholder="Filter by user name / username..."
                class="input input-bordered input-sm w-full"
            />
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
                            <th>ID</th>
                            <th>User</th>
                            <th>Origin</th>
                            <th>Destination</th>
                            <th>Departure</th>
                            <th>Visibility</th>
                            <th>Business</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!statuses.length">
                            <td colspan="8" class="text-center text-base-content/50 py-8">No statuses found.</td>
                        </tr>
                        <tr
                            v-for="status in statuses"
                            :key="status.id"
                            class="hover cursor-pointer"
                            @click="$router.push(`/admin/statuses/${status.id}`)"
                        >
                            <td class="font-mono text-xs">{{ status.id }}</td>
                            <td class="text-sm">
                                <span class="font-medium">{{ status.user?.name }}</span>
                                <span class="text-base-content/50 ml-1">@{{ status.user?.username }}</span>
                            </td>
                            <td class="text-sm max-w-36 truncate">{{ status.checkin?.origin_station_name ?? '—' }}</td>
                            <td class="text-sm max-w-36 truncate">
                                {{ status.checkin?.destination_station_name ?? '—' }}
                            </td>
                            <td class="text-xs text-base-content/60 whitespace-nowrap">
                                {{ formatDate(status.checkin?.departure) }}
                            </td>
                            <td>
                                <span class="badge badge-sm badge-ghost">{{ visibilityLabel(status.visibility) }}</span>
                            </td>
                            <td>
                                <span class="badge badge-sm badge-ghost">{{ businessLabel(status.business) }}</span>
                            </td>
                            <td class="text-xs text-base-content/60 whitespace-nowrap">
                                {{ formatDate(status.created_at) }}
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
                @click="fetchStatuses(prevCursor ?? undefined)"
            >
                ← Previous
            </button>
            <button
                class="btn btn-sm btn-ghost"
                :disabled="!nextCursor"
                @click="fetchStatuses(nextCursor ?? undefined)"
            >
                Next →
            </button>
        </div>
    </BackendLayout>
</template>
