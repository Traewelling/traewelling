<script>
import FullScreenModal from "../FullScreenModal.vue";
import _ from "lodash";
import {trans} from "laravel-vue-i18n";
import AutocompleteListEntry from "../Checkin/AutocompleteListEntry.vue";
import {DateTime} from "luxon";

export default {
  name: "StationInput",
  components: {AutocompleteListEntry, FullScreenModal},
  props: {
    placeholder: {
      type: String,
    },
    arrival: {
      type: Boolean,
      default: true
    },
    departure: {
      type: Boolean,
      default: true
    }
  },
  emits: ['update:station', 'update:timeFieldA', 'update:timeFieldB', 'delete'],
  data() {
    return {
      timeFieldA: "--:--",
      timeFieldB: "--:--",
      station: null,
      stationInput: "",
      loading: false,
      autocompleteList: [],
      recent: [],
      id: "",
      pauseAutoComplete: false
    };
  },
  computed: {
    clearInput() {
      this.stationInput = "";
    },
    timeFieldALabel() {
      if (this.arrival && this.departure) {
        return trans("trip_creation.form.arrival");
      }
      return this.arrival ? trans("trip_creation.form.arrival") : trans("trip_creation.form.departure");
    },
    timeFieldBLabel() {
      if (this.arrival && this.departure) {
        return trans("trip_creation.form.departure");
      }
      return this.arrival ? trans("trip_creation.form.arrival") : trans("trip_creation.form.departure");
    },
    timeFieldAId() {
      return "timeFieldA" + this.id;
    },
    timeFieldBId() {
      return "timeFieldB" + this.id;
    },
  },
  methods: {
    formatTime(time) {
      let object = DateTime.fromISO(time);

      if (object.isValid) {
        return object.toFormat("HH:mm");
      }
      return "--:--";

    },
    showModal() {
      this.$refs.modal.show();
      // delay focus to make sure the modal is shown
      setTimeout(() => {
        this.$refs.stationInputField.focus();
      }, 100);
    },
    showModalFocusTime(fieldB = true) {
      this.$refs.modal.show();
      // delay focus to make sure the modal is shown
      setTimeout(() => {
        if (fieldB) {
          this.$refs.timeFieldB.focus();
        } else {
          this.$refs.timeFieldA.focus();
        }
      }, 100);
    },
    setStation(item) {
      this.stationInput = item.name;
      this.$emit('update:station', item);
      this.autocompleteList = [];
      this.pauseAutoComplete = true;
    },
    timeFieldAChanged(event) {
      this.$emit('update:timeFieldA', event.target.value)
      this.timeFieldA = this.formatTime(event.target.value);
    },
    timeFieldBChanged(event) {
      this.$emit('update:timeFieldB', event.target.value)
      this.timeFieldB = this.formatTime(event.target.value);
    },
    getRecent() {
      fetch(`/api/v1/trains/station/history`).then((response) => {
        response.json().then((result) => {
          this.recent = result.data;
        });
      });
    },
    autocomplete() {
      if (this.pauseAutoComplete) {
        this.pauseAutoComplete = false;
        return;
      }
      this.loading = true;
      if (!this.stationInput || this.stationInput.length < 3) {
        this.autocompleteList = [];
        this.loading = false;
        return;
      }
      let query = this.stationInput.replace(/%2F/, ' ').replace(/\//, ' ');
      fetch(`/api/v1/stations/?query=${query}`).then((response) => {
        response.json().then((result) => {
          this.autocompleteList = result.data;
          this.loading = false;
        });
      });
    }
  },
  mounted() {
    // I hate it, it's extremely ugly, but it works
    // see https://github.com/vuejs/vue/issues/5886
    // There is a plugin for this, but it's not worth it with only one component
    this.id = Math.random().toString().substring(2);
    this.getRecent();
  },
  watch: {
    stationInput: _.debounce(function () {
      this.autocomplete();
    }, 500),
  },
};
</script>

<template>
  <FullScreenModal ref="modal">
    <template #header>
      <h5>{{ placeholder }}</h5>
    </template>
    <template #body>
      <div class="input-group">
        <input v-model="stationInput"
               :placeholder="placeholder"
               class="form-control mobile-input-fs-16"
               name="station"
               type="text"
               ref="stationInputField"
        />
        <button class="btn btn-light" @click="clearInput" type="button">
          <i class="fa-solid fa-delete-left"></i>
        </button>
      </div>
      <ul class="list-group list-group-light list-group-small mb-2">
        <AutocompleteListEntry
            v-for="item in recent"
            v-show="stationInput.length <= 0"
            :station="item"
            @click="setStation(item)"
        />
        <AutocompleteListEntry
            v-for="item in autocompleteList"
            :station="item"
            @click="setStation(item)"
        />
      </ul>


      <!-- Time Fields -->
      <div class="row g-3 align-items-center justify-content-between mt-2" v-if="departure && arrival">
        <div class="col-auto">
          <label :for="timeFieldAId" class="col-form-label">{{ timeFieldALabel }}</label>
        </div>
        <div class="col-auto">
          <input
              :id="timeFieldAId"
              :aria-label="timeFieldALabel"
              :placeholder="timeFieldALabel"
              class="form-control mobile-input-fs-16"
              type="datetime-local"
              ref="timeFieldA"
              @input="timeFieldAChanged"
          >
        </div>
      </div>

      <div class="row g-3 align-items-center justify-content-between mt-2">
        <div class="col-auto">
          <label :for="timeFieldBId" class="form-label">{{ timeFieldBLabel }}</label>
        </div>
        <div class="col-auto">
          <input
              :id="timeFieldBId"
              :aria-label="timeFieldBLabel"
              :placeholder="timeFieldBLabel"
              class="form-control mobile-input-fs-16"
              type="datetime-local"
              ref="timeFieldB"
              @input="timeFieldBChanged"
          >
        </div>
      </div>
    </template>
  </FullScreenModal>

  <!-- Station Input -->
  <div class="input-group">
    <input type="text" class="form-control mb-2" :placeholder="placeholder"
           :aria-label="placeholder" aria-describedby="basic-addon1"
           v-model="stationInput" @focusin="showModal"
    >
    <span class="input-group-text font-monospace" v-if="departure && arrival" @click="showModalFocusTime(false)">
      {{ this.timeFieldA }}
    </span>
    <span class="input-group-text font-monospace" @click="showModalFocusTime">
      {{ this.timeFieldB }}
    </span>
    <button class="btn btn-sm btn-outline-danger input-group-button py-1" type="button" @click="$emit('delete')">
      <i class="fas fa-trash-alt"></i>
    </button>
  </div>
</template>

<style>
.autocomplete-item {
  background-color: var(--mdb-modal-bg) !important;
}

.input-group-button {
  height: calc(2.08rem + 2px);
}
</style>
