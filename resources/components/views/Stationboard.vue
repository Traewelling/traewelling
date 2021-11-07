<template>
    <LayoutBasic>
        <div class="col-md-8 col-lg-7">
            <StationForm :hideTimepicker="!this.$route.query.station" :next="times.next" :now="times.now"
                         :prev="times.prev"
                         v-on:refresh="fetchData"></StationForm>
            <div v-if="this.$route.query.station" class="card">
                <div class="card-header">
                    <div class="float-end">
                        <a :aria-label="i18n.get('_.modals.setHome-title')" href="#"
                           v-on:click.prevent="toggleSetHomeModal">
                            <i aria-hidden="true" class="fa fa-home"></i>
                        </a>
                    </div>
                    <span v-if="station" id="stationTableHeader">
                        {{ station.name }}
                        <small>
                            <i aria-hidden="true" class="far fa-clock fa-sm"></i>
                            {{ moment(this.times.now).format("LT") }} ({{ moment(this.times.now).format("L") }})
                        </small>
                    </span>
                </div>

                <Spinner v-if="loading" class="mt-5"/>
                <div v-else-if="!departures || Object.keys(departures).length === 0"
                     class="card-body text-center text-danger text-bold">
                    {{ i18n.get('_.stationboard.no-departures') }}
                </div>
                <div v-else class="card-body p-0 table-responsive">
                    <table aria-labelledby="stationTableHeader"
                           class="table table-dark table-borderless table-hover table-striped m-0">
                        <thead>
                            <tr>
                                <th class="ps-2 ps-md-4" scope="col">
                                    {{ i18n.get('_.stationboard.dep-time') }}
                                </th>
                                <th class="px-0" scope="col">{{ i18n.get('_.stationboard.line') }}</th>
                                <th scope="col">{{ i18n.get('_.stationboard.destination') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(departure, index) in departures"
                                v-if="index > 0 && index < Object.keys(departures).length
                                && moment(departures[index - 1].when).isBefore(requestTime)
                                && moment(departure.when).isAfter(requestTime)" class="text-center table-primary py-0">
                                <td class="py-0" colspan="3">
                                    <small>{{
                                            i18n.choice('_.request-time', 1, {'time': requestTime.format("LT")})
                                        }}</small>
                                </td>
                            </tr>
                            <tr v-else v-on:click="goToTrip(departure)">
                                <td class="ps-2 ps-md-4">
                                    <span v-if="departure.cancelled" class="text-danger">
                                        {{ i18n.get('_.stationboard.stop-cancelled') }}
                                    </span>
                                    <span v-else>
                                        <span :class="{
                                            'text-success': departure.delay === 0,
                                            'text-warning': departure.delay && departure.delay < 600,
                                            'text-danger': departure.delay >= 600 }">
                                            <span>{{ moment(departure.when).format("LT") }}</span>
                                        </span>
                                        <small v-if="departure.delay" class="text-muted text-decoration-line-through">
                                            {{ moment(departure.plannedWhen).format("LT") }}
                                        </small>
                                    </span>
                                </td>
                                <td class="text-nowrap px-0">
                                    <img v-if="images.includes(departure.line.product)"
                                         :alt="departure.line.product"
                                         :src="`/img/${departure.line.product}.svg`"
                                         class="product-icon">
                                    <i v-else aria-hidden="true" class="fa fa-train"></i>
                                    &nbsp;
                                    <span :class="{ 'text-decoration-line-through text-danger': departure.cancelled}">
                                        <span v-if="departure.line.name">{{ departure.line.name }}</span>
                                        <span v-else>{{ departure.line.fahrtNr }}</span>
                                    </span>
                                </td>
                                <td :class="{ 'text-decoration-line-through text-danger': departure.cancelled}"
                                    class="text-wrap">
                                    {{ departure.direction }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <ModalConfirm v-if="!loading && station !== null"
                      ref="confirmHomeModal"
                      :abort-text="i18n.get('_.menu.abort')"
                      :body-text="i18n.choice('_.modals.setHome-body', 1, {'stationName': this.station.name})"
                      :confirm-text="i18n.get('_.modals.edit-confirm')"
                      :title-text="i18n.get('_.modals.setHome-title')"
                      confirm-button-color="btn-success"
                      v-on:confirm="setHome"
        ></ModalConfirm>
    </LayoutBasic>
</template>

<script>
import StationForm from "../StationForm";
import ModalConfirm from "../ModalConfirm";
import moment from "moment";
import {travelImages} from "../../js/APImodels";
import LayoutBasic from "../layouts/Basic";
import Spinner from "../Spinner";
import Checkin from "../../js/ApiClient/Checkin";

export default {
    name: "Stationboard",
    inject: ["notyf"],
    components: {
        Spinner,
        LayoutBasic,
        StationForm,
        ModalConfirm
    },
    data() {
        return {
            station: null,
            departures: null,
            requestTime: null,
            times: {
                now: 0,
                prev: 0,
                next: 0
            },
            loading: false,
            images: travelImages,
            moment: moment
        };
    },
    mounted() {
        if (this.$route.query.station) {
            this.fetchData();
        }
        this.requestTime = moment();
    },
    methods: {
        fetchData() {
            this.loading = true;
            this.station = null;

            Checkin
                .getDepartures(this.$route.query.station, this.$route.query.when, this.$route.query.travelType)
                .then((data) => {
                    this.station    = data.meta.station;
                    this.times      = data.meta.times;
                    this.departures = data.data;
                    this.loading    = false;
                })
                .catch((error) => {
                    this.loading = false;
                    this.apiErrorHandler(error);
                });
        },
        goToTrip(departure) {
            if (departure.cancelled) {
                return;
            }
            this.$router.push({
                name: "trains.trip", query: {
                    tripId: departure.tripId,
                    lineName: departure.line.name ?? departure.line.fahrtNr,
                    start: departure.station.id,
                    departure: departure.plannedWhen
                }
            });
        },
        toggleSetHomeModal() {
            this.$refs.confirmHomeModal.show();
        },
        setHome() {
            Checkin.saveHome((data) => {
                this.result = data;
                this.$auth.fetch();
                this.notyf.success(this.i18n.choice("_.user.home-set", 1, {"station": this.result.name}));
            })
                .catch((error) => {
                    this.apiErrorHandler(error);
                });
        }
    }
};
</script>

<style scoped>

</style>
