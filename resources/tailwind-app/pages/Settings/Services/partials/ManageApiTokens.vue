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
const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

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
    <SettingsListRow :title="trans('settings.title-tokens')" @click.prevent="modal?.showModal()" />
    <dialog ref="modal" class="modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold mb-4">{{ trans('settings.ics.modal') }}</h3>
            <ul class="list">
                <!-- Render grouped tokens by client -->
                <li v-for="group in groupedTokens" :key="group.client" class="mb-4">
                    <h6 class="mb-2 font-semibold">{{ group.client }}</h6>
                    <ul>
                        <li v-for="token in group.tokens" :key="token.id" class="list-row">
                            <div class="list-col-grow">
                                <h6 class="mb-0">
                                    {{ token.client }}
                                </h6>
                                <p class="mb-0 opacity-75">
                                    {{ trans('settings.expires') }}:
                                    {{
                                        token.expiresAt
                                            ? new Date(token.expiresAt).toLocaleString()
                                            : trans('settings.never')
                                    }}
                                </p>
                                <p class="mb-0 opacity-75">
                                    {{ trans('settings.created') }}
                                    {{ token.createdAt ? new Date(token.createdAt).toLocaleString() : '' }}
                                </p>
                            </div>
                            <button role="button" class="btn btn-sm btn-error" @click="removeToken(token.id)">
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </li>
                    </ul>
                </li>
            </ul>
            <div class="modal-action">
                <form method="dialog">
                    <button class="btn me-2">{{ trans('menu.close') }}</button>
                </form>
            </div>
        </div>
    </dialog>
</template>
