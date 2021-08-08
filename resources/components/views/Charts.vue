<template>
    <LayoutBasic>
        <div class="mb-4">
            <div id="daterange" class="dropdown float-end">
                <button
                    id="dateRangeDropdown"
                    aria-expanded="false"
                    class="btn btn-primary dropdown-toggle"
                    data-mdb-toggle="dropdown"
                    type="button"
                >
                    <i aria-hidden="true" class="fas fa-calendar"></i>&nbsp;
                    {{ i18n.get('_.stats.range') }} ({{ this.dateRange }})
                </button>
                <ul aria-labelledby="dateRangeDropdown" class="dropdown-menu">
                    <li><a class="dropdown-item" href="#"
                           @click.prevent="fetchRecentDays(7)">{{
                            i18n.choice('_.stats.range.days', 1, {"days": 7})
                        }}</a>
                    </li>
                    <li><a class="dropdown-item" href="#"
                           @click.prevent="fetchRecentDays(15)">{{
                            i18n.choice('_.stats.range.days', 1, {"days": 15})
                        }}</a>
                    </li>
                    <li><a class="dropdown-item" href="#"
                           @click.prevent="fetchRecentDays(30)">{{
                            i18n.choice('_.stats.range.days', 1, {"days": 30})
                        }}</a>
                    </li>
                    <li><a class="dropdown-item" href="#"
                           @click.prevent="fetchRecentDays(60)">{{
                            i18n.choice('_.stats.range.days', 1, {"days": 60})
                        }}</a>
                    </li>
                    <li class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="#" @click.prevent="picker.show()">
                            {{ i18n.choice('_.stats.range.picker') }}
                        </a>
                    </li>
                </ul>
            </div>
            <h1 class="h3 mr-auto mb-0 text-gray-800">{{ i18n.get('_.stats') }}</h1>
        </div>
        <div class="row">
            <div class="col-lg-8">
                <hr/>
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5>{{ i18n.get('_.stats.purpose') }}</h5>
                                <p v-if="travelPurpose.length <= 0" class="text-danger font-weight-bold mt-2">
                                    {{ i18n.get('_.stats.no-data') }}
                                </p>
                                <apexchart ref="purpose" :options="pieChartOptions"
                                           :series="emptySeries" type="pie" width="100%"></apexchart>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5>{{ i18n.get('_.stats.categories') }}</h5>
                                <p v-if="travelCategories.length <= 0" class="text-danger font-weight-bold mt-2">
                                    {{ i18n.get('_.stats.no-data') }}
                                </p>
                                <apexchart ref="categories" :options="pieChartOptions"
                                           :series="emptySeries" type="pie" width="100%"></apexchart>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5>{{ i18n.get('_.stats.companies') }}</h5>
                                <p v-if="trainProviders.length <= 0" class="text-danger font-weight-bold mt-2">
                                    {{ i18n.get('_.stats.no-data') }}
                                </p>
                                <apexchart ref="companies" :options="pieChartOptions"
                                           :series="emptySeries" type="pie" width="100%"></apexchart>
                            </div>
                        </div>
                    </div>
                    <hr/>
                    <div class="col-12 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5>{{ i18n.get('_.stats.volume') }}</h5>
                                <p v-if="travelTime.length <= 0" class="text-danger font-weight-bold mt-2">
                                    {{ i18n.get('_.stats.no-data') }}
                                </p>
                                <apexchart ref="travelTimeChart"
                                           :options="barChartOptions" :series="emptyDataSeries" type="line"
                                           width="100%"></apexchart>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <hr/>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-4 text-center">
                                <i aria-hidden="true" class="fas fa-ruler fa-4x mt-1"></i>
                            </div>
                            <div class="col-8 text-center">
                                <span class="font-weight-bold color-main fs-2">
                                     {{ this.globalData.distance }} km
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
                                    {{ this.globalDuration }}
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
                                     {{ globalData.activeUsers }} x
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
                            'fromDate': moment(this.fromGlobal).format('LLL'),
                            'toDate': moment(this.untilGlobal).format('LLL')
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
import Litepicker from "litepicker";

