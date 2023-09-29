<script>
import FullScreenModal from "./FullScreenModal.vue";
import ModalComponent from "./ModalComponent.vue";

export default {
    name: "CheckinSuccessHelper",
    components: {ModalComponent, FullScreenModal},
    data() {
        return {
            points: {calculation: {}},
            alsoOnThisConection: [],
        };
    },
    methods: {
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
            <p>Für deinen Checkin erhältst du {{ points.points }} Punkte!</p>
            <p v-if="points.calculation.reason === 1 || points.calculation.reason === 2" class="text-muted">
                Du hast zu weit vor/nach deiner Fahrt eingecheckt. Ansonsten hättest du {{ points.points / points.calculation.factor }} Punkte bekommen.
            </p>
            <p v-if="points.calculation.reason === 3" class="text-danger">Da du deinen Checkin forciert hast, bekommst du keine Punkte.</p>

            <template v-if="alsoOnThisConection.length > 0">
                <h5 class="mt-5">In deiner Verbindung haben folgende Leute eingecheckt:</h5>
                <div class="list-group">
                    <a href="#" v-for="status in alsoOnThisConection" class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
                        <div class="d-flex gap-2 w-100 justify-content-between">
                            <div>
                                <h6 class="mb-0 fw-bold opacity-75">{{status.username}}<small class="text-muted">@HerrLevin_</small></h6>
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
