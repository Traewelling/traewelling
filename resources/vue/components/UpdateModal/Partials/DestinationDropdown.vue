<script setup lang="ts">
import { PropType } from 'vue';
import { StopoverResource } from '../../../../types/Api.gen';

defineProps({
    stopovers: {
        type: Array as PropType<StopoverResource[]>,
        required: true,
    },
});
</script>

<template></template>

<style scoped lang="scss"></style>
