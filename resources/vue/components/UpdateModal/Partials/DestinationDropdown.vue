<script setup lang="ts">
import { StopoverResource } from '../../../../types/Api.gen';
import { PropType } from 'vue';

defineProps({
    stopovers: {
        type: Array as PropType<StopoverResource[]>,
        required: true,
    },
});
</script>

<template></template>

<style scoped lang="scss"></style>
