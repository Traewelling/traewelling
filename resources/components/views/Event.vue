<template>
    <HeroLayout>
        <template v-slot:hero>
            <div class="row justify-content-center">
                <div class="text-white col-md-8 col-lg-7">
                    <h1 class="card-title font-bold">
                        <strong> {{ event.name }}
                            <code class="text-white">#{{ event.hashtag }}</code>
                        </strong>
                    </h1>
                    <h3>
                        <span class="font-weight-bold">
                            <i aria-hidden="true" class="fa fa-route d-inline"/>&nbsp;
                            {{ event.trainDistance.toFixed(0) }}
                        </span>
                        <span class="small font-weight-lighter">km</span>
                        <span class="font-weight-bold ps-sm-2">
                            <i aria-hidden="true" class="fa fa-stopwatch d-inline"/>&nbsp;{{ duration }}
                        </span>
                        <br class="d-block d-sm-none">
                        <span class="font-weight-bold ps-sm-2">
                            <i aria-hidden="true" class="fa fa-user"/>&nbsp;{{ event.host }}
                        </span>
                        <span class="font-weight-bold ps-sm-2 text-nowrap">
                            <i aria-hidden="true" class="fa fa-link"/>&nbsp;<a :href="event.url"
                                                                               class="text-white">{{ event.url }}</a>
                        </span>
                    </h3>
                    <h2>
                        <span class="font-weight-bold"><i aria-hidden="true" class="fa fa-train"/></span>
                        <span class="font-weight-bold">
                            <a class="text-white" href="asdf">{{ event.station.name }}</a>
                        </span>
                    </h2>
                </div>
            </div>
        </template>
        <Spinner v-if="loading || statusesLoading" class="mt-5"/>
        <div v-else class="row justify-content-center mt-5">
            <div v-if="statuses.length > 0" class="col-md-8 col-lg-7">
                <div v-if="statuses">
                    <Status v-for="status in statuses" v-bind:key="status.id" :status="status"></Status>
                    <div v-if="links && links.next" class="text-center">
                        <button aria-label="i18n.get('_.menu.show-more')"
                                class="btn btn-primary btn-lg btn-floating mt-4"
                                @click.prevent="fetchMore">
                            <i aria-hidden="true" class="fas fa-caret-down"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </HeroLayout>
</template>

<script>
import Status from "../Status";
import moment from "moment";
import {EventModel, StatusModel} from "../../js/APImodels";
import LayoutBasic from "../layouts/Basic";
import HeroLayout from "../layouts/HeroLayout";
import Spinner from "../Spinner";
import Event from "../../js/ApiClient/Event";

export default {
    name: "Event",
    data() {
        return {
            username: this.$route.params.username,
            loading: false,
            statusesLoading: false,
            event: EventModel,
            statuses: [StatusModel],
            links: null
        };
    },
    metaInfo() {
        return {
            title: this.event.name //ToDo Add more Meta Tags
        };
    },
    components: {
        Spinner,
        HeroLayout,
        LayoutBasic,
        Status
    },
    computed: {
        duration() {
            const duration = moment.duration(this.event.trainDuration, "minutes").asMinutes();
            let minutes    = duration % 60;
            let hours      = Math.floor(duration / 60);

            return hours + "h " + minutes + "m";
        },
    },
    mounted() {
        this.fetchData();
    },
    methods: {
        fetchData() {
            this.error   = null;
            this.loading = true;
            Event.fetchData(this.$route.params.slug)
                .then((data) => {
                    this.loading = false;
                    this.event   = data;
                    this.fetchStatuses();
                })
                .catch((error) => {
                    this.loading = false;
                    this.apiErrorHandler(error);
                });
        },
        fetchStatuses() {
            this.error           = null;
            this.statusesLoading = true;
            Event.fetchStatuses(this.$route.params.slug)
                .then((data) => {
                    this.statusesLoading = false;
                    this.statuses        = data.data;
                    this.links           = data.links;
                })
                .catch((error) => {
                    this.statusesLoading = false;
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
    }
};
</script>

<style scoped>

</style>
