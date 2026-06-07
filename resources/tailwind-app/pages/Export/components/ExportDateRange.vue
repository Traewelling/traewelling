<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';

defineProps<{
    from: string;
    until: string;
}>();

const emit = defineEmits<{
    'update:from': [value: string];
    'update:until': [value: string];
}>();
</script>

<template>
    <div class="grid grid-cols-2 gap-3">
        <label class="form-control">
            <div class="label">
                <span class="label-text">{{ trans('export.begin') }}</span>
            </div>
            <input
                type="date"
                :value="from"
                class="input input-bordered input-sm"
                @change="emit('update:from', ($event.target as HTMLInputElement).value)"
            />
        </label>
        <label class="form-control">
            <div class="label">
                <span class="label-text">{{ trans('export.end') }}</span>
            </div>
            <input
                type="date"
                :value="until"
                class="input input-bordered input-sm"
                @change="emit('update:until', ($event.target as HTMLInputElement).value)"
            />
        </label>
    </div>
</template>
