<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { PropType } from 'vue';
import { StatusVisibility } from '../../types/Api.gen';

const props = defineProps({
    class: {
        type: String,
        default: 'btn btn-sm btn-outline-secondary',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const model = defineModel({
    type: Number as PropType<StatusVisibility>,
    default: StatusVisibility.Value0,
});

function visibilityIcon(visibility: StatusVisibility): string {
    switch (visibility) {
        case StatusVisibility.Value0:
            return 'fa fa-globe-americas';
        case StatusVisibility.Value1:
            return 'fa fa-lock-open';
        case StatusVisibility.Value2:
            return 'fa fa-user-friends';
        case StatusVisibility.Value3:
            return 'fa fa-lock';
        case StatusVisibility.Value4:
            return 'fa fa-user-check';
        case StatusVisibility.Value5:
            return 'fa fa-user-shield';
        default:
            return '';
    }
}
</script>

<template>
    <button
        :class="props.class"
        type="button"
        class="dropdown-toggle"
        data-bs-toggle="dropdown"
        aria-expanded="false"
        :disabled="props.disabled"
    >
        <i :class="visibilityIcon(model)" aria-hidden="true" />
    </button>
    <ul class="dropdown-menu" aria-labelledby="visibilityDropdownButton">
        <li class="dropdown-item" @click="model = StatusVisibility.Value0">
            <i class="fa fa-globe-americas" aria-hidden="true" /> {{ trans('status.visibility.0') }}
            <br />
            <span class="text-muted"> {{ trans('status.visibility.0.detail') }}</span>
        </li>
        <li class="dropdown-item" @click="model = StatusVisibility.Value1">
            <i class="fa fa-lock-open" aria-hidden="true" /> {{ trans('status.visibility.1') }}
            <br />
            <span class="text-muted"> {{ trans('status.visibility.1.detail') }}</span>
        </li>
        <li class="dropdown-item" @click="model = StatusVisibility.Value2">
            <i class="fa fa-user-friends" aria-hidden="true" /> {{ trans('status.visibility.2') }}
            <br />
            <span class="text-muted"> {{ trans('status.visibility.2.detail') }}</span>
        </li>
        <li class="dropdown-item" @click="model = StatusVisibility.Value3">
            <i class="fa fa-lock" aria-hidden="true" /> {{ trans('status.visibility.3') }}
            <br />
            <span class="text-muted"> {{ trans('status.visibility.3.detail') }}</span>
        </li>
        <li class="dropdown-item" @click="model = StatusVisibility.Value4">
            <i class="fa fa-user-check" aria-hidden="true" /> {{ trans('status.visibility.4') }}
            <br />
            <span class="text-muted"> {{ trans('status.visibility.4.detail') }}</span>
        </li>
        <li class="dropdown-item" @click="model = StatusVisibility.Value5">
            <i class="fa fa-user-shield" aria-hidden="true" /> {{ trans('status.visibility.5') }}
            <br />
            <span class="text-muted"> {{ trans('status.visibility.5.detail') }}</span>
        </li>
    </ul>
</template>
