<script setup lang="ts">
import {getActiveLanguage} from "laravel-vue-i18n";
import {computed} from "vue";

const baseUrl = 'https://help.traewelling.de'
const props = defineProps({
  helpPage: {
    type: String,
    default: '/'
  }
});

const activeLanguage = getActiveLanguage();
let lang = '';
if (!activeLanguage.startsWith('de')) {
  lang = '/en';
}

const helpPageUrl = computed(() => {
  return `${baseUrl}${lang}${props.helpPage}`;
});

</script>

<template>
  <a :href="helpPageUrl" target="_blank" rel="noopener noreferrer">
    <slot></slot>
  </a>
</template>
