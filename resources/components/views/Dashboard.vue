<template>
    <LayoutBasic>
        <div class="row justify-content-center align-content-center">
            <div class="col-md-8 col-lg-7">
                <StationForm></StationForm>
                <div v-if="loading" class="loading">
                    {{ i18n.get("_.vue.loading") }}
                </div>

                <div v-if="error" class="error">
                    <p>{{ error }}</p>

                    <p>
                        <button @click.prevent="fetchData">
                            {{ i18n.get("_.vue.tryAgain") }}
                        </button>
                    </p>
                </div>
                <!-- ToDo Future Check-ins -->
                <div v-if="statuses">
                    <Status v-for="status in statuses" v-bind:key="status.id"
                            :show-date="showDate(status, statuses)"
                            :status="status"
                            v-bind:stopovers="stopovers"/>

                    <div v-if="links && links.next" class="text-center">
                        <button class="btn btn-primary btn-lg btn-floating mt-4" @click.prevent="fetchMore">
                            <i aria-hidden="true" class="fas fa-caret-down"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </LayoutBasic>
</template>

<script>
import axios from "axios";
import Status from "../Status";
import moment from "moment";
import {StatusModel} from "../../js/APImodels";
import StationForm from "../StationForm";
import LayoutBasic from "../layouts/Basic";

export default {
    data() {
        return {
            loading: true,
            error: null,
            statuses: [StatusModel],
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
            this.error = this.statuses = null;
            axios
                .get(this.$route.path)
                .then((response) => {
                    this.loading = false;
                    if (!Object.keys(response.data.data).length && this.$route.path === "/dashboard") {
                        this.$router.push({name: "dashboard.global"}); //ToDo: Redirect if following nobody
                        this.fetchData();
                    }
                    this.statuses = response.data.data;
                    this.links    = response.data.links;
                    this.fetchStopovers(this.statuses);
                })
                .catch((error) => {
                    this.loading = false;
                    this.error   = error.data.message || error.message;
                });
        },
        fetchMore() {
            this.error = null;
            axios
                .get(this.links.next)
                .then((response) => {
                    this.statuses = this.statuses.concat(response.data.data);
                    this.links    = response.data.links;
                    this.fetchStopovers(response.data.data)
                })
                .catch((error) => {
                    this.loading = false;
                    this.error   = error.data.message || error.message;
                })
        },
        fetchStopovers(statuses) {
            let tripIds = "";
            statuses.forEach((status) => {
                if (!(status.train.trip in this.stopovers)) {
                    tripIds += (status.train.trip + ",");
                }
            });
            axios
                .get("/stopovers/" + tripIds)
                .then((response) => {
                    this.stopovers = this.stopovers.concat(response.data.data);
                })
                .catch((error) => {
                    this.loading = false;
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

</style>
