<script lang="ts" setup>
import { MotisCategory } from '../../types/Api.gen';

defineProps({
    product: {
        type: String,
        default: '',
        required: true,
    },
    mode: {
        type: String as () => MotisCategory | null,
        default: null,
    },
});

const iconForProduct = (product: string) => {
    if (['tram', 'bus', 'subway', 'suburban'].includes(product)) {
        return `/img/${product}.svg`;
    }

    return null;
};

const fontAwesomeIcon = (product: string) => {
    switch (product) {
        case 'taxi':
            return 'fa-taxi';
        case 'plane':
            return 'fa-plane';
        case 'ferry':
            return 'fa-ship';
        default:
            return 'fa-train';
    }
};

const iconForMode = (mode: MotisCategory | null) => {
    if (!mode) return null;
    switch (mode) {
        case MotisCategory.BUS:
            return '/img/bus.svg';
        case MotisCategory.SUBWAY:
            return '/img/subway.svg';
        case MotisCategory.TRAM:
            return '/img/tram.svg';
        case MotisCategory.SUBURBAN:
            return '/img/suburban.svg';
        default:
            return null;
    }
};

const motisFontAwesomeIcon = (mode: MotisCategory) => {
    switch (mode) {
        case MotisCategory.FERRY:
            return 'fa-ship';
        case MotisCategory.COACH:
            return 'fa-bus';
        case MotisCategory.AIRPLANE:
            return 'fa-plane';
        case MotisCategory.NIGHT_RAIL:
            return 'fa-moon';
        default:
            return 'fa-train';
    }
};
</script>
<template>
    <template v-if="mode === null">
        <img v-if="iconForProduct(product)" :alt="product" :src="iconForProduct(product) || ''" class="product-icon" />
        <i v-else class="fas" :class="fontAwesomeIcon(product)" />
    </template>
    <template v-else>
        <img v-if="iconForMode(mode)" :alt="mode?.toString()" :src="iconForMode(mode) || ''" class="product-icon" />
        <i v-else class="fas" :class="motisFontAwesomeIcon(mode)" />
    </template>
</template>
