<script lang="ts">
import { trans, transChoice } from 'laravel-vue-i18n';
import { PointReason, Points, StatusResource } from '../../types/Api.gen';
import { checkinSuccessStore } from '../stores/checkinSuccess';
import FullScreenModal from './FullScreenModal.vue';
import ModalComponent from './ModalComponent.vue';

export default {
    name: 'CheckinSuccessHelper',
    components: { ModalComponent, FullScreenModal },
    setup() {
        const checkinSuccess = checkinSuccessStore();

        return { checkinSuccess };
    },
    data() {
        return {
            points: null as Points | null,
            alsoOnThisConnection: [] as StatusResource[],
            status: null as StatusResource | null,
        };
    },
    computed: {
        PointReason() {
            return PointReason;
        },
    },
    mounted() {
        this.fetchData();
    },
    methods: {
        transChoice,
        trans,
        fetchData() {
            const success = this.checkinSuccess.checkinResponse;
            if (success === null) {
                return;
            }

            this.points = success?.points ?? null;
            this.alsoOnThisConnection = success?.alsoOnThisConnection ?? [];
            this.status = success?.status ?? null;
            this.$refs.modal.show();
            this.checkinSuccess.reset();
        },
    },
};
</script>

<template>
    <ModalComponent
        ref="modal"
        header-class="bg-success text-white"
        :title="trans('checkin.success.title')"
        :hide-footer="true"
    >
        <template #body>
            <p>
                {{ trans('checkin.success.body') }}
            </p>
            <p>
                {{
                    trans('checkin.success.body2', {
                        lineName: status?.train?.lineName ?? '',
                        distance: ((status?.train?.distance ?? 0) / 1000).toFixed(2).toString(),
                        origin: status?.train?.origin?.name ?? '',
                        destination: status?.train?.destination?.name ?? '',
                    })
                }}
            </p>
            <p v-if="points?.calculation?.reason !== 5">
                {{
                    transChoice('checkin.points.earned', points?.points ?? 0, {
                        points: points?.points?.toString() ?? '0',
                    })
                }}
            </p>
            <p v-if="points?.calculation?.reason === 1 || points?.calculation?.reason === 2" class="text-muted">
                {{ trans('checkin.points.could-have') }}
                {{
                    trans('checkin.points.full', {
                        points: (points.calculation.base + points.calculation.distance).toString(),
                    })
                }}
            </p>
            <p v-if="points?.calculation?.reason === 3" class="text-danger">
                {{ trans('checkin.points.forced') }}
            </p>

            <template v-if="alsoOnThisConnection.length > 0">
                <h5 class="mt-5">
                    {{ transChoice('controller.transport.also-in-connection', alsoOnThisConnection.length) }}
                </h5>
                <div class="list-group">
                    <a
                        v-for="status in alsoOnThisConnection"
                        :key="status.id"
                        :href="`/status/${status.id}`"
                        class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3"
                        aria-current="true"
                    >
                        <img
                            :src="status.userDetails.profilePicture"
                            alt="Profilbild"
                            class="rounded-circle flex-shrink-0"
                            style="width: 40px; height: 40px; object-fit: cover"
                        />

                        <div class="d-flex flex-column flex-grow-1">
                            <h6 class="mb-1 fw-bold opacity-75 text-truncate">
                                {{ status.userDetails.displayName }}
                                <span
                                    v-if="status.userDetails.displayName !== status.userDetails.username"
                                    class="text-muted"
                                >
                                    (@{{ status.userDetails.username }})
                                </span>
                            </h6>
                            <p class="mb-0 text-truncate">
                                {{ status?.train?.origin?.name }} ➜ {{ status?.train?.destination?.name }}
                            </p>
                        </div>
                    </a>
                </div>
            </template>
        </template>
    </ModalComponent>
</template>
