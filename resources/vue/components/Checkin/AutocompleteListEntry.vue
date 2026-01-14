<script lang="ts">
import { defineComponent } from 'vue';
import { Area, ShortStation } from '../../../types/Station';

export default defineComponent({
    name: 'AutocompleteListEntry',
    props: {
        station: {
            type: Object as () => ShortStation,
            required: false,
        },
        text: {
            type: String,
            required: false,
        },
        prefix: {
            type: String,
            required: false,
        },
    },
    methods: {
        getArea(): string {
            if (this.$props.station?.areas) {
                const defaultArea: null | Area = this.$props.station.areas.find((area: Area) => area.default) || null;
                const country: null | Area = this.$props.station.areas.find((area: Area) => area.adminLevel === 2) || null;
                if (defaultArea) {
                    return country ? `${defaultArea.name}, ${country.name}` : defaultArea.name;
                }
                if (country) {
                    return country.name;
                }
            }
            return '';
        },
    },
});
</script>

<template>
    <li class="list-group-item autocomplete-item">
        <a href="#" class="text-trwl d-flex align-items-start gap-2">
            <i v-show="prefix" :class="prefix" class="opacity-75 mt-1" />
            <div class="flex-grow-1 overflow-hidden">
                <div class="text-truncate">
                    {{ station?.name || text }}
                    <span v-if="station?.rilIdentifier" class="badge rounded-pill bg-light text-muted ms-1">
                        {{ station.rilIdentifier }}
                    </span>
                </div>
                <div v-if="getArea()" class="text-sm text-muted text-truncate">
                    {{ getArea() }}
                </div>
            </div>
        </a>
    </li>
</template>

<style scoped lang="scss">
.autocomplete-item {
  background-color: var(--bs-modal-bg) !important;
  border: none;
  border-bottom: 1px solid var(--bs-light);
}
.autocomplete-item:last-child { border-bottom: none; }
.badge { vertical-align: middle; }
</style>
