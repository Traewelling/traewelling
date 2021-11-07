<template>
    <LayoutBasic>
        <div class="col-md-8 col-lg-7">
            <Spinner v-if="loading" class="mt-5"/>
            <div v-if="hafasTrip != null" id="trip-heading" class="card">
                <div class="card-header">
                    <div class="float-end">
                        <a href="#" @click="showModal(lastStation)">
                            <i aria-hidden="true" class="fa fa-fast-forward"></i>
                        </a>
                    </div>
                    <img v-if="images.includes(hafasTrip.category)"
                         :alt="hafasTrip.category"
                         :src="`/img/${hafasTrip.category}.svg`"
                         class="product-icon">
                    <i v-else aria-hidden="true" class="fa fa-train"></i>
                    {{ this.hafasTrip.lineName }}
                    <i aria-hidden="true" class="fas fa-arrow-alt-circle-right"></i>
                    {{ this.hafasTrip.destination.name }}
                </div>

                <div class="card-body p-0 table-responsive">
                    <table aria-describedby="trip-heading"
                           class="table table-dark table-borderless table-hover table-striped m-0">
                        <thead>
                            <tr>
                                <th scope="col">{{ i18n.get('_.stationboard.stopover') }}</th>
                                <th scope="col"></th>
                                <th class="ps-0" scope="col"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="stop in stopovers" @click="showModal(stop)">
                                <td :class="{ 'text-danger text-decoration-line-through': stop.cancelled}" class="px-2">
                                    {{ stop.name }}
                                    <br/>
                                    <span v-if="stop.arrivalPlanned" class="d-sm-inline d-md-none ms-4">
                                        {{ i18n.get('_.stationboard.arr') }}&nbsp;
                                        <span :class="delay(stop.arrivalPlanned, stop.arrivalReal)">
                                            {{ moment(stop.arrival).format("LT") }}
                                        </span>
                                        <small v-if="stop.isArrivalDelayed"
                                               class="text-muted text-decoration-line-through">
                                            {{ moment(stop.arrivalPlanned).format("LT") }}
                                        </small>
                                    </span>
                                    <br/>
                                    <span v-if="stop.departurePlanned" class="d-sm-inline d-md-none ms-4">
                                        {{ i18n.get('_.stationboard.dep') }}&nbsp;
                                        <span :class="delay(stop.departurePlanned, stop.departureReal)">
                                            {{ moment(stop.departure).format("LT") }}
                                        </span>
                                        <small v-if="stop.isDepartureDelayed"
                                               class="text-muted text-decoration-line-through">
                                            {{ moment(stop.departurePlanned).format("LT") }}
                                        </small>
                                    </span>
                                </td>
                                <td v-if="!stop.cancelled">
                                    <span v-if="stop.arrivalPlanned" class="d-none d-md-inline">
                                        {{ i18n.get('_.stationboard.arr') }}&nbsp;
                                        <span :class="delay(stop.arrivalPlanned, stop.arrivalReal)">
                                            {{ moment(stop.arrival).format("LT") }}
                                        </span>
                                        <small v-if="stop.isArrivalDelayed"
                                               class="text-muted text-decoration-line-through">
                                            {{ moment(stop.arrivalPlanned).format("LT") }}
                                        </small>
                                    </span>
                                    <br/>
                                    <span v-if="stop.departurePlanned" class="d-none d-md-inline">
                                        {{ i18n.get('_.stationboard.dep') }}&nbsp;
                                        <span :class="delay(stop.departurePlanned, stop.departureReal)">
                                            {{ moment(stop.departure).format("LT") }}
                                        </span>
                                        <small v-if="stop.isDepartureDelayed"
                                               class="text-muted text-decoration-line-through">
                                            {{ moment(stop.departurePlanned).format("LT") }}
                                        </small>
                                    </span>
                                </td>
                                <td v-else class="text-danger">
                                    {{ i18n.get('_.stationboard.stop-cancelled') }}
                                </td>
                                <td :class="{ 'text-danger text-decoration-line-through': stop.cancelled}"
                                    class="ps-0">
                                    {{ stop.platform }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <CheckInModal
            ref="checkInModal"
            :destination="destination"
            :train-data="trainData"
        ></CheckInModal>
    </LayoutBasic>
</template>

<script>
import {travelImages} from "../../js/APImodels";
import moment from "moment";
import CheckInModal from "../CheckInModal";
import LayoutBasic from "../layouts/Basic";
import Spinner from "../Spinner";
import Checkin from "../../js/ApiClient/Checkin";

export default {
    name: "Trip",
    inject: ["notyf"],
    components: {Spinner, LayoutBasic, CheckInModal},
    data() {
        return {
            loading: false,
            images: travelImages,
            hafasTrip: null,
            stopovers: null,
            lastStation: null,
            moment: moment,
            destination: null,
            trainData: {
                tripId: 0,
                lineName: "",
                start: 0,
                destination: 0,
                departure: 0,
                arrival: 0
            }
        };
    },
    mounted() {
        this.fetchData();
    },
    methods: {
        fetchData() {
            this.loading = true;
            this.station = null;
            const query  = this.$route.query;
            Checkin
                .getTrip(query.tripId, query.lineName, query.start)
                .then((data) => {
                    this.hafasTrip   = data;
                    this.stopovers   = this.hafasTrip.stopovers.filter((item) => {
                        return moment(item.arrivalPlanned).isAfter(moment(this.$route.query.departure));
                    });
                    this.lastStation = this.hafasTrip.stopovers.pop();
                    this.loading     = false;
                })
                .catch((error) => {
                    this.loading = false;
                    this.apiErrorHandler(error);
                });
        },
        showModal(stop) {
            this.trainData   = {
                tripId: this.$route.query.tripId,
                lineName: this.$route.query.lineName,
                start: this.$route.query.start,
                destination: stop.id,
                departure: this.$route.query.departure,
                arrival: stop.arrivalPlanned
            };
            this.destination = stop.name;
            this.$refs.checkInModal.show();
        },
        delay(planned, current) {
            const delay = moment(current).diff(moment(planned), "seconds");

            if (delay === 0) {
                return "text-success";
            } else if (delay < 600) {
                return "text-warning";
            } else if (delay >= 600) {
                return "text-danger";
            }
        }
    }
};
</script>

<style scoped>

</style>
