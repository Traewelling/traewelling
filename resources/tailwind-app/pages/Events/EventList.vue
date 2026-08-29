<script setup lang="ts">
import { CalendarIcon } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { ref } from 'vue';
import { Api } from '../../../types/Api.gen';
import Loading from '../../components/Loading.vue';
import AppLayout from '../../layouts/AppLayout.vue';
import Calendar, { CalendarEvent } from './partials/Calendar.vue';

const loading = ref(false);
const api = new Api({ baseUrl: window.location.origin + '/api' });

const events = ref<CalendarEvent[]>([]);

function appendEvents(apiEvents: (typeof events.value)[number]['event'][]) {
    apiEvents?.forEach((event) => {
        if (!event) return;
        const start = new Date(event.begin);
        const end = new Date(event.end);
        for (let d = start; d <= end; d.setDate(d.getDate() + 1)) {
            events.value.push({
                date: new Date(d),
                title: event.name,
                style: event.isPride ? 'rainbow' : undefined,
                event: event,
            });
        }
    });
}

async function fetchEvents(date: Date) {
    loading.value = true;
    events.value = [];

    const params = {
        from: new Date(date.getFullYear(), date.getMonth() - 1, 1).toISOString().split('T')[0],
        until: new Date(date.getFullYear(), date.getMonth() + 2, 0).toISOString().split('T')[0],
    };

    try {
        let page = 1;
        let hasNextPage = true;
        while (hasNextPage) {
            const response = await api.events.getEvents({ ...params, page });
            appendEvents(response.data.data);
            hasNextPage = !!response.data.links?.next;
            page++;
        }
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <AppLayout>
        <div class="container mx-auto md:px-4 py-2 md:py-24">
            <h1 class="font-bold text-xl mb-1">
                <CalendarIcon class="size-8 inline-block" />
                {{ trans('events.live') }}
                <Loading v-if="loading" />
            </h1>
            <p class="mb-4">
                {{ trans('events.suggest.card_description') }}
                <RouterLink :to="{ name: 'events-suggest' }" class="link">
                    {{ trans('events.suggest.card_button') }}
                </RouterLink>
            </p>
            <Calendar :events @date-selected="fetchEvents" />
        </div>
    </AppLayout>
</template>
