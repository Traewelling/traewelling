<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { DateTime } from 'luxon';
import { computed } from 'vue';
import { DepartureResource } from '../../../types/Api.gen';
import LineIndicator from '../../../vue/components/LineIndicator.vue';
import TransportIcon from '../TransportIcon.vue';

const props = defineProps<{
    item: DepartureResource;
    stationName?: string;
}>();

function formatTime(time: string | null | undefined): string {
    if (!time) return '';
    return DateTime.fromISO(time).toFormat('HH:mm');
}

const isPast = computed((): boolean => {
    const when = props.item.when || props.item.plannedWhen;
    if (!when) return false;
    return DateTime.fromISO(when).plus({ minutes: 1 }) < DateTime.now();
});

const cancelled = computed((): boolean => props.item.cancelled ?? false);

const delayClass = computed((): string => {
    if (!props.item.delay) return '';
    if (props.item.delay > 5) return 'text-error';
    if (props.item.delay >= 1) return 'text-warning';
    return 'text-success';
});

const lineName = computed((): string => {
    const name = props.item.line?.name;
    if (!name) return props.item.line?.fahrtNr ?? '';
    return name.replaceAll(/\(.*?\)/g, '').trim();
});

const platform = computed((): string | null => props.item.platform ?? props.item.plannedPlatform ?? null);
</script>

<template>
    <div
        class="card bg-base-100 mb-1 cursor-pointer transition-colors hover:bg-base-200 active:scale-[0.99] select-none h-[3.5rem] overflow-hidden"
        :class="{ 'opacity-40': isPast && !cancelled, 'opacity-60': cancelled }"
    >
        <div class="card-body py-0 px-3 h-full flex flex-row items-center gap-3">
            <!-- Transport mode icon -->
            <div class="w-5 h-5 flex-shrink-0 flex items-center justify-center text-base-content/60">
                <TransportIcon :product="item.line?.product" :mode="item.line?.mode" />
            </div>

            <!-- Line badge: fixed width so destination column always starts at the same position -->
            <div class="flex-shrink-0 w-16 flex items-center overflow-hidden">
                <LineIndicator
                    :mode="item.line?.mode ?? null"
                    :product-name="item.line?.product ?? ''"
                    :number="lineName"
                    :background-color="item.line?.color ?? null"
                    :color="item.line?.textColor ?? null"
                />
            </div>

            <!-- Direction / departing stop -->
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-sm truncate" :class="{ 'line-through text-error': cancelled }">
                    {{ item.direction }}
                </p>
                <p
                    v-if="stationName && item.stop?.name && item.stop.name !== stationName"
                    class="text-xs text-base-content/50 italic truncate"
                >
                    {{ trans('stationboard.dep') }} {{ item.stop.name }}
                </p>
            </div>

            <!-- Platform badge -->
            <div v-if="platform" class="flex-shrink-0">
                <span class="badge badge-ghost badge-sm text-xs">{{ platform }}</span>
            </div>

            <!-- Time -->
            <div class="flex-shrink-0 text-right text-sm min-w-[3rem]">
                <template v-if="cancelled">
                    <span class="text-error line-through block">{{ formatTime(item.plannedWhen) }}</span>
                </template>
                <template v-else-if="item.delay">
                    <span class="text-base-content/40 line-through text-xs block">{{
                        formatTime(item.plannedWhen)
                    }}</span>
                    <span :class="delayClass">{{ formatTime(item.when) }}</span>
                </template>
                <template v-else>
                    <span>{{ formatTime(item.plannedWhen) }}</span>
                </template>
            </div>
        </div>
    </div>
</template>
