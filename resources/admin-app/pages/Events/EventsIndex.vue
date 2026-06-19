<script setup lang="ts">
import { ExternalLink, PencilLine, Plus, Trash2 } from '@lucide/vue';
import { onMounted, ref, watch } from 'vue';
import { Api, type EventAdminResource } from '../../../types/Api.gen';
import { useUserStore } from '../../../vue/stores/user';
import BackendLayout from '../../layouts/BackendLayout.vue';

const userStore = useUserStore();
const isAdmin = userStore.user?.roles.includes('admin') ?? false;
const canEdit = isAdmin || (userStore.user?.roles.includes('event-moderator') ?? false);

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const events = ref<EventAdminResource[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);
const deletingIds = ref<Set<number>>(new Set());
const nextCursor = ref<string | null>(null);
const prevCursor = ref<string | null>(null);
const activeTab = ref<'future' | 'current' | 'past'>('current');
const search = ref('');
let searchTimeout: ReturnType<typeof setTimeout> | null = null;

async function fetchEvents(cursor?: string): Promise<void> {
    loading.value = true;
    error.value = null;
    try {
        const res = await api.admin.getAdminEvents({
            status: activeTab.value,
            search: search.value || undefined,
            cursor,
        });
        events.value = res.data?.data ?? [];
        const meta = (res.data as unknown as { meta?: { next_cursor?: string; prev_cursor?: string } }).meta;
        nextCursor.value = meta?.next_cursor ?? null;
        prevCursor.value = meta?.prev_cursor ?? null;
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Unknown error';
    } finally {
        loading.value = false;
    }
}

async function deleteEvent(event: EventAdminResource): Promise<void> {
    if (!confirm(`Delete event "${event.name}"?`)) return;

    const id = event.id!;
    deletingIds.value = new Set([...deletingIds.value, id]);
    try {
        await api.admin.deleteAdminEvent(id);
        await fetchEvents();
    } catch (e) {
        window.alert(`Delete failed: ${e instanceof Error ? e.message : 'Unknown error'}`);
    } finally {
        const next = new Set(deletingIds.value);
        next.delete(id);
        deletingIds.value = next;
    }
}

function onSearchInput(): void {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => fetchEvents(), 300);
}

watch(activeTab, () => {
    nextCursor.value = null;
    prevCursor.value = null;
    fetchEvents();
});

onMounted(() => fetchEvents());
</script>

<template>
    <BackendLayout>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">Events</h1>
            <div class="flex gap-2">
                <router-link to="/admin/event-suggestions" class="btn btn-ghost btn-sm"> Suggestions </router-link>
                <router-link v-if="isAdmin" to="/admin/events/create" class="btn btn-primary btn-sm gap-1">
                    <Plus class="w-4 h-4" />
                    New Event
                </router-link>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 mb-4">
            <div class="tabs tabs-boxed">
                <button class="tab" :class="{ 'tab-active': activeTab === 'current' }" @click="activeTab = 'current'">
                    Current
                </button>
                <button class="tab" :class="{ 'tab-active': activeTab === 'future' }" @click="activeTab = 'future'">
                    Upcoming
                </button>
                <button class="tab" :class="{ 'tab-active': activeTab === 'past' }" @click="activeTab = 'past'">
                    Past
                </button>
            </div>
            <input
                v-model="search"
                type="search"
                class="input input-sm flex-1"
                placeholder="Search by name..."
                @input="onSearchInput"
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
                            <th>Name</th>
                            <th>Hashtag</th>
                            <th>Station</th>
                            <th>Check-in Start</th>
                            <th>Check-in End</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!events.length">
                            <td colspan="6" class="text-center text-base-content/50 py-8">No events found.</td>
                        </tr>
                        <tr v-for="event in events" :key="event.id" class="hover">
                            <td class="font-medium text-sm">
                                <a
                                    v-if="event.slug"
                                    :href="`/event/${event.slug}`"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1 hover:underline"
                                >
                                    {{ event.name }}
                                    <ExternalLink class="w-3 h-3 shrink-0 text-base-content/40" />
                                </a>
                                <span v-else>{{ event.name }}</span>
                            </td>
                            <td class="text-xs text-base-content/60">
                                {{ event.hashtag ? `#${event.hashtag}` : '—' }}
                            </td>
                            <td class="text-xs text-base-content/60">{{ event.station?.name ?? '—' }}</td>
                            <td class="text-xs whitespace-nowrap">{{ event.checkin_start }}</td>
                            <td class="text-xs whitespace-nowrap">{{ event.checkin_end }}</td>
                            <td class="text-right">
                                <div v-if="canEdit" class="flex gap-1 justify-end">
                                    <router-link :to="`/admin/events/${event.id}/edit`" class="btn btn-xs btn-primary">
                                        <PencilLine class="w-3 h-3" />
                                        Edit
                                    </router-link>
                                    <button
                                        v-if="isAdmin"
                                        class="btn btn-xs btn-outline btn-error"
                                        :disabled="deletingIds.has(event.id!)"
                                        @click="deleteEvent(event)"
                                    >
                                        <span
                                            v-if="deletingIds.has(event.id!)"
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
            <button class="btn btn-sm btn-ghost" :disabled="!prevCursor" @click="fetchEvents(prevCursor ?? undefined)">
                ← Previous
            </button>
            <button class="btn btn-sm btn-ghost" :disabled="!nextCursor" @click="fetchEvents(nextCursor ?? undefined)">
                Next →
            </button>
        </div>
    </BackendLayout>
</template>
