<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Info } from 'lucide-vue-next';
import { computed } from 'vue';
import type { Station, StationIdentifierResource } from '../../../types/Api.gen';

const props = defineProps<{
    station: Station;
}>();

const identifiers = computed<StationIdentifierResource[]>(() => props.station.identifiers ?? []);
</script>

<template>
    <div v-if="station.id" class="dropdown">
        <button
            tabindex="0"
            role="button"
            class="btn btn-ghost btn-xs text-base-content/50"
            :title="trans('station.technical-details')"
            type="button"
        >
            <Info class="w-3.5 h-3.5" />
        </button>
        <div
            tabindex="0"
            class="dropdown-content bg-base-200 rounded-box z-10 p-3 shadow text-xs font-mono"
            style="width: min(20rem, calc(100vw - 2rem))"
        >
            <table class="table table-xs w-full">
                <tbody>
                    <tr>
                        <td class="text-base-content/50 pr-4 pl-0 py-0.5 whitespace-nowrap">ID</td>
                        <td class="py-0.5 select-all break-all">{{ station.id }}</td>
                    </tr>
                    <tr v-for="id in identifiers" :key="id.type + id.identifier">
                        <td class="text-base-content/50 pr-4 pl-0 py-0.5 whitespace-nowrap">{{ id.type }}</td>
                        <td class="py-0.5 select-all break-all">{{ id.identifier }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
