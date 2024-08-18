<script lang="ts">
import {defineComponent} from 'vue'
import {TrwlEvent} from "../../types/TrwlEvent";
import {trans} from "laravel-vue-i18n";

export default defineComponent({
    emits: ["select-event"],
    name: "EventDropdown",
    data() {
        return {
            events: [] as TrwlEvent[],
            filteredEvents: [] as TrwlEvent[],
            search: "" as string,
            selectedEvent: null as TrwlEvent | null
        }
    },
    mounted() {
        this.fetchEvents();
    },
    methods: {
        trans,
        fetchEvents() {
            fetch("/api/v1/events")
                .then(response => response.json())
                .then(data => {
                    this.events = data.data;
                    this.filteredEvents = data.data;
                });
        },
        filterEvents() {
            this.filteredEvents = this.events.filter(event => event.name.toLowerCase().includes(this.search.toLowerCase()));
        },
        selectEvent(event: TrwlEvent) {
            this.selectedEvent = event === this.selectedEvent ? null : event;
            this.$emit("select-event", this.selectedEvent);
        },
        isSelected(event: TrwlEvent) {
            return this.selectedEvent && this.selectedEvent.slug === event.slug;
        }
    },
    watch: {
        search() {
            this.filterEvents();
        }
    },
})
</script>

<template>
    <div class="col btn-group">
        <button class="btn btn-sm dropdown-toggle btn-link px-2" type="button"
                id="eventDropdown" data-mdb-dropdown-animation="off"
                data-mdb-toggle="dropdown" aria-expanded="false" style="">
            <i class="fas" aria-hidden="true"
               :class="{'fa-calendar': !selectedEvent, 'fa-calendar-check': selectedEvent}"></i>
        </button>
        <div aria-labelledby="eventDropdown"
             class="dropdown-menu pt-0 mx-0 rounded-3 shadow overflow-hidden">
            <form class="p-2 mb-2 border-bottom">
                <input
                    v-model="search" type="search" class="form-control mobile-input-fs-16" autocomplete="off"
                    :placeholder="trans('stationboard.event-filter')">
            </form>
            <ul class="list-unstyled mb-0" v-if="filteredEvents.length > 0">
                <li v-for="event in filteredEvents" :key="event.slug">
                    <a class="dropdown-item d-flex align-items-center gap-2 py-2" @click="selectEvent(event)"
                       :class="{'active': isSelected(event)}"
                    >
                        <i class="fas" :class="{'fa-check': isSelected(event), }"></i>
                        <div class="flex-grow-1">
                            <div class="fw-bold">{{ event.name }}</div>
                            <div class="text-muted small">{{ event.station?.name }}</div>
                        </div>
                    </a>
                </li>
            </ul>
            <div v-else class="p-2 mb-0 text-center text-muted">
                <p>{{ trans("stationboard.events-none") }}</p>
                <p>{{ trans("stationboard.events-propose") }} <a href="/events"
                                                                 target="_blank">traewelling.de/events</a></p>
            </div>
        </div>
    </div>
</template>

<style scoped lang="scss">

</style>
