<template>
    <div v-if="status">
        <h5 v-if="showDate || isSingleStatus" class="mt-4">
            {{ moment(statusData.train.origin.departure).format("dddd[,] LL") }}
        </h5>
        <div class="card status mt-3">
            <div v-if="polyline" class="card-img-top">
                <Map :poly-lines="polyline" class="map embed-responsive embed-responsive-16by9"></Map>
            </div>

            <div class="card-body row py-1 ps-2 pe-3">
                <div class="col-2 image-box pe-0 d-none d-lg-flex">
                    <router-link :to="{ name: 'profile', params: {username: statusData.username}}">
                        <img :alt="statusData.username"
                             :src="`/profile/${statusData.username}/profilepicture`">
                    </router-link>
                </div>

                <div class="col ps-0">
                    <ul class="timeline">
                        <li>
                            <i aria-hidden="true" class="trwl-bulletpoint"></i>
                            <span class="text-trwl float-end">
              <small v-if="statusData.train.origin.isDepartureDelayed"
                     class="text-muted"
                     style="text-decoration: line-through;">{{
                      moment(statusData.train.origin.departurePlanned).format('LT')
                  }}
              </small>
              &nbsp; {{ departure.format('LT') }}
            </span>
                            <router-link
                                :to="{name: 'trains.stationboard', query: {station: statusData.train.origin.name}}"
                                class="text-trwl clearfix">{{ statusData.train.origin.name }}
                            </router-link>
                            <p class="train-status text-muted">
                <span>
                  <img v-if="categories.indexOf(statusData.train.category) > -1 " :alt="statusData.train.category"
                       :src="`/img/${statusData.train.category}.svg`" class="product-icon">
                  <i v-else aria-hidden="true" class="fa fa-train d-inline"></i>
                  {{ statusData.train.lineName }}
                </span>
                                <span class="ps-2">
                  <i aria-hidden="true" class="fa fa-route d-inline"></i>
                  &nbsp;{{ (statusData.train.distance / 1000).toFixed(1) }}<small>km</small>
                </span>
                                <span class="ps-2"><i aria-hidden="true" class="fa fa-stopwatch d-inline"></i>
                  &nbsp;{{ duration }}
                </span>
                                <span v-if="statusData.business > 0" class="pl-sm-2">
                  <i :class="travelReason[statusData.business].icon"
                     :title="i18n.get(travelReason[statusData.business].desc)" aria-hidden="true"
                     data-mdb-placement="top" data-mdb-toggle="tooltip"></i>
                </span>
                                <br>
                                <span v-if="statusData.event != null" class="pl-sm-2">
                  <i aria-hidden="true" class="fa fa-calendar-day"></i>
                  <router-link :to="{name: 'event', params: {slug: statusData.event.slug}}">
                    {{ statusData.event.name }}
                  </router-link>
                </span>
                            </p>
                            <p v-if="statusData.body" class="status-body"><i aria-hidden="true"
                                                                             class="fas fa-quote-right"></i>
                                {{ statusData.body }}</p>
                            <div v-if="nextStop != null">
                                <p class="text-muted font-italic">
                                    {{ i18n.get('_.stationboard.next-stop') }}
                                    <router-link :to="{name: 'trains.stationboard', query: {station: nextStop.name}}"
                                                 class="text-trwl clearfix">
                                        {{ nextStop.name }}
                                    </router-link>
                                </p>
                            </div>
                        </li>
                        <li>
                            <i aria-hidden="true" class="trwl-bulletpoint"></i>
                            <span class="text-trwl float-end">
              <small v-if="statusData.train.destination.isArrivalDelayed"
                     class="text-muted"
                     style="text-decoration: line-through;">{{
                      moment(statusData.train.destination.arrivalPlanned).format('LT')
                  }}
              </small>
              &nbsp; {{ arrival.format('LT') }}
            </span>
                            <router-link
                                :to="{name: 'trains.stationboard', query: {station: statusData.train.destination.name}}"
                                class="text-trwl clearfix">
                                {{ statusData.train.destination.name }}
                            </router-link>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="progress">
                <div class="progress-bar"
                     role="progressbar"
                     v-bind:style="{width: percentage + '%'}"></div>
            </div>
            <div class="card-footer text-muted">
        <span class="float-end like-text small">
          <i :class="visibilityIcon.icon"
             :title="i18n.get(visibilityIcon.desc)"
             aria-hidden="true"
             class="fas visibility-icon text-small"
             data-mdb-placement="top"
             data-mdb-toggle="tooltip"></i>
          <router-link :to="{name: 'profile', params: {username: statusData.username}}">
            <span v-if="$auth.check() && $auth.user().id === statusData.user">{{ i18n.get("_.user.you") }}</span>
            <span v-else>{{ statusData.username }}</span>
          </router-link>,
          <router-link :to="{ name: 'singleStatus', params: {id: statusData.id, statusData: this.status } }">
            {{ moment(statusData.createdAt).fromNow() }}
          </router-link>
        </span>
                <ul class="list-inline">
                    <li class="list-inline-item d-lg-none me-1">
                        <router-link :to="{name: 'profile', params: {username: statusData.username}}">
                            <img :alt="i18n.get('_.settings.picture')"
                                 :src="`/profile/${statusData.username}/profilepicture`"
                                 class="profile-image">
                        </router-link>
                    </li>
                    <li v-if="$auth.check()" class="list-inline-item like-text me-1" tabindex="0"
                        v-on:click="likeStatus">
                        <i aria-hidden="true" class="like fa-star small"
                           v-bind:class="{fas: statusData.liked, far: !statusData.liked}"></i>
                        <span v-if="statusData.likes" class="pl-1">{{ statusData.likes }}</span>
                    </li>
                    <li class="list-inline-item like-text">
                        <a :aria-label="i18n.get('_.menu.show-more')" class="like-text" data-mdb-toggle="dropdown"
                           role="button" tabindex="0" @click="fetchUser">
                            <i aria-hidden="true" class="fas fa-ellipsis-h small"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <li v-if="shareable">
                                <a class="dropdown-item" href="#" v-on:click.prevent="share">
                                    <i aria-hidden="true" class="fas fa-share"></i>&nbsp; {{ i18n.get("_.menu.share") }}
                                </a>
                            </li>
                            <li v-if="!editable && user">
                                <FollowButton :user="user" dropdown="true"/>
                            </li>
                            <li v-if="!editable && user">
                                <MuteButton :user="user" dropdown="true"/>
                            </li>
                            <li v-if="editable">
                                <a class="dropdown-item" href="#" v-on:click.prevent="toggleEditModal">
                                    <i aria-hidden="true" class="fas fa-edit"></i>&nbsp;
                                    {{ i18n.get("_.modals.editStatus-title") }}
                                </a>
                            </li>
                            <li v-if="editable">
                                <a class="dropdown-item" href="#" v-on:click.prevent="toggleDeleteModal">
                                    <i aria-hidden="true" class="fas fa-trash"></i>&nbsp;
                                    {{ i18n.get("_.modals.delete-confirm") }}
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>

            <div v-for="like in likes" v-bind:key="likes.id" class="card-footer text-muted clearfix">
                <ul class="list-inline">
                    <li class="list-inline-item">
                        <router-link :to="{name: 'profile', params: {username: like.username}}">
                            <img :alt="i18n.get('_.settings.picture')"
                                 :src="`/profile/${like.username}/profilepicture`"
                                 class="profile-image">
                        </router-link>
                    </li>
                    <li class="list-inline-item like-text">
                        <router-link :to="{name: 'profile', params: {username: like.username}}">
                            {{ like.username }}
                        </router-link>
                        <span v-if="like.id === statusData.user">{{ i18n.get("_.user.liked-own-status") }}</span>
                        <span v-else>{{ i18n.get("_.user.liked-status") }}</span>
                    </li>
                </ul>
            </div>
        </div>
        <ModalConfirm
            v-if="$auth.check() && statusData.user=== $auth.user().id"
            ref="deleteModal"
            :abort-text="i18n.get('_.menu.abort')"
            :confirm-text="i18n.get('_.modals.delete-confirm')"
            :title-text="i18n.get('_.modals.deleteStatus-title')"
            confirm-button-color="btn-danger"
            v-on:confirm="deleteStatus"
        ></ModalConfirm>
        <CheckInModal
            v-if="$auth.check() && statusData.user=== $auth.user().id"
            ref="editModal"
            :status-data="status"
            v-on:updated="updateStatus"
        ></CheckInModal>
    </div>
