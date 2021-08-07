<template>
    <LayoutBasic>
        <div class="row">
            <div class="col-lg-8">
                <h4>
                    {{
                        i18n.choice('_.stats.personal', 1, {
                            'fromDate': moment(this.from).format('LLL'),
                            'toDate': moment(this.until).format('LLL')
                        })
                    }}
                </h4>
                <hr/>
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5>{{ i18n.get('_.stats.purpose') }}</h5>
                                <apexchart v-if="travelPurpose.length > 0" ref="purpose" :options="pieChartOptions"
                                           type="pie" width="100%"></apexchart>
                                <p v-else class="text-danger font-weight-bold mt-2">
                                    {{ i18n.get('_.stats.no-data') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5>{{ i18n.get('_.stats.categories') }}</h5>
                                <apexchart v-if="travelPurpose.length > 0" ref="categories" :options="pieChartOptions"
                                           type="pie" width="100%"></apexchart>
                                <p v-else class="text-danger font-weight-bold mt-2">
                                    {{ i18n.get('_.stats.no-data') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5>{{ i18n.get('_.stats.companies') }}</h5>
                                <apexchart v-if="trainProviders.length > 0" ref="companies" :options="pieChartOptions"
                                           :series="series" type="pie" width="100%"></apexchart>
                                <p v-else class="text-danger font-weight-bold mt-2">
                                    {{ i18n.get('_.stats.no-data') }}</p>
                            </div>
                        </div>
                    </div>
                    <hr/>
                    <div class="col-12 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5>{{ i18n.get('_.stats.volume') }} <small>{{ i18n.get('_.stats.per-week') }}</small>
                                </h5>
                                <apexchart v-if="travelTime.length > 0" ref="travelTimeChart"
                                           :options="barChartOptions" type="line" width="100%"></apexchart>
                                <p v-else class="text-danger font-weight-bold mt-2">{{
                                        i18n.get('_.stats.no-data')
                                    }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4"><h4>{{ i18n.get('_.stats.global') }}</h4>
                <hr/>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-4 text-center">
                                <i aria-hidden="true" class="fas fa-ruler fa-4x mt-1"></i>
                            </div>
                            <div class="col-8 text-center">
                <span class="font-weight-bold color-main fs-2">
                     number($globalStats->distance, 0)  km
                </span>
                                <br>
                                <small class="text-muted">{{ i18n.get('_.stats.global.distance') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-4 text-center">
                                <i aria-hidden="true" class="fas fa-clock fa-4x mt-1"></i>
                            </div>
                            <div class="col-8 text-center">
                <span class="font-weight-bold color-main fs-2">
                    {!! durationToSpan(secondsToDuration($globalStats->duration)) !!}
                </span>
                                <br>
                                <small class="text-muted">{{ i18n.get('_.stats.global.duration') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-4 text-center">
                                <i aria-hidden="true" class="fas fa-users fa-4x mt-1"></i>
                            </div>
                            <div class="col-8 text-center">
                <span class="font-weight-bold color-main fs-2">
                     $globalStats->user_count x
                </span>
                                <br>
                                <small class="text-muted">{{ i18n.get('_.stats.global.active') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
                <hr/>
                <small class="text-muted">*{{
                        i18n.choice('_.stats.global.explain', 1, {
                            'fromDate': 'a',
                            'toDate': 'b'
                        })
                    }}</small>
                <hr/>

            </div>
        </div>

    </LayoutBasic>
</template>

<script>
import LayoutBasic from "../layouts/Basic";
import VueApexCharts from 'vue-apexcharts'
import moment from "moment";

export default {
    name: "Charts",
    components: {
        LayoutBasic,
        apexchart: VueApexCharts,
        moment
    },
    data() {
        return {
            from: null,
            until: null,
            travelPurpose: [
                {name: '', count: 0, duration: 0},
            ],
            trainProviders: [
                {name: '', count: 0, duration: 0},
            ],
            travelCategories: [
                {name: '', count: 0, duration: 0},
            ],
            travelTime: [
                {name: '1 / 2021', value: 1},
                {name: '2 / 2021', value: 1},
                {name: '3 / 2021', value: 1},
                {name: '4 / 2021', value: 1},
                {name: '5 / 2021', value: 1},
                {name: '6 / 2021', value: 17},
                {name: '7 / 2021', value: 28},
                {name: '8 / 2021', value: 4},
                {name: '9 / 2021', value: 1},
                {name: '10 / 2021', value: 1},
                {name: '11 / 2021', value: 1},
                {name: '12 / 2021', value: 1},
            ],
            pieChartOptions: {
                chart: {
                    type: "pie",
                },
                dataLabels: {
                    enabled: true
                },
                legend: false,
                series: [],
                noData: {
                    text: this.i18n.get("_.menu.loading")
                }
            },
            barChartOptions: {
                chart: {
                    height: 350,
                    type: 'line',
                    zoom: {
                        enabled: false
                    },
                    toolbar: false
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'straight'
                },
                grid: {
                    row: {
                        colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
                        opacity: 0.5
                    },
                },
                xaxis: {
                    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'],
                }
            },


        }
    },
    mounted() {
        this.$refs.travelTimeChart.updateSeries([{
            name: this.i18n.get('_.stats.time-in-minutes'),
            data:
                this.travelTime.map((x) => {
                        return {"x": x.name, "y": x.value}
                    }
                )
        }]);
        this.fetchReasons();
    },
    methods: {
        fetchReasons() {
            axios
                .get('/statistics')
                .then((response) => {
                    console.log(response.data.data.purpose);
                    this.travelPurpose    = response.data.data.purpose;
                    this.trainProviders   = response.data.data.operators;
                    this.travelCategories = response.data.data.categories;
                    this.from             = response.data.meta.from;
                    this.until            = response.data.meta.until;
                    this.updatePurpose();
                    this.updateCategories();
                    this.updateProviders();
                })
        },
        updatePurpose() {
            this.$refs.purpose.updateOptions({
                labels: this.travelPurpose.map((x) => this.i18n.get("_.stationboard.business." + x.name)),
                series: this.travelPurpose.map(x => x.count)
            });
        },
        updateCategories() {
            this.$refs.categories.updateOptions({
                labels: this.travelCategories.map((x) => this.i18n.get("_.transport_types." + x.name)),
                series: this.travelCategories.map(x => x.count)
            });
        },
        updateProviders() {
            this.$refs.companies.updateOptions({
                labels: this.trainProviders.map((x) => x.name),
                series: this.trainProviders.map(x => x.count)
            });
        }
    }
};
</script>

<style scoped>

</style>
