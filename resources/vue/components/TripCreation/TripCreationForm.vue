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
            origin: {},
            destination: {},
            trainNumberInput: "",
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
            this.form.stopovers.push("");
        },
        setOrigin(item) {
            this.origin        = item;
            this.form.originId = item.ibnr;
        },
        setDeparture(time) {
            this.form.originDeparturePlanned = DateTime.fromISO(time).setZone(this.originTimezone);
        },
        setDestination(item) {
            this.destination        = item;
            this.form.destinationId = item.ibnr;
        },
        setArrival(time) {
            this.form.destinationArrivalPlanned = DateTime.fromISO(time).setZone(this.destinationTimezone);
        },
        sendform() {
            this.form.lineName = this.trainTypeInput + " " + this.trainNumberInput;

            let trainNumber         = parseInt(this.trainNumberInput);
            this.form.journeyNumber = trainNumber || null;

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
                            start: result.origin.ibnr,
                            departure: this.form.originDeparturePlanned,
                        };

                        window.location.href = `/trains/trip/?${new URLSearchParams(query).toString()}`;
                    });
                }
            });
        }
    }
}
</script>

<template>
    <div>
        <h1 class="fs-4">
            <i class="fa fa-plus" aria-hidden="true"></i>
            Create trip manually (closed-beta)
        </h1>

        <div class="alert alert-info">
            <h2 class="fs-5">
                <i class="fa fa-info-circle" aria-hidden="true"></i>
                Beta users only
            </h2>

            This form is currently for testing purposes only.
            Beta users can create a trip with manually entered data.
            All Users can check in to this trip.
            It should be tested if the trip is created correctly and all data required for the trip is present, so no
            (500) errors occur or if features are missing which are not mentioned in the limitations section.
        </div>

        <form @submit.prevent="sendform" class="mb-3">
            <div class="row g-3 mb-3">
                <StationRow
                    placeholder="Startbahnhof"
                    :arrival="false"
                    v-on:update:station="setOrigin"
                    v-on:update:timeFieldA="setDeparture"
                ></StationRow>
            </div>
            <!--
            <a href="#" @click="addStopover">Zwischenhalt hinzufügen <i class="fa fa-plus" aria-hidden="true"></i></a>
            <div class="row g-3 mt-1" v-for="test in form.stopovers" v-bind:key="test">
                <StationRow placeholder="Zwischenhalt"></StationRow>
            </div>
            -->
            <div class="row g-3 mt-1">
                <StationRow
                    placeholder="Zielbahnhof"
                    :departure="false"
                    v-on:update:station="setDestination"
                    v-on:update:timeFieldA="setArrival"
                ></StationRow>
            </div>
            <div class="row g-3 mt-1">
                <div class="col-4">
                    <input type="text" class="form-control" placeholder="Zugart (ICE, IC,...)" v-model="trainTypeInput">
                </div>
                <div class="col-4">
                    <input type="text" class="form-control" placeholder="Zugnummer" aria-label="Zugnummer"
                           v-model="trainNumberInput">
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
                <li>Only stations available in DB-HAFAS are supported</li>
                <li>Stopovers can't be created yet</li>
                <li>Polyline is generated straight from origin to destination (Brouter generation will apply if the difference between air distance and distance by train isn't too big)</li>
            </ul>
        </div>
    </div>
</template>

<style scoped lang="scss">

</style>
