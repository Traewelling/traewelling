<template>
    <LayoutBasic>
        <ul id="ex1" class="nav nav-tabs nav-fill d-md-none  px-0 mt-n4" role="tablist">
            <li class="nav-item" role="presentation">
                <router-link :to="{ name: 'dashboard' }" active-class="" class="nav-link" exact-active-class="active"
                             role="tab">
                    {{ i18n.get('_.menu.dashboard') }}
                </router-link>
            </li>
            <li class="nav-item" role="presentation">
                <router-link :to="{ name: 'dashboard.global' }" class="nav-link" role="tab">
                    {{ i18n.get("_.menu.globaldashboard") }}
                </router-link>
            </li>
        </ul>
        <div class="col-sm-12 col-md-7">
            <StationForm class="d-none d-md-block"/>
            <Spinner v-if="loading" class="mt-5"/>

            <div v-if="futureStatuses.length > 0 && !loading" id="accordionFutureCheckIns"
                 class="accordion accordion-flush mt-5 mb-0">
                <div class="accordion-item">
                    <h1 id="flush-headingOne" class="accordion-header">
                        <button
                            aria-controls="future-check-ins"
                            aria-expanded="false"
                            class="accordion-button collapsed px-0"
                            data-mdb-target="#future-check-ins"
                            data-mdb-toggle="collapse"
                            type="button"
                        >
                            {{ i18n.get('_.dashboard.future') }}
                        </button>
                    </h1>
                    <div
                        id="future-check-ins"
                        aria-labelledby="flush-headingOne"
                        class="accordion-collapse collapse"
                        data-mdb-parent="#accordionFutureCheckIns"
                    >
                        <div class="accordion-body p-0">
                            <Status v-for="status in futureStatuses" v-bind:key="status.id"
                                    :show-date="showDate(status, statuses)"
                                    :status="status"
                                    v-bind:stopovers="stopovers"/>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="statuses">
                <Status v-for="status in statuses" v-bind:key="status.id"
                        :show-date="showDate(status, statuses)"
                        :status="status"
                        v-bind:stopovers="stopovers"/>

                <div v-if="links && links.next" class="text-center">
                    <button aria-label="i18n.get('_.menu.show-more')"
                            class="btn btn-primary btn-lg btn-floating mt-4"
                            @click.prevent="fetchMore">
                        <i aria-hidden="true" class="fas fa-caret-down"></i>
                    </button>
                </div>
            </div>
        </div>
        <ModalConfirm
            ref="successModal"
            :title-text="i18n.get('_.controller.transport.checkin-heading')"
            :confirm-text="i18n.get('_.messages.cookie-notice-button')"
            confirm-button-color="btn-success"
            header-class="bg-success text-white"
        >
            <div class="p-0 m-0" v-if="$props.checkin">
                <p class="text-center">
                    {{
                        i18n.choice(
                            "_.controller.transport.checkin-ok",
                            /\s/.test(checkin.status.train.lineName),
                            {lineName: checkin.status.train.lineName}
                        )
                    }}
                </p>

                <h4 v-if="checkin.alsoOnThisConnection.length > 0">
                    {{ i18n.choice("_.controller.transport.also-in-connection", checkin.alsoOnThisConnection.length) }}
                </h4>
                <div v-if="checkin.alsoOnThisConnection.length > 0" class="list-group">
                    <router-link v-for="status in checkin.alsoOnThisConnection"
                                 v-bind:key="status.id"
                                 :to="{ name: 'singleStatus', params: {id: status.id, statusData: status }}"
                                 class="list-group-item list-group-item-action">
                        <div class="row">
                            <div class="col-2">
                                <img :alt="status.username"
                                     :src="`/profile/${status.username}/profilepicture`"
                                     class="img-fluid rounded-circle">
                            </div>
                            <div aria-hidden="true" class="col">
                                <h5 class="mb-1 w-100">
                                    {{ status.displayName }}
                                    <small class="text-muted">@{{ status.username }}</small>
                                </h5>
                                {{ status.train.origin.name }}
                                <i aria-hidden="true" class="fas fa-arrow-right"></i>
                                {{ status.train.destination.name }}
                            </div>
                            <span class="sr-only">
                            {{
                                    i18n.choice("_.export.journey-from-to", 1, {
                                        origin: status.train.origin.name,
                                        destination: status.train.destination.name
                                    })
                                }}
                        </span>
                        </div>
                    </router-link>
                </div>
                <hr v-if="checkin.alsoOnThisConnection.length > 0">

                <h4 class="mt-3">{{ i18n.get("_.leaderboard.points") }}</h4>
                <div class="row py-2">
                    <div class="col-1"><i aria-hidden="true" class="fa fa-subway d-inline"></i></div>
                    <div class="col"><span>{{ i18n.get("_.export.title.train-type") }}</span></div>
                    <div class="col-4 text-end">
                        <small v-if="checkin.points.calculation.reason > 0"
                               class="text-danger text-decoration-line-through">
                            {{ originalPoints(checkin.points.calculation.base) }}
                        </small>
                        <strong v-if="checkin.points.calculation.reason <= 1">
                            &nbsp;{{ checkin.points.calculation.base }}
                        </strong>
                    </div>
                </div>
                <div class="row py-2 border-top">
                    <div class="col-1"><i aria-hidden="true" class="fa fa-route d-inline"></i></div>
                    <div class="col">
                        {{ i18n.get("_.leaderboard.distance") }}:
                        {{ (checkin.status.train.distance / 1000).toFixed(2) }}<small>km</small>
                    </div>
                    <div class="col-4 text-end">
                        <small v-if="checkin.points.calculation.reason > 0"
                               class="text-danger text-decoration-line-through">
                            {{ originalPoints(checkin.points.calculation.distance) }}
                        </small>
                        <strong v-if="checkin.points.calculation.reason <= 1">
                            &nbsp;{{ checkin.points.calculation.distance }}
                        </strong>
                    </div>
                </div>
                <div class="row py-2 text-bold border-top border-black">
                    <div class="col-1"><i aria-hidden="true" class="fa fa-dice-d20 d-inline"></i></div>
                    <div class="col">{{ i18n.get("_.checkin.points.earned") }}</div>
                    <div class="col-4 text-end">{{ checkin.points.points }}</div>
                </div>

                <div v-if="checkin.points.calculation.reason === 2" class="alert alert-danger mt-3 mb-0" role="alert">
                    <i aria-hidden="true" class="fas fa-exclamation-triangle d-inline"></i> &nbsp;
                    {{ i18n.get("_.checkin.points.could-have") }}
                    <router-link class="alert-link" to="/about#points-calculation" @click="$refs.successModal.hide()">
                        {{ i18n.get("_.generic.why") }}
                    </router-link>
                </div>

                <div v-if="checkin.points.calculation.reason === 3" class="alert alert-info mt-3 mb-0" role="alert">
                    <i aria-hidden="true" class="fas fa-info-circle d-inline"></i> &nbsp;
                    {{ i18n.get("_.checkin.points.forced") }}
                </div>
            </div>
        </ModalConfirm>
    </LayoutBasic>
