<script>
import StationRow from "./StationRow.vue";
import {DateTime} from "luxon";

export default {
    name: "TripCreationForm",
    components: {StationRow},
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
            ],
        };
    },
    methods: {
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
        },
        setDestination(item) {
            this.destination        = item;
            this.form.destinationId = item.id;
        },
        setArrival(time) {
            this.form.destinationArrivalPlanned = DateTime.fromISO(time).setZone(this.destinationTimezone);
        },
        sendForm() {
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
                            tripID: result.id,
                            lineName: result.lineName,
                            start: result.origin.id,
                            departure: this.form.originDeparturePlanned,
                        };

                        window.location.href = `/trains/trip/?${new URLSearchParams(query).toString()}`;
                    });
                }
                if (data.status === 422) {
                    data.json().then((result) => {
                        alert(result.message);
                    });
                }
            });
        },
        setStopoverStation(item, key) {
            this.stopovers[key].station = item;
        },
        setStopoverDeparture(time, key) {
            this.stopovers[key].departurePlanned = DateTime.fromISO(time).setZone(this.originTimezone);
        },
        setStopoverArrival(time, key) {
            this.stopovers[key].arrivalPlanned = DateTime.fromISO(time).setZone(this.destinationTimezone);
        },
    }
}
</script>

<template>
    <div>
        <h1 class="fs-4">
            <i class="fa fa-plus" aria-hidden="true"></i>
            Create trip manually
        </h1>

        <div class="alert alert-info">
            <h2 class="fs-5">
                <i class="fa fa-info-circle" aria-hidden="true"></i>
                Beta
            </h2>

            Please note, that the creation of trips is currently in beta.
            You can create trips manually by entering the stations and times.
            There are some limitations, which are listed below.
            <br/>
            If you encounter any issues, please report them to us.
            <a href="https://github.com/Traewelling/traewelling/issues/new/choose" target="_blank" class="float-end btn btn-sm btn-outline-danger">
                Report issue
            </a>

        </div>

        <form @submit.prevent="sendForm" class="mb-3">
            <div class="row g-3 mb-3">
                <StationRow
                    placeholder="Startbahnhof"
                    :arrival="false"
                    v-on:update:station="setOrigin"
                    v-on:update:timeFieldB="setDeparture"
                ></StationRow>
            </div>
            <a href="#" @click="addStopover">Zwischenhalt hinzufügen <i class="fa fa-plus" aria-hidden="true"></i></a>
            <div class="row g-3 mt-1" v-for="(stopover, key) in stopovers" v-bind:key="key">
                <StationRow
                    placeholder="Zwischenhalt"
                    v-on:update:station="setStopoverStation($event, key)"
                    v-on:update:timeFieldB="setStopoverDeparture($event, key)"
                    v-on:update:timeFieldA="setStopoverArrival($event, key)"
                ></StationRow>
                <hr>
            </div>
            <div class="row g-3 mt-1">
                <StationRow
                    placeholder="Zielbahnhof"
                    :departure="false"
                    v-on:update:station="setDestination"
                    v-on:update:timeFieldB="setArrival"
                ></StationRow>
            </div>
            <div class="row g-3 mt-1">
                <div class="col-4">
                    <input type="text" class="form-control" placeholder="Linie (S1, ICE 13, ...)"
                           v-model="trainTypeInput">
                </div>
                <div class="col-4">
                    <input type="text" class="form-control" placeholder="Nummer (optional)" aria-label="Fahrtnummer"
                           v-model="journeyNumberInput">
                </div>
                <div class="col">
                    <select class="form-select" aria-label="Default select example" v-model="form.category">
                        <option selected>Kategorie</option>
                        <option v-for="category in categories" :value="category.value">{{ category.text }}</option>
                    </select>
                </div>
            </div>
            <div class="row justify-content-end mt-3">
                <div class="col-4">
                    <button type="submit" class="btn btn-primary float-end">Speichern</button>
                </div>
            </div>
        </form>

        <div class="alert alert-warning">
            <h2 class="fs-5">
                <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                Current limitations
            </h2>

            <ul>
                <li>Only stations available in the time table of DB Navigator are supported</li>
                <li>
                    The map lines are generated straight from the given stations
                    <small>(we try to find a route via Brouter, but this can be result in wrong data)</small>
                </li>
                <li>The trip is generally created public - so if you checkin to a trip, everyone who can see your status can ride with you.</li>
                <li>The operator can't be set in this form (possible via API)</li>
                <li>There are no visible error messages for this form. So, if nothing happens on submit... sorry. There is an error.</li>
            </ul>
        </div>
    </div>
</template>
