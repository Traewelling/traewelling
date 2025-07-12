<script setup lang="ts">
import {PropType} from "vue";
import {DateTime} from "luxon";

const model = defineModel({
  type: Object as PropType<Date | null>,
  default: () => null
});

const emits = defineEmits(['update:model-value']);

defineProps({
  class: {
    type: String,
    default: 'form-control'
  },
  placeholder: {
    type: String,
    default: ''
  }
})

function setModel(value: string | null) {
  console.log('Setting model value:', value);
  if (!value) {
    model.value = null;
    return;
  }
  const date = DateTime.fromISO(value);
  if (!date.isValid) {
    console.error('Invalid date format:', value);
    return;
  }
  console.log(date, date.toISO().slice(0, 16));


  model.value = date.toJSDate();
}
</script>

<template>
  <input :value="model ? DateTime.fromJSDate(model).toISO()?.slice(0,16) : ''"
         :class
         @change="setModel($event?.target?.value)"
         type="datetime-local"
         :placeholder="placeholder"
  />
</template>
