<template>
  <div v-if="status">
    <h5 v-if="showDate || isSingleStatus" class="mt-4">
      {{ moment(statusData.train.origin.departure).format("dddd[,] LL") }}
    </h5>
    <div class="card status mt-3">
      <div class="card-img-top" v-if="polyline">
        <Map class="map embed-responsive embed-responsive-16by9" :poly-lines="polyline"></Map>
      </div>

      <div class="card-body row">
        <div class="col-2 image-box pe-0 d-none d-lg-flex">
          <router-link :to="{ name: 'profile', params: {username: statusData.username}}">
            <img :src="`/profile/${statusData.username}/profilepicture`" :alt="statusData.username">
          </router-link>
        </div>

        <div class="col ps-0">
          <ul class="timeline">
            <li>
              <i class="trwl-bulletpoint" aria-hidden="true"></i>
              <span class="text-trwl float-end">
              <small v-if="statusData.train.origin.isDepartureDelayed"
                     style="text-decoration: line-through;"
                     class="text-muted">{{ moment(statusData.train.origin.departurePlanned).format('LT') }}
              </small>
              &nbsp; {{ departure.format('LT') }}
            </span>
              <router-link :to="{name: 'trains.stationboard', query: {station: statusData.train.origin.name}}"
                           class="text-trwl clearfix">{{ statusData.train.origin.name }}
              </router-link>
              <p class="train-status text-muted">
                <span>
                  <img v-if="categories.indexOf(statusData.train.category) > -1 " class="product-icon"
                       :src="`/img/${statusData.train.category}.svg`" :alt="statusData.train.category">
                  <i v-else class="fa fa-train d-inline" aria-hidden="true"></i>
                  {{ statusData.train.lineName }}
                </span>
                <span class="ps-2">
                  <i class="fa fa-route d-inline" aria-hidden="true"></i>
                  &nbsp;{{ statusData.train.distance.toFixed(0) }}<small>km</small>
                </span>
                <span class="ps-2"><i class="fa fa-stopwatch d-inline" aria-hidden="true"></i>
                  &nbsp;{{ duration }}
                </span>
                <span v-if="statusData.business > 0" class="pl-sm-2">
                  <i :class="travelReason[statusData.business].icon" data-mdb-toggle="tooltip" data-mdb-placement="top"
                     aria-hidden="true" :title="i18n.get(travelReason[statusData.business].desc)"></i>
                </span>
                <br>
                <span v-if="statusData.event != null" class="pl-sm-2">
                  <i class="fa fa-calendar-day" aria-hidden="true"></i>
                  <router-link :to="{name: 'event', params: {slug: statusData.event.slug}}">
                    {{ statusData.event.name }}
                  </router-link>
                </span>
              </p>
              <p v-if="statusData.body" class="status-body"><i class="fas fa-quote-right" aria-hidden="true"></i>
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
              <i class="trwl-bulletpoint" aria-hidden="true"></i>
              <span class="text-trwl float-end">
              <small v-if="statusData.train.destination.isArrivalDelayed"
                     style="text-decoration: line-through;"
                     class="text-muted">{{ moment(statusData.train.destination.arrivalPlanned).format('LT') }}
              </small>
              &nbsp; {{ arrival.format('LT') }}
            </span>
              <router-link :to="{name: 'trains.stationboard', query: {station: statusData.train.destination.name}}"
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
        <span class="float-end like-text">
          <i class="fas visibility-icon text-small"
             :class="visibilityIcon.icon"
             :title="i18n.get(visibilityIcon.desc)"
             aria-hidden="true"
             data-mdb-toggle="tooltip"
             data-mdb-placement="top"></i>
          <router-link :to="{name: 'profile', params: {username: statusData.username}}">
            <span v-if="$auth.check() && $auth.user().id === statusData.user">{{ i18n.get("_.user.you") }}</span>
            <span v-else>{{ statusData.username }}</span>
          </router-link>,
          <router-link :to="{ name: 'singleStatus', params: {id: statusData.id, statusData: this.status } }">
            {{ moment(statusData.createdAt).fromNow() }}
          </router-link>
        </span>
        <ul class="list-inline" v-if="$auth.check()">
          <li v-if="$auth.check() && $auth.user().id !== statusData.user && statusData.likes === 0"
              class="list-inline-item d-lg-none">
            <router-link :to="{name: 'profile', params: {username: statusData.username}}">
              <img :src="`/profile/${statusData.username}/profilepicture`" class="profile-image"
                   :alt="i18n.get('_.settings.picture')">
            </router-link>
          </li>
          <li v-if="$auth.check()" class="list-inline-item like-text" v-on:click="likeStatus">
            <i class="like fa-star" v-bind:class="{fas: statusData.liked, far: !statusData.liked}"
               aria-hidden="true"></i>
            <span class="pl-1" v-if="statusData.likes">{{ statusData.likes }}</span>
          </li>
          <li v-if="$auth.check() && $auth.user().id === statusData.user" class="list-inline-item like-text">
            <a class="like-text" role="button" data-mdb-toggle="dropdown">
              <i class="fas fa-ellipsis-h" aria-hidden="true" :title="i18n.get('_.status.more')"></i>
            </a>
            <ul class="dropdown-menu">
              <li v-if="shareable"><a class="dropdown-item" href="#" v-on:click.prevent="share">
                <i class="fas fa-share" aria-hidden="true"></i>&nbsp; {{ i18n.get("_.menu.share") }}
              </a>
              </li>
              <li><a class="dropdown-item" href="#" v-on:click.prevent="toggleEditModal">
                <i class="fas fa-edit" aria-hidden="true"></i>&nbsp;{{ i18n.get("_.modals.editStatus-title") }}
              </a></li>
              <li><a class="dropdown-item" href="#" v-on:click.prevent="toggleDeleteModal">
                <i class="fas fa-trash" aria-hidden="true"></i>&nbsp;{{ i18n.get("_.modals.delete-confirm") }}
              </a></li>
            </ul>
          </li>
        </ul>
        <ul class="list-inline" v-else>
          <li class="list-inline-item d-lg-none">
            <router-link :to="{name: 'profile', params: {username: statusData.username}}">
              <img :src="`/profile/${statusData.username}/profilepicture`" class="profile-image"
                   :alt="i18n.get('_.settings.picture')">
            </router-link>
          </li>
        </ul>
      </div>

      <div v-for="like in likes" v-bind:key="likes.id" class="card-footer text-muted clearfix">
        <ul class="list-inline">
          <li class="list-inline-item">
            <router-link :to="{name: 'profile', params: {username: like.username}}">
              <img :src="`/profile/${like.username}/profilepicture`" class="profile-image"
                   :alt="i18n.get('_.settings.picture')">
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
        ref="deleteModal"
        v-if="$auth.check() && statusData.user === $auth.user().id"
        v-on:confirm="deleteStatus"
        :title-text="i18n.get('_.modals.deleteStatus-title')"
        :abort-text="i18n.get('_.menu.abort')"
        :confirm-text="i18n.get('_.modals.delete-confirm')"
        confirm-button-color="btn-danger"
    ></ModalConfirm>
    <CheckInModal
        ref="editModal"
        v-if="$auth.check() && statusData.user === $auth.user().id"
        v-on:updated="updateStatus"
        :status-data="status"
    ></CheckInModal>
  </div>
