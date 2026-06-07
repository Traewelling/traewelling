<script setup lang="ts">
import { Trash2 } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { ref } from 'vue';
import { Api, IcsEntryResource } from '../../../../../types/Api.gen';
import SettingsListRow from '../../SettingsListRow.vue';

const props = defineProps<{
    tokens: IcsEntryResource[];
}>();
const emits = defineEmits(['ics-updated', 'error']);

const modal = ref<HTMLDialogElement>();
const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

function removeToken(tokenId: number) {
    api.icsTokens
        .revokeIcsToken(tokenId)
        .then(() => {
            emits(
                'ics-updated',
                props.tokens.filter((t) => t.id !== tokenId),
            );
        })
        .catch((error) => {
            emits('error', error.error.message);
        });
}
</script>

<template>
    <SettingsListRow
        :title="trans('settings.ics.modal')"
        :description="trans('settings.ics.descriptor')"
        @click.prevent="modal?.showModal()"
    />
    <dialog ref="modal" class="modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold">{{ trans('settings.ics.modal') }}</h3>
            <ul class="list">
                <li v-if="tokens.length == 0">
                    {{ trans('settings.no-ics-tokens') }}
                </li>
                <li v-for="token in tokens" :key="token.id" class="list-row">
                    <div class="list-col-grow">
                        <h6 class="mb-0">
                            {{ token.name }}
                        </h6>
                        <p class="mb-0 opacity-75">
                            {{ trans('settings.last-accessed') }}:
                            {{
                                token.lastAccessed
                                    ? new Date(token.lastAccessed).toLocaleString()
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
            <div class="modal-action">
                <form method="dialog">
                    <button class="btn me-2">{{ trans('menu.close') }}</button>
                </form>
            </div>
        </div>
    </dialog>
</template>
