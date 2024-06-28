<script>
import {DateTime} from "luxon";
import {trans} from "laravel-vue-i18n";
import Spinner from "./Spinner.vue";

export default {
    name: "CheckinLineRun",
    components: {Spinner},
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
        fastCheckinId: {
            type: Number,
            required: false,
        },
        useInternalIdentifiers: {
            type: Boolean,
            required: false,
            default: false,
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
            error: false,
            errorMessage: ""
        };
    },
    methods: {
        handleSetDestination(selected) {
            this.$emit('update:destination', selected);
        },
        getLineRun() {
            this.error   = false;
            this.loading = true;

            const params = new URLSearchParams({
                hafasTripId: this.$props.selectedTrain.tripId,
                lineName: this.$props.selectedTrain.line.name,
                start: this.$props.selectedTrain.stop.id
            });
            fetch(`/api/v1/trains/trip?${params.toString()}`).then((response) => {
                this.loading = false;
                if (!response.ok) {
                    this.error        = true;
                    this.errorMessage = trans("messages.exception.hafas.502");
                }
                response.json().then((result) => {
                    this.lineRun           = result.data;
                    let remove             = true;
                    this.lineRun.stopovers = this.lineRun.stopovers.filter((item) => {
                        const identifier = this.useInternalIdentifiers ? item.id : item.evaIdentifier;
                        if (remove && Number(this.$props.selectedTrain.stop.id) === identifier) {
                            remove = false;
                            return false;
                        }
                        return !remove;
                    });
                    if (this.$props.fastCheckinId) {
                        this.fastCheckin();
                    }
                });
            }).catch(() => {
                this.error        = true;
                this.errorMessage = trans("messages.exception.hafas.502");
            });
        },
        fastCheckin() {
            let destination = null;
            if (this.useInternalIdentifiers) {
                destination = this.lineRun.stopovers.find((item) => {
                    return Number(item.id) === Number(this.fastCheckinId);
                });
            } else {
                destination = this.lineRun.stopovers.find((item) => {
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
        }
    },
    mounted() {
        this.getLineRun();
    }
}
</script>

<template>

    <div v-if="error" class="text-trwl mx-auto p-2">
        <p>{{ this.errorMessage }}</p>
    </div>
    <Spinner v-if="loading" />
    <ul class="timeline" v-else>
        <li v-for="item in lineRun.stopovers" :key="item" @click.prevent="handleSetDestination(item)">
            <i class="trwl-bulletpoint" aria-hidden="true"></i>
            <span class="float-end" :class="{'text-trwl': !item.cancelled, 'cancelled-stop': item.cancelled}">
                <small
                    :class="{'text-muted': !item.cancelled}"
                    class="text-decoration-line-through"
                    v-if="item.isArrivalDelayed || item.isDepartureDelayed"
                >
                    {{ item.isArrivalDelayed ? formatTime(item.arrivalPlanned) : formatTime(item.departurePlanned) }}
                </small>
                    &nbsp;
                <span>{{ formatTime(getTime(item)) }}</span>
            </span>

            <a href="#" class="clearfix"
               :class="{'text-trwl': !item.cancelled, 'cancelled-stop': item.cancelled}">{{ item.name }}</a>
        </li>
    </ul>
</template>

<style scoped lang="scss">
@import "../../sass/_variables.scss";

.cancelled-stop {
    color: white !important;
    opacity: 75%;
    text-decoration-color: $red !important;
    text-decoration-thickness: 2px !important;
    text-decoration: line-through;
}
</style>
