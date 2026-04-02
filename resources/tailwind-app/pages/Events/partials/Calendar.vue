<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { ArrowLeft, ArrowRight } from 'lucide-vue-next';
import { ref } from 'vue';
import { EventResource } from '../../../../types/Api.gen';
import EventPopup from './EventPopup.vue';

export type CalendarEvent = {
    date: Date;
    title: string;
    style: string | undefined;
    event: EventResource | undefined;
};

const monthNames = [
    'dates.January',
    'dates.February',
    'dates.March',
    'dates.April',
    'dates.May',
    'dates.June',
    'dates.July',
    'dates.August',
    'dates.September',
    'dates.October',
    'dates.November',
    'dates.December',
];
const selectedMonth = ref(new Date().getMonth());
const selectedYear = ref(new Date().getFullYear());
const currentYear = new Date().getFullYear();
const days = ref<number[]>([]);
const previousMonthDays = ref<number[]>([]);
const nextMonthDays = ref<number[]>([]);
const dayNames = [
    'dates.Monday',
    'dates.Tuesday',
    'dates.Wednesday',
    'dates.Thursday',
    'dates.Friday',
    'dates.Saturday',
    'dates.Sunday',
];

defineProps<{ events: CalendarEvent[] }>();

const emits = defineEmits<(e: 'dateSelected', date: Date) => void>();

function initDate() {
    const today = new Date();
    selectDate(today.getMonth(), today.getFullYear());
}

function getNoOfDays() {
    let i;
    const daysInMonth = new Date(selectedYear.value, selectedMonth.value + 1, 0).getDate();

    // find where to start calendar day of week
    let startDayOfWeek = new Date(selectedYear.value, selectedMonth.value).getDay();
    let endDayOfWeek = new Date(selectedYear.value, selectedMonth.value, daysInMonth).getDay();
    // convert to monday = first day of week
    startDayOfWeek = startDayOfWeek === 0 ? 6 : startDayOfWeek - 1;
    endDayOfWeek = endDayOfWeek === 0 ? 6 : endDayOfWeek - 1;

    previousMonthDays.value = [];
    for (i = 1; i <= startDayOfWeek; i++) {
        previousMonthDays.value.push(i);
    }
    nextMonthDays.value = [];
    for (i = endDayOfWeek; i < 6; i++) {
        nextMonthDays.value.push(i);
    }

    days.value = [];
    for (i = 1; i <= daysInMonth; i++) {
        days.value.push(i);
    }
}

function isToday(date: number) {
    const today = new Date();
    const d = new Date(selectedYear.value, selectedMonth.value, date);

    return today.toDateString() === d.toDateString();
}

function selectDate(month: number | undefined = undefined, year: number | undefined = undefined) {
    selectedMonth.value = month ?? selectedMonth.value;
    selectedYear.value = year ?? selectedYear.value;
    getNoOfDays();
    emits('dateSelected', new Date(selectedYear.value, selectedMonth.value));
}

function nextMonth() {
    if (selectedMonth.value === 11) {
        selectDate(0, selectedYear.value + 1);
        return;
    }

    selectDate(selectedMonth.value + 1);
}

function previousMonth() {
    if (selectedMonth.value === 0) {
        selectDate(11, selectedYear.value - 1);
        return;
    }

    selectDate(selectedMonth.value - 1);
}

function blur() {
    document.activeElement?.blur();
}

initDate();
getNoOfDays();
</script>

