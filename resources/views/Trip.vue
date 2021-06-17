<template>
  <div>
    <transition>
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-md-8 col-lg-7">
            <div class="loading" v-if="loading">
              {{ i18n.get("_.vue.loading") }}
            </div>
            <div class="card" v-if="hafasTrip != null">
              <div class="card-header">
                <div class="float-end">
                  <a href="#" @click="showModal(lastStation)">
                    <i class="fa fa-fast-forward" aria-hidden="true"></i>
                  </a>
                </div>
                <img v-if="images.includes(hafasTrip.category)"
                     class="product-icon"
                     :alt="hafasTrip.category"
                     :src="`/img/${hafasTrip.category}.svg`">
                <i v-else class="fa fa-train" aria-hidden="true"></i>
                {{ this.hafasTrip.lineName }}
                <i class="fas fa-arrow-alt-circle-right" aria-hidden="true"></i>
                {{ this.hafasTrip.destination.name }}
              </div>

              <div class="card-body p-0 table-responsive">
                <table class="table table-dark table-borderless table-hover table-striped m-0">
                  <thead>
                    <tr>
                      <th>{{ i18n.get('_.stationboard.stopover') }}</th>
                      <th></th>
                      <th class="ps-0"></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="stop in stopovers" @click="showModal(stop)">
                      <td :class="{ 'text-danger text-decoration-line-through': stop.cancelled}">{{ stop.name }}</td>
                      <td v-if="!stop.cancelled">
                      <span v-if="stop.arrivalPlanned">
                        {{ i18n.get('_.stationboard.arr') }}&nbsp;
                        <span :class="delay(stop.arrivalPlanned, stop.arrivalReal)">
                          {{ moment(stop.arrival).format("LT") }}
                        </span>
                        <small v-if="stop.isArrivalDelayed" class="text-muted text-decoration-line-through">
                          {{ moment(stop.arrivalPlanned).format("LT") }}
                        </small>
                      </span>
                        <br/>
                        <span v-if="stop.departurePlanned">
                        {{ i18n.get('_.stationboard.dep') }}&nbsp;
                        <span :class="delay(stop.departurePlanned, stop.departureReal)">
                          {{ moment(stop.departure).format("LT") }}
                        </span>
                        <small v-if="stop.isDepartureDelayed" class="text-muted text-decoration-line-through">
                          {{ moment(stop.departurePlanned).format("LT") }}
                        </small>
                      </span>
                      </td>
                      <td v-else class="text-danger">
                        {{ i18n.get('_.stationboard.stop-cancelled') }}
                      </td>
                      <td class="ps-0" :class="{ 'text-danger text-decoration-line-through': stop.cancelled}">
                        {{ stop.platform }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </transition>
    <CheckInModal
        ref="checkInModal"
        :destination="destination"
        :train-data="trainData"
    ></CheckInModal>
  </div>
</template>

<script>
import {travelImages} from "../js/APImodels";
import moment from "moment";
import CheckInModal from "../components/CheckInModal";

export default {
  name: "Trip",
  components: {CheckInModal},
  data() {
    return {
      loading: false,
      images: travelImages,
      hafasTrip: null,
      stopovers: null,
      lastStation: null,
      moment: moment,
      destination: null,
      trainData: {
        tripID: 0,
        lineName: "",
        start: 0,
        destination: 0,
        departure: 0,
        arrival: 0
      }
    };
  },
  mounted() {
    this.fetchData();
  },
  methods: {
    fetchData() {
      this.loading = true;
      this.station = null;
      const query  = this.$route.query;
      axios
          .get("/trains/trip?tripID=" + query.tripID + "&lineName=" + query.lineName + "&start=" + query.start)
          .then((result) => {
            this.hafasTrip = result.data.data;
            this.stopovers = this.hafasTrip.stopovers.filter((item) => {
              return moment(item.arrivalPlanned).isAfter(moment(this.$route.query.departure));
            });
            this.lastStation = this.hafasTrip.stopovers.pop();
            this.loading   = false;
          })
          .catch((error) => {
            console.error(error);
          });
    },
    showModal(stop) {
      this.trainData   = {
        tripID: this.$route.query.tripID,
        lineName: this.$route.query.lineName,
        start: this.$route.query.start,
        destination: stop.id,
        departure: this.$route.query.departure,
        arrival: stop.arrivalPlanned
      };
      this.destination = stop.name;
      this.$refs.checkInModal.show();
    },
    delay(planned, current) {
      const delay = moment(current).diff(moment(planned), 'seconds');

      if (delay === 0) {
        return "text-success";
      } else if (delay < 600) {
        return "text-warning";
      } else if (delay >= 600) {
        return "text-danger";
      }
    }
  }
};
</script>

<style scoped>

</style>
