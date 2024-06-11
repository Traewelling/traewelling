<script lang="ts">
import {defineComponent} from 'vue'
import ProductIcon from "../ProductIcon.vue";
import LineIndicator from "../LineIndicator.vue";
import {DateTime} from "luxon";
import {trans} from "laravel-vue-i18n";
import {departureEntry} from "../../../types/Departure";

export default defineComponent({
    name: "StationBoardEntry",
    components: {LineIndicator, ProductIcon},
    props: {
        item: {
            type: Object() as departureEntry,
            required: true
        },
        station: {
            type: Object,
            required: true
        }
    },
    methods: {
        trans,
        formatTime(time: any) {
            return DateTime.fromISO(time).toFormat("HH:mm");
        },
    },
    computed: {
        isPast(): boolean {
            const when = this.item.when || this.item.plannedWhen;
            if (!when) {
                return false;
            }
            return DateTime.fromISO(when).plus({minutes: 1}) < DateTime.now();
        },
        cancelled(): boolean {
            return this.item.cancelled || false;
        }
    },
})
</script>

<template>
    <div class="card mb-1 dep-card" :class="{'past-card': isPast, 'cancelled-card': cancelled}">
        <div class="card-body d-flex py-0">
            <div class="col-1 align-items-center d-flex justify-content-center">
                <ProductIcon :product="item.line.product"/>
            </div>
            <div class="col-2 align-items-center d-flex me-3 justify-content-center">
                <span class="sr-only" v-if="cancelled">{{ trans("stationboard.stop-cancelled") }}</span>
                <LineIndicator
                    :productName="item.line.product"
                    :number="item.line.name !== null ? item.line.name : item.line.fahrtNr"
                />
            </div>
            <div class="col align-items-center d-flex second-stop">
                <div>
                    <span class="fw-bold fs-6">{{ item.direction }}</span><br>
                    <span v-if="item.stop.name !== station.name" class="text-muted small font-italic">
                        {{ trans("stationboard.dep") }} {{ item.stop.name }}
                    </span>
                </div>
            </div>
            <div class="col-auto ms-auto align-items-center d-flex">
                <div v-if="item.delay">
                    <span class="text-muted text-decoration-line-through">
                        {{ formatTime(item.plannedWhen) }}<br>
                    </span>
                    <span>{{ formatTime(item.when) }}</span>
                </div>
                <div v-else>
                    <span>{{ formatTime(item.plannedWhen) }}</span>
                </div>
            </div>
        </div>
    </div>

</template>

<style scoped lang="scss">
@import "../../../sass/_variables.scss";
.dep-card {
    min-height: 4.25rem;
}

.past-card {
    opacity: 50%;
}

.cancelled-card {
    opacity: 50%;
    background-color: $red !important;
    color: $white;
    text-decoration: line-through;
    text-decoration-thickness: 2px;
}
</style>
