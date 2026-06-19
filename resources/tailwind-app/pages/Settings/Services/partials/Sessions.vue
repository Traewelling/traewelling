<script setup lang="ts">
import { Computer, Smartphone, Tablet } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { FunctionalComponent, ref } from 'vue';
import { Api, SessionResource } from '../../../../../types/Api.gen';
import SettingsListRow from '../../SettingsListRow.vue';

const emits = defineEmits(['mastodon-removed', 'error']);

const modal = ref<HTMLDialogElement>();
const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const loading = ref(false);
const sessions = ref<SessionResource[]>([]);

function fetchSessions() {
    loading.value = true;
    api.security
        .getSessions()
        .then((response) => {
            response.json().then((data) => {
                sessions.value = data.data;
            });
            loading.value = false;
        })
        .catch((error) => {
            emits('error', error);
            loading.value = false;
        });
}

function clearSessions() {
    api.security.deleteAllSessions().then(() => {
        sessions.value = [];
        modal.value?.close();
        window.document.location.reload();
    });
}

function getIcon(session: SessionResource): FunctionalComponent {
    if (session.deviceType === 'mobile') {
        return Smartphone;
    }
    if (session.deviceType === 'tablet') {
        return Tablet;
    }
    return Computer;
}

fetchSessions();
</script>

<template>
    <SettingsListRow
        :title="trans('settings.title-sessions')"
        :description="trans('settings.sessions.description')"
        @click.prevent="modal?.showModal()"
    />
    <dialog ref="modal" class="modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold">
                {{ trans('settings.title-sessions') }}
            </h3>
            <ul class="list">
                <li v-for="session in sessions" :key="session.id" class="list-row">
                    <div class="list-col-grow">
                        <h6 class="mb-0">
                            <component :is="getIcon(session)" class="inline" />
                            {{ session.platform }}
                        </h6>
                        <p class="mb-0 opacity-75">
                            {{ trans('settings.lastactivity') }}:
                            {{
                                session.lastActivity
                                    ? new Date(session.lastActivity).toLocaleString()
                                    : trans('settings.never')
                            }}
                        </p>
                        <p class="mb-0 opacity-75">
                            {{ trans('settings.ip') }}
                            {{ session.ip }}
                        </p>
                    </div>
                </li>
            </ul>

            <div class="modal-action w-full">
                <button class="btn btn-outline btn-error me-auto" @click="clearSessions()">
                    {{ trans('settings.deleteallsessions') }}
                </button>
                <form method="dialog">
                    <button class="btn me-2">{{ trans('menu.close') }}</button>
                </form>
            </div>
        </div>
    </dialog>
</template>
