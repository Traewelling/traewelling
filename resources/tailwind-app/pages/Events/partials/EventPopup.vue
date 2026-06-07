<script setup lang="ts">
import { ChevronRight } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { computed, ref } from 'vue';
import { EventResource } from '../../../../types/Api.gen';
import { contrastingColor, generateColorFromString } from '../../../../vue/helpers/ColorHelper';
import EventDetail from './EventDetail.vue';

const props = defineProps<{
    event: EventResource;
    style: string | undefined;
}>();

const modalRef = ref<HTMLDialogElement | null>(null);

function showModal() {
    modalRef.value?.showModal();
}

function redirect() {
    window.location.href = '/event/' + props.event.slug;
}

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
    <dialog :id="`${event.slug}-modal`" ref="modalRef" class="modal modal-bottom sm:modal-middle">
        <div class="modal-box">
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            </form>

            <EventDetail :event="event" />

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
