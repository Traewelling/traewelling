<script>
import FullScreenModal from "./FullScreenModal.vue";
import ProductIcon from "./ProductIcon.vue";
import LineIndicator from "./LineIndicator.vue";
import {DateTime} from "luxon";
import CheckinLineRun from "./CheckinLineRun.vue";
import CheckinInterface from "./Checkin/CheckinInterface.vue";
import StationAutocomplete from "./StationAutocomplete/StationAutocomplete.vue";
import {trans} from "laravel-vue-i18n";
import StationBoardEntry from "./Checkin/StationBoardEntry.vue";
import Spinner from "./Spinner.vue";

export default {
    components: {
        Spinner,
        StationBoardEntry,
        StationAutocomplete, CheckinInterface, CheckinLineRun, LineIndicator, ProductIcon, FullScreenModal
    },
    data() {
        return {
            data: [],
            meta: {},
            travelType: null,
            fetchTime: null,
            show: false,
            selectedTrain: null,
            selectedDestination: null,
            loading: false,
            stationName: null,
            trwlStationId: null,
            firstFetchTime: null,
            pushState: null,
            fastCheckinIbnr: null,
            useInternalIdentifiers: false,
        };
    },
    methods: {
        trans,
        showModal(selectedItem) {
            this.selectedDestination = null;
            this.selectedTrain       = selectedItem;
            this.show                = true;
            this.$refs.modal.show();

            const data = new URLSearchParams({
                tripId: selectedItem.tripId,
                lineName: selectedItem.line.name,
                start: this.meta.station.ibnr,
                departure: selectedItem.when,
            });

            this.pushHistory(data);
        },
        updateStation(station) {
            this.stationName   = station.name;
            this.trwlStationId = station.id;
            this.data          = [];
            this.fetchData();
        },
        updateTravelType(travelType) {
            this.travelType = travelType;
            this.data       = [];
            this.fetchData();
        },
        updateTime(time) {
            this.fetchData(time);
        },
        fetchPrevious() {
            this.fetchData(
                this.meta?.times?.prev ? this.meta.times.prev : this.fetchTime.minus({minutes: 15}).toString(),
            );
        },
        fetchNext() {
            this.fetchData(
                this.meta?.times?.next ? this.meta.times.next : this.fetchTime.plus({minutes: 15}).toString(),
            );
        },
        fetchData(time = null, appendPosition = 0) {
            this.loading = true;
            if (time !== null) {
                this.fetchTime = DateTime.fromISO(time).setZone("UTC");
            } else {
                time = this.fetchTime.minus({minutes: 5}).toString();
            }
            if (this.trwlStationId === null) {
                return;
            }

            let travelType = this.travelType ? this.travelType : "";

            this.pushHistory(new URLSearchParams({
                stationId: this.trwlStationId,
                stationName: this.stationName,
                when: time,
                travelType: travelType
            }))

            fetch(`/api/v1/station/${this.trwlStationId}/departures?when=${time}&travelType=${travelType}`)
                .then((response) => {
                    this.loading = false;
                    if (response.ok) {
                        response.json().then((result) => {
                            if (appendPosition === 0) {
                                this.data = result.data;
                            } else if (appendPosition === 1) {
                                this.data = this.data.concat(result.data);
                            } else {
                                this.data = result.data.concat(this.data);
                            }
                            this.meta        = result.meta;
                            this.stationName = result.meta.station.name;

                            this.firstFetchTime = DateTime.fromISO(this.meta?.times?.now);
                        });
                    } else {
                        if (response.status === 502) {
                            window.notyf.error(trans("messages.exception.hafas.502"))
                        }
                    }
                });
        },
        formatTime(time) {
            return DateTime.fromISO(time).toFormat("HH:mm");
        },
        isPast(item) {
            return DateTime.fromISO(item.when) < DateTime.now();
        },
        async analyzeUrlParams() {
            let urlParams  = new URLSearchParams(window.location.search);
            this.fetchTime = DateTime.now().setZone("UTC");

            if (urlParams.has('tripId')) {
                if (!urlParams.has('destination')) {
                    this.selectedDestination = null;
                }
                this.selectedTrain = {
                    tripId: urlParams.get('tripId'),
                    line: {
                        name: urlParams.get('lineName')
                    },
                    stop: {
                        id: urlParams.get('start')
                    },
                    plannedWhen: urlParams.get('departure'),
                };
                if (urlParams.has('destination')) {
                    this.fastCheckinIbnr = urlParams.get('destination');
                }
                if (urlParams.has('idType')) {
                    //ToDo change this form to use trwl-id per default and use db-ibnr for hafas-related-input
                    this.useInternalIdentifiers = urlParams.get('idType') === 'trwl';
                }
                this.show = true;
                this.$refs?.modal?.show();
                return new Promise((resolve) => {
                    resolve();
                });
            }

            if (!urlParams.has('stationId')) {
                window.notyf.error("No station found!");
            }
            if (urlParams.has('when')) {
                const fetchTime = DateTime.fromISO(urlParams.get('when')).setZone("UTC");
                this.fetchTime  = fetchTime.isValid ? fetchTime : this.fetchTime;
            }
            this.stationName   = urlParams.get('stationName');
            this.trwlStationId = urlParams.get('stationId');
            this.show          = false;
            this.$refs.modal.hide();
            return new Promise((resolve) => {
                resolve();
            });
        },
        popstateListener() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.toString() !== this.pushState.toString()) {
                this.analyzeUrlParams().then(() => {
                    if (!this.selectedTrain) {
                        this.fetchData();
                    }
                });
            }
        },
        pushHistory(data) {
            this.pushState = data;
            window.history.pushState({}, "", `?${data.toString()}`);
        },
        goBackToLineRun() {
            this.selectedDestination = null;
            this.fastCheckinIbnr     = null;
        }
    },
    mounted() {
        this.analyzeUrlParams().then(() => {
            if (!this.selectedTrain) {
                this.fetchData();
            }
        });

        window.addEventListener('popstate', () => {
            this.popstateListener();
        });
    },
    watch: {
        selectedDestination(value) {
            if (value === null) {
                window.history.back();
            } else {
                const params      = new URLSearchParams(window.location.search);
                const destination = this.useInternalIdentifiers ? value.id : value.evaIdentifier;
                params.set('destination', destination)
                this.pushHistory(params);
            }
        }
    },
    computed: {
        now() {
            return Object.hasOwn(this.meta, "times") && Object.hasOwn(this.meta.times, "now")
                ? DateTime.fromISO(this.meta.times.now).setZone("UTC")
                : DateTime.now().setZone("UTC");
        },
        showLineRun() {
            return !!this.selectedTrain && !this.selectedDestination;
        },
        showCheckinInterface() {
            return !!this.selectedDestination;
        }
    }
}
</script>

