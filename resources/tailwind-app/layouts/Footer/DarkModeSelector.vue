<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Moon, Sun, SunMoon } from 'lucide-vue-next';
import { ref } from 'vue';
import DarkModeService, { DarkMode } from '../../../vue/services/DarkModeService';

const selectedMode = ref(DarkModeService.getMode());

const selectMode = (mode: DarkMode) => {
    DarkModeService.setMode(mode);
    selectedMode.value = mode;
};
</script>

<template>
    <div class="dropdown dropdown-top w-full">
        <div tabindex="0" role="button" class="btn w-full btn-secondary">
            <SunMoon class="inline-block w-4 h-4 mr-2" />
            {{ trans('settings.colorscheme.set') }}
        </div>
        <ul
            tabindex="-1"
            class="dropdown-content menu bg-base-100 rounded-box z-1 w-52 p-2 shadow-sm text-base-content"
        >
            <li>
                <a href="#" :class="{ active: selectedMode === 'light' }" @click="selectMode('light')">
                    <Sun class="inline-block w-4 h-4 mr-2" />
                    {{ trans('settings.colorscheme.light') }}
                </a>
            </li>
            <li>
                <a href="#" :class="{ active: selectedMode === 'dark' }" @click="selectMode('dark')">
                    <Moon class="inline-block w-4 h-4 mr-2" />
                    {{ trans('settings.colorscheme.dark') }}
                </a>
            </li>
        </ul>
    </div>
    <input type="checkbox" class="hidden theme-controller" value="dark" :checked="selectedMode === 'dark'" />
    <input type="checkbox" class="hidden theme-controller" value="light" :checked="selectedMode === 'light'" />
</template>
