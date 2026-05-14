<script>
import { trans } from 'laravel-vue-i18n';
import { DateTime } from 'luxon';
import { Notyf } from 'notyf';
import { useActiveCheckin } from '../../stores/activeCheckin';
import { checkinSuccessStore } from '../../stores/checkinSuccess';
import { useProfileSettingsStore } from '../../stores/profileSettings';
import { useUserStore } from '../../stores/user';
import BusinessDropdown from '../BusinessDropdown.vue';
import EventDropdown from '../EventDropdown.vue';
import FriendDropdown from '../Helpers/FriendDropdown.vue';
import TagList from '../TagList.vue';
import VisibilityDropdown from '../VisibilityDropdown.vue';

export default {
    name: 'CheckinInterface',
    components: { VisibilityDropdown, BusinessDropdown, TagList, EventDropdown, FriendDropdown },
    props: {
        selectedDestination: {
            type: Object,
            required: true,
        },
        selectedTrain: {
            type: Object,
            required: true,
        },
    },
    setup() {
        const userStore = useUserStore();
        userStore.fetchSettings();
        const profileStore = useProfileSettingsStore();
        profileStore.fetchSettings();
        const activeCheckin = useActiveCheckin();
        const checkinSuccess = checkinSuccessStore();

        return { userStore, profileStore, activeCheckin, checkinSuccess, DateTime };
    },
    data() {
        return {
            allowedChars: 280,
            statusText: '',
            toot: false,
            chainPost: false,
            visibility: this.profileStore.getDefaultStatusVisibility,
            business: 0,
            loading: false,
            notyf: new Notyf({ position: { x: 'right', y: 'bottom' } }),
            collision: false,
            collisionCheckins: [],
            selectedEvent: null,
            selectedFriends: [],
        };
    },
    computed: {
        typed() {
            return this.allowedChars - this.statusText.length;
        },
        businessIcon() {
            switch (this.business) {
                case 0:
                    return 'fa fa-user';
                case 1:
                    return 'fa fa-briefcase';
                case 2:
                    return 'fa fa-building';
            }
            return '';
        },
        visibilityIcon() {
            switch (this.visibility) {
                case 0:
                    return 'fa fa-globe-americas';
                case 1:
                    return 'fa fa-lock-open';
                case 2:
                    return 'fa fa-user-friends';
                case 3:
                    return 'fa fa-lock';
                case 4:
                    return 'fa fa-user-check';
            }
            return '';
        },
    },
    methods: {
        trans,
        checkIn() {
            this.loading = true;
            const data = {
                body: this.statusText,
                toot: this.toot,
                chainPost: this.toot && this.chainPost,
                visibility: this.visibility,
                business: this.business,
                ibnr: false,
                tripId: this.selectedTrain.tripId,
                lineName: this.selectedTrain.line.name ?? this.selectedTrain.line.fahrtNr,
                start: this.selectedTrain.stop.id,
                destination: this.selectedDestination.id,
                departure: DateTime.fromISO(this.selectedTrain.plannedWhen).setZone('UTC').toISO(),
                arrival: DateTime.fromISO(this.selectedDestination.arrivalPlanned).setZone('UTC').toISO(),
                force: this.collision,
                eventId: this.selectedEvent ? this.selectedEvent.id : null,
                with: this.selectedFriends.map((friend) => friend.user.id),
            };
            fetch('/api/v1/trains/checkin', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data),
            })
                .then((response) => {
                    this.loading = false;
                    if (response.ok) {
                        response.json().then((result) => {
                            this.activeCheckin.reset();
                            this.checkinSuccess.setResponse(result.data);

                            this.$refs.tagList.postAllTags(result.data.status.id).then(() => {
                                window.location = '/status/' + result.data.status.id;
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
                                response.json().then((result) => {
                                    this.collision = true;
                                    this.collisionCheckins = result?.data?.conflicts ?? [];
                                });
                                break;
                            default:
                                this.notyf.error(trans('messages.exception.general'));
                                break;
                        }
                    }
                })
                .catch(() => {
                    this.loading = false;
                    this.notyf.error(trans('messages.exception.general'));
                });
        },
        selectEvent(event) {
            this.selectedEvent = event;
        },
        selectFriends(friends) {
            this.selectedFriends = friends;
        },
    },
};
</script>

<template>
    <div class="form-floating">
        <textarea
            id="message-text"
            v-model="statusText"
            name="body"
            class="form-control mobile-input-fs-16 pt-4"
            :maxlength="allowedChars"
            style="min-height: 130px; border: none"
        />
        <label for="message-text" class="form-label pt-4 ms-0" :class="{ 'd-none': statusText.length }">
            {{ trans('stationboard.label-message') }}
        </label>
    </div>
    <small v-show="typed < 20" class="float-end" :class="typed < 10 ? 'text-danger' : 'text-warning'">
        {{ typed }}
    </small>

    <div class="py-2 px-3 border-bottom">
        <div class="btn-group shadow-none">
            <div class="col-auto btn-group" :class="{ 'd-none': profileStore.getMastodon == null }">
                <input
                    id="toot_check"
                    v-model="toot"
                    type="checkbox"
                    class="btn-check"
                    autocomplete="off"
                    autocompleted=""
                    :disabled="visibility === 3"
                />
                <label class="btn btn-sm px-2" for="toot_check">
                    <i class="fab fa-mastodon" />
                    <span class="visually-hidden-focusable">{{ trans('stationboard.check-toot') }}</span>
                </label>
            </div>
            <div class="col-auto btn-group" :class="{ 'd-none': !toot }">
                <input
                    id="toot_chain"
                    v-model="chainPost"
                    type="checkbox"
                    class="btn-check"
                    autocomplete="off"
                    autocompleted=""
                    :disabled="visibility === 3"
                />
                <label class="btn btn-sm px-2" for="toot_chain">
                    <i class="fas fa-link" />
                    <span class="visually-hidden-focusable">{{ trans('stationboard.check-chainPost') }}</span>
                </label>
            </div>
            <div class="col-auto btn-group">
                <BusinessDropdown v-model="business" />
            </div>
            <div class="col btn-group">
                <VisibilityDropdown v-model="visibility" class="btn btn-sm btn-link px-2 dropdown-toggle" />
            </div>
            <EventDropdown @select-event="selectEvent" />
            <FriendDropdown @select-user="selectFriends" />
        </div>
        <button class="col-auto float-end ms-auto btn btn-sm btn-outline-primary" :disabled="loading" @click="checkIn">
            <span v-if="loading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true" />
            <span v-if="loading" class="visually-hidden">Loading...</span>
            {{ trans('stationboard.btn-checkin') }}
        </button>
    </div>

    <div v-if="collision" class="alert alert-warning mx-3 mt-3" role="alert">
        <div class="d-flex align-items-start gap-2">
            <i class="fa-solid fa-triangle-exclamation mt-1 flex-shrink-0" />
            <div class="flex-grow-1">
                <strong class="d-block mb-1">{{ trans('checkin.conflict') }}</strong>
                <ul v-if="collisionCheckins.length" class="list-unstyled mb-2 small">
                    <li v-for="conflict in collisionCheckins" :key="conflict.id">
                        <a
                            :href="'/status/' + conflict.id"
                            target="_blank"
                            rel="noopener"
                            class="d-inline-flex flex-wrap align-items-baseline gap-1 text-reset"
                        >
                            <strong>{{ conflict.checkin?.lineName }}</strong>
                            <span v-if="conflict.checkin?.origin?.departurePlanned" class="text-muted">{{
                                DateTime.fromISO(conflict.checkin.origin.departurePlanned).toFormat('HH:mm')
                            }}</span>
                            <span v-if="conflict.checkin?.origin?.name">{{ conflict.checkin.origin.name }}</span>
                            <span v-if="conflict.checkin?.destination?.name"
                                >&rarr; {{ conflict.checkin.destination.name }}</span
                            >
                            <span v-if="conflict.checkin?.destination?.arrivalPlanned" class="text-muted">{{
                                DateTime.fromISO(conflict.checkin.destination.arrivalPlanned).toFormat('HH:mm')
                            }}</span>
                            <i class="fa-solid fa-arrow-up-right-from-square small opacity-50" />
                        </a>
                    </li>
                </ul>
                <p class="small mb-2">{{ trans('checkin.conflict.question') }}</p>
                <div class="text-end">
                    <button class="btn btn-sm btn-warning" :disabled="loading" @click="checkIn">
                        <span
                            v-if="loading"
                            class="spinner-border spinner-border-sm"
                            role="status"
                            aria-hidden="true"
                        />
                        {{ trans('checkin.conflict.force') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-5 mx-3 mb-4 border-top pt-3">
        <TagList ref="tagList" :cache-locally="true" />
    </div>
</template>

<style scoped>
.form-floating > .form-control:not(:placeholder-shown) ~ label::after {
    background-color: transparent;
}

.form-control:focus {
    box-shadow: none;
}
</style>
