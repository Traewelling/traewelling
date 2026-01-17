<script lang="ts" setup>
import { ref, watch } from 'vue';
import { trans } from 'laravel-vue-i18n';
import { Api, EventResource } from '../../types/Api.gen';

const props = defineProps({
    preSelectedEvent: {
        type: Object as () => EventResource | null,
        default: null,
    },
    prefetchEvents: {
        type: Boolean,
        default: true,
    },
    class: {
        type: String,
        default: 'btn btn-sm btn-link px-2',
    },
});
const emits = defineEmits(['select-event']);
const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const events = ref<EventResource[]>([]);
const filteredEvents = ref<EventResource[]>([]);
const search = ref<string>('');
const selectedEvent = ref<EventResource | null>(props.preSelectedEvent || null);

function fetchEvents(timestamp: string | null = null) {
    fetch('/api/v1/events')
        .then((response) => response.json())
        .then((data) => {
            events.value = data.data;
            filteredEvents.value = data.data;
        });
    let query = {};
    if (timestamp) {
        query = { timestamp: timestamp };
    }

    api.events
        .getEvents(query)
        .then((response) => {
            events.value = response.data?.data || [];
        })
        .catch((error) => {
            console.error('Error fetching events:', error);
        });
}

defineExpose({
    fetchEvents,
});

function filterEvents() {
    filteredEvents.value = events.value.filter((event) =>
        event.name.toLowerCase().includes(search.value.toLowerCase()),
    );
}

function selectEvent(event: EventResource) {
    selectedEvent.value = event === selectedEvent.value ? null : event;
    emits('select-event', selectedEvent.value);
}

function isSelected(event: EventResource) {
    return selectedEvent.value && selectedEvent.value.slug === event.slug;
}

watch(search, () => {
    filterEvents();
});

// initialize
if (props.prefetchEvents) {
    fetchEvents();
}
</script>

<template>
    <div class="col btn-group">
        <button
            id="eventDropdown"
            :class
            type="button"
            class="dropdown-toggle"
            data-bs-dropdown-animation="off"
            data-bs-toggle="dropdown"
            aria-expanded="false"
            style=""
        >
            <i
                class="fas"
                aria-hidden="true"
                :class="{ 'fa-calendar': !selectedEvent, 'fa-calendar-check': selectedEvent }"
            />
        </button>
        <div aria-labelledby="eventDropdown" class="dropdown-menu pt-0 mx-0 rounded-3 shadow overflow-hidden">
            <form class="p-2 mb-2 border-bottom">
                <input
                    v-model="search"
                    type="search"
                    class="form-control mobile-input-fs-16"
                    autocomplete="off"
                    :placeholder="trans('stationboard.event-filter')"
                />
            </form>
            <ul v-if="filteredEvents.length > 0" class="list-unstyled mb-0">
                <li v-for="event in filteredEvents" :key="event.slug">
                    <a
                        class="dropdown-item d-flex align-items-center gap-2 py-2"
                        :class="{ active: isSelected(event) }"
                        @click="selectEvent(event)"
                    >
                        <i class="fas" :class="{ 'fa-check': isSelected(event) }" />
                        <div class="flex-grow-1">
                            <div class="fw-bold">{{ event.name }}</div>
                            <div class="text-muted small">{{ event.station?.name }}</div>
                        </div>
                    </a>
                </li>
            </ul>
            <div v-else class="p-2 mb-0 text-center text-muted">
                <p>{{ trans('stationboard.events-none') }}</p>
                <p>
                    {{ trans('stationboard.events-propose') }}
                    <a href="/events" target="_blank">traewelling.de/events</a>
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped lang="scss"></style>
