<script setup lang="ts">
import { ArrowLeft, ExternalLink, Trash2 } from '@lucide/vue';
import { computed, onMounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import { type AdminUserResource, Api } from '../../../types/Api.gen';
import BackendLayout from '../../layouts/BackendLayout.vue';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const route = useRoute();
const userId = computed(() => Number(route.params.id));

const user = ref<AdminUserResource | null>(null);
const loading = ref(true);
const error = ref<string | null>(null);

const emailInput = ref('');
const emailSaving = ref(false);
const emailError = ref<string | null>(null);
const emailSuccess = ref(false);

const avatarDeleting = ref(false);
const avatarError = ref<string | null>(null);

const rolesSaving = ref(false);
const rolesError = ref<string | null>(null);
const rolesSuccess = ref(false);
const selectedRoles = ref<string[]>([]);

async function fetchUser(): Promise<void> {
    loading.value = true;
    error.value = null;
    try {
        const res = await api.admin.getAdminUser(userId.value);
        user.value = res.data.data ?? null;
        if (user.value) {
            emailInput.value = user.value.email ?? '';
            selectedRoles.value = [...(user.value.roles ?? [])];
        }
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Unknown error';
    } finally {
        loading.value = false;
    }
}

async function saveEmail(): Promise<void> {
    if (!user.value) return;
    emailSaving.value = true;
    emailError.value = null;
    emailSuccess.value = false;
    try {
        await api.admin.updateAdminUserEmail(userId.value, { email: emailInput.value });
        emailSuccess.value = true;
        await fetchUser();
    } catch (e: unknown) {
        const err = e as { error?: { message?: string } };
        emailError.value = err?.error?.message ?? (e instanceof Error ? e.message : 'Failed to update email');
    } finally {
        emailSaving.value = false;
    }
}

async function saveRoles(): Promise<void> {
    if (!user.value) return;
    rolesSaving.value = true;
    rolesError.value = null;
    rolesSuccess.value = false;
    try {
        await api.admin.updateAdminUserRoles(userId.value, { roles: selectedRoles.value });
        rolesSuccess.value = true;
        await fetchUser();
    } catch (e: unknown) {
        const err = e as { error?: { message?: string } };
        rolesError.value = err?.error?.message ?? (e instanceof Error ? e.message : 'Failed to update roles');
    } finally {
        rolesSaving.value = false;
    }
}

async function deleteProfilePicture(): Promise<void> {
    if (!user.value?.uuid) return;
    if (!confirm(`Delete the profile picture of @${user.value.username}?`)) return;
    avatarDeleting.value = true;
    avatarError.value = null;
    try {
        await api.settings.deleteProfilePicture(user.value.uuid);
        await fetchUser();
    } catch (e: unknown) {
        const err = e as { error?: { message?: string } };
        avatarError.value =
            err?.error?.message ?? (e instanceof Error ? e.message : 'Failed to delete profile picture');
    } finally {
        avatarDeleting.value = false;
    }
}

function toggleRole(roleName: string): void {
    if (roleName === 'admin') return;
    const idx = selectedRoles.value.indexOf(roleName);
    if (idx === -1) {
        selectedRoles.value.push(roleName);
    } else {
        selectedRoles.value.splice(idx, 1);
    }
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

function formatDistance(metres: number): string {
    return (metres / 1000).toFixed(0) + ' km';
}

function formatDuration(minutes: number): string {
    const h = Math.floor(minutes / 60);
    const m = Math.round(minutes % 60);
    return h > 0 ? `${h}h ${m}m` : `${m}m`;
}

onMounted(fetchUser);
watch(userId, fetchUser);
</script>

<template>
    <BackendLayout>
        <div v-if="loading" class="flex justify-center py-16">
            <span class="loading loading-spinner loading-lg" />
        </div>

        <div v-else-if="error" role="alert" class="alert alert-error mb-4">
            <span>{{ error }}</span>
        </div>

        <template v-else-if="user">
            <div class="flex items-center gap-3 mb-6">
                <router-link to="/admin/users" class="btn btn-ghost btn-sm gap-1">
                    <ArrowLeft class="w-4 h-4" />
                    Users
                </router-link>
                <h1 class="text-2xl font-bold">@{{ user.username }}</h1>
                <span class="text-base-content/50">{{ user.displayName }}</span>
                <a :href="`/@${user.username}`" target="_blank" class="btn btn-ghost btn-sm ml-auto gap-1">
                    Frontend
                    <ExternalLink class="w-4 h-4" />
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-[5fr_7fr] gap-6">
                <!-- Left column -->
                <div class="space-y-4">
                    <!-- Profile picture card -->
                    <div class="card bg-base-100 shadow">
                        <div class="card-body gap-3">
                            <h2 class="card-title text-base">Profile Picture</h2>
                            <div class="flex items-center gap-4">
                                <img
                                    :src="user.profilePictureUrl ?? `/@${user.username}/picture`"
                                    alt=""
                                    class="w-20 h-20 rounded-full object-cover bg-base-200"
                                />
                                <p v-if="!user.profilePictureUrl" class="flex-1 text-sm text-base-content/50">
                                    No uploaded picture, showing the generated default.
                                </p>
                                <button
                                    v-else
                                    class="btn btn-sm btn-error ml-auto"
                                    :disabled="avatarDeleting"
                                    @click="deleteProfilePicture"
                                >
                                    <span v-if="avatarDeleting" class="loading loading-spinner loading-xs" />
                                    <Trash2 v-else class="w-4 h-4" />
                                    Delete
                                </button>
                            </div>
                            <p v-if="avatarError" class="text-xs text-error">{{ avatarError }}</p>
                        </div>
                    </div>

                    <!-- User info card -->
                    <div class="card bg-base-100 shadow">
                        <div class="card-body gap-3">
                            <h2 class="card-title text-base">Account Info</h2>
                            <dl class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-2 text-sm">
                                <dt class="text-base-content/50 font-medium">ID</dt>
                                <dd class="font-mono">{{ user.id }}</dd>

                                <dt class="text-base-content/50 font-medium">UUID</dt>
                                <dd class="font-mono text-xs break-all">{{ user.uuid }}</dd>

                                <dt class="text-base-content/50 font-medium">Username</dt>
                                <dd>@{{ user.username }}</dd>

                                <dt class="text-base-content/50 font-medium">Password</dt>
                                <dd>
                                    <span v-if="user.hasPassword" class="text-success text-xs">✓ set</span>
                                    <span v-else class="text-error text-xs">✗ not set</span>
                                </dd>

                                <dt class="text-base-content/50 font-medium">Email</dt>
                                <dd class="text-xs break-all">
                                    {{ user.email ?? '—' }}
                                    <span v-if="user.emailVerifiedAt" class="text-success ml-1">✓</span>
                                    <span v-else-if="user.email" class="text-error ml-1">✗</span>
                                </dd>

                                <dt class="text-base-content/50 font-medium">Mastodon</dt>
                                <dd class="text-xs break-all">
                                    <a
                                        v-if="user.mastodonUrl"
                                        :href="user.mastodonUrl"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="link link-hover"
                                        >{{ user.mastodonUrl }}</a
                                    >
                                    <span v-else>—</span>
                                </dd>

                                <dt class="text-base-content/50 font-medium">Last Login</dt>
                                <dd class="text-xs">{{ formatDate(user.lastLogin) }}</dd>

                                <dt class="text-base-content/50 font-medium">Created</dt>
                                <dd class="text-xs">{{ formatDate(user.createdAt) }}</dd>

                                <dt class="text-base-content/50 font-medium">Privacy (current)</dt>
                                <dd class="text-xs">
                                    <span v-if="user.privacyPolicyCurrent" class="text-success">
                                        ✓ {{ formatDate(user.privacyPolicyCurrent) }}
                                    </span>
                                    <span v-else class="text-error">✗ not accepted</span>
                                </dd>

                                <template v-if="user.privacyPolicyFutureExists">
                                    <dt class="text-base-content/50 font-medium">Privacy (future)</dt>
                                    <dd class="text-xs">
                                        <span v-if="user.privacyPolicyFuture" class="text-success">
                                            ✓ {{ formatDate(user.privacyPolicyFuture) }}
                                        </span>
                                        <span v-else class="text-warning">✗ not accepted</span>
                                    </dd>
                                </template>
                            </dl>

                            <!-- Mail changes -->
                            <div v-if="user.mailChanges?.length" class="mt-2">
                                <details class="text-sm">
                                    <summary class="cursor-pointer text-base-content/60">
                                        {{ user.mailChanges.length }} mail change(s)
                                    </summary>
                                    <ul class="mt-2 space-y-1">
                                        <li
                                            v-for="mc in user.mailChanges"
                                            :key="mc.id"
                                            class="text-xs font-mono bg-base-200 rounded px-2 py-1"
                                        >
                                            {{ mc.oldEmail }} → {{ mc.newEmail }}
                                            <span class="text-base-content/50 ml-1">{{
                                                formatDate(mc.createdAt)
                                            }}</span>
                                        </li>
                                    </ul>
                                </details>
                            </div>
                        </div>
                    </div>

                    <!-- Change email card -->
                    <div class="card bg-base-100 shadow">
                        <div class="card-body gap-3">
                            <h2 class="card-title text-base">Change Email</h2>
                            <div class="flex gap-2">
                                <input
                                    v-model="emailInput"
                                    type="email"
                                    class="input input-bordered input-sm flex-1"
                                    placeholder="New email address"
                                    autocomplete="false"
                                />
                                <button class="btn btn-sm btn-primary" :disabled="emailSaving" @click="saveEmail">
                                    <span v-if="emailSaving" class="loading loading-spinner loading-xs" />
                                    Save
                                </button>
                            </div>
                            <p v-if="emailSuccess" class="text-xs text-success">Email updated.</p>
                            <p v-if="emailError" class="text-xs text-error">{{ emailError }}</p>
                        </div>
                    </div>

                    <!-- Roles card -->
                    <div class="card bg-base-100 shadow">
                        <div class="card-body gap-3">
                            <h2 class="card-title text-base">Roles</h2>
                            <ul class="space-y-2 text-sm">
                                <li v-for="role in user.allRoles" :key="role.name">
                                    <label class="flex items-start gap-2 cursor-pointer">
                                        <input
                                            type="checkbox"
                                            class="checkbox checkbox-sm mt-0.5"
                                            :disabled="role.name === 'admin'"
                                            :checked="selectedRoles.includes(role.name)"
                                            @change="toggleRole(role.name)"
                                        />
                                        <div>
                                            <span class="font-medium">{{ role.name }}</span>
                                            <div
                                                v-if="role.permissions?.length"
                                                class="text-xs text-base-content/50 mt-0.5"
                                            >
                                                <code v-for="perm in role.permissions" :key="perm" class="mr-1">{{
                                                    perm
                                                }}</code>
                                            </div>
                                        </div>
                                    </label>
                                </li>
                            </ul>
                            <div class="flex items-center gap-2 mt-1">
                                <button class="btn btn-sm btn-primary" :disabled="rolesSaving" @click="saveRoles">
                                    <span v-if="rolesSaving" class="loading loading-spinner loading-xs" />
                                    Update Roles
                                </button>
                                <span v-if="rolesSuccess" class="text-xs text-success">Saved.</span>
                                <span v-if="rolesError" class="text-xs text-error">{{ rolesError }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right column -->
                <div class="space-y-4">
                    <!-- Stats card -->
                    <div class="card bg-base-100 shadow">
                        <div class="card-body">
                            <h2 class="card-title text-base">Stats</h2>
                            <div class="stats stats-horizontal shadow w-full">
                                <div class="stat">
                                    <div class="stat-title">Distance</div>
                                    <div class="stat-value text-lg">{{ formatDistance(user.trainDistance) }}</div>
                                </div>
                                <div class="stat">
                                    <div class="stat-title">Duration</div>
                                    <div class="stat-value text-lg">{{ formatDuration(user.trainDuration) }}</div>
                                </div>
                                <div class="stat">
                                    <div class="stat-title">Points</div>
                                    <div class="stat-value text-lg">{{ user.points }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent statuses card -->
                    <div class="card bg-base-100 shadow">
                        <div class="card-body">
                            <h2 class="card-title text-base">Recent Statuses</h2>
                            <p v-if="!user.recentStatuses?.length" class="text-sm text-base-content/50">
                                No statuses yet.
                            </p>
                            <div v-else class="overflow-x-auto">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th class="w-16">ID</th>
                                            <th>Origin → Destination</th>
                                            <th class="w-24">Points</th>
                                            <th class="w-36">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="status in user.recentStatuses" :key="status.id" class="hover">
                                            <td>
                                                <a
                                                    :href="`/admin/statuses/${status.id}`"
                                                    class="link link-hover font-mono text-xs"
                                                >
                                                    #{{ status.id }}
                                                </a>
                                            </td>
                                            <td class="text-sm">
                                                <div v-if="status.checkin">
                                                    <router-link
                                                        v-if="status.checkin.origin?.id"
                                                        :to="`/admin/stations/${status.checkin.origin.id}`"
                                                        class="link link-hover"
                                                    >
                                                        {{ status.checkin.origin.name }}
                                                    </router-link>
                                                    <span class="text-base-content/50 mx-1">→</span>
                                                    <router-link
                                                        v-if="status.checkin.destination?.id"
                                                        :to="`/admin/stations/${status.checkin.destination.id}`"
                                                        class="link link-hover"
                                                    >
                                                        {{ status.checkin.destination.name }}
                                                    </router-link>
                                                    <div class="text-xs text-base-content/50 font-mono mt-0.5">
                                                        {{ status.checkin.lineName }}
                                                    </div>
                                                </div>
                                                <span v-else class="text-base-content/40 text-xs">—</span>
                                            </td>
                                            <td class="text-sm">{{ status.checkin?.points ?? '—' }}</td>
                                            <td class="text-xs text-base-content/60 whitespace-nowrap">
                                                {{ formatDate(status.createdAt) }}
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
