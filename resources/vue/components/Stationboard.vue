<script>
import FullScreenModal from "./FullScreenModal.vue";
import ProductIcon from "./ProductIcon.vue";
import LineIndicator from "./LineIndicator.vue";
import { DateTime } from "luxon";
import CheckinLineRun from "./CheckinLineRun.vue";

export default {
    components: {CheckinLineRun, LineIndicator, ProductIcon, FullScreenModal},
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
        };
    },
    methods: {
        showModal(selectedItem) {
            this.selectedDestination = null;
            this.selectedTrain = selectedItem;
            this.show = true;
            this.$refs.modal.show();
        },
        fetchData() {
            this.loading = true;
            fetch(`/api/v1/trains/station/${this.$props.station}/departures`).then((response) => {
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
        this.fetchData();
    }
}
</script>

<template>
    <div v-if="loading" class="spinner-grow text-trwl mx-auto p-2" role="status">
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
        <template #idk v-if="!!selectedTrain && !selectedDestination">
            <CheckinLineRun :selectedTrain="selectedTrain" v-model:destination="selectedDestination"/>
        </template>
        <template #idk v-if="!!selectedDestination">
            <form action="https://traewelling.de/trains/checkin" method="POST" id="checkinForm">
                <div class="form-outline">
                    <textarea name="body" class="form-control" id="message-text" maxlength="280"
                              style="min-height: 130px;"></textarea>
                    <label for="message-text" class="form-label" style="margin-left: 0px;">Status-Nachricht:</label>
                    <div class="form-notch">
                        <div class="form-notch-leading" style="width: 9px;"></div>
                        <div class="form-notch-middle" style="width: 97.6px;"></div>
                        <div class="form-notch-trailing"></div>
                    </div>
                </div>
                <small class="text-muted float-end"><span id="message-length">0</span>/280</small>

                <div class="mt-2">
                    <div class="btn-group">
                        <input type="checkbox" class="btn-check" id="toot_check" autocomplete="off" name="toot_check"
                               autocompleted="">
                        <label class="btn btn-sm btn-outline-mastodon" for="toot_check" style="">
                            <i class="fab fa-mastodon"></i>
                            <span class="visually-hidden-focusable">
                                                Tooten
                                            </span>
                        </label>
                    </div>
                    <div class="btn-group">
                        <input type="checkbox" class="btn-check" id="chainPost_check" autocomplete="off"
                               name="chainPost_check" autocompleted="">
                        <label class="btn btn-sm btn-outline-mastodon" for="chainPost_check" style="">
                            <i class="fa-solid fa-list-ol"></i>
                            <span class="visually-hidden-focusable">
                                                    An den letzten geposteten Checkin anhängen
                                                </span>
                        </label>
                    </div>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button"
                                id="businessDropdownButton" data-mdb-dropdown-animation="off" data-mdb-toggle="dropdown"
                                aria-expanded="false">
                            <i class="fa fa-user"></i>
                        </button>
                        <ul id="businessDropdown" class="dropdown-menu" aria-labelledby="businessDropdownButton">
                            <li class="dropdown-item trwl-business-item" data-trwl-business="0">
                                <i class="fa fa-user"></i> Privat
                            </li>
                            <li class="dropdown-item trwl-business-item" data-trwl-business="1">
                                <i class="fa fa-briefcase"></i> Geschäftlich
                                <br>
                                <span class="text-muted"> Dienstfahrten</span>
                            </li>
                            <li class="dropdown-item trwl-business-item" data-trwl-business="2">
                                <i class="fa fa-building"></i> Arbeitsweg
                                <br>
                                <span class="text-muted"> Weg zwischen Wohnort und Arbeitsplatz</span>
                            </li>
                        </ul>
                        <input type="hidden" id="business_check" name="business_check" value="0">
                    </div>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button"
                                id="visibilityDropdownButton" data-mdb-dropdown-animation="off"
                                data-mdb-toggle="dropdown" aria-expanded="false" style="">
                            <i class="fa fa-globe-americas" aria-hidden="true"></i>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="visibilityDropdownButton" style="">
                            <li class="dropdown-item trwl-visibility-item" data-trwl-visibility="0">
                                <i class="fa fa-globe-americas" aria-hidden="true"></i> Öffentlich
                                <br>
                                <span
                                    class="text-muted"> Sichtbar für alle, angezeigt im Dashboard, bei Events, etc.</span>
                            </li>
                            <li class="dropdown-item trwl-visibility-item" data-trwl-visibility="1">
                                <i class="fa fa-lock-open" aria-hidden="true"></i> Ungelistet
                                <br>
                                <span class="text-muted"> Sichtbar für alle, nur im Profil aufrufbar</span>
                            </li>
                            <li class="dropdown-item trwl-visibility-item" data-trwl-visibility="2">
                                <i class="fa fa-user-friends" aria-hidden="true"></i> Nur für Follower
                                <br>
                                <span class="text-muted"> Nur für (akzeptierte) Follower sichtbar</span>
                            </li>
                            <li class="dropdown-item trwl-visibility-item" data-trwl-visibility="3">
                                <i class="fa fa-lock" aria-hidden="true"></i> Privat
                                <br>
                                <span class="text-muted"> Nur für dich sichtbar</span>
                            </li>
                            <li class="dropdown-item trwl-visibility-item" data-trwl-visibility="4">
                                <i class="fa fa-user-check" aria-hidden="true"></i> Nur angemeldete Nutzer
                                <br>
                                <span class="text-muted"> Nur für angemeldete Nutzer sichtbar</span>
                            </li>
                        </ul>
                        <input type="hidden" id="checkinVisibility" name="checkinVisibility" value="0">
                    </div>
                </div>

                <div class="custom-control custom-checkbox mt-2">
                    <input type="checkbox" class="custom-control-input" id="event_check" name="event" value="329">
                    <label class="custom-control-label" for="event_check">
                        Unterwegs wegen: Oktoberfest 2023
                    </label>
                </div>

                <input type="hidden" id="input-tripID" name="tripID" value="1|245584|0|80|21092023">
                <input type="hidden" id="input-destination" name="destination" value="8000191">
                <input type="hidden" id="input-start" name="start" value="8003184">
                <input type="hidden" id="input-departure" name="departure" value="2023-09-21T20:39:00+02:00">
                <input type="hidden" id="input-arrival" name="arrival" value="2023-09-21 18:44:00">
                <input type="hidden" name="_token" value="gttTIdDZpbQPQii2INuIJsOz2a6NAFNevJjVSLnX"></form>
        </template>
    </FullScreenModal>
    <v-template v-for="(item, key) in data" :key="item.id">
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
    </v-template>
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
