<template>
    <div class="card bg-base-200 shadow-sm w-full">
        <div class="card-body p-6 sm:p-8">
            <h3 class="card-title text-xl mb-4">
                <History class="w-5 h-5" />
                {{ trans('contribute.history.title') }}
            </h3>

            <div v-if="loading && entries.length === 0" class="flex justify-center py-8">
                <span class="loading loading-spinner loading-md"></span>
            </div>

            <div v-else-if="entries.length === 0" class="text-center py-8 opacity-60">
                {{ trans('contribute.history.empty') }}
            </div>

            <div v-else class="@container flex flex-col gap-2">
                <div
                    v-for="entry in entries"
                    :key="entry.id"
                    class="flex flex-col gap-2 @md:flex-row @md:items-center @md:gap-4 w-full rounded-lg bg-base-100 px-4 py-3"
                >
                    <div class="flex items-center gap-2 shrink-0">
                        <TrendingUp v-if="entry.xpChange > 0" class="w-4 h-4 text-success" />
                        <TrendingDown v-else class="w-4 h-4 text-error" />
                        <span
                            class="badge badge-sm font-mono"
                            :class="entry.xpChange > 0 ? 'badge-success' : 'badge-error'"
                        >
                            {{ entry.xpChange > 0 ? '+' : '' }}{{ entry.xpChange }} XP
                        </span>
                        <span v-if="entry.levelAfter !== entry.levelBefore" class="badge badge-sm badge-warning">
                            Level {{ entry.levelBefore }} → {{ entry.levelAfter }}
                        </span>
                    </div>
                    <p v-if="entry.note" class="text-sm opacity-70 flex-1 truncate">{{ entry.note }}</p>
                    <time class="text-xs opacity-50 shrink-0 @md:ml-auto">{{ formatDate(entry.createdAt) }}</time>
                </div>
            </div>

            <div v-if="hasMore" class="flex justify-center mt-4">
                <button class="btn btn-ghost btn-sm" :disabled="loading" @click="loadMore">
                    <span v-if="loading" class="loading loading-spinner loading-sm"></span>
                    {{ trans('contribute.history.load_more') }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { History, TrendingDown, TrendingUp } from 'lucide-vue-next';
import { DateTime } from 'luxon';
import { onMounted, ref } from 'vue';

interface HistoryEntry {
    id: string;
    actionType: string;
    entityType: string;
    entityId: number;
    xpChange: number;
    levelBefore: number;
    levelAfter: number;
    note: string | null;
    createdAt: string;
}

const props = withDefaults(defineProps<{ limit?: number }>(), { limit: 15 });

const entries = ref<HistoryEntry[]>([]);
const loading = ref(false);
const hasMore = ref(false);
const nextCursor = ref<string | null>(null);

function formatDate(iso: string): string {
    return DateTime.fromISO(iso).toLocaleString(DateTime.DATETIME_MED);
}

async function fetchHistory(cursor?: string) {
    loading.value = true;
    try {
        const params = new URLSearchParams({ limit: String(props.limit) });
        if (cursor) {
            params.set('cursor', cursor);
        }
        const response = await fetch(`/api/v1/community/history?${params}`);
        if (response.ok) {
            const json = await response.json();
            entries.value.push(...json.data);
            nextCursor.value = json.meta?.next_cursor ?? null;
            hasMore.value = nextCursor.value !== null;
        }
    } catch (error) {
        console.error('Failed to fetch contribution history:', error);
    } finally {
        loading.value = false;
    }
}

function loadMore() {
    if (nextCursor.value) {
        fetchHistory(nextCursor.value);
    }
}

onMounted(() => {
    fetchHistory();
});
</script>
