<script setup lang="ts">
import { Moon, Sun, SunMoon } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { onMounted, onUnmounted, ref } from 'vue';
import DarkModeService, { DarkMode } from '../../../vue/services/DarkModeService';

const selectedMode = ref(DarkModeService.getMode());

const syncMode = () => {
    selectedMode.value = DarkModeService.getMode();
};

const selectMode = (mode: DarkMode) => {
    DarkModeService.setMode(mode);
};

onMounted(() => window.addEventListener('trwl:darkmode-change', syncMode));
onUnmounted(() => window.removeEventListener('trwl:darkmode-change', syncMode));
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
                <button type="button" :class="{ 'menu-active': selectedMode === 'light' }" @click="selectMode('light')">
                    <Sun class="inline-block w-4 h-4 mr-2" />
                    {{ trans('settings.colorscheme.light') }}
                </button>
            </li>
            <li>
                <button type="button" :class="{ 'menu-active': selectedMode === 'dark' }" @click="selectMode('dark')">
                    <Moon class="inline-block w-4 h-4 mr-2" />
                    {{ trans('settings.colorscheme.dark') }}
                </button>
            </li>
        </ul>
    </div>
</template>
