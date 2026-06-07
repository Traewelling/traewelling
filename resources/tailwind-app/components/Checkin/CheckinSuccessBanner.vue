<script setup lang="ts">
import { CheckCheck } from '@lucide/vue';
import { trans, transChoice } from 'laravel-vue-i18n';
import { computed } from 'vue';
import { CheckinSuccessResource, PointReason } from '../../../types/Api.gen';

const props = defineProps<{ data: CheckinSuccessResource }>();

const pointsDisabled = computed(
    () =>
        props.data.points?.calculation?.reason === PointReason.Value5 ||
        props.data.points?.calculation?.reason === PointReason.Value3 ||
        props.data.points?.calculation?.reason === PointReason.Value4,
);
</script>

<template>
    <div class="alert alert-success shadow-md mb-4">
        <CheckCheck class="w-5 h-5 shrink-0" />
        <div class="flex flex-col gap-1 min-w-0 w-full">
            <span class="font-semibold">
                <template v-if="pointsDisabled">
                    {{ trans('checkin.success.title') }}
                </template>
                <template v-else>
                    {{
                        transChoice('checkin.points.earned', data.points?.points ?? 0, {
                            points: (data.points?.points ?? 0).toString(),
                        })
                    }}
                </template>
            </span>

            <!-- Also on this connection -->
            <div v-if="data.alsoOnThisConnection?.length" class="mt-1">
                <p class="text-sm opacity-80 mb-1">
                    {{ transChoice('controller.transport.also-in-connection', data.alsoOnThisConnection.length) }}
                </p>
                <div class="flex flex-col gap-1">
                    <a
                        v-for="s in data.alsoOnThisConnection"
                        :key="s.id"
                        :href="`/@${s.user.username}`"
                        class="flex items-center gap-2 hover:opacity-80 transition-opacity"
                    >
                        <img
                            :src="s.user.profilePicture"
                            :alt="s.user.username"
                            class="w-7 h-7 rounded-full border-2 border-success object-cover shrink-0"
                        />
                        <span class="text-sm font-medium leading-tight">
                            {{ s.user.displayName ?? s.user.username }}
                            <span class="opacity-60 font-normal">@{{ s.user.username }}</span>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>
