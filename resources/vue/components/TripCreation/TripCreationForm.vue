<script>
import StationRow from "./StationRow.vue";
import {DateTime} from "luxon";
import {trans} from "laravel-vue-i18n";

export default {
    name: "TripCreationForm",
    components: {StationRow},
    mounted() {
        this.loadOperators();
    },
    data() {
        return {
            form: {
                originId: "",
                originDeparturePlanned: "",
                destinationId: "",
                destinationArrivalPlanned: "",
                lineName: "",
                journeyNumber: 0,
                operatorId: null,
                category: "",
                stopovers: [],
            },
            originTimezone: "Europe/Berlin",
            destinationTimezone: "Europe/Berlin",
            stopovers: [],
            origin: {},
            destination: {},
            journeyNumberInput: "",
            trainTypeInput: "",
            categories: [
                {value: "nationalExpress", text: "nationalExpress"},
                {value: "national", text: "national"},
                {value: "regionalExp", text: "regionalExpress"},
                {value: "regional", text: "regional"},
                {value: "suburban", text: "suburban"},
                {value: "bus", text: "bus"},
                {value: "ferry", text: "ferry"},
                {value: "subway", text: "subway"},
                {value: "tram", text: "tram"},
                {value: "taxi", text: "taxi"},
                {value: "plane", text: "plane"},
            ],
            operators: null,
            disallowed: ["fahrrad", "auto", "fuss", "fuß", "foot", "car", "bike"],
            showDisallowed: false,
            validation: {
                times: null
            }
        };
    },
    methods: {
        trans,
        addStopover() {
            const dummyStopover = {
                station: {
                    name: "",
                    id: "",
                },
                departurePlanned: "",
                arrivalPlanned: "",
            };
            this.stopovers.push(dummyStopover);
        },
        setOrigin(item) {
            this.origin        = item;
            this.form.originId = item.id;
        },
        setDeparture(time) {
            this.form.originDeparturePlanned = DateTime.fromISO(time).setZone(this.originTimezone);
            this.validateTimes();
        },
        setDestination(item) {
            this.destination        = item;
            this.form.destinationId = item.id;
        },
        setArrival(time) {
            this.form.destinationArrivalPlanned = DateTime.fromISO(time).setZone(this.destinationTimezone);
            this.validateTimes();
        },
        validateTimes() {
            console.log("Validating times");
            //iterate over stopovers and destination, check if time is valid
            let time = DateTime.fromISO(this.form.originDeparturePlanned);

            this.stopovers.forEach((stopover) => {
                if (time > DateTime.fromISO(stopover.departurePlanned)) {
                    this.validation.times = false;
                    return false;
                }
                time = DateTime.fromISO(stopover.arrivalPlanned);
            });

            if (time > DateTime.fromISO(this.form.destinationArrivalPlanned)) {
                this.validation.times = false;
                return false;
            }
            this.validation.times = true;
            return true;
        },
        sendForm() {
            if (!this.validateTimes()) {
                notyf.error(trans("trip_creation.no-valid-times"));
                return;
            }

            this.form.lineName      = this.trainTypeInput;
            this.form.journeyNumber = !isNaN(this.journeyNumberInput) && !isNaN(parseInt(this.journeyNumberInput))
                ? parseInt(this.journeyNumberInput) : null;
            this.form.stopovers     = this.stopovers.map((stopover) => {
                return {
                    stationId: stopover.station.id,
                    departure: stopover.departurePlanned,
                    arrival: stopover.arrivalPlanned,
                };
            });

            fetch("/api/v1/trains/trip", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(this.form),
            }).then((data) => {
                if (data.ok) {
                    data.json().then((result) => {
                        result    = result.data;
                        let query = {
                            tripId: result.id,
                            lineName: result.lineName,
                            start: result.origin.id,
                            departure: this.form.originDeparturePlanned,
                            idType: 'trwl'
                        };

                        window.location.href = `/stationboard?${new URLSearchParams(query).toString()}`;
                    });
                } else if (data.status === 403 || data.status === 422) {
                    data.json().then((result) => {
                        notyf.error(result.message);
                    });
                } else {
                    notyf.error(trans("messages.exception.general-values"));
                }
            });
        },
        setStopoverStation(item, key) {
            this.stopovers[key].station = item;
        },
        setStopoverDeparture(time, key) {
            this.stopovers[key].departurePlanned = DateTime.fromISO(time).setZone(this.originTimezone);
            this.validateTimes();
        },
        setStopoverArrival(time, key) {
            this.stopovers[key].arrivalPlanned = DateTime.fromISO(time).setZone(this.destinationTimezone);
            this.validateTimes();
        },
        checkDisallowed() {
            this.showDisallowed = this.disallowed.some((disallowed) => {
                return this.trainTypeInput.toLowerCase().includes(disallowed);
            });
        },
        loadOperators() {
            fetch("/api/v1/operators", {
                method: "GET",
                headers: {
                    "Content-Type": "application/json",
                },
            }).then((data) => {
                if (data.ok) {
                    data.json().then((result) => {
                        this.operators = result.data;
                    });
                }
            });
        }
    }
}
</script>

