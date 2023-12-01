<script>
import StationRow from "./StationRow.vue";
import {isNumber, random} from "lodash";
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
        }
    },
    methods: {
        addStopover() {
            this.form.stopovers.push('');
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
            this.form.lineName      = this.trainTypeInput + " " + this.trainNumberInput;
            this.form.journeyNumber = isNumber(this.trainNumberInput) ? this.trainNumberInput : random(1000, 9999, false);

            fetch('/api/v1/trains/trip', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(this.form),
            }).then((data) => {
                if (data.ok) {
                    data.json().then((result) => {
                        result = result.data;
                        let query = {
                            tripID: result.id,
                            lineName: result.lineName,
                            start: result.origin.ibnr,
                            departure: this.form.originDeparturePlanned,
                        };

                        console.log(`/trains/trip?${new URLSearchParams(query).toString()}`);
                        window.location.href = `/trains/trip/?${new URLSearchParams(query).toString()}`;
                    });
                }
            })
        }
    }
}
</script>

<template>
    <div>
        <h1>TripCreationForm</h1>
        <form @submit.prevent="sendform">
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
    </div>
</template>

<style scoped lang="scss">

</style>
