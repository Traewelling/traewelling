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
        <div v-if="loading || statusesLoading">
            {{ i18n.get("_.vue.loading") }}
        </div>

        <div v-if="!statusesLoading && !loading" class="row justify-content-center mt-5">
            <div v-if="statuses.length > 0" class="col-md-8 col-lg-7">

                <div v-if="statuses">
                    <Status v-for="status in statuses" v-bind:key="status.id" :status="status"></Status>
                </div>
                <div class="mt-5">
                    $statuses->links()
                </div>
            </div>
        </div>
    </HeroLayout>
</template>

<script>
import Status from "../Status";
import moment from "moment";
import axios from "axios";
import {EventModel, StatusModel} from "../../js/APImodels";
import LayoutBasic from "../layouts/Basic";
import HeroLayout from "../layouts/HeroLayout";

export default {
    name: "Event",
    //ToDo add Meta Tags
    data() {
        return {
            username: this.$route.params.username,
            loading: false,
            statusesLoading: false,
            event: EventModel,
            statuses: [StatusModel]
        };
    },
    components: {
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
    created() {
        this.fetchData();
    },
    methods: {
        fetchData() {
            this.error   = null;
            this.loading = true;
            axios
                .get("/event/" + this.$route.params.slug)
                .then((response) => {
                    this.loading = false;
                    this.event   = response.data.data;
                    this.fetchStatuses();
                })
                .catch((error) => {
                    this.loading = false;
                    this.error   = error.data.message || error.message;
                });
        },
        fetchStatuses() {
            this.error           = null;
            this.statusesLoading = true;
            axios
                .get("/event/" + this.$route.params.slug + "/statuses")
                .then((response) => {
                    this.statusesLoading = false;
                    this.statuses        = response.data.data;
                })
                .catch((error) => {
                    this.statusesLoading = false;
                    this.error           = error.data.message || error.message;
                });
        }
    }
};
</script>

<style scoped>

</style>
