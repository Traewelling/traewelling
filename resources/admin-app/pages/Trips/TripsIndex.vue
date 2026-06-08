<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { type AdminTripResource, Api } from '../../../types/Api.gen';
import BackendLayout from '../../layouts/BackendLayout.vue';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const trips = ref<AdminTripResource[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);
const nextCursor = ref<string | null>(null);
const prevCursor = ref<string | null>(null);

async function fetchTrips(cursor?: string): Promise<void> {
    loading.value = true;
    error.value = null;
    try {
        const res = await api.admin.getAdminTrips({ cursor });
        trips.value = res.data.data ?? [];
        const meta = (res.data as { meta?: { next_cursor?: string; prev_cursor?: string } }).meta;
        nextCursor.value = meta?.next_cursor ?? null;
        prevCursor.value = meta?.prev_cursor ?? null;
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Unknown error';
    } finally {
        loading.value = false;
    }
}

onMounted(() => fetchTrips());
</script>

<template>
    <BackendLayout>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">Trips</h1>
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
                            <th>Checkins</th>
                            <th>Source</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Category</th>
                            <th>Mode</th>
                            <th>Number</th>
                            <th>Line</th>
                            <th>Journey Nr.</th>
                            <th>Operator</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!trips.length">
                            <td colspan="11" class="text-center text-base-content/50 py-8">No trips found.</td>
                        </tr>
                        <tr
                            v-for="trip in trips"
                            :key="trip.id"
                            class="hover cursor-pointer"
                            @click="$router.push(`/admin/trips/${trip.id}`)"
                        >
                            <td class="font-mono text-xs">{{ trip.id }}</td>
                            <td>{{ trip.checkinsCount }}</td>
                            <td class="text-sm">
                                <a
                                    v-if="trip.user"
                                    :href="`/admin/users/${trip.user.id}`"
                                    class="link link-hover"
                                    @click.stop
                                >
                                    @{{ trip.user.username }}
                                </a>
                                <span v-else class="text-base-content/50 text-xs">{{ trip.source ?? '—' }}</span>
                            </td>
                            <td class="text-sm">
                                <a
                                    v-if="trip.origin"
                                    :href="`/admin/stations/${trip.origin.id}`"
                                    class="link link-hover"
                                    @click.stop
                                >
                                    {{ trip.origin.name }}
                                </a>
                                <span v-else>—</span>
                            </td>
                            <td class="text-sm">
                                <a
                                    v-if="trip.destination"
                                    :href="`/admin/stations/${trip.destination.id}`"
                                    class="link link-hover"
                                    @click.stop
                                >
                                    {{ trip.destination.name }}
                                </a>
                                <span v-else>—</span>
                            </td>
                            <td class="text-xs font-mono">{{ trip.category }}</td>
                            <td class="text-xs font-mono">{{ trip.mode ?? '—' }}</td>
                            <td class="text-xs">{{ trip.number ?? '—' }}</td>
                            <td>
                                <span
                                    v-if="trip.lineName"
                                    class="badge badge-sm font-mono"
                                    :style="trip.routeColor ? `background-color:#${trip.routeColor}` : ''"
                                >
                                    {{ trip.lineName }}
                                </span>
                                <span v-else>—</span>
                            </td>
                            <td class="font-mono text-xs">{{ trip.journeyNumber ?? '—' }}</td>
                            <td class="text-sm">{{ trip.operator ?? '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="prevCursor || nextCursor" class="flex justify-center gap-2 mt-4">
            <button class="btn btn-sm btn-ghost" :disabled="!prevCursor" @click="fetchTrips(prevCursor ?? undefined)">
                ← Previous
            </button>
            <button class="btn btn-sm btn-ghost" :disabled="!nextCursor" @click="fetchTrips(nextCursor ?? undefined)">
                Next →
            </button>
        </div>
    </BackendLayout>
</template>
