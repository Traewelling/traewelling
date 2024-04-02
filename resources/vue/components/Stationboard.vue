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
    props: {
        station: {
            type: String,
            required: true,
            default: "Karlsruhe Hbf"
        },
        stationId: {
            type: Number,
            required: true,
            default: 0
        }
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
            trwlStationId: null
        };
    },
    methods: {
        trans,
        showModal(selectedItem) {
            this.selectedDestination = null;
            this.selectedTrain       = selectedItem;
            this.show                = true;
            this.$refs.modal.show();
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

            let travelType = this.travelType ? this.travelType : "";

            fetch(`/api/v1/station/${this.trwlStationId}/departures?when=${time}&travelType=${travelType}`)
                .then((response) => {
                    this.loading = false;
                    this.now     = DateTime.now();
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
                        });
                    }
                });
        },
        formatTime(time) {
            return DateTime.fromISO(time).toFormat("HH:mm");
        },
        //show divider if this.times.now is between this and the next item
        showDivider(item, key) {
            if (key === 0 || typeof this.meta.times === "undefined") {
                return false;
            }
            const prev = DateTime.fromISO(this.data[key - 1].when);
            const next = DateTime.fromISO(item.when);
            return this.now >= prev && this.now <= next;
        }
    },
    mounted() {
        this.fetchTime     = DateTime.now().setZone("UTC");

        // These are needed for the communication with blade templates
        this.stationName   = this.$props.station;
        this.trwlStationId = this.$props.stationId;

        this.fetchData();
    },
    computed: {
        now() {
            return Object.hasOwn(this.meta, "times") && Object.hasOwn(this.meta.times, "now")
                ? DateTime.fromISO(this.meta.times.now).setZone("UTC")
                : DateTime.now().setZone("UTC");
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
                <LineIndicator :product-name="selectedTrain.line.product" :number="selectedTrain.line.name !== null ? selectedTrain.line.name : selectedTrain.line.fahrtNr"/>
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
        <template #body v-if="!!selectedTrain && !selectedDestination">
            <CheckinLineRun :selectedTrain="selectedTrain" v-model:destination="selectedDestination"/>
        </template>
        <template #body v-if="!!selectedDestination">
            <CheckinInterface :selectedTrain="selectedTrain" :selectedDestination="selectedDestination"/>
        </template>
    </FullScreenModal>

    <div class="text-center mb-2" v-if="!loading" @click="fetchPrevious">
        <button type="button" class="btn btn-primary"><i class="fa-solid fa-angle-up"></i></button>
    </div>
    <template v-show="!loading" v-for="(item, key) in data" :key="item.id">
        <div class="card mb-1 dep-card" @click="showModal(item)">
            <div class="card-body d-flex py-0">
                <div class="col-1 align-items-center d-flex justify-content-center">
                    <ProductIcon :product="item.line.product"/>
                </div>
                <div class="col-2 align-items-center d-flex me-3 justify-content-center">
                    <LineIndicator :productName="item.line.product" :number="item.line.name !== null ? item.line.name : item.line.fahrtNr"/>
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
                        <span class="text-muted text-decoration-line-through">{{
                                formatTime(item.plannedWhen)
                            }}<br></span>
                        <span>{{ formatTime(item.when) }}</span>
                    </div>
                    <div v-else>
                        <span>{{ formatTime(item.plannedWhen) }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="showDivider(item, key)">
            <hr>
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
</style>
