<template>
    <div>
        <div class="card">
            <div class="card-header">{{ i18n.get('_.stationboard.where-are-you') }}
                <span class="float-end">
                    <a :title="i18n.get('_.stationboard.search-by-location')" class="text-trwl" href="#"
                       @click.prevent="getGeoLocation">
                        <i aria-hidden="true" class="fa fa-map-marker-alt"></i>
                    </a>
                </span>
            </div>
            <div class="card-body">
                <form autocomplete="off" v-on:submit.prevent="submitStation">
                    <div id="station-autocomplete-container">
                        <input id="autoComplete" v-model="station" class="form-control w-100 mb-3" type="text"/>
                    </div>
                    <div class="btn-group float-end">
                        <router-link v-if="$auth.user().home"
                                     :to="{name: 'trains.stationboard', query: {station: $auth.user().home.name }}"
                                     class="btn btn-outline-primary">
                            <i aria-hidden="true" class="fa fa-home mr-2"></i>
                        </router-link>
                        <button v-if="history.length > 0" class="btn btn-outline-primary"
                                data-mdb-target="#collapseHistory"
                                data-mdb-toggle="collapse">
                            <i aria-hidden="true" class="fa fa-history"></i>
                        </button>
                        <button class="btn btn-primary float-end" type="submit"
                                v-on:click.prevent="submitStation('')">
                            {{ i18n.get('_.stationboard.submit-search') }}
                            <span class="sr-only-focusable">{{ i18n.get('_.stationboard.last-stations') }}</span>
                        </button>
                    </div>
                    <button aria-expanded="false" class="btn btn-outline-secondary" data-mdb-target="#collapseFilter"
                            data-mdb-toggle="collapse" type="button">
                        {{ i18n.get('_.stationboard.filter-products') }}
                    </button>
                    <div id="collapseFilter" class="collapse">
                        <div class="mt-3 d-flex justify-content-center">
                            <div class="btn-group flex-wrap" role="group">
                                <button class="btn btn-primary btn-sm" v-on:click.prevent="submitStation('ferry')">
                                    {{ i18n.get('_.transport_types.ferry') }}
                                </button>
                                <button class="btn btn-primary btn-sm" v-on:click.prevent="submitStation('bus')">
                                    {{ i18n.get('_.transport_types.bus') }}
                                </button>
                                <button class="btn btn-primary btn-sm" v-on:click.prevent="submitStation('tram')">
                                    {{ i18n.get('_.transport_types.tram') }}
                                </button>
                                <button class="btn btn-primary btn-sm" v-on:click.prevent="submitStation('subway')">
                                    {{ i18n.get('_.transport_types.subway') }}
                                </button>
                                <button class="btn btn-primary btn-sm" v-on:click.prevent="submitStation('suburban')">
                                    {{ i18n.get('_.transport_types.suburban') }}
                                </button>
                                <button class="btn btn-primary btn-sm" v-on:click.prevent="submitStation('regional')">
                                    {{ i18n.get('_.transport_types.regional') }}
                                </button>
                                <button class="btn btn-primary btn-sm" v-on:click.prevent="submitStation('express')">
                                    {{ i18n.get('_.transport_types.express') }}
                                </button>
                            </div>
                        </div>
                    </div>
                    <div id="collapseHistory" class="collapse mt-2">
                        <span class="list-group-item title list-group-item-action disabled">
                            {{ i18n.get("_.stationboard.last-stations") }}
                        </span>
                        <router-link v-for="station in history"
                                     v-bind:key="station.id"
                                     :to="{name: 'trains.stationboard', query: {station: station.name }}"
                                     active-class=""
                                     class="list-group-item list-group-item-action">
                            {{ station.name }}
                        </router-link>
                    </div>
                </form>
            </div>
        </div>

        <div v-if="now != null && !hideTimepicker" id="timepicker-wrapper">
            <div class="text-center">
                <div class="btn-group" role="group">
                    <a :title="i18n.get('_.stationboard.minus-15')" class="btn btn-light" data-mdb-target="tooltip"
                       href="#"
                       @click.prevent="submitStation(currentTravelType, prev)">
                        <i aria-hidden="true" class="fas fa-arrow-circle-left"></i>
                    </a>
                    <a :title="i18n.get('_.stationboard.dt-picker')" class="btn btn-light btn-rounded"
                       data-mdb-target="#collapseDateTime"
                       data-mdb-toggle="collapse" href="#">
                        <i aria-hidden="true" class="fas fa-clock"></i>
                    </a>
                    <a :title="i18n.get('_.stationboard.plus-15')" class="btn btn-light" data-mdb-target="tooltip"
                       href="#"
                       @click.prevent="submitStation(currentTravelType, next)">
                        <i aria-hidden="true" class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="text-center mt-4">
                <div id="collapseDateTime" class="input-group mb-3 mx-auto collapse">
                    <input id="timepicker" v-model="now" aria-describedby="button-addontime"
                           class="form-control" type="datetime-local"/>
                    <button id="button-addontime" class="btn btn-outline-primary" data-mdb-target="#collapseDateTime"
                            data-mdb-toggle="collapse"
                            type="submit"
                            @click.prevent="submitStation(currentTravelType, now)">
                        {{ i18n.get('_.stationboard.set-time') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import moment from "moment";
import axios from "axios";
import autoComplete from "@tarekraafat/autocomplete.js";
import Checkin from "../js/ApiClient/Checkin";

export default {
    name: "StationForm",
    inject: ["notyf"],
    data() {
        return {
            station: null,
            errors: null,
            loading: false,
            history: [],
        };
    },
    props: {
        now: null,
        next: null,
        prev: null,
        hideTimepicker: false
    },
    computed: {
        currentTravelType() {
            if (this.$route.query.travelType) {
                return this.$route.query.travelType;
            }
            return null;
        },
        when() {
            if (this.$route.query.when) {
                return this.$route.query.when;
            }
            return moment().toISOString();
        }
    },
    mounted() {
        this.station          = this.$route.query.station;
        const popularStations = [
            {name: "Hamburg Hbf"}, {name: "Berlin Hbf"}, {name: "München Hbf"}, {name: "Köln Hbf"}, {name: "Frankfurt(Main)Hbf"}, {name: "Stuttgart Hbf"},
            {name: "Düsseldorf Hbf"}, {name: "Leipzig Hbf"}, {name: "Dortmund Hbf"}, {name: "Essen Hbf"}, {name: "Bremen Hbf"}, {name: "Dresden Hbf"},
            {name: "Hannover Hbf"}, {name: "Nürnberg Hbf"}, {name: "Duisburg Hbf"}, {name: "Bochum Hbf"}, {name: "Wuppertal Hbf"}, {name: "Bielefeld Hbf"},
            {name: "Bonn Hbf"}, {name: "Münster Hbf"}, {name: "Karlsruhe Hbf"}, {name: "Mannheim Hbf"}, {name: "Augsburg Hbf"}, {name: "Wiesbaden Hbf"},
            {name: "Mönchengladbach Hbf"}
        ];
        const autoCompleteJS  = new autoComplete({
            selector: "#autoComplete",
            debounce: 300,
            threshold: 2,
            placeHolder: this.i18n.get('_.stationboard.station-placeholder') + " / DS100", events: {
                input: {
                    selection: (event) => {
                        const selection            = event.detail.selection.value;
                        autoCompleteJS.input.value = selection.name;
                        this.station               = selection.name;
                        this.submitStation('');
                    }
                }
            },
            data: {
                src: async (query) => {
                    if (query.length < 3) {
                        return popularStations;
                    }
                    try {
                        const source = await axios.get(`/trains/station/autocomplete/${query.replace("/", " ")}`);
                        return await source.data.data;
                    } catch (error) {
                        this.apiErrorHandler(error);
                    }
                },
                keys: ['name'],
            },
            resultsList: {
                class: "mt-0",
                element: (list, data) => {
                    if (!data.results.length) {
                        const message = document.createElement("div");
                        message.setAttribute("class", "no_result");
                        message.innerHTML = `<span>${this.i18n.get("_.controller.transport.no-station-found")}</span>`;
                        list.prepend(message);
                    }
                },
                maxResults: 8,
                noResults: true,
            },
            resultItem: {
                highlight: "p-0"
            }
        });
        this.fetchData();
    },
    methods: {
        fetchData() {
            Checkin
                .getHistory()
                .then((data) => {
                    this.history = data;
                })
                .catch((error) => {
                    this.apiErrorHandler(error);
                });
        },
        submitStation(travelType = null, time = this.when) {
            if (typeof travelType != "string") {
                travelType = this.currentTravelType;
            }

            if (this.station != null) {
                this.$router.push({
                    name: "trains.stationboard",
                    query: {station: this.station, travelType: travelType, when: time}
                })
                    .then(() => {
                        this.$emit("refresh");
                    })
                    .catch(() => {
                        this.$emit("refresh");
                    });
            } else {
                console.error("station null");
            }
        },
        fetchNearbyStation(position) {
            Checkin
                .getNearbyStations(position.coords.latitude, position.coords.longitude)
                .then((data) => {
                    this.station = data.name;
                    this.submitStation();
                })
                .catch((error) => {
                    this.apiErrorHandler(error);
                });
        },
        getGeoLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition((position) => {
                    this.fetchNearbyStation(position);
                }, () => {
                    this.notyf.error(this.i18n.get("_.stationboard.position-unavailable"));
                });
            }
        }
    }
};
</script>

<style scoped>

</style>
