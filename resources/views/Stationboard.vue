<template>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-7">
        <StationForm v-on:refresh="fetchData"></StationForm>
        <div id="timepicker-wrapper">
          <div class="text-center">
            <div class="btn-group" role="group">
              <a
                  :title="i18n.get('_.stationboard.minus-15')"
                  class="btn btn-light">
                <i class="fas fa-arrow-circle-left" aria-hidden="true"></i>
              </a>
              <a href="#" id="timepicker-reveal" :title="i18n.get('_.stationboard.dt-picker')"
                 class="btn btn-light btn-rounded">
                <i class="fas fa-clock" aria-hidden="true"></i>
              </a>
              <a
                  :title="i18n.get('_.stationboard.plus-15')"
                  class="btn btn-light">
                <i class="fas fa-arrow-circle-right" aria-hidden="true"></i>
              </a>
            </div>
          </div>
          <div class="text-center mt-4">
            <form class="form-inline" v-if="false">
              <div class="input-group mb-3 mx-auto">
                <input type="datetime-local" class="form-control" id="timepicker" name="when"
                       aria-describedby="button-addontime"/>
                <button class="btn btn-outline-primary" type="submit" id="button-addontime">
                  {{ i18n.get('_.stationboard.set-time') }}
                </button>
              </div>
            </form>
          </div>
        </div>
        <div class="card" v-if="station">
          <div class="card-header">
            <div class="float-end">
              <a>
                <!-- ToDo: set home, but with modal! -->
                <i class="fa fa-home" aria-hidden="true"></i>
              </a>
            </div>
            {{ station.name }}
            <small>
              <i class="far fa-clock fa-sm" aria-hidden="true"></i>
              {{ moment(this.times.now).format("LLL") }}
            </small>
          </div>

          <div class="card-body p-0 table-responsive">
            <table class="table table-dark table-borderless m-0" v-if="departures.length == 0">
              <tr>
                <td>{{ i18n.get('_.stationboard.no-departures') }}</td>
              </tr>
            </table>
            <table class="table table-dark table-borderless table-hover m-0" v-else>
              <thead>
                <tr>
                  <th>{{ i18n.get('_.stationboard.line') }}</th>
                  <th>{{ i18n.get('_.stationboard.destination') }}</th>
                  <th>{{ i18n.get('_.stationboard.dep-time') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="departure in departures" :class="{trainrow: !departure.cancelled}"
                    data-tripID="$departure->tripId"
                    data-lineName="$departure->line->name != null ? $departure->line->name : $departure->line->fahrtNr"
                    data-start="$departure->stop->id"
                    data-departure="$departure->plannedWhen">
                  <td>
                    <!--                    ToDo: train Icons as a enum? -->
                    <!--                    @if (file_exists(public_path('img/'.$departure->line->product.'.svg')))-->
                    <!--                    <img class="product-icon"-->
                    <!--                         alt="Icon of $departure->line->product"-->
                    <!--                         src="asset('img/'.$departure->line->product.'.svg')">-->
                    <!--                    @else-->
                    <i class="fa fa-train" aria-hidden="true"></i>
                    <!--                    @endif-->
                    &nbsp;
                    <span v-if="departure.line.name">{{ departure.line.name }}</span>
                    <span v-else>{{ departure.line.fahrtNr }}</span>

                  </td>
                  <td> {{ departure.direction }}</td>
                  <td>
                    <span class="text-danger" v-if="departure.cancelled">
                      {{ i18n.get('_.stationboard.stop-cancelled') }}
                    </span>
                    <span v-else>
                      {{ moment(departure.plannedWhen).format("LT")}}
                    </span>
                    <small v-if="departure.delay">
                      (<span :class="{
                        'text-success': departure.delay < 180,
                        'text-warning': departure.delay >=180 && departure.delay < 600,
                        'text-danger': departure.delay >= 600 }">+ {{ departure.delay / 60}}</span>)
                    </small>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
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
      times: null,
      moment: moment
    };
  },
  mounted() {
    this.fetchData();
  },
  methods: {
    fetchData() {
      axios
          .get('/trains/station/' + this.$route.query.station + "/departures")
          .then((result) => {
            this.station    = result.data.meta.station;
            this.times      = result.data.meta.times;
            this.departures = result.data.data;

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