</template>

<script>
import moment from "moment";
import Map from "../components/Map";
import {StatusModel, travelReason, visibility} from "../js/APImodels";
import axios from "axios";
import ModalConfirm from "./ModalConfirm";
import CheckInModal from "./CheckInModal";

export default {
  name: "Status.vue",
  data() {
    return {
      moment: moment,
      isSingleStatus: false,
      categories: ["bus", "suburban", "subway", "tram"],
      loading: false,
      error: false,
      now: moment(),
      travelReason: travelReason,
      statusResponse: null
    };
  },
  components: {
    CheckInModal,
    Map,
    ModalConfirm
  },
  props: {
    status: StatusModel,
    showDate: false,
    likes: null,
    polyline: null, //ToDo Typedef
    stopovers: null //ToDo Typedef
  },
  computed: {
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
    likeStatus() {
      if (this.statusData.liked === false) {
        axios
            .post("/like/" + this.statusData.id)
            .then(() => {
              this.statusData.liked = true;
              this.statusData.likes += 1;
              this.likes.push(this.$auth.user());
            })
            .catch((error) => {
              console.error(error);
            });
      } else {
        axios
            .delete("/like/" + this.statusData.id)
            .then(() => {
              this.statusData.liked = false;
              this.statusData.likes -= 1;
              let index             = this.likes.indexOf(this.$auth.user());
              if (index !== -1) {
                this.likes.splice(index);
              }
            })
            .catch((error) => {
              console.error(error);
            });
      }
    },
    deleteStatus() {
      axios
          .delete("/statuses/" + this.statusData.id)
          .then(() => {
            this.status = null;
          })
          .catch((error) => {
            console.error(error);
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
        text: this.i18n.choice("description.status", 1, {
          "username": this.statusData.username,
          "origin": this.statusData.train.origin.name,
          "destination": this.statusData.train.destination.name,
          "date": moment(this.statusData.train.origin.departure).format("LLL"),
          "lineName": this.statusData.train.lineName
        }),
        url: window.location.origin + "/statuses/" + this.statusData.id,
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
