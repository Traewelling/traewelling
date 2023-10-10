<script>
import {DateTime} from "luxon";
import {Notyf} from "notyf";

export default {
    name: "CheckinInterface",
    props: {
        selectedDestination: {
            type: Object,
            required: true
        },
        selectedTrain: {
            type: Object,
            required: true
        },
    },
    data() {
        return {
            allowedChars: 280,
            statusText: "",
            toot: false,
            visibility: 0,
            business: 0,
            loading: false,
            notyf: new Notyf({position: {x: 'right', y: 'bottom'}}),
            collision: false,
        };
    },
    methods: {
        checkIn() {
            this.loading = true;
            const data = {
                body: this.statusText,
                toot: this.toot,
                visibility: this.visibility,
                business: this.business,
                ibnr: true,
                tripId: this.selectedTrain.tripId,
                lineName: this.selectedTrain.line.name,
                start: this.selectedTrain.stop.id,
                destination: this.selectedDestination.evaIdentifier,
                departure: DateTime.fromISO(this.selectedTrain.plannedWhen).setZone("UTC").toISO(),
                arrival: DateTime.fromISO(this.selectedDestination.arrivalPlanned).setZone("UTC").toISO(),
                force: this.collision,
                eventId: null,
            };
            fetch('/api/v1/trains/checkin', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data),
            }).then((response) => {
                this.loading = false;
                if (response.ok) {
                    response.json().then((result) => {
                        localStorage.setItem("points", JSON.stringify(result.data.points));
                        localStorage.setItem("alsoOnThisConnection", JSON.stringify(result.data.alsoOnThisConnection));
                        window.location = "/status/" + result.data.status.id;
                    });
                }
                if (response.status === 409) {
                    response.json().then(() => {
                        this.collision = true;
                        this.notyf.error("Du bist bereits in einem Zug eingecheckt. Falls du den Checkin forcieren möchtest, drücke nochmal auf \"einchecken\".");
                    });
                }
                if (response.status === 500) {
                    this.notyf.error("Ein unbekannter Fehler ist aufgetreten. Bitte versuche es später erneut.");
                }
            }).catch((reason) => {
                console.log(reason);
                this.loading = false;
                this.notyf.error(reason);
            });
        }
    },
    computed: {
        typed() {
            return this.allowedChars - this.statusText.length;
        },
        businessIcon() {
            switch (this.business) {
                case 0:
                    return "fa fa-user";
                case 1:
                    return "fa fa-briefcase";
                case 2:
                    return "fa fa-building";
            }
        },
        visibilityIcon() {
            switch (this.visibility) {
                case 0:
                    return "fa fa-globe-americas";
                case 1:
                    return "fa fa-lock-open";
                case 2:
                    return "fa fa-user-friends";
                case 3:
                    return "fa fa-lock";
                case 4:
                    return "fa fa-user-check";
            }
        }
    }
}
</script>

<template>
        <div class="form-outline">
            <textarea
                name="body"
                id="message-text"
                class="form-control"
                v-model="statusText"
                :maxlength="allowedChars"
                style="min-height: 130px;">
            </textarea>
            <label for="message-text" class="form-label" style="margin-left: 0px;">Status-Nachricht:</label>
        </div>
        <small class="float-end" :class="typed < 10 ? 'text-danger' : 'text-warning'" v-show="typed < 20">
            {{ typed }}
        </small>

        <div class="mt-2">
            <div class="col-auto btn-group me-1">
                <input type="checkbox" class="btn-check" autocomplete="off" v-model="toot" autocompleted="" id="toot_check" :disabled="visibility === 3">
                <label class="btn btn-sm btn-outline-mastodon" for="toot_check" style="">
                    <i class="fab fa-mastodon"></i>
                    <span class="visually-hidden-focusable">Tooten</span>
                </label>
            </div>
            <div class="col-auto btn-group me-1">
                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button"
                        id="businessDropdownButton" data-mdb-dropdown-animation="off" data-mdb-toggle="dropdown"
                        aria-expanded="false">
                    <i :class="businessIcon"></i>
                </button>
                <ul id="businessDropdown" class="dropdown-menu" aria-labelledby="businessDropdownButton">
                    <li class="dropdown-item" @click="business = 0">
                        <i class="fa fa-user"></i> Privat
                    </li>
                    <li class="dropdown-item" @click="business = 1">
                        <i class="fa fa-briefcase"></i> Geschäftlich
                        <br>
                        <span class="text-muted"> Dienstfahrten</span>
                    </li>
                    <li class="dropdown-item" @click="business = 2">
                        <i class="fa fa-building"></i> Arbeitsweg
                        <br>
                        <span class="text-muted"> Weg zwischen Wohnort und Arbeitsplatz</span>
                    </li>
                </ul>
            </div>
            <div class="col btn-group me-1">
                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button"
                        id="visibilityDropdownButton" data-mdb-dropdown-animation="off"
                        data-mdb-toggle="dropdown" aria-expanded="false" style="">
                    <i :class="visibilityIcon" aria-hidden="true"></i>
                </button>
                <ul class="dropdown-menu" aria-labelledby="visibilityDropdownButton" style="">
                    <li class="dropdown-item" @click="visibility = 0">
                        <i class="fa fa-globe-americas" aria-hidden="true"></i> Öffentlich
                        <br>
                        <span
                            class="text-muted"> Sichtbar für alle, angezeigt im Dashboard, bei Events, etc.</span>
                    </li>
                    <li class="dropdown-item" @click="visibility = 1">
                        <i class="fa fa-lock-open" aria-hidden="true"></i> Ungelistet
                        <br>
                        <span class="text-muted"> Sichtbar für alle, nur im Profil aufrufbar</span>
                    </li>
                    <li class="dropdown-item" @click="visibility = 2">
                        <i class="fa fa-user-friends" aria-hidden="true"></i> Nur für Follower
                        <br>
                        <span class="text-muted"> Nur für (akzeptierte) Follower sichtbar</span>
                    </li>
                    <li class="dropdown-item" @click="visibility = 3">
                        <i class="fa fa-lock" aria-hidden="true"></i> Privat
                        <br>
                        <span class="text-muted"> Nur für dich sichtbar</span>
                    </li>
                    <li class="dropdown-item" @click="visibility = 4">
                        <i class="fa fa-user-check" aria-hidden="true"></i> Nur angemeldete Nutzer
                        <br>
                        <span class="text-muted"> Nur für angemeldete Nutzer sichtbar</span>
                    </li>
                </ul>
            </div>
            <button class="col-auto float-end ms-auto btn btn-sm btn-outline-primary" @click="checkIn">
                <span v-if="loading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                <span v-if="loading" class="visually-hidden">Loading...</span>
                Einchecken!
            </button>
        </div>
</template>

<style scoped lang="scss">

</style>
