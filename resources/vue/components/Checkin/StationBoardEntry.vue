<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { DateTime } from 'luxon';
import { computed } from 'vue';
import { departureEntry } from '../../../types/Departure';
import LineIndicator from '../LineIndicator.vue';
import ProductIcon from '../ProductIcon.vue';

const props = defineProps({
    item: {
        type: Object as () => departureEntry,
        required: true,
    },
    station: {
        type: Object,
        required: true,
    },
});

function formatTime(time: string | null): string {
    if (!time) return '';
    return DateTime.fromISO(time).toFormat('HH:mm');
}

const isPast = computed((): boolean => {
    const when = props.item.when || props.item.plannedWhen;
    if (!when) return false;
    return DateTime.fromISO(when).plus({ minutes: 1 }) < DateTime.now();
});

const cancelled = computed((): boolean => {
    return props.item.cancelled || false;
});

const delayClass = computed((): string => {
    if (props.item.delay === null) {
        return '';
    }
    let color = 'text-success';
    if (props.item.delay > 300) {
        color = 'text-danger';
    } else if (props.item.delay >= 60) {
        color = 'text-warning';
    }
    return color;
});

const backgroundColor = computed<string | null>(() => {
    return (props.item as any)?.line?.color || null;
});

const textColor = computed<string | null>(() => {
    return (props.item as any)?.line?.textColor || null;
});

function normalizeLineName(name: string | null): string {
    if (!name) return '';
    // remove numbers in brackets
    return name.replaceAll(/\(.*?\)/g, '').trim();
}
</script>

<template>
    <div class="card mb-1 dep-card" :class="{ 'past-card': isPast, 'cancelled-card': cancelled }">
        <div class="card-body d-flex py-0">
            <div class="col-1 align-items-center d-flex justify-content-center">
                <ProductIcon :product="item.line.product" :mode="item.line.mode" />
            </div>

            <div class="col-2 align-items-center d-flex me-3 justify-content-center">
                <span v-if="cancelled" class="sr-only">{{ trans('stationboard.stop-cancelled') }}</span>
                <LineIndicator
                    :mode="item.line.mode"
                    :product-name="item.line.product"
                    :number="item.line.name !== null ? normalizeLineName(item.line.name) : item.line.fahrtNr"
                    :background-color="backgroundColor"
                    :color="textColor"
                />
            </div>

            <div class="col align-items-center d-flex second-stop">
                <div>
                    <span class="fw-bold fs-6">{{ item.direction }}</span
                    ><br />
                    <span v-if="item.stop.name !== station.name" class="text-muted small font-italic">
                        {{ trans('stationboard.dep') }} {{ item.stop.name }}
                    </span>
                </div>
            </div>

            <div class="col-auto ms-auto align-items-center d-flex">
                <div v-if="item.delay">
                    <span class="text-muted text-decoration-line-through">
                        {{ formatTime(item.plannedWhen) }}<br />
                    </span>
                    <span :class="delayClass">{{ formatTime(item.when) }}</span>
                </div>
                <div v-else>
                    <span :class="delayClass">{{ formatTime(item.plannedWhen) }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped lang="scss">
@import '../../../sass/_variables.scss';

.dep-card {
    min-height: 4.25rem;
}

.past-card {
    opacity: 50%;
}

.cancelled-card {
    opacity: 50%;
    background-color: $red !important;
    color: $white;
    text-decoration: line-through;
    text-decoration-thickness: 2px;
}

.second-stop {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>