export default {
    name: "Charts",
    components: {
        LayoutBasic,
        apexchart: VueApexCharts,
        moment,
        Litepicker
    },
    data() {
        return {
            loading: true,
            picker: null,
            from: moment().subtract(1, "month").toISOString(),
            until: moment().toISOString(),
            fromGlobal: 0,
            untilGlobal: 0,
            emptySeries: ['1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1',],
            emptyDataSeries: [{
                name: 'series-1',
                data: []
            }],
            globalData: {
                distance: 15.123,
                duration: 0,
                activeUsers: 0
            },
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
                {date: 0, duration: 2021, count: 1},
            ],
            pieChartOptions: {
                chart: {
                    type: "pie",
                },
                dataLabels: {
                    enabled: true
                },
                legend: false,
            },
            barChartOptions: {
                chart: {
                    height: 350,
                    zoom: {
                        enabled: false
                    },
                    toolbar: false
                },
                dataLabels: {
                    enabled: true,
                    enabledOnSeries: [1]
                },
                stroke: {
                    curve: 'smooth',
                },
                grid: {
                    row: {
                        colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
                        opacity: 0.5
                    },
                },
                yaxis: [
                    {
                        title: {
                            text: this.i18n.get('_.stats.time-in-minutes')
                        },
                    },
                    {
                        opposite: true,
                        title: {
                            text: this.i18n.get('_.stats.trips')
                        }
                    }
                ],
            },


        }
    },
    mounted() {
        this.fetchGlobalData();
        this.fetchPersonalData();
        this.picker = new Litepicker({
            element: document.getElementById('daterange'),
            singleMode: false,
            lang: this.i18n.getLocale(),
            tooltipNumber: (totalDays) => {
                return totalDays - 1;
            },
            setup: (picker) => {
                picker.on('selected', (date1, date2) => {
                    this.from  = moment(date1.toDateString()).toISOString();
                    this.until = moment(date2.toDateString()).toISOString();
                    this.fetchPersonalData();
                });
            },
        });
    },
    computed: {
        globalDuration() {
            //ToDo this needs localization, also this is code duplication...
            const duration = moment.duration(this.globalData.duration, "minutes").asMinutes();
            let minutes    = duration % 60;
            let hours      = Math.floor(duration / 60);

            return hours + "h " + minutes + "m";
        },
        dateRange() {
            let from  = moment(this.from);
            let until = moment(this.until);
            let diff  = from.diff(until, "day");

            return moment.duration(diff, "days").humanize();
        }
    },
    methods: {
        fetchRecentDays(delta) {
            this.from  = moment().subtract(delta, "days").toISOString();
            this.until = moment().toISOString();
            this.fetchPersonalData();
        },
        fetchPersonalData() {
            axios
                .get('/statistics?from=' + this.from + "&until=" + this.until)
                .then((response) => {
                    this.travelPurpose    = response.data.data.purpose;
                    this.trainProviders   = response.data.data.operators;
                    this.travelCategories = response.data.data.categories;
                    this.travelTime       = response.data.data.time;
                    this.from             = response.data.meta.from;
                    this.until            = response.data.meta.until;
                    this.loading          = false;
                    this.updatePurpose();
                    this.updateCategories();
                    this.updateProviders();
                    this.updateVolume();
                })
                .catch((error) => {
                    console.error(error)
                });
        },
        fetchGlobalData() {
            axios
                .get('/statistics/global')
                .then((response) => {
                    this.globalData  = response.data.data;
                    this.untilGlobal = response.data.meta.until;
                    this.fromGlobal  = response.data.meta.from;
                })
                .catch((error) => {
                    console.error(error)
                });
        },
        updatePurpose() {
            this.$refs.purpose.updateOptions({
                labels: this.travelPurpose.map((x) => this.i18n.get("_.stationboard.business." + x.name)),
                series: this.travelPurpose.map(x => x.count)
            }, true);
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
        },
        updateVolume() {
            let fixedTravelTime = [];
            let store           = moment(this.travelTime[0].date);
            this.travelTime.forEach(function callback(value) {
                let currentDate = moment(value.date);
                while (value.date && store.diff(currentDate) !== 0) {
                    fixedTravelTime.push({
                        date: store.format('L'),
                        count: 0,
                        duration: 0,
                    });
                    store.add(1, 'day');
                }
                value.date = currentDate.format('L');
                fixedTravelTime.push(value);
                store = currentDate.add(1, 'day');
            });

            this.$refs.travelTimeChart.updateSeries([
                {
                    name: this.i18n.get('_.stats.time-in-minutes'),
                    type: 'line',
                    data: fixedTravelTime.map((x) => {
                        return {
                            x: x.date,
                            y: x.duration
                        }
                    })
                }, {
                    name: this.i18n.get('_.stats.trips'),
                    type: 'column',
                    data: fixedTravelTime.map((x) => {
                        return {
                            x: x.date,
                            y: x.count
                        }
                    })
                }
            ]);
        }
    }
};
</script>

<style scoped>

</style>
