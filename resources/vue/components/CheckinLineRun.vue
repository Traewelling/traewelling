<script>
import { DateTime } from 'luxon';
import { trans } from 'laravel-vue-i18n';
import Spinner from './Loader/Spinner.vue';
import LoadingSkeletonRows from './Loader/LoadingSkeletonRows.vue';

export default {
    name: 'CheckinLineRun',
    components: { LoadingSkeletonRows, Spinner },
    props: {
        selectedTrain: {
            type: Object,
            required: true,
        },
        destination: {
            type: Object,
            required: false,
            default: {},
        },
        fastCheckinId: {
            type: Number,
            required: false,
        },
        useInternalIdentifiers: {
            type: Boolean,
            required: false,
            default: false,
        },
    },
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
                .then(response => {
                    this.loading = false;
                    if (!response.ok) {
                        this.error = true;
                        this.errorMessage = trans('messages.exception.hafas.502');
                    }
                    response.json().then(result => {
                        this.lineRun = result.data;
                        const givenDeparture = DateTime.fromISO(this.$props.selectedTrain.plannedWhen);
                        this.lineRun.stopovers = this.lineRun.stopovers.filter(item => {
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
                                    const identifier = this.useInternalIdentifiers
                                        ? Number(item.id)
                                        : Number(item.evaIdentifier);
                                    return Number(this.$props.selectedTrain.stop.id) !== identifier;
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
                    this.errorMessage = trans('messages.exception.hafas.502');
                });
        },
        fastCheckin() {
            let destination = null;
            if (this.useInternalIdentifiers) {
                destination = this.lineRun.stopovers.find(item => {
                    return Number(item.id) === Number(this.fastCheckinId);
                });
            } else {
                destination = this.lineRun.stopovers.find(item => {
                    return Number(item.evaIdentifier) === Number(this.fastCheckinId);
                });
            }

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

    <ul v-else class="timeline">
        <li v-for="item in lineRun.stopovers" v-if="lineRun" :key="item" @click.prevent="handleSetDestination(item)">
            <i class="trwl-bulletpoint" aria-hidden="true" />
            <span class="float-end" :class="{ 'text-trwl': !item.cancelled, 'cancelled-stop': item.cancelled }">
                <small
                    v-if="item.isArrivalDelayed || item.isDepartureDelayed"
                    :class="{ 'text-muted': !item.cancelled }"
                    class="text-decoration-line-through"
                >
                    {{ item.isArrivalDelayed ? formatTime(item.arrivalPlanned) : formatTime(item.departurePlanned) }}
                </small>
                &nbsp;
                <span>{{ formatTime(getTime(item)) }}</span>
            </span>

            <a href="#" class="clearfix" :class="{ 'text-trwl': !item.cancelled, 'cancelled-stop': item.cancelled }">{{
                item.name
            }}</a>
        </li>
    </ul>
    <div v-if="lineRun?.dataSource?.attribution" class="pt-5 pb-2">
        <span class="text-xs text-muted" v-html="lineRun.dataSource?.attribution" />
    </div>
</template>

<style scoped lang="scss">
@import '../../sass/_variables.scss';

.cancelled-stop {
    color: white !important;
    opacity: 75%;
    text-decoration-color: $red !important;
    text-decoration-thickness: 2px !important;
    text-decoration: line-through;
}
</style>