<template>
    <div class="bg-base-100 rounded-lg shadow overflow-hidden">
        <div class="flex items-center justify-between py-2 px-6 flex-col md:flex-row">
            <div>
                <span class="text-lg font-bold" v-text="trans(monthNames[selectedMonth])"></span>
                <span class="ml-1 text-lg text-content opacity-65 font-normal" v-text="selectedYear"></span>
            </div>
            <div class="flex gap-2 mb-2 pt-2px">
                <div class="join">
                    <div class="dropdown">
                        <div tabindex="0" role="button" class="btn btn-outline btn-base btn-sm join-item">
                            {{ trans(monthNames[selectedMonth]) }}
                        </div>
                        <ul tabindex="-1" class="dropdown-content menu bg-base-100 rounded-box z-1 p-2 shadow-sm">
                            <li
                                v-for="(monthName, index) in monthNames"
                                :key="index"
                                @click="
                                    selectDate(index);
                                    blur();
                                "
                            >
                                <a :class="{ 'menu-active': index === selectedMonth }" href="#">
                                    {{ trans(monthName) }}
                                </a>
                            </li>
                        </ul>
                    </div>
                    <button class="btn btn-sm btn-outline btn-base" @click="initDate()">
                        {{ trans('dates.Today') }}
                    </button>
                    <div class="dropdown">
                        <div tabindex="0" role="button" class="btn btn-outline btn-base btn-sm join-item">
                            {{ selectedYear }}
                        </div>
                        <ul tabindex="-1" class="dropdown-content menu bg-base-100 rounded-box z-1 p-2 shadow-sm">
                            <li
                                v-for="y in Array(4)
                                    .fill(0)
                                    .map((_, i) => currentYear - 2 + i)"
                                :key="y"
                                @click="
                                    selectDate(undefined, y);
                                    blur();
                                "
                            >
                                <a :class="{ 'menu-active': y === selectedYear }" href="#">{{ y }}</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="join">
                    <button type="button" class="btn btn-outline btn-base btn-sm join-item" @click="previousMonth()">
                        <ArrowLeft class="h-5 w-5 inline-block leading-none" />
                    </button>
                    <button type="button" class="btn btn-outline btn-base btn-sm join-item" @click="nextMonth()">
                        <ArrowRight class="h-5 w-5 inline-block leading-none" />
                    </button>
                </div>
            </div>
        </div>

        <div class="-mx-1 -mb-1">
            <div class="flex flex-wrap">
                <template v-for="(day, index) in dayNames" :key="index">
                    <div class="px-2 py-2 calendar-cell-width">
                        <div
                            class="text-content z-1 text-sm uppercase tracking-wide font-bold text-center"
                            v-text="trans(day).substring(0, 2)"
                        ></div>
                    </div>
                </template>
            </div>

            <div class="flex flex-wrap border-t border-l">
                <template v-for="blankDay in previousMonthDays" :key="blankDay">
                    <div
                        class="text-center border-b px-4 pt-2 bg-base-200 calendar-cell"
                        :class="{ 'border-r': blankDay === previousMonthDays.length }"
                    ></div>
                </template>
                <template v-for="(date, dateIndex) in days" :key="dateIndex">
                    <div class="md:px-4 pt-2 border-r border-b relative calendar-cell">
                        <div
                            class="inline-block w-5 h-5 items-center justify-center text-center leading-none rounded-full"
                            :class="{
                                'bg-primary text-white': isToday(date) == true,
                                'text-content': isToday(date) == false,
                            }"
                            v-text="date"
                        ></div>
                        <div class="overflow-y-auto mt-1 calendar-cell-event-space">
                            <template
                                v-for="event in events.filter(
                                    (e) =>
                                        new Date(e.date).toDateString() ===
                                        new Date(selectedYear, selectedMonth, date).toDateString(),
                                )"
                                :key="event.event?.id || event.title"
                            >
                                <EventPopup v-if="event.event" :event="event.event" :style="event.style" />
                            </template>
                        </div>
                    </div>
                </template>
                <template v-for="blankDay in nextMonthDays" :key="blankDay">
                    <div class="text-center border-b px-4 pt-2 bg-base-200 calendar-cell"></div>
                </template>
            </div>
        </div>
    </div>
</template>
<style scoped>
.calendar-cell {
    width: 14.28%;
    height: 120px;
}
.calendar-cell-event-space {
    height: 80px;
}
.calendar-cell-width {
    width: 14.26%;
}
.event-body {
    white-space: pre-wrap;
    overflow-wrap: break-word;
}

.pt-2px {
    padding-top: 2px;
}
</style>
