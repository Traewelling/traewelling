<template>
  <transition name="component-fade" mode="out-in">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
          <StationForm v-on:refresh="fetchData" :next="times.next" :now="times.now" :prev="times.prev"></StationForm>
          <div class="card">
            <div class="card-header">
              <div class="float-end">
                <a>
                  <!-- ToDo: set home, but with modal! -->
                  <i class="fa fa-home" aria-hidden="true"></i>
                </a>
              </div>
              <span v-if="station" id="stationTableHeader">
              {{ station.name }}
              <small>
                <i class="far fa-clock fa-sm" aria-hidden="true"></i>
                {{ moment(this.times.now).format("LLL") }}
              </small>
            </span>
            </div>

            <div class="loading" v-if="loading">
              {{ i18n.get("_.vue.loading") }}
            </div>
            <div class="card-body text-center text-danger text-bold" v-else-if="departures.length === 0">
              {{ i18n.get('_.stationboard.no-departures') }}
            </div>
            <div class="card-body p-0 table-responsive" v-else>
              <table class="table table-dark table-borderless table-hover table-striped m-0"
                     aria-labelledby="stationTableHeader">
                <thead>
                  <tr>
                    <th scope="col" class="ps-2 ps-md-4">{{ i18n.get('_.stationboard.dep-time') }}</th>
                    <th scope="col" class="px-0">{{ i18n.get('_.stationboard.line') }}</th>
                    <th scope="col">{{ i18n.get('_.stationboard.destination') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="departure in departures" :class="{trainrow: !departure.cancelled}"
                      data-tripID="$departure->tripId"
                      data-lineName="$departure->line->name != null ? $departure->line->name : $departure->line->fahrtNr"
                      data-start="$departure->stop->id"
                      data-departure="$departure->plannedWhen">
                    <td class="ps-2 ps-md-4">
                      <span class="text-danger" v-if="departure.cancelled">
                        {{ i18n.get('_.stationboard.stop-cancelled') }}
                      </span>
                      <span v-else>
                        {{ moment(departure.plannedWhen).format("LT") }}
                      </span>
                      <small v-if="departure.delay">
                        <br>
                        (<span :class="{
                        'text-success': departure.delay < 180,
                        'text-warning': departure.delay >=180 && departure.delay < 600,
                        'text-danger': departure.delay >= 600 }">+ {{ departure.delay / 60 }}</span>)
                      </small>
                    </td>
                    <td class="text-nowrap px-0">
                      <img v-if="images.includes(departure.line.product)"
                           class="product-icon"
                           :alt="departure.line.product"
                           :src="`/img/${departure.line.product}.svg`">
                      <i v-else class="fa fa-train" aria-hidden="true"></i>
                      &nbsp;<span v-if="departure.line.name">{{ departure.line.name }}</span>
                      <span v-else>{{ departure.line.fahrtNr }}</span>
                    </td>
                    <td class="text-wrap"> {{ departure.direction }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
import StationForm from "../components/StationForm";
import moment from "moment";

export default {
  name: "Stationboard",
  components: {StationForm},
  data() {
    return {
      station: null,
      departures: null,
      times: {
        now: 0,
        prev: 0,
        next: 0
      },
      loading: false,
      images: ['bus', 'suburban', 'subway', 'tram'],
      moment: moment
    };
  },
  mounted() {
    this.fetchData();
  },
  methods: {
    fetchData() {
      this.loading     = true;
      this.station     = null;
      const when       = this.$route.query.when ?? "";
      const travelType = this.$route.query.travelType ?? "";
      axios
          .get('/trains/station/' + this.$route.query.station + "/departures?when=" + when + "&travelType=" + travelType)
          .then((result) => {
            this.station    = result.data.meta.station;
            this.times      = result.data.meta.times;
            this.departures = result.data.data;
            this.loading    = false;

          })
          .catch((error) => {
            console.error(error);
          });
    }
  }
};
</script>

<style scoped>

</style>
