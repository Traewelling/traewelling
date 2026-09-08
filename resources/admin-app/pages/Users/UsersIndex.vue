<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { type AdminUserListItem, Api } from '../../../types/Api.gen';
import BackendLayout from '../../layouts/BackendLayout.vue';

const api = new Api({ baseUrl: window.location.origin + '/api' });
const route = useRoute();
const router = useRouter();

const users = ref<AdminUserListItem[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);
const nextCursor = ref<string | null>(null);
const prevCursor = ref<string | null>(null);
const search = ref<string>((route.query.query as string) ?? '');

let debounceTimer: ReturnType<typeof setTimeout> | null = null;

async function fetchUsers(cursor?: string): Promise<void> {
    loading.value = true;
    error.value = null;
    try {
        const res = await api.admin.getAdminUsers({ cursor, query: search.value || undefined });
        users.value = res.data.data ?? [];
        const meta = (res.data as { meta?: { next_cursor?: string; prev_cursor?: string } }).meta;
        nextCursor.value = meta?.next_cursor ?? null;
        prevCursor.value = meta?.prev_cursor ?? null;
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Unknown error';
    } finally {
        loading.value = false;
    }
}

function onSearchInput(): void {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.replace({ query: search.value ? { query: search.value } : {} });
        fetchUsers();
    }, 300);
}

function formatDate(iso: string | null | undefined): string {
    if (!iso) return '—';
    return new Date(iso).toLocaleString(undefined, {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

watch(
    () => route.query.query,
    (val) => {
        search.value = (val as string) ?? '';
        fetchUsers();
    },
);
onMounted(() => fetchUsers());
</script>

<template>
    <BackendLayout>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">Users</h1>
        </div>

        <div class="mb-4">
            <input
                v-model="search"
                type="search"
                placeholder="Search by name, username, or email…"
                class="input input-bordered w-full max-w-md"
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
                            <th class="w-16">ID</th>
                            <th>Username</th>
                            <th>Display Name</th>
                            <th>Email</th>
                            <th class="w-36">Last Login</th>
                            <th class="w-36">Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!users.length">
                            <td colspan="6" class="text-center text-base-content/50 py-8">No users found.</td>
                        </tr>
                        <tr
                            v-for="user in users"
                            :key="user.id"
                            class="hover cursor-pointer"
                            @click="$router.push(`/admin/users/${user.id}`)"
                        >
                            <td class="font-mono text-xs">{{ user.id }}</td>
                            <td class="font-medium">@{{ user.username }}</td>
                            <td>{{ user.displayName }}</td>
                            <td class="text-xs text-base-content/70">
                                <span>{{ user.email ?? '—' }}</span>
                                <span
                                    v-if="user.emailVerifiedAt"
                                    class="badge badge-success badge-xs ml-1"
                                    title="Verified"
                                    >✓</span
                                >
                                <span v-else-if="user.email" class="badge badge-error badge-xs ml-1" title="Unverified"
                                    >✗</span
                                >
                            </td>
                            <td class="text-xs text-base-content/60 whitespace-nowrap">
                                {{ formatDate(user.lastLogin) }}
                            </td>
                            <td class="text-xs text-base-content/60 whitespace-nowrap">
                                {{ formatDate(user.createdAt) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="prevCursor || nextCursor" class="flex justify-center gap-2 mt-4">
            <button class="btn btn-sm btn-ghost" :disabled="!prevCursor" @click="fetchUsers(prevCursor ?? undefined)">
                ← Previous
            </button>
            <button class="btn btn-sm btn-ghost" :disabled="!nextCursor" @click="fetchUsers(nextCursor ?? undefined)">
                Next →
            </button>
        </div>
    </BackendLayout>
</template>
