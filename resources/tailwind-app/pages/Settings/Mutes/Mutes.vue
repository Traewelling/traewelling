<script setup lang="ts">
import { Eye, EyeOff } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { inject, ref } from 'vue';
import { Api, LightUser } from '../../../../types/Api.gen';
import SettingsLayout from '../../../layouts/SettingsLayout.vue';
import FollowersSubMenu from '../Followers/partials/FollowersSubMenu.vue';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const notyf = inject('notyf') as Notyf;

const mutedUsers = ref<LightUser[]>([]);
const nextCursor = ref<string | null>(null);
const hasMore = ref(false);
const loading = ref(true);

async function fetchMutedUsers() {
    loading.value = true;
    const response = await api.users.getMutedUsers({ cursor: nextCursor.value ?? undefined });
    const data = await response.json();
    mutedUsers.value.push(...(data.data ?? []));
    nextCursor.value = data.meta?.next_cursor ?? null;
    hasMore.value = nextCursor.value !== null;
    loading.value = false;
}

async function unmute(user: LightUser) {
    const response = await api.user.destroyMute(user.id);
    if (response.ok) {
        mutedUsers.value = mutedUsers.value.filter((u) => u.id !== user.id);
        notyf.success(trans('user.unmute-tooltip'));
    } else {
        notyf.error(trans('error'));
    }
}

fetchMutedUsers();
</script>

<template>
    <SettingsLayout>
        <FollowersSubMenu />
        <div class="mt-4">
            <div v-if="loading && mutedUsers.length === 0" class="flex justify-center py-12">
                <span class="loading loading-spinner loading-lg"></span>
            </div>

            <div v-else-if="!loading && mutedUsers.length === 0" class="text-center py-12 text-base-content/50">
                <EyeOff class="size-8 mx-auto mb-2" />
                <p>{{ trans('user.muted.noMutedUsers') }}</p>
            </div>

            <ul v-else class="list bg-base-100 rounded-box shadow-md">
                <li v-for="user in mutedUsers" :key="user.id" class="list-row items-center">
                    <div class="avatar">
                        <div class="rounded-full w-12 h-12">
                            <a :href="`/@${user.username}`">
                                <img :src="user.profilePicture" :alt="user.displayName" />
                            </a>
                        </div>
                    </div>
                    <div class="list-col-grow">
                        <a :href="`/@${user.username}`">
                            <p class="font-semibold mb-0">{{ user.displayName }}</p>
                            <p class="text-sm opacity-75 mb-0">@{{ user.username }}</p>
                        </a>
                    </div>
                    <button class="btn btn-sm btn-primary" @click="unmute(user)">
                        <Eye class="size-4" />
                        {{ trans('user.unmute-tooltip') }}
                    </button>
                </li>
            </ul>

            <div v-if="hasMore" class="text-center mt-4">
                <button class="btn" :disabled="loading" @click="fetchMutedUsers">
                    {{ loading ? trans('menu.loading') : trans('menu.show-more') }}
                </button>
            </div>
        </div>
    </SettingsLayout>
</template>