<template>
    <div>
        <h1 class="fs-2">
            <i class="fa fa-plus" aria-hidden="true"></i>
            {{ trans("trip_creation.title") }}
        </h1>

        <div class="alert alert-info">
            <h2 class="fs-5">
                <i class="fa fa-info-circle" aria-hidden="true"></i>
                Beta
            </h2>

            {{ trans("trip_creation.beta") }}
            <br/>
            {{ trans("trip_creation.beta2") }}
            <a href="https://github.com/Traewelling/traewelling/issues/new/choose" target="_blank"
               class="float-end btn btn-sm btn-outline-danger">
                {{ trans("trip_creation.report_issue") }}
            </a>
        </div>

        <div class="card mb-3">
            <form @submit.prevent="sendForm" class="card-body">
                <div class="row g-3 mb-3">
                    <StationRow
                        :placeholder="trans('trip_creation.form.origin')"
                        :arrival="false"
                        v-on:update:station="setOrigin"
                        v-on:update:timeFieldB="setDeparture"
                    ></StationRow>
                </div>
                <a href="#" @click="addStopover">{{ trans("trip_creation.form.add_stopover") }} <i class="fa fa-plus"
                                                                                                   aria-hidden="true"></i></a>
                <div class="row g-3 mt-1" v-for="(stopover, key) in stopovers" v-bind:key="key">
                    <StationRow
                        :placeholder="trans('trip_creation.form.stopover')"
                        v-on:update:station="setStopoverStation($event, key)"
                        v-on:update:timeFieldB="setStopoverDeparture($event, key)"
                        v-on:update:timeFieldA="setStopoverArrival($event, key)"
                    ></StationRow>
                    <hr>
                </div>
                <div class="row g-3 mt-1">
                    <StationRow
                        :placeholder="trans('trip_creation.form.destination')"
                        :departure="false"
                        v-on:update:station="setDestination"
                        v-on:update:timeFieldB="setArrival"
                    ></StationRow>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-3">
                        <input type="text" class="form-control mobile-input-fs-16"
                               :placeholder="trans('trip_creation.form.line')" v-model="trainTypeInput"
                               @focusout="checkDisallowed">
                    </div>
                    <div class="col-3">
                        <input type="text" class="form-control mobile-input-fs-16"
                               :placeholder="trans('trip_creation.form.number')" v-model="journeyNumberInput">
                    </div>
                    <div class="col">
                        <select class="form-select" v-model="form.category">
                            <option selected>{{ trans("trip_creation.form.travel_type") }}</option>
                            <option v-for="category in categories" :value="category.value">{{ category.text }}</option>
                        </select>
                    </div>
                    <div class="col">
                        <select class="form-select" v-model="form.operatorId">
                            <option selected>{{ trans("trip_creation.form.operator") }}</option>
                            <option v-for="operator in operators" :value="operator.id">{{ operator.name }}</option>
                        </select>
                    </div>
                </div>
                <div class="row g-3 mt-1">
                    <span class="text-danger" v-show="showDisallowed">
                        <i class="fas fa-triangle-exclamation"></i>
                        {{ trans('trip_creation.limitations.6') }}
                        <a :href="trans('trip_creation.limitations.6.link')" target="_blank">
                            {{ trans('trip_creation.limitations.6.rules') }}
                        </a>
                    </span>
                </div>
                <div class="row justify-content-end mt-3">
                    <div class="col-12">
                        <div class="alert alert-danger" v-if="validation.times === false">
                            {{ trans("trip_creation.no-valid-times") }}
                        </div>
                    </div>
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary float-end">
                            {{ trans("trip_creation.form.save") }}
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="alert alert-warning">
            <h2 class="fs-5">
                <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                {{ trans("trip_creation.limitations") }}
            </h2>

            <ul>
                <li>{{ trans("trip_creation.limitations.1") }}</li>
                <li>
                    {{ trans("trip_creation.limitations.2") }}
                    <small>{{ trans("trip_creation.limitations.2.small") }}</small>
                </li>
                <li>{{ trans("trip_creation.limitations.3") }}</li>
                <li>{{ trans("trip_creation.limitations.4") }}</li>
                <li>{{ trans("trip_creation.limitations.5") }}</li>
            </ul>

            <p class="fw-bold text-danger">
                {{ trans("trip_creation.limitations.6") }}
                <a :href="trans('trip_creation.limitations.6.link')" target="_blank">
                    {{ trans('trip_creation.limitations.6.rules') }}
                </a>
            </p>
        </div>
    </div>
</template>
