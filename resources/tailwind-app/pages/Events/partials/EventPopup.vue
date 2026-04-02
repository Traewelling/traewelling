<script setup lang="ts">
import { currentLocale, trans } from 'laravel-vue-i18n';
import { Calendar, ChevronRight, HashIcon, LinkIcon, SquareArrowOutUpRight } from 'lucide-vue-next';
import { computed } from 'vue';
import { EventResource } from '../../../../types/Api.gen';
import { contrastingColor, generateColorFromString } from '../../../../vue/helpers/ColorHelper';

const props = defineProps<{
    event: EventResource;
    style: string | undefined;
}>();

function showModal() {
    document.getElementById(`${props.event.slug}-modal`)?.showModal();
}

function redirect() {
    window.location.href = '/event/' + props.event.slug;
}

const duration = computed(() => {
    const locale = currentLocale.value.startsWith('de') ? 'de' : currentLocale.value;
    const begin = new Date(props.event.begin);
    const end = new Date(props.event.end);
    const options: Intl.DateTimeFormatOptions = { day: 'numeric', month: 'long', year: 'numeric' };
    if (begin.getFullYear() !== end.getFullYear()) {
        options.year = 'numeric';
    }
    if (begin.getMonth() !== end.getMonth() || begin.getDate() !== end.getDate()) {
        return `${begin.toLocaleDateString(locale, options)} - ${end.toLocaleDateString(locale, options)}`;
    } else {
        return begin.toLocaleDateString(locale, options);
    }
});

const fallbackStyle = computed(() => {
    if (props.style !== undefined) {
        return null;
    }

    const background = generateColorFromString(props.event.name);
    const color = contrastingColor(background);

    return `background-color: #${background}; color: #${color};`;
});
</script>

<template>
    <div
        class="px-2 py-1 rounded-lg mt-1 overflow-hidden border cursor-pointer"
        :class="style"
        :style="fallbackStyle"
        @click="showModal()"
    >
        <p class="text-sm truncate leading-tight" v-text="event.name"></p>
    </div>
    <dialog :id="`${event.slug}-modal`" class="modal modal-bottom sm:modal-middle">
        <div class="modal-box">
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            </form>
            <h3 class="mb-4 text-lg font-bold">{{ event.name }}</h3>
            <p>
                <Calendar class="inline-block size-5 me-1">
                    <title>
                        {{ trans('events.duration') }}
                    </title>
                </Calendar>
                {{ duration }}
            </p>
            <p v-if="event.hashtag">
                <HashIcon class="inline-block size-5 me-2">
                    <title>#</title>
                </HashIcon>
                {{ event.hashtag }}
            </p>
            <p v-if="event.url">
                <LinkIcon class="inline-block size-5 me-2">
                    <title>{{ trans('events.url') }}</title>
                </LinkIcon>
                <a :href="event.url" target="_blank" class="link link-primary">
                    {{ event.url }}
                    <SquareArrowOutUpRight class="inline-block size-5" />
                </a>
            </p>
            <div v-if="event.host || event.station" class="mt-3">
                <p v-if="event.host">{{ trans('events.host') }}: {{ event.host }}</p>
                <p v-if="event.station">
                    {{ trans('events.closestStation') }}:
                    <a
                        class="link"
                        href="`/stationboard?stationId=${event.station.id}&stationName=${event.station.name}`"
                        >{{ event.station.name }}</a
                    >
                </p>
            </div>

            <div class="modal-action">
                <button class="btn btn-primary" @click="redirect()">
                    {{ trans('menu.show-more') }}
                    <ChevronRight class="inline-block size-5" />
                </button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>
</template>

<style scoped>
.rainbow {
    background: linear-gradient(
        90deg,
        rgba(255, 0, 0, 1) 0%,
        rgba(255, 154, 0, 1) 10%,
        rgba(208, 222, 33, 1) 20%,
        rgba(79, 220, 74, 1) 30%,
        rgba(63, 218, 216, 1) 40%,
        rgba(47, 201, 226, 1) 50%,
        rgba(28, 127, 238, 1) 60%,
        rgba(95, 21, 242, 1) 70%,
        rgba(186, 12, 248, 1) 80%,
        rgba(251, 7, 217, 1) 90%,
        rgba(255, 0, 0, 1) 100%
    );
    color: #fff;
    text-shadow:
        #0a0a0f 1px 0px 2px,
        #0a0a0f 0px 1px 2px,
        #0a0a0f -1px 0px 2px,
        #0a0a0f 0px -1px 2px;
}
</style>
