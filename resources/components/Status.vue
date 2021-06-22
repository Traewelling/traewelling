<template>
  <div v-if="status">
    <h5 v-if="showDate || isSingleStatus" class="mt-4">
      {{ moment(status.train.origin.departure).format("dddd[,] LL") }}
    </h5>
    <div class="card status mt-3">
      <div class="card-img-top" v-if="polyline">
        <Map class="map embed-responsive embed-responsive-16by9" :poly-lines="polyline"></Map>
      </div>

      <div class="card-body row">
        <div class="col-2 image-box pe-0 d-none d-lg-flex">
          <router-link :to="{ name: 'profile', params: {username: status.username}}">
            <img :src="`/profile/${status.username}/profilepicture`" :alt="status.username">
          </router-link>
        </div>

        <div class="col ps-0">
          <ul class="timeline">
            <li>
              <i class="trwl-bulletpoint" aria-hidden="true"></i>
              <span class="text-trwl float-end">
              <small v-if="status.train.origin.isDepartureDelayed"
                     style="text-decoration: line-through;"
                     class="text-muted">{{ moment(status.train.origin.departurePlanned).format('LT') }}
              </small>
              &nbsp; {{ departure.format('LT') }}
            </span>
              <router-link :to="{name: 'trains.stationboard', query: {station: status.train.origin.name}}"
                           class="text-trwl clearfix">{{ status.train.origin.name }}
              </router-link>
              <p class="train-status text-muted">
                <span>
                  <img v-if="categories.indexOf(status.train.category) > -1 " class="product-icon"
                       :src="`/img/${status.train.category}.svg`" :alt="status.train.category">
                  <i v-else class="fa fa-train d-inline" aria-hidden="true"></i>
                  {{ status.train.lineName }}
                </span>
                <span class="ps-2">
                  <i class="fa fa-route d-inline" aria-hidden="true"></i>
                  &nbsp;{{ status.train.distance.toFixed(0) }}<small>km</small>
                </span>
                <span class="ps-2"><i class="fa fa-stopwatch d-inline" aria-hidden="true"></i>
                  &nbsp;{{ duration }}
                </span>
                <span v-if="status.business > 0" class="pl-sm-2">
                  <i :class="travelReason[status.business].icon" data-mdb-toggle="tooltip" data-mdb-placement="top"
                     aria-hidden="true" :title="i18n.get(travelReason[status.business].desc)"></i>
                </span>
                <br>
                <span v-if="status.event != null" class="pl-sm-2">
                  <i class="fa fa-calendar-day" aria-hidden="true"></i>
                  <router-link :to="{name: 'event', params: {slug: status.event.slug}}">
                    {{ status.event.name }}
                  </router-link>
                </span>
              </p>
              <p v-if="status.body" class="status-body"><i class="fas fa-quote-right" aria-hidden="true"></i>
                {{ status.body }}</p>
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
              <small v-if="status.train.destination.isArrivalDelayed"
                     style="text-decoration: line-through;"
                     class="text-muted">{{ moment(status.train.destination.arrivalPlanned).format('LT') }}
              </small>
              &nbsp; {{ arrival.format('LT') }}
            </span>
              <router-link :to="{name: 'trains.stationboard', query: {station: status.train.destination.name}}"
                           class="text-trwl clearfix">
                {{ status.train.destination.name }}
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
          <router-link :to="{name: 'profile', params: {username: status.username}}">
            <span v-if="$auth.check() && $auth.user().id === status.user">{{ i18n.get("_.user.you") }}</span>
            <span v-else>{{ status.username }}</span>
          </router-link>,
          <router-link :to="{ name: 'singleStatus', params: {id: status.id, statusData: this.status } }">
            {{ moment(status.createdAt).fromNow() }}
          </router-link>
        </span>
        <ul class="list-inline" v-if="$auth.check()">
          <li v-if="$auth.user().id !== status.user && status.likes === 0" class="list-inline-item d-lg-none">
            <router-link :to="{name: 'profile', params: {username: status.username}}">
              <img :src="`/profile/${status.username}/profilepicture`" class="profile-image"
                   :alt="i18n.get('_.settings.picture')">
            </router-link>
          </li>

          <li class="list-inline-item like-text" v-on:click="likeStatus">
            <i class="like fa-star" v-bind:class="{fas: status.liked, far: !status.liked}" aria-hidden="true"></i>
            <span class="pl-1" v-if="status.likes">{{ status.likes }}</span>
          </li>

          <li class="list-inline-item like-text" v-if="$auth.user().id === status.user">
            <a class="like-text" role="button" data-mdb-toggle="dropdown">
              <i class="fas fa-ellipsis-h" aria-hidden="true" :title="i18n.get('_.status.more')"></i>
            </a>
            <ul class="dropdown-menu">
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
            <router-link :to="{name: 'profile', params: {username: status.username}}">
              <img :src="`/profile/${status.username}/profilepicture`" class="profile-image"
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
            <span v-if="like.id === status.user">{{ i18n.get("_.user.liked-own-status") }}</span>
            <span v-else>{{ i18n.get("_.user.liked-status") }}</span>
          </li>
        </ul>
      </div>
    </div>
    <ModalConfirm
        ref="deleteModal"
        v-if="status.user === $auth.user().id"
        v-on:confirm="deleteStatus"
        :title-text="i18n.get('_.modals.deleteStatus-title')"
        :abort-text="i18n.get('_.menu.abort')"
        :confirm-text="i18n.get('_.modals.delete-confirm')"
        confirm-button-color="btn-danger"
    ></ModalConfirm>
    <CheckInModal
        ref="editModal"
        v-if="status.user === $auth.user().id"
        :status-data="status"
    ></CheckInModal>
  </div>
</template>

<script>
import moment from "moment";
import Map from "../components/Map";
import {StatusModel} from "../js/APImodels";
import axios from "axios";
import ModalConfirm from "./ModalConfirm";
import {visibility, travelReason} from "../js/APImodels";
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
      travelReason: travelReason
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
    departure() {
      return moment(this.status.train.origin.departure);
    },
    arrival() {
      return moment(this.status.train.destination.arrival);
    },
    duration() {
      // ToDo: This needs localization, currently handled in `durationToSpan`
      const duration = moment.duration(this.status.train.duration, 'minutes').asMinutes();
      let minutes    = duration % 60;
      let hours      = Math.floor(duration / 60);

      return hours + "h " + minutes + "m";
    },
    percentage() {
      const start = moment(this.status.train.origin.departure);
      const end   = moment(this.status.train.destination.arrival);
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
        let stopOvers = this.stopovers[this.status.train.trip];
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
      return visibility[this.status.visibility];
    }
  },
  methods: {
    startRefresh() {
      setInterval(() => (this.now = moment()), 1000);
    },
    likeStatus() {
      if (this.status.liked === false) {
        axios
            .post("/like/" + this.status.id)
            .then(() => {
              this.status.liked = true;
              this.status.likes += 1;
              this.likes.push(this.$auth.user());
            })
            .catch((error) => {
              console.error(error);
            });
      } else {
        axios
            .delete("/like/" + this.status.id)
            .then(() => {
              this.status.liked = false;
              this.status.likes -= 1;
              let index         = this.likes.indexOf(this.$auth.user());
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
          .delete("/statuses/" + this.status.id)
          .then(() => {
            this.status = null;
          })
          .catch((error) => {
            console.error(error);
          });
    },
    toggleDeleteModal() {
      this.$refs.deleteModal.show();
    },
    toggleEditModal() {
      this.$refs.editModal.show();
    }
  },
  created() {
    this.startRefresh();
  }
}
</script>

<style scoped>

</style>