</template>

<script>
import Status from "../Status";
import moment from "moment";
import {StatusModel} from "../../js/APImodels";
import StationForm from "../StationForm";
import LayoutBasic from "../layouts/Basic";
import Spinner from "../Spinner";
import Dashboard from "../../js/ApiClient/Dashboard";
import ApiStatus from "../../js/ApiClient/Status";
import ModalConfirm from "../ModalConfirm";

export default {
    name: "dashboard",
    inject: ["notyf"],
    data() {
        return {
            loading: true,
            statuses: [],
            futureStatuses: [StatusModel],
            stopovers: [], //ToDo Typedef
            moment: moment,
            links: null,
        };
    },
    props: ["checkin"],
    metaInfo() {
        return {
            title: this.i18n.get("_.menu.dashboard")
        };
    },
    components: {
        ModalConfirm,
        Spinner,
        LayoutBasic,
        StationForm,
        Status
    },
    mounted() {
        this.fetchData();
        if (this.$props.checkin) {
            this.$refs.successModal.show();
        }
    },
    methods: {
        showDate(item, statuses) {
            let index = statuses.indexOf(item);
            if (index === -1 || index === 0) {
                return true;
            }
            return moment(item.train.origin.departure).date() !== moment(statuses[index - 1].train.origin.departure).date();
        },
        duration(inDuration) {
            // ToDo: This needs localization, currently handled in `durationToSpan`
            const duration = moment.duration(inDuration, "minutes").asMinutes();
            let minutes    = duration % 60;
            let hours      = Math.floor(duration / 60);

            return hours + "h " + minutes + "m";
        },
        originalPoints(points) {
            let factor = this.checkin.points.calculation.factor;
            return points / (factor !== 0 ? factor : 1);
        },
        fetchData() {
            this.statuses = this.futureStatuses = [];
            Dashboard
                .getFuture()
                .then((data) => {
                    this.futureStatuses = data;
                })
                .catch((error) => {
                    this.loading = false;
                    this.apiErrorHandler(error);
                });

            Dashboard
                .get(this.$route.path === "/dashboard/global")
                .then((data) => {
                    this.loading = false;
                    if (!Object.keys(data.data).length && this.$route.path === "/dashboard") {
                        this.$router.push({name: "dashboard.global"}); //ToDo: Redirect if following nobody
                        this.fetchData();
                    }
                    this.statuses = data.data;
                    this.links    = data.links;
                    this.fetchStopovers(this.statuses);
                })
                .catch((error) => {
                    this.apiErrorHandler(error);
                });
        },
        fetchMore() {
            this.fetchMoreData(this.links.next)
                .then((data) => {
                    this.statuses = this.statuses.concat(data.data);
                    this.links    = data.links;
                    this.fetchStopovers(data.data);
                });
        },
        fetchStopovers(statuses) {
            let tripIds = "";
            statuses.forEach((status) => {
                if (!(status.train.trip in this.stopovers)) {
                    tripIds += (status.train.trip + ",");
                }
            });
            ApiStatus
                .fetchStopovers(tripIds)
                .then((data) => {
                    this.stopovers = this.stopovers.concat(data);
                })
                .catch((error) => {
                    this.loading = false;
                    this.apiErrorHandler(error);
                });
        },
    },
    watch: {
        '$route'() {
            this.fetchData();
        }
    },
};
</script>

<style scoped>
.accordion-button {
    background: none;
}
</style>
