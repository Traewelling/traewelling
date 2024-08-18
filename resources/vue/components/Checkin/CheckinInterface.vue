<script>
import {DateTime} from "luxon";
import {Notyf} from "notyf";
import {trans} from "laravel-vue-i18n";
import {useProfileSettingsStore} from "../../stores/profileSettings";
import EventDropdown from "../EventDropdown.vue";
import FriendDropdown from "../Helpers/FriendDropdown.vue";
import TagList from "../TagList.vue";
import {useActiveCheckin} from "../../stores/activeCheckin";
import {checkinSuccessStore} from "../../stores/checkinSuccess";
import {useUserStore} from "../../stores/user";

export default {
    components: {TagList, EventDropdown, FriendDropdown},
    setup() {
        const userStore = useUserStore();
        userStore.fetchSettings();
        const profileStore = useProfileSettingsStore();
        profileStore.fetchSettings();
        const activeCheckin  = useActiveCheckin();
        const checkinSuccess = checkinSuccessStore();

        return {userStore, profileStore, activeCheckin, checkinSuccess};
    },
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
        useInternalIdentifiers: {
            type: Boolean,
            required: false,
            default: false,
        }
    },
    data() {
        return {
            allowedChars: 280,
            statusText: "",
            toot: false,
            chainPost: false,
            visibility: this.profileStore.getDefaultStatusVisibility,
            business: 0,
            loading: false,
            notyf: new Notyf({position: {x: "right", y: "bottom"}}),
            collision: false,
            selectedEvent: null,
            selectedFriends: []
        };
    },
    methods: {
        trans,
        checkIn() {
            console.log(this.selectedFriends);
            console.log(this.selectedFriends.map((friend) => friend.user.id));
            this.loading = true;
            const data   = {
                body: this.statusText,
                toot: this.toot,
                chainPost: this.toot && this.chainPost,
                visibility: this.visibility,
                business: this.business,
                ibnr: !this.useInternalIdentifiers,
                tripId: this.selectedTrain.tripId,
                lineName: this.selectedTrain.line.name ?? this.selectedTrain.line.fahrtNr,
                start: this.selectedTrain.stop.id,
                destination: this.useInternalIdentifiers ? this.selectedDestination.id : this.selectedDestination.evaIdentifier,
                departure: DateTime.fromISO(this.selectedTrain.plannedWhen).setZone("UTC").toISO(),
                arrival: DateTime.fromISO(this.selectedDestination.arrivalPlanned).setZone("UTC").toISO(),
                force: this.collision,
                eventId: this.selectedEvent ? this.selectedEvent.id : null,
                with: this.selectedFriends.map((friend) => friend.user.id)
            };
            fetch("/api/v1/trains/checkin", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(data),
            }).then((response) => {
                this.loading = false;
                if (response.ok) {
                    response.json().then((result) => {
                        this.activeCheckin.reset();
                        this.checkinSuccess.setResponse(result.data);

                        this.$refs.tagList.postAllTags(result.data.status.id).then(() => {
                            window.location = "/status/" + result.data.status.id;
                        });
                    });
                } else {
                    switch (response.status) {
                        case 400:
                            response.json().then((result) => {
                                this.notyf.error(result.message);
                            });
                            break;
                        case 409:
                            response.json().then(() => {
                                this.collision = true;
                                this.notyf.error(trans("checkin.conflict") + "<br>" + trans("checkin.conflict.question"));
                            });
                            break;
                        default:
                            this.notyf.error(trans("messages.exception.general"));
                            break;
                    }
                }
            }).catch(() => {
                this.loading = false;
                this.notyf.error(trans("messages.exception.general"));
            });
        },
        selectEvent(event) {
            this.selectedEvent = event;
        },
        selectFriends(friends) {
            this.selectedFriends = friends;
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
            class="form-control mobile-input-fs-16 pt-4"
            v-model="statusText"
            :maxlength="allowedChars"
            style="min-height: 130px;">
        </textarea>
        <label for="message-text" class="form-label pt-4 ms-0">
            {{ trans("stationboard.label-message") }}
        </label>
    </div>
    <small class="float-end" :class="typed < 10 ? 'text-danger' : 'text-warning'" v-show="typed < 20">
        {{ typed }}
    </small>

    <div class="py-2 px-3 border-bottom">
        <div class="btn-group shadow-none">
            <div class="col-auto btn-group" :class="{'d-none' : profileStore.getMastodon == null}">
                <input type="checkbox" class="btn-check" autocomplete="off" v-model="toot" autocompleted=""
                       id="toot_check"
                       :disabled="visibility === 3">
                <label class="btn btn-sm px-2" for="toot_check">
                    <i class="fab fa-mastodon"></i>
                    <span class="visually-hidden-focusable">{{ trans("stationboard.check-toot") }}</span>
                </label>
            </div>
            <div class="col-auto btn-group" :class="{'d-none' : !toot}">
                <input type="checkbox" class="btn-check" autocomplete="off" v-model="chainPost" autocompleted=""
                       id="toot_chain"
                       :disabled="visibility === 3">
                <label class="btn btn-sm px-2" for="toot_chain">
                    <i class="fas fa-link"></i>
                    <span class="visually-hidden-focusable">{{ trans("stationboard.check-chainPost") }}</span>
                </label>
            </div>
            <div class="col-auto btn-group">
                <button class="btn btn-sm btn-link px-2 dropdown-toggle" type="button"
                        id="businessDropdownButton" data-mdb-dropdown-animation="off" data-mdb-toggle="dropdown"
                        aria-expanded="false">
                    <i :class="businessIcon"></i>
                </button>
                <ul id="businessDropdown" class="dropdown-menu" aria-labelledby="businessDropdownButton">
                    <li class="dropdown-item" @click="business = 0">
                        <i class="fa fa-user"></i> {{ trans("stationboard.business.private") }}
                    </li>
                    <li class="dropdown-item" @click="business = 1">
                        <i class="fa fa-briefcase"></i> {{ trans("stationboard.business.business") }}
                        <br>
                        <span class="text-muted"> {{ trans("stationboard.business.business.detail") }}</span>
                    </li>
                    <li class="dropdown-item" @click="business = 2">
                        <i class="fa fa-building"></i> {{ trans("stationboard.business.commute") }}
                        <br>
                        <span class="text-muted"> {{ trans("stationboard.business.commute.detail") }}</span>
                    </li>
                </ul>
            </div>
            <div class="col btn-group">
                <button class="btn btn-sm btn-link px-2 dropdown-toggle" type="button"
                        id="visibilityDropdownButton" data-mdb-dropdown-animation="off"
                        data-mdb-toggle="dropdown" aria-expanded="false">
                    <i :class="visibilityIcon" aria-hidden="true"></i>
                </button>
                <ul class="dropdown-menu" aria-labelledby="visibilityDropdownButton">
                    <li class="dropdown-item" @click="visibility = 0">
                        <i class="fa fa-globe-americas" aria-hidden="true"></i> {{ trans("status.visibility.0") }}
                        <br>
                        <span
                            class="text-muted"> {{ trans("status.visibility.0.detail") }}</span>
                    </li>
                    <li class="dropdown-item" @click="visibility = 1">
                        <i class="fa fa-lock-open" aria-hidden="true"></i> {{ trans("status.visibility.1") }}
                        <br>
                        <span class="text-muted"> {{ trans("status.visibility.1.detail") }}</span>
                    </li>
                    <li class="dropdown-item" @click="visibility = 2">
                        <i class="fa fa-user-friends" aria-hidden="true"></i> {{ trans("status.visibility.2") }}
                        <br>
                        <span class="text-muted"> {{ trans("status.visibility.2.detail") }}</span>
                    </li>
                    <li class="dropdown-item" @click="visibility = 3">
                        <i class="fa fa-lock" aria-hidden="true"></i> {{ trans("status.visibility.3") }}
                        <br>
                        <span class="text-muted"> {{ trans("status.visibility.3.detail") }}</span>
                    </li>
                    <li class="dropdown-item" @click="visibility = 4">
                        <i class="fa fa-user-check" aria-hidden="true"></i> {{ trans("status.visibility.4") }}
                        <br>
                        <span class="text-muted"> {{ trans("status.visibility.4.detail") }}</span>
                    </li>
                </ul>
            </div>
            <EventDropdown @select-event="selectEvent"/>
            <FriendDropdown v-if="userStore.hasBeta" @select-user="selectFriends"/>
        </div>
        <button class="col-auto float-end ms-auto btn btn-sm btn-outline-primary" @click="checkIn">
            <span v-if="loading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            <span v-if="loading" class="visually-hidden">Loading...</span>
            {{ trans("stationboard.btn-checkin") }}
        </button>
    </div>

    <div class="mt-5 mx-3 mb-4 border-top pt-3">
        <TagList ref="tagList" :cache-locally="true"/>
    </div>
</template>
