<script setup lang="ts">
import { Copy, RefreshCw } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { inject, ref } from 'vue';
import { Api } from '../../../../../types/Api.gen';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const notyf = inject('notyf') as Notyf;

const token = ref<string | null>(null);
const generating = ref(false);

async function generate() {
    generating.value = true;
    try {
        const response = await api.security.createToken();
        if (!response.ok) {
            notyf.error(trans('error'));
            return;
        }
        const data = await response.json();
        token.value = data.data?.token ?? null;
    } finally {
        generating.value = false;
    }
}

function copy() {
    if (!token.value) return;
    navigator.clipboard.writeText(token.value);
    notyf.success(trans('access-token-copied'));
}
</script>

<template>
    <div class="bg-base-100 rounded-box shadow p-4">
        <h3 class="font-semibold text-base mb-1">{{ trans('your-access-token') }}</h3>
        <p class="text-sm text-base-content/60 mb-3">{{ trans('your-access-token-description') }}</p>

        <div v-if="token" class="flex flex-col gap-2">
            <div role="alert" class="alert alert-warning text-sm py-2">
                {{ trans('access-token-remove-at') }}
            </div>
            <div class="flex gap-2 items-center">
                <code class="bg-base-200 rounded px-3 py-2 text-xs flex-1 break-all select-all">{{ token }}</code>
                <button class="btn btn-sm btn-ghost" @click="copy">
                    <Copy class="size-4" />
                </button>
            </div>
        </div>

        <div v-else>
            <button class="btn btn-outline btn-sm" :disabled="generating" @click="generate">
                <span v-if="generating" class="loading loading-spinner loading-xs"></span>
                <RefreshCw v-else class="size-4" />
                {{ trans('generate-token') }}
            </button>
        </div>

        <p class="text-xs text-error mt-3">{{ trans('your-access-token.ask') }}</p>
    </div>
</template>
