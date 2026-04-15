<template>
    <div class="relative">
        <div v-if="loading" class="flex justify-center items-center" :style="{ minHeight: minHeight + 'px' }">
            <span class="loading loading-spinner loading-lg"></span>
        </div>
        <iframe
            ref="iframe"
            :src="src"
            :style="{ height: iframeHeight + 'px', minHeight: minHeight + 'px' }"
            class="w-full border-0"
            :class="{ 'invisible absolute': loading }"
            @load="onLoad"
        ></iframe>
    </div>
</template>

<script setup lang="ts">
import { onMounted, onUnmounted, ref, watch } from 'vue';
import DarkModeService from '../../vue/services/DarkModeService';

const props = withDefaults(
    defineProps<{
        src: string;
        minHeight?: number;
    }>(),
    {
        minHeight: 400,
    },
);

const iframe = ref<HTMLIFrameElement | null>(null);
const iframeHeight = ref(props.minHeight);
const loading = ref(true);
let initialLoadDone = false;

function onMessage(event: MessageEvent) {
    if (event.data && event.data.type === 'trwl-embed-resize') {
        iframeHeight.value = event.data.height;
    }
}

function syncDarkMode() {
    if (iframe.value?.contentWindow) {
        iframe.value.contentWindow.postMessage({ type: 'trwl-embed-darkmode', mode: DarkModeService.getMode() }, '*');
    }
}

function onDarkModeChange() {
    syncDarkMode();
}

function onLoad() {
    if (!initialLoadDone) {
        initialLoadDone = true;
        loading.value = false;
        syncDarkMode();
        return;
    }

    // Fallback for browsers without Navigation API (Firefox/Safari):
    // iframe navigated via JS (window.location) — redirect parent to the new URL
    try {
        const iframeHref = iframe.value?.contentWindow?.location.href;
        if (iframeHref) {
            const url = new URL(iframeHref);
            if (!url.pathname.startsWith('/embed/')) {
                window.location.href = url.pathname + url.search + url.hash;
            }
        }
    } catch {
        // cross-origin or access error, ignore
    }
}

onMounted(() => {
    window.addEventListener('message', onMessage);
    window.addEventListener('trwl:darkmode-change', onDarkModeChange);
});

onUnmounted(() => {
    window.removeEventListener('message', onMessage);
    window.removeEventListener('trwl:darkmode-change', onDarkModeChange);
});

watch(
    () => props.src,
    () => {
        loading.value = true;
    },
);
</script>
