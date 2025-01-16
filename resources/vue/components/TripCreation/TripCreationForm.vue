<script>
import {DateTime} from "luxon";
import {trans} from "laravel-vue-i18n";
import StationInput from "./StationInput.vue";
import TripCreationMap from "./TripCreationMap.vue";

export default {
  name: "TripCreationForm",
  components: {TripCreationMap, StationInput},
  mounted() {
    this.initForm();
    this.loadOperators();
    this.getOriginFromQuery();
  },
  data() {
    return {
      form: {
        originId: "",
        originDeparturePlanned: "",
        destinationId: "",
        destinationArrivalPlanned: "",
        lineName: "",
        journeyNumber: 0,
        operatorId: "",
        category: {},
        stopovers: [],
      },
      tripDataActive: true,
      originTimezone: "Europe/Berlin",
      destinationTimezone: "Europe/Berlin",
      stopovers: [],
      origin: {},
      destination: {},
      journeyNumberInput: "",
      trainTypeInput: "",
      selectedCategory: {},
      selectedOperator: null,
      categories: [
        {value: "nationalExpress", text: "nationalExpress", emoji: "🚄"},
        {value: "national", text: "national", emoji: "🚅"},
        {value: "regionalExp", text: "regionalExpress", emoji: "🚆"},
        {value: "regional", text: "regional", emoji: "🚞"},
        {value: "suburban", text: "suburban", emoji: "🚋"},
        {value: "bus", text: "bus", emoji: "🚌"},
        {value: "ferry", text: "ferry", emoji: "⛴"},
        {value: "subway", text: "subway", emoji: "🚇"},
        {value: "tram", text: "tram", emoji: "🚊"},
        {value: "taxi", text: "taxi", emoji: "🚖"},
        {value: "plane", text: "plane", emoji: "✈️"},
      ],
      operators: [],
      disallowed: ["fahrrad", "auto", "fuss", "fuß", "foot", "car", "bike"],
      showDisallowed: false,
      validation: {
        times: null
      }
    };
  },
  methods: {
    trans,
    addStopover() {
      const dummyStopover = {
        station: {
          name: "",
          id: "",
        },
        departurePlanned: "",
        arrivalPlanned: "",
      };
      this.stopovers.push(dummyStopover);
    },
    showData() {
      this.tripDataActive = true;
    },
    showMap() {
      this.tripDataActive = false;
      this.$refs.map.invalidateSize();
    },
    removeStopover(index) {
      this.$refs.map.removeMarker(index);
      this.stopovers.splice(index, 1);
      this.validateTimes(); // Optional: Zeiten erneut validieren
    },
    setOrigin(item) {
      this.$refs.map.addMarker(item, "origin", this.stopovers.length);
      this.origin = item;
      this.form.originId = item.id;
    },
    setDeparture(time) {
      this.form.originDeparturePlanned = DateTime.fromISO(time).setZone(this.originTimezone);
      this.validateTimes();
    },
    setDestination(item) {
      this.$refs.map.addMarker(item, "destination", this.stopovers.length);
      this.destination = item;
      this.form.destinationId = item.id;
    },
    setArrival(time) {
      this.form.destinationArrivalPlanned = DateTime.fromISO(time).setZone(this.destinationTimezone);
      this.validateTimes();
    },
    validateTimes() {
      console.log("Validating times");
      //iterate over stopovers and destination, check if time is valid
      let time = DateTime.fromISO(this.form.originDeparturePlanned);

      this.stopovers.forEach((stopover) => {
        if (time > DateTime.fromISO(stopover.departurePlanned)) {
          this.validation.times = false;
          return false;
        }
        time = DateTime.fromISO(stopover.arrivalPlanned);
      });

      if (time > DateTime.fromISO(this.form.destinationArrivalPlanned)) {
        this.validation.times = false;
        return false;
      }
      this.validation.times = true;
      return true;
    },
    sendForm() {
      if (!this.validateTimes()) {
        notyf.error(trans("trip_creation.no-valid-times"));
        return;
      }

      this.form.lineName = this.trainTypeInput;
      this.form.journeyNumber = !isNaN(this.journeyNumberInput) && !isNaN(parseInt(this.journeyNumberInput))
          ? parseInt(this.journeyNumberInput) : null;
      this.form.stopovers = this.stopovers.map((stopover) => {
        return {
          stationId: stopover.station.id,
          departure: stopover.departurePlanned,
          arrival: stopover.arrivalPlanned,
        };
      });
      this.form.category = this.selectedCategory.value;

      fetch("/api/v1/trains/trip", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(this.form),
      }).then((data) => {
        if (data.ok) {
          data.json().then((result) => {
            result = result.data;
            let query = {
              tripId: result.id,
              lineName: result.lineName,
              start: result.origin.id,
              departure: this.form.originDeparturePlanned,
              idType: 'trwl'
            };

            window.location.href = `/stationboard?${new URLSearchParams(query).toString()}`;
          });
        } else if (data.status === 403 || data.status === 422) {
          data.json().then((result) => {
            notyf.error(result.message);
          });
        } else {
          notyf.error(trans("messages.exception.general-values"));
        }
      });
    },
    setStopoverStation(item, key) {
      this.$refs.map.addMarker(item, key, this.stopovers.length);
      this.stopovers[key].station = item;
    },
    setStopoverDeparture(time, key) {
      this.stopovers[key].departurePlanned = DateTime.fromISO(time).setZone(this.originTimezone);
      this.validateTimes();
    },
    setStopoverArrival(time, key) {
      this.stopovers[key].arrivalPlanned = DateTime.fromISO(time).setZone(this.destinationTimezone);
      this.validateTimes();
    },
    checkDisallowed() {
      this.showDisallowed = this.disallowed.some((disallowed) => {
        return this.trainTypeInput.toLowerCase().includes(disallowed);
      });
    },
    guessModeOfTransport() {
      // todo: guess mode of transport based on line input
      // e.g.: if line starts with ICE or TGV, set category to nationalExpress
    },
    getOriginFromQuery() {
      const urlParams = new URLSearchParams(window.location.search);
      const stationId = urlParams.get("from");

      if (stationId) {
        fetch(`/api/v1/stations/${stationId}`, {
          method: "GET",
          headers: {
            "Content-Type": "application/json",
          },
        })
            .then((response) => {
              if (!response.ok) {
                throw new Error(response.statusText);
              }
              return response.json();
            })
            .then((result) => {
              console.log(result.data);
              this.$refs.originInput.setStation(result.data);
            })
            .catch((error) => {
              console.error(error);
            });
      }
    },
    onLineInput() {
      this.checkDisallowed()
      this.guessModeOfTransport();
    },
    loadOperators(cursor = null) {
      fetch("/api/v1/operators?cursor=" + cursor, {
        method: "GET",
        headers: {
          "Content-Type": "application/json",
        },
      })
          .then((response) => {
            if (!response.ok) {
              throw new Error(response.statusText);
            }
            return response.json();
          })
          .then((result) => {
            this.operators.push(...result.data);

            if (result.meta.next_cursor) {
              this.loadOperators(result.meta.next_cursor);
            }
          })
          .catch((error) => {
            console.error(error);
          });
    },
    initForm() {
      this.selectedCategory = this.categories[0];
    },
    onChangeCat(event) {
      console.log(event);
      console.log(this.selectedCategory)
    }
  }
}
</script>

