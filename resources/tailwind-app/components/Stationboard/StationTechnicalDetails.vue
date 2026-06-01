<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Info } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import type { Station, StationIdentifierResource } from '../../../types/Api.gen';

const props = defineProps<{
    station: Station;
}>();

const identifiers = computed<StationIdentifierResource[]>(() => props.station.identifiers ?? []);
const modalRef = ref<HTMLDialogElement | null>(null);
</script>

<template>
    <button
        v-if="station.id"
        class="btn btn-ghost btn-xs text-base-content/50"
        :title="trans('station.technical-details')"
        type="button"
        @click="modalRef?.showModal()"
    >
        <Info class="w-3.5 h-3.5">
            <title>{{ $t('stationboard.station-info') }}</title>
        </Info>
    </button>
    <dialog ref="modalRef" class="modal modal-bottom sm:modal-middle">
        <div class="modal-box">
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            </form>
            <h3 class="text-lg font-bold">{{ $t('stationboard.station-info') }}</h3>
            <div class="modal-action">
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
    </dialog>
</template>
