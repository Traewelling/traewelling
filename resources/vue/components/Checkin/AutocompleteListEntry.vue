<script lang="ts">
import {defineComponent} from 'vue'
import {ShortStation} from "../../../types/Station";

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
})
</script>

<template>
  <li class="list-group-item autocomplete-item">
    <a href="#" class="text-trwl">
      <i v-show="prefix" :class="prefix"></i>
      {{ station?.name || text }} <span v-if="station?.rilIdentifier">({{ station.rilIdentifier }})</span>
    </a>
  </li>
</template>

<style scoped lang="scss">
.autocomplete-item {
  background-color: var(--mdb-modal-bg) !important;
}
</style>
