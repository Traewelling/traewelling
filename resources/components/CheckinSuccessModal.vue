<template>
    <ModalConfirm
        ref="successModal"
        :title-text="i18n.get('_.controller.transport.checkin-heading')"
        :confirm-text="i18n.get('_.messages.cookie-notice-button')"
        confirm-button-color="btn-success"
        header-class="bg-success text-white"
    >
        <div class="p-0 m-0">
            <p class="text-center">
                {{
                    i18n.choice(
                        "_.controller.transport.checkin-ok",
                        /\s/.test(status.train.lineName),
                        {lineName: status.train.lineName}
                    )
                }}
            </p>

            <h4 v-if="alsoOnThisConnection.length > 0">
                {{ i18n.choice("_.controller.transport.also-in-connection", alsoOnThisConnection.length) }}
            </h4>
            <div v-if="alsoOnThisConnection.length > 0" class="list-group">
                <router-link v-for="otherStatus in alsoOnThisConnection"
                             v-bind:key="otherStatus.id"
                             :to="{ name: 'singleStatus', params: {id: otherStatus.id, statusData: otherStatus }}"
                             class="list-group-item list-group-item-action">
                    <div class="row">
                        <div class="col-2">
                            <img :alt="otherStatus.username"
                                 :src="`/profile/${otherStatus.username}/profilepicture`"
                                 class="img-fluid rounded-circle">
                        </div>
                        <div aria-hidden="true" class="col">
                            <h5 class="mb-1 w-100">
                                {{ otherStatus.displayName }}
                                <small class="text-muted">@{{ otherStatus.username }}</small>
                            </h5>
                            {{ otherStatus.train.origin.name }}
                            <i aria-hidden="true" class="fas fa-arrow-right"></i>
                            {{ otherStatus.train.destination.name }}
                        </div>
                        <span class="sr-only">
                            {{
                                i18n.choice("_.export.journey-from-to", 1, {
                                    origin: otherStatus.train.origin.name,
                                    destination: otherStatus.train.destination.name
                                })
                            }}
                        </span>
                    </div>
                </router-link>
            </div>
            <hr v-if="alsoOnThisConnection.length > 0">

            <h4 class="mt-3">{{ i18n.get("_.leaderboard.points") }}</h4>
            <div class="row py-2">
                <div class="col-1"><i aria-hidden="true" class="fa fa-subway d-inline"></i></div>
                <div class="col"><span>{{ i18n.get("_.export.title.train-type") }}</span></div>
                <div class="col-4 text-end">
                    <small v-if="calculation.reason > 0"
                           class="text-danger text-decoration-line-through">
                        {{ originalPoints(calculation.base) }}
                    </small>
                    <strong v-if="calculation.reason <= 1">
                        &nbsp;{{ calculation.base }}
                    </strong>
                </div>
            </div>
            <div class="row py-2 border-top">
                <div class="col-1"><i aria-hidden="true" class="fa fa-route d-inline"></i></div>
                <div class="col">
                    {{ i18n.get("_.leaderboard.distance") }}:
                    {{ (status.train.distance / 1000).toFixed(2) }}<small>km</small>
                </div>
                <div class="col-4 text-end">
                    <small v-if="calculation.reason > 0"
                           class="text-danger text-decoration-line-through">
                        {{ originalPoints(calculation.distance) }}
                    </small>
                    <strong v-if="calculation.reason <= 1">
                        &nbsp;{{ calculation.distance }}
                    </strong>
                </div>
            </div>
            <div class="row py-2 text-bold border-top border-black">
                <div class="col-1"><i aria-hidden="true" class="fa fa-dice-d20 d-inline"></i></div>
                <div class="col">{{ i18n.get("_.checkin.points.earned") }}</div>
                <div class="col-4 text-end">{{ points }}</div>
            </div>

            <div v-if="calculation.reason === 2" class="alert alert-danger mt-3 mb-0" role="alert">
                <i aria-hidden="true" class="fas fa-exclamation-triangle d-inline"></i> &nbsp;
                {{ i18n.get("_.checkin.points.could-have") }}
                <router-link class="alert-link" to="/about#points-calculation" @click="$refs.successModal.hide()">
                    {{ i18n.get("_.generic.why") }}
                </router-link>
            </div>

            <div v-if="calculation.reason === 3" class="alert alert-info mt-3 mb-0" role="alert">
                <i aria-hidden="true" class="fas fa-info-circle d-inline"></i> &nbsp;
                {{ i18n.get("_.checkin.points.forced") }}
            </div>
        </div>
    </ModalConfirm>
</template>

<script>
import ModalConfirm from "./ModalConfirm";
export default {
    name: "CheckinSuccessModal",
    components: {ModalConfirm},
    props: {
        points: null,
        calculation: null,
        status: null,
        alsoOnThisConnection: []
    },
    mounted() {
        this.$refs.successModal.show();
    }
};
</script>
