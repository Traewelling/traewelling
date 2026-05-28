<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { computed } from 'vue';

interface RideSummary {
    id: number;
    distance_km: number;
    departure: string;
    start: string;
    end: string;
    linename: string | null;
    number: string | null;
    operator: string | null;
    origin: string | null;
    destination: string | null;
}

interface AdvancedSummary {
    total_checkins: number;
    active_days: number;
    total_distance_km: number;
    mean_distance_km: number;
    longest_ride: RideSummary | null;
    shortest_ride: RideSummary | null;
}

interface PeriodData {
    period: string;
    period_type: string;
    checkin_count: number;
    distance_km: number;
}

interface AdvancedStatsData {
    summary: AdvancedSummary;
    by_period: {
        yearly: PeriodData[];
        monthly: PeriodData[];
        weekly: PeriodData[];
    };
    predefined_periods: {
        last_week: AdvancedSummary;
        last_month: AdvancedSummary;
        last_year: AdvancedSummary;
    };
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

function formatDate(dateString: string) {
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
    } catch {
        return dateString;
    }
}

function getPeriodLabel(period: string, periodType: string) {
    if (periodType === 'year') {
        return period;
    }

    if (periodType === 'month') {
        return new Date(period + '-01').toLocaleDateString(undefined, { year: 'numeric', month: 'long' });
    }

    if (periodType === 'week') {
        return `${trans('stats.week-short', {}, 'Week')} ${period.split('-W')[1]}`;
    }

    return period;
}

function averageDistance(distanceKm: number, checkins: number) {
    if (checkins <= 0) {
        return '0.00';
    }

    return (distanceKm / checkins).toFixed(2);
}
</script>

