<script setup lang="ts">
import { Check, Users } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { computed, onMounted, ref } from 'vue';
import { Api, TrustedUserResource } from '../../../types/Api.gen';

const emit = defineEmits<{
    (e: 'update:modelValue', ids: number[]): void;
}>();

const api = new Api({ baseUrl: window.location.origin + '/api' });
const friends = ref<TrustedUserResource[]>([]);
const selected = ref<number[]>([]);
const search = ref('');
const open = ref(false);

const filtered = computed(() =>
    search.value
        ? friends.value.filter(
              (f) =>
                  f.user?.displayName?.toLowerCase().includes(search.value.toLowerCase()) ||
                  f.user?.username?.toLowerCase().includes(search.value.toLowerCase()),
          )
        : friends.value,
);

async function fetchFriends(): Promise<void> {
    try {
        const res = await api.user.trustedByUserIndex();
        friends.value = res.data?.data ?? [];
    } catch {
        // best-effort
    }
}

function toggle(friend: TrustedUserResource): void {
    const id = friend.user?.id;
    if (!id) return;
    if (selected.value.includes(id)) {
        selected.value = selected.value.filter((i) => i !== id);
    } else {
        selected.value.push(id);
    }
    emit('update:modelValue', selected.value);
}

onMounted(fetchFriends);
</script>

<template>
    <div class="relative">
        <div v-if="open" class="fixed inset-0 z-40" @click="open = false" />
        <button
            type="button"
            class="btn btn-sm gap-1"
            :class="selected.length ? 'btn-primary' : 'btn-ghost'"
            @click="open = !open"
        >
            <Users class="w-4 h-4" />
            <span v-if="selected.length" class="text-xs">{{ selected.length }}</span>
        </button>

        <div
            v-if="open"
            class="absolute bottom-full left-0 z-50 mb-1 w-72 border border-base-300 rounded-box overflow-hidden bg-base-100 shadow-lg"
        >
            <div class="p-2 border-b border-base-300">
                <input
                    v-model="search"
                    type="search"
                    class="input input-bordered input-sm w-full"
                    :placeholder="trans('stationboard.friend-filter')"
                    autocomplete="off"
                />
            </div>

            <ul v-if="filtered.length" class="max-h-48 overflow-y-auto divide-y divide-base-200">
                <li v-for="friend in filtered" :key="friend.user?.id">
                    <button
                        type="button"
                        class="w-full flex items-center gap-2 px-3 py-2 hover:bg-base-200 text-left text-sm transition-colors"
                        :class="{ 'bg-primary/10': friend.user?.id && selected.includes(friend.user.id) }"
                        @click="toggle(friend)"
                    >
                        <Check
                            class="w-4 h-4 flex-shrink-0"
                            :class="friend.user?.id && selected.includes(friend.user.id) ? 'text-primary' : 'invisible'"
                        />
                        <img
                            v-if="friend.user?.profilePicture"
                            :src="friend.user.profilePicture"
                            class="w-6 h-6 rounded-full object-cover flex-shrink-0"
                            :alt="friend.user.displayName"
                        />
                        <div class="min-w-0">
                            <p class="truncate font-medium">{{ friend.user?.displayName }}</p>
                            <p class="text-xs text-base-content/50 truncate">@{{ friend.user?.username }}</p>
                        </div>
                    </button>
                </li>
            </ul>

            <div v-else class="p-4 text-center text-sm text-base-content/50">
                <p>{{ trans('stationboard.friends-none') }}</p>
                <p class="mt-1">
                    {{ trans('stationboard.friends-set') }}
                    <a href="/settings/followers" target="_blank" class="link link-primary"
                        >traewelling.de/settings/followers</a
                    >
                </p>
            </div>
        </div>
    </div>
</template>
