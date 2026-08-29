<script setup lang="ts">
import { Funnel, RotateCcw } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { Business, HafasTravelType } from '../../../../types/Api.gen';
import TransportIcon from '../../../components/TransportIcon.vue';
import { CATEGORY_ORDER, categoryColor } from '../categoryColors';
import { defaultFilterState, isFilterActive, RouteMapFilterState, TRAVEL_PURPOSES } from '../filters';

const props = defineProps<{
    modelValue: RouteMapFilterState;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: RouteMapFilterState];
}>();

function patch(partial: Partial<RouteMapFilterState>): void {
    emit('update:modelValue', { ...props.modelValue, ...partial });
}

function toggle<T>(list: T[], value: T): T[] {
    return list.includes(value) ? list.filter((entry) => entry !== value) : [...list, value];
}

function toggleTravelType(type: HafasTravelType): void {
    patch({ travelTypes: toggle(props.modelValue.travelTypes, type) });
}

function togglePurpose(purpose: Business): void {
    patch({ travelPurposes: toggle(props.modelValue.travelPurposes, purpose) });
}
</script>

<template>
    <div class="card bg-base-100">
        <div class="card-body gap-4 py-4">
            <div class="flex items-center justify-between gap-2">
                <h2 class="font-semibold flex items-center gap-2">
                    <Funnel class="size-4" />
                    {{ trans('filter.title') }}
                </h2>
                <button
                    class="btn btn-ghost btn-xs"
                    :disabled="!isFilterActive(modelValue)"
                    @click="emit('update:modelValue', defaultFilterState())"
                >
                    <RotateCcw class="size-3.5" />
                    {{ trans('filter.reset') }}
                </button>
            </div>

            <div>
                <p class="text-sm text-base-content/60 mb-2">{{ trans('filter.period') }}</p>
                <div class="flex flex-wrap gap-3">
                    <label class="flex min-w-0 grow flex-col">
                        <span class="mb-1 text-xs text-base-content/60">{{ trans('stats.from') }}</span>
                        <input
                            type="date"
                            class="input input-bordered input-sm w-full"
                            :value="modelValue.from"
                            :max="modelValue.until || undefined"
                            @change="patch({ from: ($event.target as HTMLInputElement).value })"
                        />
                    </label>
                    <label class="flex min-w-0 grow flex-col">
                        <span class="mb-1 text-xs text-base-content/60">{{ trans('stats.to') }}</span>
                        <input
                            type="date"
                            class="input input-bordered input-sm w-full"
                            :value="modelValue.until"
                            :min="modelValue.from || undefined"
                            @change="patch({ until: ($event.target as HTMLInputElement).value })"
                        />
                    </label>
                </div>
            </div>

            <div>
                <p class="text-sm text-base-content/60 mb-2">{{ trans('filter.travel-type') }}</p>
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="category in CATEGORY_ORDER"
                        :key="category"
                        class="btn btn-xs h-auto max-w-full gap-1.5 whitespace-normal py-1 text-left"
                        :class="modelValue.travelTypes.includes(category) ? 'btn-primary' : 'btn-outline'"
                        :aria-pressed="modelValue.travelTypes.includes(category)"
                        @click="toggleTravelType(category)"
                    >
                        <span
                            class="size-2 rounded-full shrink-0"
                            :style="{ backgroundColor: categoryColor(category) }"
                        />
                        <TransportIcon :product="category" />
                        {{ trans('transport_types.' + category) }}
                    </button>
                </div>
            </div>

            <div>
                <p class="text-sm text-base-content/60 mb-2">{{ trans('filter.travel-purpose') }}</p>
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="purpose in TRAVEL_PURPOSES"
                        :key="purpose.value"
                        class="btn btn-xs h-auto max-w-full whitespace-normal py-1 text-left"
                        :class="modelValue.travelPurposes.includes(purpose.value) ? 'btn-primary' : 'btn-outline'"
                        :aria-pressed="modelValue.travelPurposes.includes(purpose.value)"
                        @click="togglePurpose(purpose.value)"
                    >
                        {{ trans(purpose.label) }}
                    </button>
                </div>
            </div>

            <label class="flex cursor-pointer items-start gap-3">
                <input
                    type="checkbox"
                    class="toggle toggle-sm toggle-primary mt-0.5 shrink-0"
                    :checked="modelValue.includeApproximated"
                    @change="patch({ includeApproximated: ($event.target as HTMLInputElement).checked })"
                />
                <span class="flex min-w-0 flex-col">
                    <span class="text-sm">{{ trans('route-map.include-approximated') }}</span>
                    <span class="text-xs text-base-content/60">{{ trans('route-map.approximated.hint') }}</span>
                </span>
            </label>
        </div>
    </div>
</template>
