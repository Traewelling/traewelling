<template>
    <div class="p-0 m-0">
        <div ref="checkinModal" class="modal fade" role="dialog" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">{{ this.lineName }}
                            <i aria-hidden="true" class="fas fa-arrow-alt-circle-right"></i> {{ this.dest }}</h4>
                        <button aria-label="Close" class="close" type="button" v-on:click="hide">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="checkinForm" method="POST">
                            <div class="form-group">
                                <label class="col-form-label" for="message-text">
                                    {{ i18n.get('_.stationboard.label-message') }}
                                </label>
                                <textarea id="message-text" v-model="status.body" class="form-control"></textarea>
                            </div>

                            <div class="mt-2">
                                <div v-if="!edit && $auth.user().twitterUrl != null" class="btn-group">
                                    <input id="tweet_check" v-model="status.tweet" autocomplete="off" class="btn-check"
                                           type="checkbox"/>
                                    <label class="btn btn-sm btn-outline-twitter" for="tweet_check">
                                        <i :title="i18n.get('stationboard.check-tweet')" aria-hidden="true"
                                           class="fab fa-twitter"></i>
                                        <span class="visually-hidden-focusable">{{
                                                i18n.get('_.stationboard.check-tweet')
                                            }}</span>
                                    </label>
                                </div>
                                <div v-if="!edit && $auth.user().mastodonUrl != null" class="btn-group">
                                    <input id="toot_check" v-model="status.toot" autocomplete="off" class="btn-check"
                                           type="checkbox"/>
                                    <label class="btn btn-sm btn-outline-mastodon" for="toot_check">
                                        <i :title="i18n.get('stationboard.check-toot')" aria-hidden="true"
                                           class="fab fa-mastodon"></i>
                                        <span class="visually-hidden-focusable">{{
                                                i18n.get('_.stationboard.check-toot')
                                            }}</span>
                                    </label>
                                </div>

                                <div class="float-end">
                                    <FADropdown v-model="status.business" :dropdown-content="travelReason"
                                                :pre-select="status.business"></FADropdown>
                                    <!-- @todo Add features from PR#463 (Use default visibility) -->
                                    <FADropdown v-model="status.visibility" :dropdown-content="visibility"
                                                :pre-select="status.visibility"></FADropdown>
                                </div>
                            </div>

                            <!--            @if($events->count() == 1)-->
                            <!--            <div class="custom-control custom-checkbox mt-2">-->
                            <!--              <input type="checkbox" class="custom-control-input" id="event_check" name="event"-->
                            <!--                     value="{{ $events[0]->id }}"/>-->
                            <!--              <label class="custom-control-label" for="event_check">-->
                            <!--                {{ i18n.get('_.events.on-my-way-to', ['name' => $events[0]->name]) }}-->
                            <!--              </label>-->
                            <!--            </div>-->
                            <!--            @elseif($events->count() > 1)-->
                            <!--            <div class="form-group">-->
                            <!--              <label for="event-dropdown" class="col-form-label">-->
                            <!--                {{i18n.get('_.events.on-my-way-dropdown')}}-->
                            <!--              </label>-->
                            <!--              <select class="form-control" id="event-dropdown" name="event">-->
                            <!--                <option value="0" selected>{{ i18n.get('_.events.no-event-dropdown') }}</option>-->
                            <!--                @foreach($events as $event)-->
                            <!--&lt;!&ndash;                <option value="{{ $event->id }}">{{ $event->name }}</option>&ndash;&gt;-->
                            <!--                @endforeach-->
                            <!--              </select>-->
                            <!--            </div>-->
                            <!--            @else-->
                            <input name="event" type="hidden" value="0"/>
                            <!--            <input type="hidden" id="input-tripID" name="tripID" value=""/>-->
                            <!--            <input type="hidden" id="input-destination" name="destination" value=""/>-->
                            <!--            <input type="hidden" name="start" value="{{request()->start}}"/>-->
                            <!--            <input type="hidden" name="departure" value="{{request()->departure}}"/>-->
                            <!--            <input type="hidden" id="input-arrival" name="arrival"/>-->
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-light" type="button" v-on:click="hide">{{
                                i18n.get('_.menu.abort')
                            }}
                        </button>
                        <button v-if="edit" class="btn btn-primary" type="button" v-on:click="editCheckin">
                            {{ i18n.get("_.modals.edit-confirm") }}
                        </button>
                        <button v-else class="btn btn-primary" type="button" v-on:click="submitCheckin">
                            {{ i18n.get('_.stationboard.btn-checkin') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <ModalConfirm
            ref="conflict"
            :abort-text="i18n.get('_.menu.abort')"
            :confirm-text="i18n.get('_.stationboard.btn-checkin')"
            :title-text="i18n.get('_.generic.error')"
            confirm-button-color="btn-primary"
            header-class="bg-danger text-white"
            v-on:abort="$router.push({name: 'dashboard'})"
            v-on:confirm="forceCheckin"
        >
            <p v-if="this.error.id">
                {{ i18n.choice("_.controller.transport.overlapping-checkin", 1, {linename: this.error.line}) }}&nbsp;
                <router-link :to="{name: 'singleStatus', params: {id: this.error.id }}" @click="$refs.conflict.hide">
                    {{ this.error.id }}
                </router-link>
            </p>
            <p>{{ i18n.get("_.checkin.conflict.question") }}</p>
        </ModalConfirm>
    </div>
</template>

<script>
import {Modal} from "bootstrap";
import FADropdown from "./FADropdown";
import {travelReason, visibility} from "../js/APImodels";
import Checkin from "../js/ApiClient/Checkin";
import ModalConfirm from "./ModalConfirm";

export default {
    name: "CheckInModal",
    inject: ["notyf"],
    components: {ModalConfirm, FADropdown},
    data() {
        return {
            modal: null,
            notifications: null,
            status: {
                body: "",
                business: 0,
                visibility: 0,
                eventId: null,
                tweet: false,
                toot: false,
                force: false
            },
            error: {
                id: null,
                line: null,
            },
            travelReason: travelReason,
            visibility: visibility,
            result: null
        };
    },
    props: {
        destination: null,
        statusData: {
            type: Object
        },
        trainData: {
            type: Object
        }
    },
    mounted() {
        this.modal = new Modal(this.$refs.checkinModal);
        if (this.edit) {
            this.status.body       = this.$props.statusData.body;
            this.status.business   = this.$props.statusData.business;
            this.status.visibility = this.$props.statusData.visibility;
        }
    },
    computed: {
        edit() {
            return !!this.$props.statusData;

        },
        dest() {
            if (this.edit) {
                return this.$props.statusData.train.destination.name;
            }
            return this.$props.destination;
        },
        lineName() {
            if (this.edit) {
                return this.$props.statusData.train.lineName;
            }
            return this.$props.trainData.lineName;
        }
    },
    methods: {
        show() {
            this.modal.show();
        },
        hide() {
            this.modal.hide();
        },
        forceCheckin() {
            this.status.force = true;
            this.submitCheckin();
        },
        submitCheckin() {
            const formData = {};
            Object.assign(formData, this.status);
            Object.assign(formData, this.$props.trainData);
            Checkin
                .checkIn(formData)
                .then((data) => {
                    this.$router.push({name: "dashboard", params: {checkin: data}});
                    this.hide();
                })
                .catch((error) => {
                    if (error.status === 409 && this.error.id === null) {
                        this.error.id   = error.errors[0].status_id;
                        this.error.line = error.errors[0].lineName;
                        this.hide();
                        this.$refs.conflict.show();
                    } else {
                        this.$router.push({name: "dashboard"});
                    }
                    this.apiErrorHandler(error);
                });
        },
        editCheckin() {
            const formData = {};
            Object.assign(formData, this.status);
            Checkin
                .editCheckin(this.statusData.id, formData)
                .then((data) => {
                    this.result = data;
                    this.notyf.success(this.i18n.get("_.settings.saved"));
                    this.$emit("updated");
                    this.hide();
                })
                .catch((error) => {
                    this.apiErrorHandler(error);
                });

        }
    }
};
</script>

<style scoped>

</style>
