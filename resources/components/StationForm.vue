<template>
  <div class="card">
    <div class="card-header">{{ i18n.get('_.stationboard.where-are-you') }}</div>
    <div class="card-body">
      <div id="gps-disabled-error" class="alert my-3 alert-danger d-none" role="alert">
        {{ i18n.get('_.stationboard.position-unavailable') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form v-on:submit.prevent="submitStation">
        <div id="station-autocomplete-container">
          <div class="input-group mb-2 mr-sm-2">
            <input type="text" id="station-autocomplete" v-model="station" class="form-control"
                   :placeholder="`${i18n.get('_.stationboard.station-placeholder')} / DS100`"/>
            <!--                         @isset(request()->station) value="{{request()->station}}" @endisset-->


            <!--                  @if($latest->count() > 0 || Auth::user()->home)-->
            <div class="btn btn-outline-grey stationSearchButton"
                 data-mdb-toggle="collapse"
                 data-mdb-target="#last-stations"
                 :title="i18n.get('_.stationboard.last-stations')"
            >
              <i class="fa fa-history"></i>
            </div>
            <!--                  @endif-->

            <div class="btn btn-outline-grey stationSearchButton" id="gps-button"
                 :title="i18n.get('_.stationboard.search-by-location')">
              <i class="fa fa-map-marker-alt"></i>
            </div>
          </div>
        </div>
        <div class="list-group collapse" id="last-stations">
          <!--                @if(Auth::user()->home)-->
          <a href="route('trains.stationboard', ['provider' => 'train', 'station' => Auth::user()->home->name ])"
             class="list-group-item list-group-item-action">
            <!--                   title="{{ Auth::user()->home->name }}" id="home-button"-->
            <i class="fa fa-home mr-2"></i> Auth::user()->home->name
          </a>
          <!--                @endif-->

          <!--                @if($latest->count())-->
          <span
              class="list-group-item title list-group-item-action disabled">{{
              i18n.get('_.stationboard.last-stations')
            }}</span>
          <!--                @endif-->
          <!--                @foreach($latest as $station)-->
          <!--                <a href="route('trains.stationboard', ['provider' => 'train', 'station' => $station->name ])"-->
          <!--                   title="{{ $station->name }}" id="home-button"-->
          <!--                   class="list-group-item list-group-item-action">-->
          <!--                  {{ $station->name }}-->
          <!--                </a>-->
          <!--                @endforeach-->
        </div>
        <button class="btn btn-outline-primary float-end" type="submit" v-on:click.prevent="submitStation('')">
          {{ i18n.get('_.stationboard.submit-search') }}
        </button>
        <button class="btn btn-outline-secondary" type="button" data-mdb-toggle="collapse"
                data-mdb-target="#collapseFilter" aria-expanded="false">
          {{ i18n.get('_.stationboard.filter-products') }}
        </button>
        <div class="collapse" id="collapseFilter">
          <div class="mt-3 d-flex justify-content-center">
            <div class="btn-group flex-wrap" role="group">
              <button class="btn btn-primary btn-sm" v-on:click="submitStation('ferry')">
                {{ i18n.get('_.transport_types.ferry') }}
              </button>
              <button class="btn btn-primary btn-sm" v-on:click="submitStation('bus')">
                {{ i18n.get('_.transport_types.bus') }}
              </button>
              <button class="btn btn-primary btn-sm" v-on:click="submitStation('tram')">
                {{ i18n.get('_.transport_types.tram') }}
              </button>
              <button class="btn btn-primary btn-sm" v-on:click="submitStation('subway')">
                {{ i18n.get('_.transport_types.subway') }}
              </button>
              <button class="btn btn-primary btn-sm" v-on:click="submitStation('suburban')">
                {{ i18n.get('_.transport_types.suburban') }}
              </button>
              <button class="btn btn-primary btn-sm" v-on:click="submitStation('regional')">
                {{ i18n.get('_.transport_types.regional') }}
              </button>
              <button class="btn btn-primary btn-sm" v-on:click="submitStation('express')">
                {{ i18n.get('_.transport_types.express') }}
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import moment from "moment";

export default {
  name: "StationForm",
  data() {
    return {
      station: null,
      when: moment().toISOString()
    };
  },
  mounted() {
    this.station = this.$route.query.station;
  },
  methods: {
    submitStation(travelType = null) {
      if (typeof travelType != "string") {
        return;
      }
      if (this.$route.query.when) {
        this.when = this.$route.query.when
      }

      if (this.station != null) {
        this.$router.push({
          name: "trains.stationboard",
          query: {station: this.station, travelType: travelType, when: this.when}
        })
            .then(() => {
              this.$emit("refresh");
            })
            .catch(() => {
              this.$emit("refresh");
            })
      } else {
        console.error("station null")
      }
    }
  }
}
</script>

<style scoped>

</style>
