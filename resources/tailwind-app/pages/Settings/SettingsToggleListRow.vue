<script setup lang="ts">
import { Check } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = defineProps<{
    checked: boolean;
    title: string;
    description?: string;
}>();

const value = ref(props.checked);

const emits = defineEmits(['change']);

watch(value, (newValue) => {
    emits('change', newValue);
});
</script>

<template>
    <li class="list-row hover:bg-base-200 cursor-pointer">
        <div class="list-col-grow" @click="value = !value">
            <div class="font-semibold">{{ title }}</div>
            <div class="text-xs opacity-60" v-if="description">{{ description }}</div>
        </div>
        <div class="flex items-center">
            <label class="toggle text-base-content">
                <input type="checkbox" v-model="value" />
                <!-- empty svg to prevent the toggle from shrinking -->
                <svg class="hidden" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M0 0h24v24H0z" fill="none" />
                </svg>
                <Check class="size-4" />
            </label>
        </div>
    </li>
</template>
