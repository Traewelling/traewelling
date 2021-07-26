<template>
    <LayoutBasic>
        <div class="container">
            <div class="row justify-content-center">
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
                        <!--          ToDo Pagination-->
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
            stopovers: null, //ToDo Typedef
            moment: moment
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
                    this.fetchStopovers();
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
                    this.loading = false;
                });
        },
    },
};
</script>

<style scoped>

</style>
