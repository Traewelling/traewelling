<script>
import FullScreenModal from "./FullScreenModal.vue";
import ProductIcon from "./ProductIcon.vue";
import LineIndicator from "./LineIndicator.vue";
import { DateTime } from "luxon";
import CheckinLineRun from "./CheckinLineRun.vue";
import CheckinInterface from "./CheckinInterface.vue";
import StationAutocomplete from "./StationAutocomplete.vue";

export default {
    components: {StationAutocomplete, CheckinInterface, CheckinLineRun, LineIndicator, ProductIcon, FullScreenModal},
    props: {
        station: {
            type: String,
            required: true,
            default: 'Karlsruhe Hbf'
        }
    },
    data() {
        return {
            data: [],
            meta: {},
            show: false,
            selectedTrain: null,
            selectedDestination: null,
            loading: false,
            stationString: null,
        };
    },
    methods: {
        showModal(selectedItem) {
            this.selectedDestination = null;
            this.selectedTrain = selectedItem;
            this.show = true;
            this.$refs.modal.show();
        },
        updateStation(station) {
            this.stationString = station.name;
            this.data = [];
            this.fetchData();
        },
        fetchPrevious() {
            this.fetchData(this.meta.times.prev)
        },
        fetchNext() {
            this.fetchData(this.meta.times.next)
        },
        fetchData(time = null) {
            this.loading = true;
            const when = time ? `?when=${time}` : ``;
            let query = this.stationString.replace(/%2F/, ' ').replace(/\//, ' ');
            fetch(`/api/v1/trains/station/${query}/departures${when}`).then((response) => {
                response.json().then((result) => {
                    this.data = result.data;
                    this.meta = result.meta;
                    this.loading = false;
                });
            });
        },
        formatTime(time) {
            return DateTime.fromISO(time).toFormat('HH:mm');
        },
        //show divider if this.times.now is between this and the next item
        showDivider(item, key) {
            if (key === 0 || typeof this.meta.times === undefined) {
                return false;
            }
            const now = DateTime.fromISO(this.meta.times.now);
            const prev = DateTime.fromISO(this.data[key - 1].when);
            const next = DateTime.fromISO(item.when);
            return now >= prev && now <= next;
        }
    },
    mounted() {
        this.stationString = this.$props.station;
        this.fetchData();
    }
}
</script>

<template>
    <StationAutocomplete v-on:update:station="updateStation" :station="{name: stationString}"/>
    <div v-if="loading" style="max-width: 200px;" class="spinner-grow text-trwl mx-auto p-2" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <FullScreenModal ref="modal">
        <template #header v-if="selectedTrain">
            <div class="col-1 align-items-center d-flex">
                <ProductIcon :product="selectedTrain.line.product" />
            </div>
            <div class="col-auto align-items-center d-flex me-3">
                <LineIndicator :product-name="selectedTrain.line.product" :number="selectedTrain.line.name" />
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

    <div class="text-center mb-2" v-if="data.length !== 0" @click="fetchPrevious">
        <button type="button" class="btn btn-primary"><i class="fa-solid fa-angle-up"></i></button>
    </div>
    <template v-show="!loading" v-for="(item, key) in data" :key="item.id">
        <div class="card mb-1 dep-card" @click="showModal(item)">
            <div class="card-body d-flex py-0">
                <div class="col-1 align-items-center d-flex justify-content-center">
                    <ProductIcon :product="item.line.product"/>
                </div>
                <div class="col-2 align-items-center d-flex me-3 justify-content-center">
                    <LineIndicator :productName="item.line.product" :number="item.line.name"/>
                </div>
                <div class="col align-items-center d-flex second-stop">
                    <div>
                        <span class="fw-bold fs-6">{{ item.direction }}</span><br>
                        <span v-if="item.stop.name !== meta.station.name" class="text-muted small font-italic">
                        ab {{ item.stop.name }}
                    </span>
                    </div>
                </div>
                <div class="col-auto ms-auto align-items-center d-flex">
                    <div v-if="item.delay">
                        <span class="text-muted text-decoration-line-through">{{ formatTime(item.plannedWhen) }}<br></span>
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
    <div class="text-center mt-2" v-if="data.length !== 0" @click="fetchNext">
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
