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
        <div class="col-md-9 col-lg-7">
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
    metaInfo() {
        return {
            title: this.i18n.get("_.menu.dashboard")
        };
    },
    components: {
        Spinner,
        LayoutBasic,
        StationForm,
        Status
    },
    mounted() {
        this.fetchData();
    },
    methods: {
        showDate(item, statuses) {
            let index = statuses.indexOf(item);
            if (index === -1 || index === 0) {
                return true;
            }
            return moment(item.train.origin.departure).date() !== moment(statuses[index - 1].train.origin.departure).date();
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
