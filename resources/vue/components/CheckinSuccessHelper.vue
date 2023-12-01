<script>
import FullScreenModal from "./FullScreenModal.vue";
import ModalComponent from "./ModalComponent.vue";
import {trans, transChoice} from "laravel-vue-i18n";

export default {
    name: "CheckinSuccessHelper",
    components: {ModalComponent, FullScreenModal},
    data() {
        return {
            points: {points: 0, calculation: {}},
            alsoOnThisConection: [],
        };
    },
    methods: {
        transChoice,
        trans,
        fetchData() {
            this.points              = JSON.parse(localStorage.getItem("points"));
            this.alsoOnThisConection = JSON.parse(localStorage.getItem("alsoOnThisConnection"));
            localStorage.removeItem("points");
            localStorage.removeItem("alsoOnThisConnection");
            console.log(this.alsoOnThisConection[0]);
            if (this.points && this.points.points) {
                this.$refs.modal.show();
            }
        }
    },
    mounted() {
        this.fetchData();
    }
}
</script>

<template>
    <ModalComponent ref="modal" header-class="bg-success text-white" title="Erfolgreich eingecheckt!" :hide-footer="true">
        <template #body>
            <p>{{ transChoice("checkin.points.earned", points.points, { points: points.points.toString() }) }}</p>
            <p v-if="points.calculation.reason === 1 || points.calculation.reason === 2" class="text-muted">
                {{ trans("checkin.points.could-have") }}
                {{ trans("checkin.points.full", { points: (points.points / points.calculation.factor).toString() }) }}
            </p>
            <p v-if="points.calculation.reason === 3" class="text-danger">{{ trans("checkin.points.forced") }}</p>

            <template v-if="alsoOnThisConection.length > 0">
                <h5 class="mt-5">{{ transChoice("controller.transport.also-in-connection", alsoOnThisConection.length) }}</h5>
                <div class="list-group">
                    <a href="#" v-for="status in alsoOnThisConection" class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
                        <div class="d-flex gap-2 w-100 justify-content-between">
                            <div>
                                <h6 class="mb-0 fw-bold opacity-75">{{status.username}}</h6>
                                <p class="mb-0">{{status.train.origin.name}} ➜ {{status.train.destination.name}}</p>
                            </div>
                        </div>
                    </a>
                </div>
            </template>
        </template>
    </ModalComponent>
</template>

<style scoped lang="scss">

</style>
