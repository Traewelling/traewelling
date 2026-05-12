<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { computed } from 'vue';

interface AdvancedSummary {
    total_checkins: number;
    active_days: number;
    total_distance_km: number;
    mean_distance_km: number;
    longest_ride: {
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
    } | null;
    shortest_ride: {
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
    } | null;
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

const hasData = computed(() => props.data !== null && props.data !== undefined);

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
    } else if (periodType === 'month') {
        return new Date(period + '-01').toLocaleDateString(undefined, { year: 'numeric', month: 'long' });
    } else if (periodType === 'week') {
        return `Week ${period.split('-W')[1]}`;
    }
    return period;
}
</script>

<template>
    <div v-if="hasData && data.summary" class="advanced-statistics">
        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-6 col-lg-3 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fa fa-list fa-2x text-trwl mb-2" />
                        <h6 class="card-title">{{ trans('stats.checkins', {}, 'Check-ins') }}</h6>
                        <p class="card-text h5">{{ data.summary.total_checkins }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fa fa-route fa-2x text-trwl mb-2" />
                        <h6 class="card-title">{{ trans('stats.total-distance', {}, 'Total Distance') }}</h6>
                        <p class="card-text h5">{{ data.summary.total_distance_km }} km</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fa fa-equals fa-2x text-trwl mb-2" />
                        <h6 class="card-title">{{ trans('stats.mean-distance', {}, 'Mean Distance') }}</h6>
                        <p class="card-text h5">{{ data.summary.mean_distance_km }} km</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fa fa-calendar-day fa-2x text-trwl mb-2" />
                        <h6 class="card-title">{{ trans('stats.travel-days', {}, 'Travel Days') }}</h6>
                        <p class="card-text h5">{{ data.summary.active_days }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Extremes Section -->
        <div class="row mb-4" v-if="data.summary.longest_ride || data.summary.shortest_ride">
            <div class="col-12">
                <h5 class="mb-3">{{ trans('stats.extremes', {}, 'Extremes') }}</h5>
            </div>
            <div v-if="data.summary.longest_ride" class="col-md-6 mb-3">
                <div class="card border-success">
                    <div class="card-body">
                        <h6 class="card-title text-success">
                            <i class="fa fa-arrow-up" /> {{ trans('stats.longest-ride', {}, 'Longest Ride') }}
                        </h6>
                        <p class="mb-1">
                            <strong>{{ data.summary.longest_ride.distance_km }} km</strong>
                            <span v-if="data.summary.longest_ride.linename" class="ms-2 text-muted">
                                · {{ data.summary.longest_ride.linename }}
                            </span>
                            <span v-else-if="data.summary.longest_ride.number" class="ms-2 text-muted">
                                · {{ data.summary.longest_ride.number }}
                            </span>
                        </p>
                        <small class="text-muted" v-if="data.summary.longest_ride.origin || data.summary.longest_ride.destination">
                            {{ data.summary.longest_ride.origin ?? '?' }} → {{ data.summary.longest_ride.destination ?? '?' }}
                        </small>
                        <br v-if="data.summary.longest_ride.origin || data.summary.longest_ride.destination" />
                        <small class="text-muted">
                            {{ formatDate(data.summary.longest_ride.departure) }}
                        </small>
                        <br />
                        <small v-if="data.summary.longest_ride.operator" class="text-muted">
                            {{ data.summary.longest_ride.operator }}
                        </small>
                    </div>
                </div>
            </div>
            <div v-if="data.summary.shortest_ride" class="col-md-6 mb-3">
                <div class="card border-warning">
                    <div class="card-body">
                        <h6 class="card-title text-warning">
                            <i class="fa fa-arrow-down" /> {{ trans('stats.shortest-ride', {}, 'Shortest Ride') }}
                        </h6>
                        <p class="mb-1">
                            <strong>{{ data.summary.shortest_ride.distance_km }} km</strong>
                            <span v-if="data.summary.shortest_ride.linename" class="ms-2 text-muted">
                                · {{ data.summary.shortest_ride.linename }}
                            </span>
                            <span v-else-if="data.summary.shortest_ride.number" class="ms-2 text-muted">
                                · {{ data.summary.shortest_ride.number }}
                            </span>
                        </p>
                        <small class="text-muted" v-if="data.summary.shortest_ride.origin || data.summary.shortest_ride.destination">
                            {{ data.summary.shortest_ride.origin ?? '?' }} → {{ data.summary.shortest_ride.destination ?? '?' }}
                        </small>
                        <br v-if="data.summary.shortest_ride.origin || data.summary.shortest_ride.destination" />
                        <small class="text-muted">
                            {{ formatDate(data.summary.shortest_ride.departure) }}
                        </small>
                        <br />
                        <small v-if="data.summary.shortest_ride.operator" class="text-muted">
                            {{ data.summary.shortest_ride.operator }}
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Predefined Periods Comparison -->
        <div class="row mb-4" v-if="data.predefined_periods">
            <div class="col-12">
                <h5 class="mb-3">{{ trans('stats.time-comparison', {}, 'Time Comparison') }}</h5>
            </div>
            <div class="col-md-4 mb-3" v-if="data.predefined_periods.last_week">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">{{ trans('stats.last-week', {}, 'Last Week') }}</h6>
                        <div class="mb-2">
                            <small class="text-muted">{{ trans('stats.checkins', {}, 'Check-ins') }}</small>
                            <p class="h6 mb-0">{{ data.predefined_periods.last_week.total_checkins }}</p>
                        </div>
                        <div>
                            <small class="text-muted">{{ trans('stats.distance', {}, 'Distance') }}</small>
                            <p class="h6 mb-0">{{ data.predefined_periods.last_week.total_distance_km }} km</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3" v-if="data.predefined_periods.last_month">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">{{ trans('stats.last-month', {}, 'Last Month') }}</h6>
                        <div class="mb-2">
                            <small class="text-muted">{{ trans('stats.checkins', {}, 'Check-ins') }}</small>
                            <p class="h6 mb-0">{{ data.predefined_periods.last_month.total_checkins }}</p>
                        </div>
                        <div>
                            <small class="text-muted">{{ trans('stats.distance', {}, 'Distance') }}</small>
                            <p class="h6 mb-0">{{ data.predefined_periods.last_month.total_distance_km }} km</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3" v-if="data.predefined_periods.last_year">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">{{ trans('stats.last-year', {}, 'Last Year') }}</h6>
                        <div class="mb-2">
                            <small class="text-muted">{{ trans('stats.checkins', {}, 'Check-ins') }}</small>
                            <p class="h6 mb-0">{{ data.predefined_periods.last_year.total_checkins }}</p>
                        </div>
                        <div>
                            <small class="text-muted">{{ trans('stats.distance', {}, 'Distance') }}</small>
                            <p class="h6 mb-0">{{ data.predefined_periods.last_year.total_distance_km }} km</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Yearly Breakdown -->
        <div class="row mb-4" v-if="data.by_period.yearly && data.by_period.yearly.length > 0">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ trans('stats.yearly-breakdown', {}, 'Yearly Breakdown') }}</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ trans('stats.year', {}, 'Year') }}</th>
                                        <th class="text-end">{{ trans('stats.checkins', {}, 'Check-ins') }}</th>
                                        <th class="text-end">{{ trans('stats.distance', {}, 'Distance') }}</th>
                                        <th class="text-end">{{ trans('stats.avg', {}, 'Average') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="row in data.by_period.yearly" :key="row.period">
                                        <td>{{ getPeriodLabel(row.period, row.period_type) }}</td>
                                        <td class="text-end">{{ row.checkin_count }}</td>
                                        <td class="text-end">{{ row.distance_km }} km</td>
                                        <td class="text-end">
                                            {{
                                                row.checkin_count > 0
                                                    ? (row.distance_km / row.checkin_count).toFixed(2)
                                                    : '0.00'
                                            }}
                                            km
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Breakdown -->
        <div class="row mb-4" v-if="data.by_period.monthly && data.by_period.monthly.length > 0">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ trans('stats.monthly-breakdown', {}, 'Monthly Breakdown') }}</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ trans('stats.month', {}, 'Month') }}</th>
                                        <th class="text-end">{{ trans('stats.checkins', {}, 'Check-ins') }}</th>
                                        <th class="text-end">{{ trans('stats.distance', {}, 'Distance') }}</th>
                                        <th class="text-end">{{ trans('stats.avg', {}, 'Average') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="row in data.by_period.monthly" :key="row.period">
                                        <td>{{ getPeriodLabel(row.period, row.period_type) }}</td>
                                        <td class="text-end">{{ row.checkin_count }}</td>
                                        <td class="text-end">{{ row.distance_km }} km</td>
                                        <td class="text-end">
                                            {{
                                                row.checkin_count > 0
                                                    ? (row.distance_km / row.checkin_count).toFixed(2)
                                                    : '0.00'
                                            }}
                                            km
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Favorites Section -->
        <div v-if="data.favorites" class="row mb-4">
            <!-- Favorite Stations -->
            <div
                v-if="data.favorites.stations && data.favorites.stations.length > 0"
                class="col-md-4 mb-4"
            >
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">{{ trans('stats.favorite-stations', {}, 'Favourite Stations') }}</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <tbody>
                                    <tr v-for="(station, i) in data.favorites.stations" :key="station.station_id">
                                        <td class="text-muted fw-semibold ps-3" style="width: 3rem">#{{ Number(i) + 1 }}</td>
                                        <td>{{ station.name }}</td>
                                        <td class="text-end pe-3">
                                            <span class="badge rounded-pill bg-primary">{{ station.count }}x</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Favorite Lines -->
            <div
                v-if="data.favorites.lines && data.favorites.lines.length > 0"
                class="col-md-4 mb-4"
            >
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">{{ trans('stats.favorite-lines', {}, 'Favourite Lines') }}</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <tbody>
                                    <tr v-for="(line, i) in data.favorites.lines" :key="`${line.linename}-${line.number ?? i}`">
                                        <td class="text-muted fw-semibold ps-3" style="width: 3rem">#{{ Number(i) + 1 }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ line.linename }}</div>
                                            <small class="text-muted">{{ line.distance_km }} km total</small>
                                        </td>
                                        <td class="text-end pe-3">
                                            <span class="badge rounded-pill bg-primary">{{ line.count }}x</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Favorite Routes -->
            <div
                v-if="data.favorites.routes && data.favorites.routes.length > 0"
                class="col-md-4 mb-4"
            >
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">{{ trans('stats.favorite-routes', {}, 'Favourite Routes') }}</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <tbody>
                                    <tr
                                        v-for="(route, i) in data.favorites.routes"
                                        :key="`${route.origin_id}-${route.destination_id}`"
                                    >
                                        <td class="text-muted fw-semibold ps-3" style="width: 3rem">#{{ Number(i) + 1 }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ route.origin }} → {{ route.destination }}</div>
                                            <small class="text-muted">{{ route.distance_km }} km total</small>
                                        </td>
                                        <td class="text-end pe-3">
                                            <span class="badge rounded-pill bg-primary">{{ route.count }}x</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped lang="scss">
.advanced-statistics {
    .text-trwl {
        color: var(--bs-primary, #0d6efd);
    }
}
</style>
