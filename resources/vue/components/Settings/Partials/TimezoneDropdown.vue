<script lang="ts" setup>
import { trans } from 'laravel-vue-i18n';
import { ref, watch } from 'vue';

defineProps({
    name: {
        type: String,
        default: '',
    },
    title: {
        type: String,
        default: '',
    },
    errors: {
        type: Array,
        default: () => [],
    },
});

const model = defineModel<string>();
const timezones = ref<{ value: string; label: string }[]>([]);
const filteredTimezones = ref<{ value: string; label: string }[]>([]);
const search = ref('');

timezones.value = Intl.supportedValuesOf('timeZone').map((timezone) => {
    return { value: timezone, label: timezone };
});

function filterTimezones() {
    if (search.value.length === 0) {
        filteredTimezones.value = [];
        return;
    }
    const searchLower = search.value.toLowerCase();
    filteredTimezones.value = timezones.value.filter((timezone) => {
        return timezone.label.toLowerCase().includes(searchLower);
    });
    // maximum 10 results
    filteredTimezones.value = filteredTimezones.value.slice(0, 10);
}

function selectTimezone(timezone: string) {
    if (!timezone) {
        return;
    }
    model.value = timezone;
    filterTimezones();
}

watch(model, (newValue) => {
    search.value = newValue || '';
    filterTimezones();
});
</script>

<template>
    <div class="form-group row">
        <label :for="name" class="col-md-4 col-form-label text-md-right">
            {{ trans('user.timezone') }}
        </label>

        <div class="col-md-6">
            <div class="col btn-group me-1">
                <button
                    id="timezoneDropdown"
                    class="btn btn-sm btn-outline-primary dropdown-toggle"
                    type="button"
                    data-bs-dropdown-animation="off"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                >
                    {{ $props.modelValue }}
                </button>
                <div
                    aria-labelledby="timezoneDropdown"
                    class="dropdown-menu pt-0 mx-0 rounded-3 shadow overflow-hidden"
                >
                    <form class="p-2 mb-2 border-bottom">
                        <input
                            v-model="search"
                            type="search"
                            class="form-control mobile-input-fs-16"
                            autocomplete="off"
                            :placeholder="trans('user.timezone')"
                            @input="filterTimezones"
                            @keydown.enter="selectTimezone(filteredTimezones[0]?.value ?? '')"
                        />
                    </form>
                    <ul v-if="filteredTimezones.length > 0" class="list-unstyled mb-0">
                        <li v-for="timezone in filteredTimezones" :key="timezone?.value">
                            <a
                                href="#"
                                class="dropdown-item d-flex align-items-center gap-2 py-2"
                                @click="selectTimezone(timezone?.value)"
                            >
                                <div class="flex-grow-1">
                                    <div class="fw-bold">{{ timezone?.label }}</div>
                                </div>
                            </a>
                        </li>
                    </ul>
                    <div v-else class="p-2 mb-0 text-center text-muted" />
                </div>
            </div>
            <span v-for="error in errors" :key="error" class="invalid-feedback" role="alert">
                <strong>{{ error }}</strong>
            </span>
        </div>
    </div>
</template>
