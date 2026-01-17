<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';

defineProps({
    name: {
        type: String,
        default: '',
    },
    options: {
        type: Object as PropType<SelectOption[]>,
        default: '',
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
        default: [],
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
                <input
                    :id="name"
                    v-model="model"
                    class="form-select"
                    :class="{ 'is-invalid': errors.length }"
                    :name="name"
                    :autocomplete="autocomplete"
                    :required="required"
                    :list="name + 'datalist'"
                />
                <datalist :id="name + 'datalist'">
                    <option v-for="option in options" :key="option" :value="option.value">
                        <template v-if="option.translationKey">
                            {{ trans(option.translationKey) }}
                        </template>
                        <template v-else>
                            {{ option.label ?? '' }}
                        </template>
                    </option>
                </datalist>
                <span v-for="error in errors" class="invalid-feedback" role="alert">
                    <strong>{{ error }}</strong>
                </span>
            </div>
        </div>
    </div>
</template>

<style scoped lang="scss"></style>