</template>

<script>
import moment from "moment";
import Map from "../components/Map";
import {StatusModel, travelReason, visibility} from "../js/APImodels";
import ModalConfirm from "./ModalConfirm";
import CheckInModal from "./CheckInModal";
import FollowButton from "./FollowButton";
import MuteButton from "./MuteButton";
import User from "../js/ApiClient/User";
import Status from "../js/ApiClient/Status";

export default {
    name: "Status.vue",
    inject: ["notyf"],
    data() {
        return {
            isSingleStatus: false,
            categories: ["bus", "suburban", "subway", "tram"],
            loading: false,
            error: false,
            now: moment(),
            travelReason: travelReason,
            statusResponse: null,
            user: null
        };
    },
    components: {
        MuteButton,
        FollowButton,
        CheckInModal,
        Map,
        ModalConfirm,
        moment
    },
    props: {
        status: StatusModel,
        showDate: false,
        likes: null,
        polyline: null, //ToDo Typedef
        stopovers: null //ToDo Typedef
    },
    computed: {
        editable() {
            return this.$auth.check() && this.$auth.user().id === this.statusData.user;
        },
        shareable() {
            return navigator.share;
        },
        statusData() {
            if (!this.statusResponse) {
                return this.$props.status;
            }
            return this.statusResponse;
        },
        departure() {
            return moment(this.statusData.train.origin.departure);
        },
        arrival() {
            return moment(this.statusData.train.destination.arrival);
        },
        duration() {
            // ToDo: This needs localization, currently handled in `durationToSpan`
            const duration = moment.duration(this.statusData.train.duration, 'minutes').asMinutes();
            let minutes    = duration % 60;
            let hours      = Math.floor(duration / 60);

            return hours + "h " + minutes + "m";
        },
        percentage() {
            const start = moment(this.statusData.train.origin.departure);
            const end   = moment(this.statusData.train.destination.arrival);
            let percent;
            if (this.now > start && this.now < end) {
                percent = 100 * ((this.now - start) / (end - start));
            } else if (this.now >= end) {
                percent = 100;
            }
            return percent;
        },
        showStopOvers() {
            return this.departure.isBefore() && this.arrival.isAfter() && this.nextStop() !== null;
        },
        nextStop() {
            if (this.stopovers != null && this.percentage < 100 && this.percentage > 0) {
                let stopOvers = this.stopovers[this.statusData.train.trip];
                if (stopOvers && stopOvers.length > 0) {
                    let future = stopOvers.filter((stopover) => {
                        return moment(stopover.arrival).isAfter(this.now);
                    });
                    return future[0];
                }
            }
            return null;
        },
        visibilityIcon() {
            return visibility[this.statusData.visibility];
        }
    },
    methods: {
        startRefresh() {
            setInterval(() => (this.now = moment()), 1000);
        },
        fetchUser() {
            User
                .getByUsername(this.status.username)
                .then((data) => {
                    this.user = data;
                })
                .catch((error) => {
                    this.apiErrorHandler(error);
                });
        },
        likeStatus() {
            if (this.statusData.liked === false) {
                Status
                    .like(this.statusData.id)
                    .then(() => {
                        this.statusData.liked = true;
                        this.statusData.likes += 1;
                        this.likes.push(this.$auth.user());
                    })
                    .catch((error) => {
                        this.apiErrorHandler(error);
                    });
            } else {
                Status
                    .dislike(this.statusData.id)
                    .then(() => {
                        this.statusData.liked = false;
                        this.statusData.likes -= 1;
                        let index             = this.likes.indexOf(this.$auth.user());
                        if (index !== -1) {
                            this.likes.splice(index);
                        }
                    })
                    .catch((error) => {
                        this.apiErrorHandler(error);
                    });
            }
        },
        deleteStatus() {
            Status
                .delete(this.statusData.id)
                .then(() => {
                    this.status = null;
                })
                .catch((error) => {
                    this.apiErrorHandler(error);
                });
        },
        updateStatus() {
            this.statusResponse = this.$refs.editModal.result;
        },
        toggleDeleteModal() {
            this.$refs.deleteModal.show();
        },
        toggleEditModal() {
            this.$refs.editModal.show();
        },
        share() {

            const shareData = {
                title: this.i18n.get("_.menu.share"),
                text: this.i18n.choice("_.description.status", 1, {
                    "username": this.statusData.username,
                    "origin": this.statusData.train.origin.name,
                    "destination": this.statusData.train.destination.name,
                    "date": moment(this.statusData.train.origin.departure).format("LLL"),
                    "lineName": this.statusData.train.lineName
                }),
                url: window.location.origin + this.$router.resolve({
                    name: "singleStatus",
                    params: {id: this.statusData.id}
                }).href,
            };

            if (navigator.share) {
                navigator.share(shareData);
            }
        }
    },
    created() {
        this.startRefresh();
    }
}
</script>

<style scoped>

</style>
