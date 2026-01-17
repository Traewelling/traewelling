<script setup lang="ts">
import { PropType } from 'vue';
import { Business } from '../../types/Api.gen';
import { IconHelper } from '../helpers/IconHelper';
import { trans } from 'laravel-vue-i18n';

defineProps({
    class: {
        type: String,
        default: 'btn btn-sm btn-link px-2',
    },
});

const model = defineModel({
    type: Object as PropType<Business>,
    default: Business.Value0,
});
</script>

<template>
    <button
        id="businessDropdownButton"
        class="dropdown-toggle"
        :class
        type="button"
        data-bs-dropdown-animation="off"
        data-bs-toggle="dropdown"
        aria-expanded="false"
    >
        <i class="fas" :class="IconHelper.getBusinessIcon(model)" />
    </button>
    <ul id="businessDropdown" class="dropdown-menu" aria-labelledby="businessDropdownButton">
        <li class="dropdown-item" @click="model = Business.Value0">
            <i class="fa fa-user" /> {{ trans(IconHelper.getBusinessTitle(Business.Value0)) }}
        </li>
        <li class="dropdown-item" @click="model = Business.Value1">
            <i class="fa fa-briefcase" /> {{ trans(IconHelper.getBusinessTitle(Business.Value1)) }}
            <br />
            <span class="text-muted"> {{ trans(IconHelper.getBusinessDescription(Business.Value1)) }}</span>
        </li>
        <li class="dropdown-item" @click="model = Business.Value2">
            <i class="fa fa-building" /> {{ trans(IconHelper.getBusinessTitle(Business.Value2)) }}
            <br />
            <span class="text-muted"> {{ trans(IconHelper.getBusinessDescription(Business.Value2)) }}</span>
        </li>
    </ul>
</template>
