<script>
import { trans } from 'laravel-vue-i18n';
// todo: refactor like BusinessDropdown.vue
export default {
    name: 'VisibilityDropdown',
    props: {
        startValue: {
            type: Number, // idk why I need this here. value did not update without it
            default: 0,
        },
        disabled: {
            type: Boolean,
            default: false,
        },
        class: {
            type: String,
            default: 'btn btn-sm btn-outline-secondary',
        },
    },
    emits: ['update:model-value'],
    data() {
        return {
            visibility: this.startValue,
        };
    },
    computed: {
        visibilityIcon() {
            switch (this.visibility) {
                case 0:
                    return 'fa fa-globe-americas';
                case 1:
                    return 'fa fa-lock-open';
                case 2:
                    return 'fa fa-user-friends';
                case 3:
                    return 'fa fa-lock';
                case 4:
                    return 'fa fa-user-check';
                case 5:
                    return 'fa fa-user-shield';
            }
        },
    },
    watch: {
        startValue(value) {
            this.visibility = value;
        },
    },
    mounted() {
        this.visibility = this.startValue;
    },
    methods: {
        trans,
        setVisibility(visibility) {
            this.visibility = visibility;
            this.$emit('update:model-value', visibility);
        },
    },
};
</script>

<template>
    <button
        :class
        type="button"
        class="dropdown-toggle"
        data-bs-toggle="dropdown"
        aria-expanded="false"
        :disabled="disabled"
    >
        <i :class="visibilityIcon" aria-hidden="true" />
    </button>
    <ul class="dropdown-menu" aria-labelledby="visibilityDropdownButton">
        <li class="dropdown-item" @click="setVisibility(0)">
            <i class="fa fa-globe-americas" aria-hidden="true" /> {{ trans("status.visibility.0") }}
            <br>
            <span
                class="text-muted"
            > {{ trans("status.visibility.0.detail") }}</span>
        </li>
        <li class="dropdown-item" @click="setVisibility(1)">
            <i class="fa fa-lock-open" aria-hidden="true" /> {{ trans("status.visibility.1") }}
            <br>
            <span class="text-muted"> {{ trans("status.visibility.1.detail") }}</span>
        </li>
        <li class="dropdown-item" @click="setVisibility(2)">
            <i class="fa fa-user-friends" aria-hidden="true" /> {{ trans("status.visibility.2") }}
            <br>
            <span class="text-muted"> {{ trans("status.visibility.2.detail") }}</span>
        </li>
        <li class="dropdown-item" @click="setVisibility(3)">
            <i class="fa fa-lock" aria-hidden="true" /> {{ trans("status.visibility.3") }}
            <br>
            <span class="text-muted"> {{ trans("status.visibility.3.detail") }}</span>
        </li>
        <li class="dropdown-item" @click="setVisibility(4)">
            <i class="fa fa-user-check" aria-hidden="true" /> {{ trans("status.visibility.4") }}
            <br>
            <span class="text-muted"> {{ trans("status.visibility.4.detail") }}</span>
        </li>
        <li class="dropdown-item" @click="setVisibility(5)">
            <i class="fa fa-user-shield" aria-hidden="true" /> {{ trans("status.visibility.5") }}
            <br>
            <span class="text-muted"> {{ trans("status.visibility.5.detail") }}</span>
        </li>
    </ul>
</template>
