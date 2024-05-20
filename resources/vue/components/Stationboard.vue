<script>
import FullScreenModal from "./FullScreenModal.vue";
import ProductIcon from "./ProductIcon.vue";
import LineIndicator from "./LineIndicator.vue";
import {DateTime} from "luxon";
import CheckinLineRun from "./CheckinLineRun.vue";
import CheckinInterface from "./CheckinInterface.vue";
import StationAutocomplete from "./StationAutocomplete.vue";
import {trans} from "laravel-vue-i18n";

export default {
    components: {StationAutocomplete, CheckinInterface, CheckinLineRun, LineIndicator, ProductIcon, FullScreenModal},
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
            nextFetched: 0,
            firstFetchTime: null,
            pushState: null,
            fastCheckinIbnr: null,
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
                            this.meta = result.meta;
                            this.stationName = result.meta.station.name;

                            if (this.nextFetched === 0) {
                                this.firstFetchTime = DateTime.fromISO(this.meta?.times?.now);
                            }

                            if (this.data.length === 0 && this.nextFetched < 3) {
                                this.nextFetched++;
                                this.fetchNext();
                            } else {
                                this.nextFetched = 0;
                            }
                        });
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
            let urlParams = new URLSearchParams(window.location.search);
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
                this.fetchTime = DateTime.fromISO(urlParams.get('when')).setZone("UTC");
            }
            this.stationName = urlParams.get('stationName');
            this.trwlStationId = urlParams.get('stationId');
            this.show = false;
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
            this.fastCheckinIbnr = null;
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
                const params = new URLSearchParams(window.location.search);
                params.append('destination', value.evaIdentifier)
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
        :station="{name: stationName}"
        :time="now"
        :show-filter-button="true"
    />
    <div v-if="loading" style="max-width: 200px;" class="spinner-grow text-trwl mx-auto p-2" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <FullScreenModal ref="modal">
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
                :fastCheckinIbnr="fastCheckinIbnr"
                v-model:destination="selectedDestination"
            />
        </template>
        <template #close v-if="showCheckinInterface">
            <button type="button" class="btn-close" aria-label="Back" @click="goBackToLineRun"></button>
        </template>
        <template #body v-if="showCheckinInterface">
            <CheckinInterface :selectedTrain="selectedTrain" :selectedDestination="selectedDestination"/>
        </template>
    </FullScreenModal>

    <div class="text-center mb-2" v-if="!loading" @click="fetchPrevious">
        <button type="button" class="btn btn-primary"><i class="fa-solid fa-angle-up"></i></button>
    </div>
    <template v-if="!loading && data.length === 0">
        <div class="card mb-1 dep-card mt-3 mb-3">
            <div class="text-center my-auto">
                {{ trans("stationboard.no-departures") }}
                ({{ formatTime(this.firstFetchTime) }} - {{ formatTime(this.meta?.times?.now) }})
            </div>
        </div>
    </template>
    <template v-show="!loading" v-for="item in data" :key="item.id">
        <div class="card mb-1 dep-card" @click="showModal(item)" :class="{'past-card': isPast(item)}">
            <div class="card-body d-flex py-0">
                <div class="col-1 align-items-center d-flex justify-content-center">
                    <ProductIcon :product="item.line.product"/>
                </div>
                <div class="col-2 align-items-center d-flex me-3 justify-content-center">
                    <LineIndicator :productName="item.line.product"
                                   :number="item.line.name !== null ? item.line.name : item.line.fahrtNr"/>
                </div>
                <div class="col align-items-center d-flex second-stop">
                    <div>
                        <span class="fw-bold fs-6">{{ item.direction }}</span><br>
                        <span v-if="item.stop.name !== meta.station.name" class="text-muted small font-italic">
                        {{ trans("stationboard.dep") }} {{ item.stop.name }}
                    </span>
                    </div>
                </div>
                <div class="col-auto ms-auto align-items-center d-flex">
                    <div v-if="item.delay">
                        <span class="text-muted text-decoration-line-through">
                            {{ formatTime(item.plannedWhen) }}<br>
                        </span>
                        <span>{{ formatTime(item.when) }}</span>
                    </div>
                    <div v-else>
                        <span>{{ formatTime(item.plannedWhen) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </template>
    <div class="text-center mt-2" v-if="!loading" @click="fetchNext">
        <button type="button" class="btn btn-primary"><i class="fa-solid fa-angle-down"></i></button>
    </div>
</template>

<style scoped lang="scss">
.dep-card {
    min-height: 4.25rem;
}

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

.past-card {
    opacity: 50%;
}
</style>
