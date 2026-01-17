<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { PropType } from 'vue';
import { SelectOption } from './SelectOption';

defineProps({
    name: {
        type: String,
        default: '',
    },
    options: {
        type: Array as PropType<SelectOption[]>,
        default: () => [],
    },
    title: {
        type: String,
        default: '',
    },
    prefix: {
        type: String,
        default: '',
    },
    errors: {
        type: Array,
        default: () => [],
    },
    autocomplete: {
        type: String,
        default: '',
    },
    required: {
        type: Boolean,
        default: false,
    },
});

const model = defineModel();
</script>

<template>
    <div class="form-group row">
        <label :for="name" class="col-md-4 col-form-label text-md-right">
            {{ title }}
        </label>

        <div class="col-md-6">
            <div :class="{ 'input-group': !!prefix }">
                <span v-if="prefix" class="input-group-text">{{ prefix }}</span>
                <select
                    :id="name"
                    v-model="model"
                    class="form-select"
                    :class="{ 'is-invalid': errors.length }"
                    :name="name"
                    :autocomplete="autocomplete"
                    :required="required"
                >
                    <option v-for="option in options" :key="option" :value="option.value">
                        <template v-if="option.translationKey">
                            {{ trans(option.translationKey) }}
                        </template>
                        <template v-else>
                            {{ option.label ?? '' }}
                        </template>
                    </option>
                </select>
                <span v-for="error in errors" :key="error" class="invalid-feedback" role="alert">
                    <strong>{{ error }}</strong>
                </span>
            </div>
        </div>
    </div>
</template>
