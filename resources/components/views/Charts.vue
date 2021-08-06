<template>
    <LayoutBasic>
        <div class="row">
            <div class="col-lg-8">
                <h4>
                    {{
                        i18n.choice('_.stats.personal', 1, {
                            'fromDate': 'a',
                            'toDate': 'b'
                        })
                    }}
                </h4>
                <hr/>
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5>{{ i18n.get('_.stats.purpose') }}</h5>
                                <apexchart v-if="travelPurpose.length > 0" ref="purpose" :options="testoptions"
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
                                <apexchart v-if="travelPurpose.length > 0" ref="categories" :options="testoptions"
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
                                <apexchart v-if="travelPurpose.length > 0" ref="companies" :options="testoptions"
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
                                @if($travelTime->count() > 0)
                                <apexchart v-if="travelPurpose.length > 0" ref="testchart"
                                           :options="ajaxChartsOptions" type="line" width="380"></apexchart>
                                @else
                                <p class="text-danger font-weight-bold mt-2">{{ i18n.get('_.stats.no-data') }}</p>
                                @endif
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
                                <i class="fas fa-ruler fa-4x mt-1"></i>
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
                                <i class="fas fa-clock fa-4x mt-1"></i>
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
                                <i class="fas fa-users fa-4x mt-1"></i>
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

export default {
    name: "Charts",
    components: {
        LayoutBasic,
        apexchart: VueApexCharts
    },
    data() {
        return {
            travelPurpose: [
                {name: 'private', value: 20},
                {name: 'business', value: 5},
                {name: 'commute', value: 10}
            ],
            trainProviders: [
                {name: 'üstra Hannoversche Verkehsbetriebe AG', value: 325},
                {name: 'DB Regio AG Nord', value: 63},
                {name: 'metronom', value: 9},
                {name: 'cantus Verkehrsgesellschaft', value: 7},
                {name: 'erixx', value: 3},
                {name: 'DB Regio AG Südost', value: 1},
                {name: 'RegioTram', value: 1},
                {name: 'DB Fernverkehr AG', value: 1},
            ],
            travelCategories: [
                {name: 'Fernverkehr', value: 2},
                {name: 'Regional', value: 47},
                {name: 'Bus', value: 76},
                {name: 'S-Bahn', value: 107},
                {name: 'Tram', value: 532},
            ],
            series: [44, 55, 13, 43, 22],
            chartOptions: {
                chart: {
                    width: 380,
                    type: 'pie',
                },
                labels: ['Team A', 'Team B', 'Team C', 'Team D', 'Team E'],
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: 200
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }]
            },
            ajaxChartsOptions: {
                chart: {
                    height: 350,
                    type: 'bar',
                },
                dataLabels: {
                    enabled: false
                },
                series: [],
                title: {
                    text: 'Ajax Example',
                },
                noData: {
                    text: 'Loading...'
                }
            },
            testoptions: {
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
            }
        }
    },
    mounted() {
        this.$refs.testchart.updateSeries([{
            name: "Sales",
            data: this.travelPurpose
        }]);
        this.$refs.purpose.updateOptions({
            labels: this.travelPurpose.map((x) => this.i18n.get("_.stationboard.business." + x.name)),
            series: this.travelPurpose.map(x => x.value)
        });
        this.$refs.companies.updateOptions({
            labels: this.trainProviders.map((x) => x.name),
            series: this.trainProviders.map(x => x.value)
        })
        this.$refs.categories.updateOptions({
            labels: this.travelCategories.map((x) => x.name),
            series: this.travelCategories.map(x => x.value)
        })
    }
};
</script>

<style scoped>

</style>
