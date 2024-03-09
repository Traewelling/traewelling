<script>
import FullScreenModal from "./FullScreenModal.vue";
import _ from "lodash";
import {trans} from "laravel-vue-i18n";
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'
import {DateTime} from "luxon";

export default {
    name: "StationAutocomplete",
    components: {FullScreenModal, VueDatePicker},
    props: {
        station: {
            type: Object,
            required: false
        },
        dashboard: {
            type: Boolean,
            required: false,
            default: false
        },
        time: {
            type: DateTime,
            required: false,
            default: null
        }
    },
    data() {
        return {
            recent: [],
            loading: false,
            autocompleteList: [],
            stationInput: "",
            date: null
        };
    },
    methods: {
        trans,
        showModal() {
            this.$refs.modal.show();
        },
        getRecent() {
            fetch(`/api/v1/trains/station/history`).then((response) => {
                response.json().then((result) => {
                    this.recent = result.data;
                });
            });
        },
        autocomplete() {
            this.loading = true;
            if (!this.stationInput || this.stationInput.length < 3) {
                this.autocompleteList = [];
                this.loading          = false;
                return;
            }
            let query = this.stationInput.replace(/%2F/, " ").replace(/\//, " ");
            fetch(`/api/v1/trains/station/autocomplete/${query}`).then((response) => {
                response.json().then((result) => {
                    this.autocompleteList = result.data;
                    this.loading          = false;
                });
            });
        },
        showPicker() {
            this.$refs.picker.openMenu();
        },
        setTime() {
            this.$emit("update:time", DateTime.fromJSDate(this.date).setZone('UTC').toISO());
        },
        setStationFromText() {
            this.setStation({name: this.stationInput});
        },
        setStation(item) {
            this.stationInput = item.name;
            this.$emit("update:station", item);
            this.$refs.modal.hide();
            window.location = "/trains/stationboard?station=" + item.name;
        },
        setStationFromGps() {
            if (!navigator.geolocation) {
                notyf.error(trans("stationboard.position-unavailable"));
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    window.location.href = `/trains/nearby?latitude=${position.coords.latitude}&longitude=${position.coords.longitude}`;
                },
                () => {
                    notyf.error(trans("stationboard.position-unavailable"));
                }
            );
        }
    },
    watch: {
        stationInput: _.debounce(function () {
            this.autocomplete();
        }, 500),
        station() {
            this.stationInput = this.station ? this.station.name : this.stationInput;
        }
    },
    mounted() {
        this.date         = this.time;
        this.stationInput = this.station ? this.station.name : "";
        this.getRecent();
    },
    computed: {
        placeholder() {
            return `${trans('stationboard.station-placeholder')} ${trans('or-alternative')} ${trans('ril100')}`;
        },
        dark() {
            return localStorage.getItem('darkMode') === 'dark';
        }
    }
}
</script>

<template>
    <FullScreenModal ref="modal">
        <template #header>
            <input type="text" name="station" class="form-control"
                   :placeholder="placeholder"
                   v-model="stationInput"
                   @keyup.enter="setStationFromText"
            />
        </template>
        <template #body>
            <ul class="list-group list-group-light list-group-small">
                <li class="list-group-item autocomplete-item pb-3 mb-3" v-show="autocompleteList.length === 0" @click="setStationFromGps">
                    <a href="#" class="text-trwl">
                        <i class="fa fa-map-marker-alt"></i>
                        {{ trans("stationboard.search-by-location") }}
                    </a>
                </li>
                <li class="list-group-item autocomplete-item" v-for="item in recent" v-show="autocompleteList.length === 0">
                    <a href="#" class="text-trwl" @click="setStation(item)">
                        {{ item.name }} <span v-if="item.rilIdentifier">({{ item.rilIdentifier }})</span>
                    </a>
                </li>
                <li class="list-group-item autocomplete-item" v-for="item in autocompleteList" @click="setStation(item)">
                    <a href="#" class="text-trwl">
                        {{ item.name }} <span v-if="item.rilIdentifier">({{ item.rilIdentifier }})</span>
                    </a>
                </li>
            </ul>
        </template>
    </FullScreenModal>
    <div class="card mb-4">
        <div class="card-header">{{ trans("stationboard.where-are-you") }}</div>
        <div class="card-body">
            <div id="station-autocomplete-container" style="z-index: 3;">
                <div class="input-group mb-2 mr-sm-2">
                    <input type="text" name="station" class="form-control"
                           :placeholder="placeholder"
                           v-model="stationInput"
                           @focusin="showModal"
                            @keyup.enter="setStationFromText"
                    />
                    <button type="button" class="btn btn-outline-dark stationSearchButton" @click="showPicker">
                        <i class="fa fa-clock" aria-hidden="true"></i>
                    </button>
                </div>
                <VueDatePicker
                        v-model="date"
                        ref="picker"
                        @update:model-value="setTime"
                        time-picker-inline
                        :dark="dark"
                        :action-row="{ showSelect: true, showCancel: true, showNow: true, showPreview: true }"
                >
                    <template #trigger>
                        <button type="button" class="btn btn-outline-dark stationSearchButton" hidden>
                            <i class="fa fa-calendar" aria-hidden="true"></i>
                        </button>
                    </template>
                </VueDatePicker>
            </div>
        </div>
    </div>

</template>

<style lang="scss">
    .autocomplete-item {
        background-color: var(--mdb-modal-bg) !important;
    }
</style>
