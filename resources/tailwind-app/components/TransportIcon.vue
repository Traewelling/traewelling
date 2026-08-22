<script setup lang="ts">
import { CarTaxiFront, Container, Moon, Plane, Ship, Train } from '@lucide/vue';
import { computed } from 'vue';

const props = defineProps<{
    product?: string | null;
    mode?: string | null;
}>();

const svgMap: Record<string, string> = {
    BUS: '/img/bus.svg',
    COACH: '/img/bus.svg',
    FLEX: '/img/bus.svg',
    bus: '/img/bus.svg',
    bus_pill: '/img/bus.svg',
    TRAM: '/img/tram.svg',
    CABLE_CAR: '/img/tram.svg',
    FUNICULAR: '/img/tram.svg',
    AERIAL_LIFT: '/img/tram.svg',
    AERAL_LIFT: '/img/tram.svg',
    tram: '/img/tram.svg',
    SUBWAY: '/img/subway.svg',
    METRO: '/img/subway.svg',
    subway: '/img/subway.svg',
    SUBURBAN: '/img/suburban.svg',
    suburban: '/img/suburban.svg',
};

type LucideComponent = typeof Train | typeof Ship | typeof Plane | typeof Moon | typeof CarTaxiFront | typeof Container;
const lucideMap: Record<string, LucideComponent> = {
    FERRY: Ship,
    ferry: Ship,
    AIRPLANE: Plane,
    plane: Plane,
    NIGHT_RAIL: Moon,
    taxi: CarTaxiFront,
    freightTrain: Container,
};

const svgSrc = computed((): string | null => {
    if (props.mode && svgMap[props.mode]) return svgMap[props.mode];
    if (props.product && svgMap[props.product]) return svgMap[props.product];
    return null;
});

const lucideIcon = computed((): LucideComponent | null => {
    if (props.mode && lucideMap[props.mode]) return lucideMap[props.mode];
    if (props.product && lucideMap[props.product]) return lucideMap[props.product];
    return null;
});
</script>

<template>
    <img v-if="svgSrc" :src="svgSrc" class="w-4 h-4" :alt="mode ?? product ?? ''" />
    <component :is="lucideIcon" v-else-if="lucideIcon" class="w-4 h-4" />
    <Train v-else class="w-4 h-4" />
</template>
