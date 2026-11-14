<script setup lang="ts">
import { X } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { ref } from 'vue';
import Mastodon from '../../../components/Icons/Mastodon.vue';

const dialogRef = ref<HTMLDialogElement | null>(null);

function open(): void {
    dialogRef.value?.showModal();
}

defineExpose({ open });
</script>

<template>
    <dialog ref="dialogRef" class="modal modal-top sm:modal-middle">
        <div class="modal-box">
            <form method="dialog">
                <button
                    class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"
                    :aria-label="trans('menu.close')"
                >
                    <X class="size-4" />
                </button>
            </form>
            <h3 class="text-lg font-bold">{{ trans('user.login.mastodon') }}</h3>

            <form method="GET" action="/auth/redirect/mastodon" class="mt-4 join w-full">
                <input
                    type="text"
                    name="domain"
                    required
                    :placeholder="trans('user.mastodon-instance-url')"
                    class="input input-bordered w-full join-item"
                />
                <button type="submit" class="btn btn-primary join-item">
                    <Mastodon class="size-4 fill-current" aria-hidden="true" />
                    {{ trans('user.login') }}
                </button>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>{{ trans('menu.close') }}</button>
        </form>
    </dialog>
</template>
