<script lang="ts">
import {defineComponent} from 'vue'
import {Area, ShortStation} from "../../../types/Station";

export default defineComponent({
  name: "AutocompleteListEntry",
  props: {
    station: {
      type: Object() as ShortStation,
      required: false
    },
    text: {
      type: String,
      required: false
    },
    prefix: {
      type: String,
      required: false
    }
  },
  methods: {
    getArea(): string {
      if (this.$props.station?.areas) {
        let defaultArea: null | Area = this.$props.station.areas.find((area: Area) => area.default);
        let country: null | Area = this.$props.station.areas.find((area: Area) => area.adminLevel === 2);
        if (defaultArea) {
          return country ? `${defaultArea.name}, ${country.name}` : defaultArea.name;
        }
        if (country) {
          return country.name;
        }
      }
      return '';
    }
  },
})
</script>

<template>
  <li class="list-group-item autocomplete-item">
    <a href="#" class="text-trwl">
      <i v-show="prefix" :class="prefix"></i>
      {{ station?.name || text }} <span v-if="station?.rilIdentifier">({{ station.rilIdentifier }})</span>
      <span class="text-sm text-muted overflow-hidden">{{ getArea() }}</span>
    </a>
  </li>
</template>

<style scoped lang="scss">
.autocomplete-item {
  background-color: var(--bs-modal-bg) !important;
  border: none;
  border-bottom: 1px solid var(--bs-light);
}

.autocomplete-item:last-child {
  border-bottom: none;
}
</style>
