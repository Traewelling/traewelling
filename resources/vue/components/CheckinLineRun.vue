<script>
import {DateTime} from "luxon";

export default {
    name: "CheckinLineRun",
    props: {
        selectedTrain: {
            type: Object,
            required: true
        },
        destination: {
            type: Object,
            required: false,
            default: {}
        },
        fastCheckinIbnr: {
            type: Number,
            required: false,
        }
    },
    watch: {
        selectedTrain() {
            this.getLineRun();
        }
    },
    data() {
        return {
            lineRun: [],
            loading: false,
        };
    },
    methods: {
        handleSetDestination(selected) {
            this.$emit('update:destination', selected);
        },
        getLineRun() {
            this.loading = true;
            const params = new URLSearchParams({
                hafasTripId: this.$props.selectedTrain.tripId,
                lineName: this.$props.selectedTrain.line.name,
                start: this.$props.selectedTrain.stop.id
            });
            fetch(`/api/v1/trains/trip?${params.toString()}`).then((response) => {
                response.json().then((result) => {
                    this.lineRun = result.data;
                    let remove = true;
                    this.lineRun.stopovers = this.lineRun.stopovers.filter((item) => {
                        if (remove && item.evaIdentifier === Number(this.$props.selectedTrain.stop.id)) {
                            remove = false;
                            return false;
                        }
                        return !remove;
                    });
                    this.loading = false;
                    if (this.$props.fastCheckinIbnr) {
                        this.fastCheckin();
                    }
                });
            });
        },
        fastCheckin() {
            const destination = this.lineRun.stopovers.find((item) => {
                return Number(item.evaIdentifier) === Number(this.fastCheckinIbnr);
            })

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
        }
    },
    mounted() {
        this.getLineRun();
    }
}
</script>

<template>
    <div v-if="loading" class="spinner-grow text-trwl mx-auto p-2" style="max-width: 200px;" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <ul class="timeline" v-else>
        <li v-for="item in lineRun.stopovers" :key="item" @click="handleSetDestination(item)">
            <i class="trwl-bulletpoint" aria-hidden="true"></i>
            <span class="text-trwl float-end">
                    <small
                        class="text-muted text-decoration-line-through"
                        v-if="item.isArrivalDelayed || item.isDepartureDelayed">
                        {{
                            item.isArrivalDelayed ? formatTime(item.arrivalPlanned) : formatTime(item.departurePlanned)
                        }}
                    </small>
                        &nbsp;
                    <span>{{ formatTime(getTime(item)) }}</span>
                </span>

            <a href="#" class="text-trwl clearfix">{{ item.name }}</a>
        </li>
    </ul>
</template>