<template>
    <div v-if="hasData" class="space-y-4 mb-6">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="card bg-base-100">
                <div class="card-body py-4 items-center text-center">
                    <p class="text-xs text-base-content/60 uppercase tracking-wide">{{ trans('stats.checkins', {}, 'Check-ins') }}</p>
                    <p class="text-2xl font-bold">{{ data!.summary.total_checkins }}</p>
                </div>
            </div>
            <div class="card bg-base-100">
                <div class="card-body py-4 items-center text-center">
                    <p class="text-xs text-base-content/60 uppercase tracking-wide">{{ trans('stats.total-distance', {}, 'Total Distance') }}</p>
                    <p class="text-2xl font-bold">{{ data!.summary.total_distance_km }} km</p>
                </div>
            </div>
            <div class="card bg-base-100">
                <div class="card-body py-4 items-center text-center">
                    <p class="text-xs text-base-content/60 uppercase tracking-wide">{{ trans('stats.mean-distance', {}, 'Mean Distance') }}</p>
                    <p class="text-2xl font-bold">{{ data!.summary.mean_distance_km }} km</p>
                </div>
            </div>
            <div class="card bg-base-100">
                <div class="card-body py-4 items-center text-center">
                    <p class="text-xs text-base-content/60 uppercase tracking-wide">{{ trans('stats.travel-days', {}, 'Travel Days') }}</p>
                    <p class="text-2xl font-bold">{{ data!.summary.active_days }}</p>
                </div>
            </div>
        </div>

        <div v-if="data!.summary.longest_ride || data!.summary.shortest_ride" class="space-y-3">
            <h2 class="text-base font-semibold">{{ trans('stats.extremes', {}, 'Extremes') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div v-if="data!.summary.longest_ride" class="card bg-base-100 border border-success/30">
                    <div class="card-body py-4">
                        <h3 class="font-semibold text-success">{{ trans('stats.longest-ride', {}, 'Longest Ride') }}</h3>
                        <p class="font-semibold mt-1">{{ data!.summary.longest_ride.distance_km }} km</p>
                        <p v-if="data!.summary.longest_ride.origin || data!.summary.longest_ride.destination" class="text-sm text-base-content/70">
                            {{ data!.summary.longest_ride.origin ?? '?' }} → {{ data!.summary.longest_ride.destination ?? '?' }}
                        </p>
                        <p class="text-sm text-base-content/70">{{ formatDate(data!.summary.longest_ride.departure) }}</p>
                        <p v-if="data!.summary.longest_ride.operator" class="text-sm text-base-content/60">
                            {{ data!.summary.longest_ride.operator }}
                        </p>
                    </div>
                </div>
                <div v-if="data!.summary.shortest_ride" class="card bg-base-100 border border-warning/30">
                    <div class="card-body py-4">
                        <h3 class="font-semibold text-warning">{{ trans('stats.shortest-ride', {}, 'Shortest Ride') }}</h3>
                        <p class="font-semibold mt-1">{{ data!.summary.shortest_ride.distance_km }} km</p>
                        <p v-if="data!.summary.shortest_ride.origin || data!.summary.shortest_ride.destination" class="text-sm text-base-content/70">
                            {{ data!.summary.shortest_ride.origin ?? '?' }} → {{ data!.summary.shortest_ride.destination ?? '?' }}
                        </p>
                        <p class="text-sm text-base-content/70">{{ formatDate(data!.summary.shortest_ride.departure) }}</p>
                        <p v-if="data!.summary.shortest_ride.operator" class="text-sm text-base-content/60">
                            {{ data!.summary.shortest_ride.operator }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="data!.predefined_periods" class="space-y-3">
            <h2 class="text-base font-semibold">{{ trans('stats.time-comparison', {}, 'Time Comparison') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="card bg-base-100">
                    <div class="card-body py-4">
                        <h3 class="font-semibold">{{ trans('stats.last-week', {}, 'Last Week') }}</h3>
                        <p class="text-sm text-base-content/60 mt-2">{{ trans('stats.checkins', {}, 'Check-ins') }}</p>
                        <p class="font-semibold">{{ data!.predefined_periods.last_week.total_checkins }}</p>
                        <p class="text-sm text-base-content/60 mt-1">{{ trans('stats.distance', {}, 'Distance') }}</p>
                        <p class="font-semibold">{{ data!.predefined_periods.last_week.total_distance_km }} km</p>
                    </div>
                </div>
                <div class="card bg-base-100">
                    <div class="card-body py-4">
                        <h3 class="font-semibold">{{ trans('stats.last-month', {}, 'Last Month') }}</h3>
                        <p class="text-sm text-base-content/60 mt-2">{{ trans('stats.checkins', {}, 'Check-ins') }}</p>
                        <p class="font-semibold">{{ data!.predefined_periods.last_month.total_checkins }}</p>
                        <p class="text-sm text-base-content/60 mt-1">{{ trans('stats.distance', {}, 'Distance') }}</p>
                        <p class="font-semibold">{{ data!.predefined_periods.last_month.total_distance_km }} km</p>
                    </div>
                </div>
                <div class="card bg-base-100">
                    <div class="card-body py-4">
                        <h3 class="font-semibold">{{ trans('stats.last-year', {}, 'Last Year') }}</h3>
                        <p class="text-sm text-base-content/60 mt-2">{{ trans('stats.checkins', {}, 'Check-ins') }}</p>
                        <p class="font-semibold">{{ data!.predefined_periods.last_year.total_checkins }}</p>
                        <p class="text-sm text-base-content/60 mt-1">{{ trans('stats.distance', {}, 'Distance') }}</p>
                        <p class="font-semibold">{{ data!.predefined_periods.last_year.total_distance_km }} km</p>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="data!.by_period.yearly?.length" class="card bg-base-100">
            <div class="card-body p-0">
                <div class="px-4 py-3 border-b border-base-300">
                    <h2 class="font-semibold">{{ trans('stats.yearly-breakdown', {}, 'Yearly Breakdown') }}</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>{{ trans('stats.year', {}, 'Year') }}</th>
                                <th class="text-right">{{ trans('stats.checkins', {}, 'Check-ins') }}</th>
                                <th class="text-right">{{ trans('stats.distance', {}, 'Distance') }}</th>
                                <th class="text-right">{{ trans('stats.avg', {}, 'Average') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in data!.by_period.yearly" :key="row.period">
                                <td>{{ getPeriodLabel(row.period, row.period_type) }}</td>
                                <td class="text-right">{{ row.checkin_count }}</td>
                                <td class="text-right">{{ row.distance_km }} km</td>
                                <td class="text-right">{{ averageDistance(row.distance_km, row.checkin_count) }} km</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div v-if="data!.by_period.monthly?.length" class="card bg-base-100">
            <div class="card-body p-0">
                <div class="px-4 py-3 border-b border-base-300">
                    <h2 class="font-semibold">{{ trans('stats.monthly-breakdown', {}, 'Monthly Breakdown') }}</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>{{ trans('stats.month', {}, 'Month') }}</th>
                                <th class="text-right">{{ trans('stats.checkins', {}, 'Check-ins') }}</th>
                                <th class="text-right">{{ trans('stats.distance', {}, 'Distance') }}</th>
                                <th class="text-right">{{ trans('stats.avg', {}, 'Average') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in data!.by_period.monthly" :key="row.period">
                                <td>{{ getPeriodLabel(row.period, row.period_type) }}</td>
                                <td class="text-right">{{ row.checkin_count }}</td>
                                <td class="text-right">{{ row.distance_km }} km</td>
                                <td class="text-right">{{ averageDistance(row.distance_km, row.checkin_count) }} km</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div v-if="data!.favorites" class="grid grid-cols-1 lg:grid-cols-3 gap-3">
            <div v-if="data!.favorites.stations?.length" class="card bg-base-100">
                <div class="card-body p-0">
                    <div class="px-4 py-3 border-b border-base-300">
                        <h2 class="font-semibold">{{ trans('stats.favorite-stations', {}, 'Favourite Stations') }}</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <tbody>
                                <tr v-for="(station, i) in data!.favorites.stations" :key="station.station_id">
                                    <td class="w-12 text-base-content/50">#{{ i + 1 }}</td>
                                    <td>{{ station.name }}</td>
                                    <td class="text-right"><span class="badge badge-primary badge-sm">{{ station.count }}x</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div v-if="data!.favorites.lines?.length" class="card bg-base-100">
                <div class="card-body p-0">
                    <div class="px-4 py-3 border-b border-base-300">
                        <h2 class="font-semibold">{{ trans('stats.favorite-lines', {}, 'Favourite Lines') }}</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <tbody>
                                <tr v-for="(line, i) in data!.favorites.lines" :key="`${line.linename}-${line.number ?? i}`">
                                    <td class="w-12 text-base-content/50">#{{ i + 1 }}</td>
                                    <td>
                                        <p class="font-medium">{{ line.linename }}</p>
                                        <p class="text-xs text-base-content/60">{{ line.distance_km }} km total</p>
                                    </td>
                                    <td class="text-right"><span class="badge badge-primary badge-sm">{{ line.count }}x</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div v-if="data!.favorites.routes?.length" class="card bg-base-100">
                <div class="card-body p-0">
                    <div class="px-4 py-3 border-b border-base-300">
                        <h2 class="font-semibold">{{ trans('stats.favorite-routes', {}, 'Favourite Routes') }}</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <tbody>
                                <tr v-for="(route, i) in data!.favorites.routes" :key="`${route.origin_id}-${route.destination_id}`">
                                    <td class="w-12 text-base-content/50">#{{ i + 1 }}</td>
                                    <td>
                                        <p class="font-medium">{{ route.origin }} → {{ route.destination }}</p>
                                        <p class="text-xs text-base-content/60">{{ route.distance_km }} km total</p>
                                    </td>
                                    <td class="text-right"><span class="badge badge-primary badge-sm">{{ route.count }}x</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