<template>
    <StationAutocomplete
        v-on:update:time="updateTime"
        v-on:update:station="updateStation"
        v-on:update:travel-type="updateTravelType"
        :stationName="stationName"
        :station="meta?.station"
        :time="now"
        :show-filter-button="true"
    />
    <Spinner v-if="loading"/>
    <FullScreenModal ref="modal" :body-class="{'p-0': showCheckinInterface}">
        <template #header v-if="selectedTrain">
            <div class="col-1 align-items-center d-flex">
                <ProductIcon :product="selectedTrain.line.product"/>
            </div>
            <div class="col-auto align-items-center d-flex me-3">
                <LineIndicator :product-name="selectedTrain.line.product"
                               :number="selectedTrain.line.name !== null ? selectedTrain.line.name : selectedTrain.line.fahrtNr"/>
            </div>
            <template v-if="selectedDestination">
                <div class="col-auto align-items-center d-flex me-3">
                    <i class="fas fa-arrow-alt-circle-right"></i>
                </div>
                <div class="col-auto align-items-center d-flex me-3">
                    {{ selectedDestination.name }}
                </div>
            </template>
        </template>
        <template #body v-if="showLineRun">
            <CheckinLineRun
                :selectedTrain="selectedTrain"
                :fastCheckinId="fastCheckinIbnr"
                :useInternalIdentifiers="useInternalIdentifiers"
                v-model:destination="selectedDestination"
            />
        </template>
        <template #close v-if="showCheckinInterface">
            <button type="button" class="btn-close" aria-label="Back" @click="goBackToLineRun"></button>
        </template>
        <template #body v-if="showCheckinInterface">
            <CheckinInterface
                :selectedTrain="selectedTrain"
                :selectedDestination="selectedDestination"
                :useInternalIdentifiers="useInternalIdentifiers"
            />
        </template>
    </FullScreenModal>

    <div class="text-center mb-2" v-if="!loading" @click="fetchPrevious">
        <button type="button" class="btn btn-primary"><i class="fa-solid fa-angle-up"></i></button>
    </div>
    <template v-if="!loading && data.length === 0">
        <div class="card mb-1 dep-card mt-3 mb-3">
            <div class="text-center my-auto">
                {{ trans("stationboard.no-departures") }}
                <span v-if="firstFetchTime">
                    ({{ formatTime(this.firstFetchTime) }} - {{ formatTime(this.meta?.times?.now) }})
                </span>
            </div>
        </div>
    </template>
    <StationBoardEntry
        v-show="!loading"
        v-for="item in data"
        :key="item.id"
        @click="showModal(item)"
        :item="item"
        :station="meta.station"
    />
    <div class="text-center mt-2" v-if="!loading" @click="fetchNext">
        <button type="button" class="btn btn-primary"><i class="fa-solid fa-angle-down"></i></button>
    </div>
</template>

<style scoped lang="scss">
.product-icon {
    width: 1.25rem;
    height: 1.25rem;
}

.timeline {
    margin-left: -1rem;
}

.second-stop {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>
