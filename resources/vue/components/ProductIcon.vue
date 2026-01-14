<script lang="ts" setup>
import { MotisCategory } from '../../types/Api.gen';
import busIcon from '../../images/transport/bus.svg';
import tramIcon from '../../images/transport/tram.svg';
import subwayIcon from '../../images/transport/subway.svg';
import suburbanIcon from '../../images/transport/suburban.svg';

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

const transportIcons: Record<string, string> = {
    bus: busIcon,
    tram: tramIcon,
    subway: subwayIcon,
    suburban: suburbanIcon,
};

const iconForProduct = (product: string) => {
    return transportIcons[product] || null;
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
            return busIcon;
        case MotisCategory.SUBWAY:
            return subwayIcon;
        case MotisCategory.TRAM:
            return tramIcon;
        case MotisCategory.SUBURBAN:
            return suburbanIcon;
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
        <img
            v-if="iconForProduct(product)"
            :alt="product"
            :src="iconForProduct(product) || ''"
            class="product-icon"
        >
        <i v-else class="fas" :class="fontAwesomeIcon(product)" />
    </template>
    <template v-else>
        <img
            v-if="iconForMode(mode)"
            :alt="mode?.toString()"
            :src="iconForMode(mode) || ''"
            class="product-icon"
        >
        <i v-else class="fas" :class="motisFontAwesomeIcon(mode)" />
    </template>
</template>
