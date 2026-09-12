<script setup lang="ts">
import { Trash2 } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { computed, ref } from 'vue';
import { Api, TokenResource } from '../../../../../types/Api.gen';
import SettingsListRow from '../../SettingsListRow.vue';

const props = defineProps<{
    tokens: TokenResource[];
}>();
const emits = defineEmits(['tokens-updated', 'error']);

const modal = ref<HTMLDialogElement>();
const api = new Api({ baseUrl: window.location.origin + '/api' });

function removeToken(tokenId: string) {
    api.security
        .revokeToken(tokenId)
        .then(() => {
            // Update local tokens and emit the updated list
            emits(
                'tokens-updated',
                props.tokens.filter((t) => t.id !== tokenId),
            );
        })
        .catch((error) => {
            emits('error', error.error.message);
        });
}

function removeAllTokens() {
    api.security
        .revokeAllTokens()
        .then(() => {
            emits('tokens-updated', []);
        })
        .catch((error) => {
            emits('error', error.error.message);
        });
}

function scopeLabel(scope: string): string {
    return scope === '*' ? trans('scopes.all') : trans('scopes.' + scope);
}

/**
 * How long the application keeps its access without the user doing anything: the refresh token
 * expiry whenever one is still valid, because the client renews the short lived access token on
 * its own. The access token expiry itself is a technical detail that reads as already expired
 * for most entries and only confuses.
 */
function accessUntil(token: TokenResource): string {
    const until = token.refreshExpiresAt ?? token.expiresAt;

    return until ? new Date(until).toLocaleString() : trans('settings.never');
}

const groupedTokens = computed(() => {
    const map = new Map<string, TokenResource[]>();

    for (const t of props.tokens) {
        const client = t.client || 'Unknown';
        if (!map.has(client)) map.set(client, []);
        map.get(client)!.push(t);
    }

    return Array.from(map.entries()).map(([client, list]) => ({ client, tokens: list }));
});
</script>

<template>
    <SettingsListRow
        :title="trans('settings.title-tokens')"
        :description="trans('settings.tokens.description')"
        @click.prevent="modal?.showModal()"
    />
    <dialog ref="modal" class="modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold mb-4">{{ trans('settings.title-tokens') }}</h3>
            <ul class="list">
                <li v-if="tokens.length === 0">
                    {{ trans('settings.no-tokens') }}
                </li>
                <!-- Render grouped tokens by client -->
                <li v-for="group in groupedTokens" :key="group.client" class="mb-4">
                    <h6 class="mb-2 font-semibold">{{ group.client }}</h6>
                    <ul>
                        <li v-for="token in group.tokens" :key="token.id" class="list-row">
                            <div class="list-col-grow">
                                <p class="mb-0 opacity-75">
                                    {{ trans('settings.scopes') }}:
                                    {{ token.scopes.map(scopeLabel).join(', ') }}
                                </p>
                                <p class="mb-0 opacity-75">
                                    {{ trans('settings.tokens.access-until') }}:
                                    {{ accessUntil(token) }}
                                </p>
                            </div>
                            <button role="button" class="btn btn-sm btn-error" @click="removeToken(token.id)">
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </li>
                    </ul>
                </li>
            </ul>
            <div class="modal-action w-full">
                <button v-if="tokens.length > 0" class="btn btn-outline btn-error me-auto" @click="removeAllTokens()">
                    {{ trans('settings.revoke-all-tokens') }}
                </button>
                <form method="dialog">
                    <button class="btn me-2">{{ trans('menu.close') }}</button>
                </form>
            </div>
        </div>
    </dialog>
</template>
