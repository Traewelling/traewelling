<script lang="ts">
import {defineComponent} from 'vue'
import {useActiveCheckin} from "../stores/activeCheckin";
import {NextStation} from "../helpers/NextStation";
import {StopoverResource} from "../../types/Api";
import {DateTime} from "luxon";
import LineIndicator from "./LineIndicator.vue";

export default defineComponent({
    name: "ActiveStatusCard",
    setup() {
        const state = useActiveCheckin();

        return {state};
    },
    components: {LineIndicator},
    data() {
        return {
            progress: 0,
            nextStation: null as StopoverResource | null,
            fetchInterval: null as number | null,
            nextStationInterval: null as number | null,
        };
    },
    methods: {
        getNextStation() {
            this.getProgress()
            if (this.state.stopovers && this.progress < 100) {
                this.nextStation = NextStation.getNextStation(this.state.stopovers);
            }
        },
        fetchState() {
            this.state.fetchActiveStatus();
        },
        getProgress() {
            if (this.departure && this.arrival) {
                const now = DateTime.now();
                const total = this.arrival - this.departure;
                const current = now - this.departure;
                this.progress = Math.round((current / total) * 100);
            }
        },
        format(dateTime: DateTime): string {
            return dateTime.toFormat('HH:mm');
        },
        goToStatus() {
            if (this.state.status?.id) {
                window.location = "/status/" + this.state.status.id;
            }
        }
    },
    computed: {
        departure() {
            const dep = this.state.status?.train?.origin?.departure ?? this.state.status?.train?.origin?.arrival ?? null;
            return DateTime.fromISO(dep);
        },
        arrival() {
            const arr = this.state.status?.train?.destination?.arrival ?? this.state.status?.train?.destination?.departure ?? null;
            return DateTime.fromISO(arr);
        },
        showCard() {
            return this.state.status !== null && this.state.status !== undefined;
        }
    },
    mounted() {
        this.fetchState();
        setTimeout(this.getNextStation, 500);
        this.fetchInterval = setInterval(this.fetchState, 30000);
        this.nextStationInterval = setInterval(this.getNextStation, 10000);
    },
    beforeDestroy() {
        if (this.fetchInterval) {
            clearInterval(this.fetchInterval);
        }
        if (this.nextStationInterval) {
            clearInterval(this.nextStationInterval);
        }
    }
})
</script>

<template>
    <div v-show="showCard" class="fab-container d-md-none">
        <div class="card hover-card w-100 shadow-sm" @click="goToStatus">
            <div class="card-body py-2 px-3">
                <p class="mb-0">{{ state.status?.train?.origin?.name }} <small
                    class="float-end text-muted">{{ format(departure) }}</small></p>

                <p class="ms-2 col-auto align-items-center d-flex my-0" v-show="state.status?.train?.lineName">
                    <LineIndicator :product-name="state.status?.train?.category"
                                   :number="state.status?.train?.lineName ?? ''"/>
                    <span class="ms-1" v-show="nextStation">next: {{ nextStation?.name }}</span>
                </p>
                <p class="mb-0">{{ state.status?.train?.destination?.name }} <small
                    class="float-end text-muted">{{ format(arrival) }}</small></p>

                <div class="progress">
                    <div class="progress-bar bg-trwl" role="progressbar" :style="`width: ${progress}%`"
                         :aria-valuenow="progress" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped lang="scss">
.fab-container {
    z-index: 50;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    align-items: center;
    user-select: none;
    position: fixed;
    bottom: 30px;
    left: 50%;
    width: 100%;
    max-width: 100%;
    margin-left: -50%;
    padding-right: calc(var(--mdb-gutter-x) * 0.5);
    padding-left: calc(var(--mdb-gutter-x) * 0.5);
}
</style>
