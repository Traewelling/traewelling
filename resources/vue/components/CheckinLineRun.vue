<script>
import { trans } from 'laravel-vue-i18n';
import { DateTime } from 'luxon';
import LoadingSkeletonRows from './Loader/LoadingSkeletonRows.vue';

export default {
    name: 'CheckinLineRun',
    components: { LoadingSkeletonRows },
    props: {
        selectedTrain: {
            type: Object,
            required: true,
        },
        destination: {
            type: Object,
            required: false,
            default: () => {},
        },
        fastCheckinId: {
            type: Number,
            required: false,
            default: null,
        },
    },
    emits: ['update:destination'],
    data() {
        return {
            lineRun: [],
            loading: false,
            error: false,
            errorMessage: '',
        };
    },
    watch: {
        selectedTrain() {
            this.getLineRun();
        },
    },
    mounted() {
        this.getLineRun();
    },
    methods: {
        handleSetDestination(selected) {
            this.$emit('update:destination', selected);
        },
        getLineRun() {
            this.error = false;
            this.loading = true;

            const params = new URLSearchParams({
                hafasTripId: this.$props.selectedTrain.tripId,
                lineName: this.$props.selectedTrain.line.name,
                start: this.$props.selectedTrain.stop.id,
            });
            fetch(`/api/v1/trains/trip?${params.toString()}`)
                .then((response) => {
                    this.loading = false;
                    if (!response.ok) {
                        this.error = true;
                        this.errorMessage = trans('messages.exception.motis.502');
                    }
                    response.json().then((result) => {
                        this.lineRun = result.data;
                        const givenDeparture = DateTime.fromISO(this.$props.selectedTrain.plannedWhen);
                        this.lineRun.stopovers = this.lineRun.stopovers.filter((item) => {
                            // Get the planned departure time
                            let departure = null;
                            if (item.arrivalPlanned) {
                                departure = DateTime.fromISO(item.arrivalPlanned);
                            } else if (item.departurePlanned) {
                                departure = DateTime.fromISO(item.departurePlanned);
                            }

                            if (departure) {
                                if (departure.toMillis() < givenDeparture.toMillis()) {
                                    return false; // Filter out past stops
                                } else if (departure.toMillis() > givenDeparture.toMillis()) {
                                    return true; // Keep future stops
                                } else if (departure.equals(givenDeparture)) {
                                    // Check if the stop is the selected train's stop at the given time
                                    return Number(this.$props.selectedTrain.stop.id) !== Number(item.id);
                                }
                            }

                            return true;
                        });
                        if (this.$props.fastCheckinId) {
                            this.fastCheckin();
                        }
                    });
                })
                .catch(() => {
                    this.error = true;
                    this.errorMessage = trans('messages.exception.motis.502');
                });
        },
        fastCheckin() {
            const destination = this.lineRun.stopovers.find((item) => {
                return Number(item.id) === Number(this.fastCheckinId);
            });

            if (destination) {
                this.handleSetDestination(destination);
            }
        },
        formatTime(time) {
            return DateTime.fromISO(time).toFormat('HH:mm');
        },
        getTime(item) {
            if (item.arrivalPlanned) {
                return item.arrivalReal ? item.arrivalReal : item.arrivalPlanned;
            }
            return item.departureReal ? item.departureReal : item.departurePlanned;
        },
    },
};
</script>

<template>
    <div v-if="error" class="text-trwl mx-auto p-2">
        <p>{{ errorMessage }}</p>
    </div>

    <LoadingSkeletonRows v-if="loading" :row-height="30" class="mt-4" :rows="10" />

    <ul v-else-if="lineRun" class="timeline">
        <li
            v-for="item in lineRun.stopovers"
            :key="item"
            :class="{ 'cancelled-row': item.cancelled }"
            @click.prevent="handleSetDestination(item)"
        >
            <i class="trwl-bulletpoint" :class="{ 'cancelled-bullet': item.cancelled }" aria-hidden="true" />
            <span class="float-end">
                <template v-if="item.cancelled">
                    <span class="cancelled-time">{{ formatTime(getTime(item)) }}</span>
                </template>
                <template v-else>
                    <small
                        v-if="item.isArrivalDelayed || item.isDepartureDelayed"
                        class="text-muted text-decoration-line-through"
                    >
                        {{
                            item.isArrivalDelayed ? formatTime(item.arrivalPlanned) : formatTime(item.departurePlanned)
                        }}
                    </small>
                    &nbsp;
                    <span class="text-trwl">{{ formatTime(getTime(item)) }}</span>
                </template>
            </span>

            <a href="#" class="clearfix text-trwl" :class="{ 'cancelled-name': item.cancelled }">
                {{ item.name }}
                <small v-if="item.cancelled" class="badge cancelled-badge ms-1">{{
                    $t('stationboard.stop-cancelled')
                }}</small>
            </a>
        </li>
    </ul>
    <div v-if="lineRun?.dataSource?.attribution" class="pt-5 pb-2">
        <!-- eslint-disable-next-line vue/no-v-html -->
        <span class="text-xs text-muted" v-html="lineRun.dataSource?.attribution" />
    </div>
</template>

<style scoped lang="scss">
@import '../../sass/_variables.scss';

.cancelled-row {
    opacity: 0.65;
    cursor: pointer;
}

.cancelled-bullet {
    background-color: $trwlRot !important;
}

.cancelled-time {
    color: $trwlRot;
    text-decoration: line-through;
    text-decoration-thickness: 2px;
}

.cancelled-name {
    text-decoration: line-through !important;
    text-decoration-color: $trwlRot !important;
    text-decoration-thickness: 2px !important;
}

.cancelled-badge {
    font-size: 0.6em;
    background-color: $trwlRot;
    color: white;
    border-radius: 3px;
    padding: 1px 5px;
    vertical-align: middle;
    text-decoration: none;
    display: inline-block;
}
</style>
