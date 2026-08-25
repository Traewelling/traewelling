<script setup lang="ts">
import { CableCar, CarTaxiFront, Container, Moon, Plane, Ship, Train, TrainFront } from '@lucide/vue';
import { computed, type Component } from 'vue';
import { HafasTravelType, MotisCategory } from '../../types/Api.gen';

const props = defineProps<{
    product?: string | null;
    mode?: string | null;
}>();

type TransportIconDefinition = { svg: string } | { icon: Component };

const svg = (name: string): TransportIconDefinition => ({ svg: `/img/${name}.svg` });
const icon = (component: Component): TransportIconDefinition => ({ icon: component });

const modeIcons: Partial<Record<MotisCategory, TransportIconDefinition>> = {
    [MotisCategory.ODM]: icon(CarTaxiFront),
    [MotisCategory.FLEX]: svg('bus'),
    [MotisCategory.TRAM]: svg('tram'),
    [MotisCategory.SUBWAY]: svg('subway'),
    [MotisCategory.FERRY]: icon(Ship),
    [MotisCategory.AIRPLANE]: icon(Plane),
    [MotisCategory.SUBURBAN]: svg('suburban'),
    [MotisCategory.BUS]: svg('bus'),
    [MotisCategory.COACH]: svg('bus'),
    [MotisCategory.RAIL]: icon(Train),
    [MotisCategory.HIGHSPEED_RAIL]: icon(TrainFront),
    [MotisCategory.LONG_DISTANCE]: icon(TrainFront),
    [MotisCategory.NIGHT_RAIL]: icon(Moon),
    [MotisCategory.REGIONAL_FAST_RAIL]: icon(Train),
    [MotisCategory.REGIONAL_RAIL]: icon(Train),
    [MotisCategory.CABLE_CAR]: icon(CableCar),
    [MotisCategory.FUNICULAR]: icon(CableCar),
    [MotisCategory.AERIAL_LIFT]: icon(CableCar),
    [MotisCategory.AERAL_LIFT]: icon(CableCar),
    [MotisCategory.METRO]: svg('suburban'),
};

/** Icon per (legacy named) category. Every category must resolve to something visible. */
const categoryIcons: Record<HafasTravelType, TransportIconDefinition> = {
    [HafasTravelType.NationalExpress]: icon(TrainFront),
    [HafasTravelType.National]: icon(TrainFront),
    [HafasTravelType.RegionalExp]: icon(Train),
    [HafasTravelType.Regional]: icon(Train),
    [HafasTravelType.Suburban]: svg('suburban'),
    [HafasTravelType.Bus]: svg('bus'),
    [HafasTravelType.Ferry]: icon(Ship),
    [HafasTravelType.Subway]: svg('subway'),
    [HafasTravelType.Tram]: svg('tram'),
    [HafasTravelType.Taxi]: icon(CarTaxiFront),
    [HafasTravelType.Plane]: icon(Plane),
    [HafasTravelType.FreightTrain]: icon(Container),
};

const lookup = (
    map: Partial<Record<string, TransportIconDefinition>>,
    key?: string | null,
): TransportIconDefinition | undefined => {
    return key ? map[key] : undefined;
};

const definition = computed((): TransportIconDefinition => {
    return lookup(modeIcons, props.mode) ?? lookup(categoryIcons, props.product) ?? icon(Train);
});

const svgSrc = computed((): string | null => ('svg' in definition.value ? definition.value.svg : null));
const iconComponent = computed((): Component | null => ('icon' in definition.value ? definition.value.icon : null));
</script>

<template>
    <img v-if="svgSrc" :src="svgSrc" class="w-4 h-4" :alt="mode ?? product ?? ''" />
    <component :is="iconComponent" v-else class="w-4 h-4" />
</template>
