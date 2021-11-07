<template>
    <LayoutBasic>
        <div class="col-md-8 col-lg-7">
            <Spinner v-if="loading" class="mt-5"/>

            <div v-if="error" class="error">
                <p>{{ error }}</p>

                <p>
                    <button @click.prevent="fetchData">
                        {{ i18n.get("_.vue.tryAgain") }}
                    </button>
                </p>
            </div>

            <div v-if="status">
                <Status :likes="likes" :polyline="polyline" :status="status" :stopovers="stopovers"
                        show-date="true"></Status>
            </div>
        </div>
    </LayoutBasic>
</template>

<script>
import Status from "../Status";
import moment from "moment";
import {StatusModel} from "../../js/APImodels";
import LayoutBasic from "../layouts/Basic";
import Spinner from "../Spinner";
import StatusApi from "../../js/ApiClient/Status";

export default {
    name: "SingleStatus",
    inject: ["notyf"],
    data() {
        return {
            error: false,
            loading: false,
            status: StatusModel,
            polyline: null, //ToDo Typedef
            stopovers: null, //ToDo Typedef
            likes: null,
            moment: moment,
            metaData: {
                title: undefined,
                url: undefined,
                image: undefined,
                description: undefined,
                robots: undefined
            }
        };
    },
    metaInfo() {
        return {
            title: this.metaData.title,
            meta: [
                {name: "robots", content: this.metaData.robots, vmid: "robots"},
                {name: "description", content: this.metaData.description, vmid: "description"},
                {name: "DC.Description", content: this.metaData.description, vmid: "DC.Description"},
                {name: "og:title", content: this.metaData.title, vmid: "og:title"},
                {name: "og:url", content: this.metaData.url, vmid: "og:url"},
                {name: "og:image", content: this.metaData.image, vmid: "og:image"},
                {name: "og:description", content: this.metaData.description, vmid: "og:description"},
                {name: "twitter:card", content: "summary", vmid: "twitter:card"},
                {name: "twitter:site", content: "@traewelling", vmid: "twitter:site"},
                {name: "twitter:title", content: this.metaData.title, vmid: "twitter:title"},
                {name: "twitter:description", content: this.metaData.description, vmid: "twitter:description"},
                {name: "twitter:image", content: this.metaData.image, vmid: "twitter:image"}
            ]
        };
    },
    created() {
        if (this.statusData == null) {
            this.fetchData();
        } else {
            this.status = this.statusData;
            this.updateMetadata();
            this.fetchPolyline();
            this.fetchLikes();
        }
    },
    components: {
        Spinner,
        LayoutBasic,
        Status
    },
    props: {
        statusData: null
    },
    computed: {
        rilIdentifierOrigin() {
            if (this.status.train.origin.rilIdentifier) {
                return " (" + this.status.train.origin.rilIdentifier + ")";
            }
            return "";
        },
        rilIdentifierDestination() {
            if (this.status.train.destination.rilIdentifier) {
                return " (" + this.status.train.destination.rilIdentifier + ")";
            }
            return "";
        },
    },
    methods: {
        fetchData() {
            this.error   = null;
            this.loading = true;
            StatusApi
                .getById(this.$route.params.id)
                .then((data) => {
                    this.loading = false;
                    this.status  = data;
                    this.updateMetadata();
                    this.fetchPolyline();
                    this.fetchStopovers();
                    this.fetchLikes();
                })
                .catch((errors) => {
                    this.loading = false;
                    this.apiErrorHandler(errors);
                });
        },
        fetchPolyline() {
            StatusApi
                .fetchPolyLine(this.status.id)
                .then((data) => {
                    this.polyline = data;
                })
                .catch((error) => {
                    this.loading = false;
                    this.apiErrorHandler(error);
                });
        },
        fetchStopovers() {
            StatusApi
                .fetchStopovers(this.status.train.trip)
                .then((data) => {
                    this.stopovers = data;
                })
                .catch((error) => {
                    this.loading = false;
                    this.apiErrorHandler(error);
                });
        },
        fetchLikes() {
            StatusApi
                .fetchLikes(this.status.id)
                .then((data) => {
                    this.likes = data;
                })
                .catch((error) => {
                    this.loading = false;
                    this.apiErrorHandler(error);
                });
        },
        updateMetadata() {
            if (this.status.preventIndex) {
                this.metaData.robots = "noindex";
            }
            this.metaData.description = this.i18n.choice("_.description.status", 1, {
                "username": this.status.username,
                "origin": this.status.train.origin.name + this.rilIdentifierOrigin,
                "destination": this.status.train.destination.name + this.rilIdentifierDestination,
                "date": this.moment(this.status.train.origin.departure).format("LLL"),
                "lineName": this.status.train.lineName
            });
            this.metaData.url         = window.location.origin + this.$router.resolve({
                name: "singleStatus",
                params: {id: this.status.id}
            }).href; //ToDo combine all window.location.origin...-methods to one single method
            this.metaData.title       = this.i18n.choice("_.status.ogp-title", 1, {"name": this.status.username});
            this.metaData.image       = "/profile/" + this.status.username + "/profilepicture";
        }
    }
};
</script>

<style scoped>

</style>
