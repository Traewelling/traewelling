<script setup lang="ts">

defineProps({
    name: {
        type: String,
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
            <div :class="{'input-group': !!prefix}">
                <span v-if="prefix" class="input-group-text">{{ prefix }}</span>
                <input
                    :id="name"
                    v-model="model"
                    type="text"
                    class="form-control"
                    :class="{'is-invalid': errors.length}"
                    :name="name"
                    :autocomplete="autocomplete"
                    :required="required"
                >
                <span v-for="error in errors" class="invalid-feedback" role="alert">
                    <strong>{{ error }}</strong>
                </span>
            </div>
        </div>
    </div>
</template>

<style scoped lang="scss">

</style>
