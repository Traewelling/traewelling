<script lang="ts">
import {defineComponent} from 'vue'
import {trans} from "laravel-vue-i18n";

export default defineComponent({
    name: "Spinner",
    methods: {trans}
})
</script>

<template>
    <div class="d-flex justify-content-center p-2">
        <div style="max-width: 200px;" class="spinner-grow text-trwl mx-auto">
            <span class="visually-hidden">{{ trans('menu.loading') }}...</span>
        </div>
    </div>
</template>
