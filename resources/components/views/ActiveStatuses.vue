<template>
    <LayoutBasicNoSidebar>
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card sticky-top">
                    <Map :poly-lines="polylines" class="map embed-responsive embed-responsive-1by1"></Map>
                </div>
            </div>
            <div class="col-md-8 col-lg-6">
                <Spinner v-if="loading" class="mt-5"/>

                <div v-if="error" class="error">
                    <p>{{ error }}</p>

                    <p>
                        <button @click.prevent="fetchData">
                            {{ i18n.get("_.vue.tryAgain") }}
                        </button>
                    </p>
                </div>
                <div v-if="statuses">
                    <h4 class="mt-4"> {{ i18n.get("_.menu.active") }} </h4>
                    <Status v-for="status in statuses" v-bind:key="status.id" :status="status"
                            v-bind:stopovers="stopovers"></Status>
                </div>
            </div>
        </div>
    </LayoutBasicNoSidebar>
</template>

<script>
import axios from "axios";
import Status from "../Status";
import Map from "../Map";
import {StatusModel} from "../../js/APImodels";
import LayoutBasic from "../layouts/Basic";
import Spinner from "../Spinner";
import LayoutBasicNoSidebar from "../layouts/BasicNoSidebar";

export default {
    name: "ActiveStatuses",
    data() {
        return {
            loading: true,
            error: null,
            interval: null,
            statuses: [StatusModel],
            stopovers: null, //ToDo Typedef
            polylines: null //ToDo Typedef
        };
    },
    metaInfo() {
        return {
            title: this.i18n.get("_.menu.active"),
            meta: [
                {name: "robots", content: "index", vmid: "robots"},
                {name: "description", content: this.i18n.get("_.description.en-route"), vmid: "description"},
                {name: "DC.Description", content: this.i18n.get("_.description.en-route"), vmid: "DC.Description"}
            ]
        };
    },
    components: {
        LayoutBasicNoSidebar,
        Spinner,
        Status,
        Map,
        LayoutBasic,
    },
    created() {
        this.fetchData();
        this.startRefresh();
    },
    methods: {
        fetchData() {
            const oldStatuses = this.statuses;
            this.error        = this.statuses = null;
            axios
                .get("/statuses")
                .then((response) => {
                    this.loading = false;
                    // FixMe: Why is this comparison not working correctly?
                    if (oldStatuses != response.data.data) {
                        this.statuses = response.data.data;
                        if (this.statuses.length) {
                            this.fetchPolyline();
                            this.fetchStopovers();
                        }
                    }
                })
                .catch((error) => {
                    this.loading = false;
                    this.error   = error.data.message || error.message;
                });
        },
        fetchStopovers() {
            let tripIds = "";
            this.statuses.forEach((status) => {
                tripIds += (status.train.trip + ",");
            });
            axios
                .get("/stopovers/" + tripIds)
                .then((response) => {
                    this.stopovers = response.data.data;
                })
                .catch((error) => {
                    console.error(error);
                });
        },
        fetchPolyline() {
            let tripIds = "";
            this.statuses.forEach((status) => {
                tripIds += (status.id + ",");
            });
            axios
                .get("/polyline/" + tripIds)
                .then((response) => {
                    this.polylines = [response.data.data];
                })
                .catch((error) => {
                    console.error(error);
                });
        },
        startRefresh() {
            setInterval(() => (this.fetchData()), 70000);
        }
    },
};
</script>

<style scoped>

</style>
