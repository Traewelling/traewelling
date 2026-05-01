<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Calendar, Check } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import { Api, EventResource } from '../../../types/Api.gen';

const props = defineProps<{
    modelValue?: EventResource | null;
    timestamp?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', event: EventResource | null): void;
}>();

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const events = ref<EventResource[]>([]);
const search = ref('');
const open = ref(false);

const filtered = computed(() =>
    search.value ? events.value.filter((e) => e.name.toLowerCase().includes(search.value.toLowerCase())) : events.value,
);

async function fetchEvents(): Promise<void> {
    try {
        const res = await api.events.getEvents(props.timestamp ? { timestamp: props.timestamp } : {});
        events.value = res.data?.data ?? [];
    } catch {
        // best-effort
    }
}

function select(event: EventResource): void {
    if (props.modelValue?.slug === event.slug) {
        emit('update:modelValue', null);
    } else {
        emit('update:modelValue', event);
        open.value = false;
    }
}

onMounted(fetchEvents);
</script>

<template>
    <div class="relative">
        <div v-if="open" class="fixed inset-0 z-40" @click="open = false" />
        <button
            type="button"
            class="btn btn-sm gap-1"
            :class="modelValue ? 'btn-primary' : 'btn-ghost'"
            @click="open = !open"
        >
            <Calendar class="w-4 h-4" />
            <span v-if="modelValue" class="max-w-[10rem] truncate text-xs">{{ modelValue.name }}</span>
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
                    :placeholder="trans('stationboard.event-filter')"
                    autocomplete="off"
                />
            </div>

            <ul v-if="filtered.length" class="max-h-48 overflow-y-auto divide-y divide-base-200">
                <li v-for="event in filtered" :key="event.slug">
                    <button
                        type="button"
                        class="w-full flex items-center gap-2 px-3 py-2 hover:bg-base-200 text-left text-sm transition-colors"
                        :class="{ 'bg-primary/10 font-medium': modelValue?.slug === event.slug }"
                        @click="select(event)"
                    >
                        <Check
                            class="w-4 h-4 flex-shrink-0"
                            :class="modelValue?.slug === event.slug ? 'text-primary' : 'invisible'"
                        />
                        <div class="min-w-0">
                            <p class="truncate">{{ event.name }}</p>
                            <p v-if="event.station?.name" class="text-xs text-base-content/50 truncate">
                                {{ event.station.name }}
                            </p>
                        </div>
                    </button>
                </li>
            </ul>

            <div v-else class="p-4 text-center text-sm text-base-content/50">
                <p>{{ trans('stationboard.events-none') }}</p>
                <p class="mt-1">
                    {{ trans('stationboard.events-propose') }}
                    <a href="/events" target="_blank" class="link link-primary">traewelling.de/events</a>
                </p>
            </div>
        </div>
    </div>
</template>