<template>
  <div class="row mt-n4 mb-4 border-bottom d-block d-md-none">
    <ul class="nav nav-tabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link" :class="{'active': tripDataActive}" @click="showData">
          {{ trans('trip_creation.form.trip_data') }}
        </button>
      </li>
      <li class="nav-item" role="presentation" @click="showMap">
        <button class="nav-link" :class="{'active': !tripDataActive}">
          {{ trans('trip_creation.form.map') }}
        </button>
      </li>
    </ul>
  </div>
  <div class="row full-height mt-n4 mx-0">
    <div class="col d-md-block col-md-5 col-lg-4 col-xl-3 p-0 h-100" :class="{'d-none': !tripDataActive}">
      <div class="accordion accordion-flush border-bottom" id="TripCreationMetaDataAccordion">
        <div class="accordion-item">
          <h2 class="accordion-header" id="accordionTripInfo">
            <button class="accordion-button collapsed" type="button" data-mdb-toggle="collapse"
                    data-mdb-target="#collapseTripInfo" aria-expanded="false" aria-controls="collapseTripInfo">
              <div class="d-flex justify-start w-100">
                <i class="fa-solid fa-list-check"></i>
                <span class="d-flex justify-content-between w-100 px-2">
                  <span class="fw-bold" v-if="!trainTypeInput.length">
                    {{ trans('trip_creation.form.trip_data') }}
                  </span>
                  <span class="fw-bold" v-else>
                    {{ trainTypeInput }}
                    <span class="fw-lighter fst-italic text-secondary">{{ journeyNumberInput }}</span>
                  </span>
                </span>
              </div>
            </button>
          </h2>
          <div id="collapseTripInfo" class="accordion-collapse collapse" aria-labelledby="accordionTripInfo"
               data-mdb-parent="#accordionTripInfo">
            <div class="accordion-body">
              <input type="text" class="form-control mb-2" :placeholder="trans('trip_creation.form.line')"
                     :aria-label="trans('trip_creation.form.line')" aria-describedby="basic-addon1"
                     v-model="trainTypeInput" @focusout="onLineInput"
              >
              <input type="text" class="form-control" :placeholder="trans('trip_creation.form.number')"
                     :aria-label="trans('trip_creation.form.number')" aria-describedby="basic-addon1"
                     v-model="journeyNumberInput"
              >
              <div class="alert alert-danger mt-2" v-show="showDisallowed">
                <i class="fas fa-triangle-exclamation"></i>
                {{ trans('trip_creation.limitations.6') }}
                <a :href="trans('trip_creation.limitations.6.link')" target="_blank">
                  {{ trans('trip_creation.limitations.6.rules') }}
                </a>
              </div>
            </div>
          </div>
        </div>

        <div class="accordion-item">
          <h2 class="accordion-header" id="accordionTripCategory">
            <button class="accordion-button collapsed" type="button" data-mdb-toggle="collapse"
                    data-mdb-target="#collapseTripCategory" aria-expanded="false" aria-controls="collapseTripCategory">
              <div class="d-flex justify-start w-100">
                {{ this.selectedCategory.emoji }}
                <span class="d-flex justify-content-between w-100 px-2">
                  <span class="fw-bold">{{ trans('trip_creation.form.travel_type') }}</span>
                  <span>{{ trans("transport_types." + this.selectedCategory.value) }}</span>
                </span>
              </div>
            </button>
          </h2>
          <div id="collapseTripCategory" class="accordion-collapse collapse" aria-labelledby="accordionTripCategory"
               data-mdb-parent="#accordionTripCategory">
            <div class="accordion-body">
              <ul class="list-group">
                <li v-for="item in categories" class="list-group-item">
                  <input type="radio" class="form-check-input me-1" name="categoryRadio" :id="item.value" :value="item"
                         v-model="selectedCategory" @change="onChangeCat">
                  <label class="form-check-label stretched-link" :for="item.value">
                    {{ item.emoji }} {{ trans("transport_types." + item.value) }}
                  </label>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <div class="accordion-item">
          <h2 class="accordion-header" id="accordionTripOperator">
            <button class="accordion-button collapsed" type="button" data-mdb-toggle="collapse"
                    data-mdb-target="#collapseTripOperator" aria-expanded="false" aria-controls="collapseTripOperator">
              <div class="d-flex justify-start w-100">
                <i class="fa-solid fa-building"></i>
                <span class="d-flex justify-content-between w-100 px-2">
                  <span class="fw-bold" v-if="selectedOperator == null">
                    {{ trans('export.title.operator') }}
                  </span>
                  <span class="fw-bold" v-else>
                    {{ selectedOperator.name }}
                  </span>
                </span>
              </div>
            </button>
          </h2>
          <div id="collapseTripOperator" class="accordion-collapse collapse" aria-labelledby="accordionTripOperator"
               data-mdb-parent="#accordionTripOperator">
            <div class="accordion-body">
              <!-- todo: make searchable -->
              <select class="form-select" v-model="selectedOperator">
                <option selected>-/-</option>
                <option v-for="operator in operators" :value="operator">{{ operator.name }}</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <form @submit.prevent="sendForm" class="px-4 mt-4">
        <StationInput
            ref="originInput"
            :placeholder="trans('trip_creation.form.origin')"
            :arrival="false"
            v-on:update:station="setOrigin"
            v-on:update:timeFieldB="setDeparture"
        ></StationInput>

        <div class="row g-3 mt-1" v-for="(stopover, key) in stopovers" :key="key">
          <div class="d-flex align-items-center w-100">
            <div class="flex-grow-1 d-flex">
              <StationInput
                  :placeholder="trans('trip_creation.form.stopover')"
                  v-on:update:station="setStopoverStation($event, key)"
                  v-on:update:timeFieldB="setStopoverDeparture($event, key)"
                  v-on:update:timeFieldA="setStopoverArrival($event, key)"
                  v-on:delete="removeStopover(key)"
              ></StationInput>
            </div>
          </div>
        </div>

        <div class="mb-2 px-3">
          <a href="#" @click="addStopover">{{ trans("trip_creation.form.add_stopover") }}
            <i class="fa fa-plus" aria-hidden="true"></i>
          </a>
        </div>

        <StationInput
            :placeholder="trans('trip_creation.form.destination')"
            :arrival="true"
            :departure="false"
            v-on:update:station="setDestination"
            v-on:update:timeFieldB="setArrival"
        ></StationInput>

        <div class="mt-4 border-top pt-4 d-flex justify-content-end">
          <button type="submit" class="btn btn-primary">
            {{ trans("trip_creation.form.save") }}
          </button>
        </div>

      </form>

      <div class="alert alert-warning m-2">
        <h2 class="fs-5">
          <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
          {{ trans("trip_creation.limitations") }}
        </h2>

        <ul>
          <li>{{ trans("trip_creation.limitations.1") }}</li>
          <li>
            {{ trans("trip_creation.limitations.2") }}
            <small>{{ trans("trip_creation.limitations.2.small") }}</small>
          </li>
          <li>{{ trans("trip_creation.limitations.3") }}</li>
          <li>{{ trans("trip_creation.limitations.5") }}</li>
        </ul>

        <p class="fw-bold text-danger">
          {{ trans("trip_creation.limitations.6") }}
          <a :href="trans('trip_creation.limitations.6.link')" target="_blank">
            {{ trans('trip_creation.limitations.6.rules') }}
          </a>
        </p>
      </div>

    </div>
    <div class="col d-md-block bg-warning px-0" :class="{'d-none': tripDataActive}">
      <TripCreationMap ref="map"></TripCreationMap>
    </div>
  </div>
</template>

<style scoped>
.full-height {
  min-height: 90vh;
}
</style>
