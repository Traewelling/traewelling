<script setup lang="ts">
import { TriangleAlert } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { computed } from 'vue';

const ALL_COLUMNS = [
    'status_id',
    'journey_type',
    'line_name',
    'journey_number',
    'origin_name',
    'origin_coordinates',
    'departure_planned',
    'departure_real',
    'destination_name',
    'destination_coordinates',
    'arrival_planned',
    'arrival_real',
    'duration',
    'distance',
    'points',
    'body',
    'travel_type',
    'status_tags',
    'operator',
] as const;

const NOMINAL_COLUMNS = [
    'status_id',
    'line_name',
    'origin_name',
    'departure_planned',
    'destination_name',
    'arrival_planned',
    'distance',
    'points',
    'body',
];

const props = defineProps<{
    modelValue: string[];
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string[]];
}>();

const showPdfWarning = computed(() => props.modelValue.length > 8);

function selectNominal() {
    emit('update:modelValue', [...NOMINAL_COLUMNS]);
}

function selectNominalAndTags() {
    emit('update:modelValue', [...NOMINAL_COLUMNS, 'status_tags']);
}

function selectAll() {
    emit('update:modelValue', [...ALL_COLUMNS]);
}

function toggleColumn(col: string) {
    if (props.modelValue.includes(col)) {
        emit(
            'update:modelValue',
            props.modelValue.filter((c) => c !== col),
        );
    } else {
        emit('update:modelValue', [...props.modelValue, col]);
    }
}
</script>

<template>
    <div>
        <p class="italic text-sm text-center mb-2">{{ trans('export.predefined') }}...</p>

        <div class="flex gap-2 justify-center flex-wrap mb-3">
            <button type="button" class="btn btn-sm btn-outline btn-primary" @click="selectNominal">
                {{ trans('export.nominal') }}
            </button>
            <button type="button" class="btn btn-sm btn-outline btn-primary" @click="selectNominalAndTags">
                {{ trans('export.nominal-tags') }}
            </button>
            <button type="button" class="btn btn-sm btn-outline btn-primary" @click="selectAll">
                {{ trans('export.all') }}
            </button>
        </div>

        <p class="italic text-sm text-center mb-2">...{{ trans('export.or-choose') }}:</p>

        <div class="border border-base-300 rounded-box overflow-y-auto max-h-64">
            <ul class="list bg-base-100">
                <li
                    v-for="col in ALL_COLUMNS"
                    :key="col"
                    class="list-row items-center cursor-pointer hover:bg-base-200 py-1 px-3"
                    @click="toggleColumn(col)"
                >
                    <input
                        type="checkbox"
                        :checked="modelValue.includes(col)"
                        class="checkbox checkbox-sm checkbox-primary"
                        tabindex="-1"
                        @click.stop
                        @change="toggleColumn(col)"
                    />
                    <span class="text-sm">{{ trans(`export.title.${col}`) }}</span>
                </li>
            </ul>
        </div>

        <div v-if="showPdfWarning" role="alert" class="alert alert-warning mt-3 py-2">
            <TriangleAlert class="w-4 h-4 shrink-0" />
            <span class="text-sm">{{ trans('export.pdf.many') }}</span>
        </div>
    </div>
</template>
