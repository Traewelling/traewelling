<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { CalendarIcon } from 'lucide-vue-next';
import { ref } from 'vue';
import { Api } from '../../../types/Api.gen';
import Loading from '../../components/Loading.vue';
import AppLayout from '../../layouts/AppLayout.vue';
import Calendar, { CalendarEvent } from './partials/Calendar.vue';

const loading = ref(false);
const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const events = ref<CalendarEvent[]>([]);

function fetchEvents(date: Date) {
    loading.value = true;
    const startOfMonth = new Date(date.getFullYear(), date.getMonth() - 1, 1);
    const endOfMonth = new Date(date.getFullYear(), date.getMonth() + 2, 0);
    api.events
        .getEvents({
            from: startOfMonth.toISOString().split('T')[0],
            until: endOfMonth.toISOString().split('T')[0],
        })
        .then((response) => {
            loading.value = false;
            events.value = [];
            response.data.data?.forEach((event) => {
                const start = new Date(event.begin);
                const end = new Date(event.end);
                for (let d = start; d <= end; d.setDate(d.getDate() + 1)) {
                    events.value.push({
                        date: new Date(d),
                        title: event.name,
                        style: event.isPride
                            ? 'bg-gradient-to-r from-pink-700 to-purple-700 text-white border-orange-500'
                            : 'border-blue-200 text-blue-800 bg-blue-100',
                        event: event,
                    });
                }
            });
        })
        .catch(() => {
            loading.value = false;
        });
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
