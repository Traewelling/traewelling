<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { computed } from 'vue';
import { StatusResource } from '../../../types/Api.gen';
import StatusCard from '../Status/StatusCard.vue';

interface AdvancedSummary {
    total_checkins: number;
    active_days: number;
    total_distance_km: number;
    mean_distance_km: number;
    longest_ride: StatusResource | null;
    shortest_ride: StatusResource | null;
}

interface AdvancedStatsData {
    summary: AdvancedSummary;
    favorites: {
        stations: { station_id: number; name: string; count: number }[];
        lines: { linename: string; number: string | null; count: number; distance_km: number }[];
        routes: {
            origin_id: number;
            origin: string;
            destination_id: number;
            destination: string;
            count: number;
            distance_km: number;
        }[];
    };
}

const props = defineProps<{
    data: AdvancedStatsData | null;
}>();

const hasData = computed(() => props.data !== null && props.data !== undefined && !!props.data.summary);
</script>

<template>
    <div v-if="hasData" class="space-y-4 mb-6">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="card bg-base-100">
                <div class="card-body py-4 items-center text-center">
                    <p class="text-xs text-base-content/60 uppercase tracking-wide">
                        {{ trans('stats.checkins') }}
                    </p>
                    <p class="text-2xl font-bold">{{ data!.summary.total_checkins }}</p>
                </div>
            </div>
            <div class="card bg-base-100">
                <div class="card-body py-4 items-center text-center">
                    <p class="text-xs text-base-content/60 uppercase tracking-wide">
                        {{ trans('stats.total-distance') }}
                    </p>
                    <p class="text-2xl font-bold">{{ data!.summary.total_distance_km }} km</p>
                </div>
            </div>
            <div class="card bg-base-100">
                <div class="card-body py-4 items-center text-center">
                    <p class="text-xs text-base-content/60 uppercase tracking-wide">
                        {{ trans('stats.mean-distance') }}
                    </p>
                    <p class="text-2xl font-bold">{{ data!.summary.mean_distance_km }} km</p>
                </div>
            </div>
            <div class="card bg-base-100">
                <div class="card-body py-4 items-center text-center">
                    <p class="text-xs text-base-content/60 uppercase tracking-wide">
                        {{ trans('stats.travel-days') }}
                    </p>
                    <p class="text-2xl font-bold">{{ data!.summary.active_days }}</p>
                </div>
            </div>
        </div>

        <div v-if="data!.summary.longest_ride || data!.summary.shortest_ride" class="space-y-3">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div v-if="data!.summary.longest_ride">
                    <p class="text-xs font-medium text-base-content/60 uppercase tracking-wide mb-1">
                        {{ trans('stats.longest-ride') }}
                    </p>
                    <StatusCard :status="data!.summary.longest_ride" :show-map="false" />
                </div>
                <div v-if="data!.summary.shortest_ride">
                    <p class="text-xs font-medium text-base-content/60 uppercase tracking-wide mb-1">
                        {{ trans('stats.shortest-ride') }}
                    </p>
                    <StatusCard :status="data!.summary.shortest_ride" :show-map="false" />
                </div>
            </div>
        </div>

        <div v-if="data!.favorites" class="grid grid-cols-1 lg:grid-cols-3 gap-3">
            <div v-if="data!.favorites.stations?.length" class="card bg-base-100">
                <div class="card-body p-0">
                    <div class="px-4 py-3 border-b border-base-300">
                        <h2 class="font-semibold">{{ trans('stats.favorite-stations') }}</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <tbody>
                                <tr v-for="(station, i) in data!.favorites.stations" :key="station.station_id">
                                    <td class="w-12 text-base-content/50">#{{ i + 1 }}</td>
                                    <td>{{ station.name }}</td>
                                    <td class="text-right">
                                        <span class="badge badge-primary badge-sm">{{ station.count }}x</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div v-if="data!.favorites.lines?.length" class="card bg-base-100">
                <div class="card-body p-0">
                    <div class="px-4 py-3 border-b border-base-300">
                        <h2 class="font-semibold">{{ trans('stats.favorite-lines') }}</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <tbody>
                                <tr
                                    v-for="(line, i) in data!.favorites.lines"
                                    :key="`${line.linename}-${line.number ?? i}`"
                                >
                                    <td class="w-12 text-base-content/50">#{{ i + 1 }}</td>
                                    <td>
                                        <p class="font-medium">{{ line.linename }}</p>
                                        <p class="text-xs text-base-content/60">{{ line.distance_km }} km total</p>
                                    </td>
                                    <td class="text-right">
                                        <span class="badge badge-primary badge-sm">{{ line.count }}x</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div v-if="data!.favorites.routes?.length" class="card bg-base-100">
                <div class="card-body p-0">
                    <div class="px-4 py-3 border-b border-base-300">
                        <h2 class="font-semibold">{{ trans('stats.favorite-routes') }}</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <tbody>
                                <tr
                                    v-for="(route, i) in data!.favorites.routes"
                                    :key="`${route.origin_id}-${route.destination_id}`"
                                >
                                    <td class="w-12 text-base-content/50">#{{ i + 1 }}</td>
                                    <td>
                                        <p class="font-medium">{{ route.origin }} → {{ route.destination }}</p>
                                        <p class="text-xs text-base-content/60">{{ route.distance_km }} km total</p>
                                    </td>
                                    <td class="text-right">
                                        <span class="badge badge-primary badge-sm">{{ route.count }}x</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
