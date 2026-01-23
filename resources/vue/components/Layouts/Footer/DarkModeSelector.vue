<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { ref } from 'vue';
import DarkModeService, { DarkMode } from '../../../services/DarkModeService';

const selectedMode = ref(DarkModeService.getMode());

const selectMode = (mode: DarkMode) => {
    DarkModeService.setMode(mode);
    selectedMode.value = mode;
};
</script>

<template>
    <div class="btn-group dropup w-100">
        <button
            type="button"
            class="btn btn-primary btn-block dropdown-toggle"
            data-bs-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false"
        >
            <i class="fas fa-circle-half-stroke"></i>
            {{ trans('settings.colorscheme.set') }}
        </button>
        <div class="dropdown-menu">
            <a
                href="#"
                class="dropdown-item"
                :class="{ active: selectedMode === 'light' }"
                @click.prevent="selectMode('light')"
            >
                <i class="fas fa-sun"></i>
                {{ trans('settings.colorscheme.light') }}
            </a>
            <a
                href="#"
                class="dropdown-item"
                :class="{ active: selectedMode === 'dark' }"
                @click.prevent="selectMode('dark')"
            >
                <i class="fas fa-moon"></i>
                {{ trans('settings.colorscheme.dark') }}
            </a>
            <a
                href="#"
                class="dropdown-item"
                :class="{ active: selectedMode === 'auto' }"
                @click.prevent="selectMode('auto')"
            >
                <i class="fas fa-circle-half-stroke"></i>
                {{ trans('settings.colorscheme.auto') }}
            </a>
        </div>
    </div>
</template>
