<script>
import {trans} from "laravel-vue-i18n";

export default {
    name: "VisibilityDropdown",
    props: {
        startValue: {
            type: Number, // idk why I need this here. value did not update without it
            default: 0
        },
        disabled: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            visibility: this.startValue,
        };
    },
    methods: {
        trans,
        setVisibility(visibility) {
            this.visibility = visibility;
            this.$emit("update:model-value", visibility);
        }
    },
    computed: {
        visibilityIcon() {
            switch (this.visibility) {
                case 0:
                    return "fa fa-globe-americas";
                case 1:
                    return "fa fa-lock-open";
                case 2:
                    return "fa fa-user-friends";
                case 3:
                    return "fa fa-lock";
                case 4:
                    return "fa fa-user-check";
            }
        }
    },
    mounted() {
        this.visibility = this.startValue;
    },
    watch: {
        startValue(value) {
            this.visibility = value;
        }
    },
    emits: ["update:model-value"]
};
</script>

<template>
    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
            data-mdb-toggle="dropdown" aria-expanded="false" :disabled="disabled">
        <i :class="visibilityIcon" aria-hidden="true"></i>
    </button>
    <ul class="dropdown-menu" aria-labelledby="visibilityDropdownButton">
        <li class="dropdown-item" @click="setVisibility(0)">
            <i class="fa fa-globe-americas" aria-hidden="true"></i> {{ trans("status.visibility.0") }}
            <br>
            <span
                class="text-muted"> {{ trans("status.visibility.0.detail") }}</span>
        </li>
        <li class="dropdown-item" @click="setVisibility(1)">
            <i class="fa fa-lock-open" aria-hidden="true"></i> {{ trans("status.visibility.1") }}
            <br>
            <span class="text-muted"> {{ trans("status.visibility.1.detail") }}</span>
        </li>
        <li class="dropdown-item" @click="setVisibility(2)">
            <i class="fa fa-user-friends" aria-hidden="true"></i> {{ trans("status.visibility.2") }}
            <br>
            <span class="text-muted"> {{ trans("status.visibility.2.detail") }}</span>
        </li>
        <li class="dropdown-item" @click="setVisibility(3)">
            <i class="fa fa-lock" aria-hidden="true"></i> {{ trans("status.visibility.3") }}
            <br>
            <span class="text-muted"> {{ trans("status.visibility.3.detail") }}</span>
        </li>
        <li class="dropdown-item" @click="setVisibility(4)">
            <i class="fa fa-user-check" aria-hidden="true"></i> {{ trans("status.visibility.4") }}
            <br>
            <span class="text-muted"> {{ trans("status.visibility.4.detail") }}</span>
        </li>
    </ul>
</template>
