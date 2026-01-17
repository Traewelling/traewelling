<script>
import { getActiveLanguage, trans, transChoice } from 'laravel-vue-i18n';
import { DateTime } from 'luxon';
import CheckinInterface from './Checkin/CheckinInterface.vue';
import StationBoardEntry from './Checkin/StationBoardEntry.vue';
import CheckinLineRun from './CheckinLineRun.vue';
import FullScreenModal from './FullScreenModal.vue';
import LineIndicator from './LineIndicator.vue';
import LoadingSkeletonRows from './Loader/LoadingSkeletonRows.vue';
import ProductIcon from './ProductIcon.vue';
import StationAutocomplete from './StationAutocomplete/StationAutocomplete.vue';

export default {
    components: {
        LoadingSkeletonRows,
        StationBoardEntry,
        StationAutocomplete,
        CheckinInterface,
        CheckinLineRun,
        LineIndicator,
        ProductIcon,
        FullScreenModal,
    },
    data() {
        return {
            data: [],
            meta: {},
            travelType: null,
            fetchTime: null,
            show: false,
            selectedTrain: null,
            selectedTrainBackgroundColor: null,
            selectedTrainTextColor: null,
            selectedDestination: null,
            loading: false,
            stationName: null,
            trwlStationId: null,
            firstFetchTime: null,
            pushState: null,
            fastCheckinIbnr: null,
            useInternalIdentifiers: true,
            removedLicenses: [],
        };
    },
    computed: {
        now() {
            return Object.hasOwn(this.meta, 'times') && Object.hasOwn(this.meta.times, 'now')
                ? DateTime.fromISO(this.meta.times.now).setZone('UTC')
                : DateTime.now().setZone('UTC');
        },
        showLineRun() {
            return !!this.selectedTrain && !this.selectedDestination;
        },
        showCheckinInterface() {
            return !!this.selectedDestination;
        },
        removedLicensesCount() {
            return Array.isArray(this.removedLicenses) ? this.removedLicenses.length : 0;
        },
        showHiddenSection() {
            return !this.loading && this.removedLicensesCount > 0;
        },
    },
    watch: {
        selectedDestination(value) {
            if (value === null) {
                window.history.back();
            } else {
                const params = new URLSearchParams(window.location.search);
                const destination = this.useInternalIdentifiers ? value.id : value.evaIdentifier;
                params.set('destination', destination);
                this.pushHistory(params);
            }
        },
        selectedTrain(value) {
            if (value !== null) {
                this.selectedTrainBackgroundColor = this.selectedTrain?.line?.color || null;

                if (this.selectedTrainBackgroundColor) {
                    this.selectedTrainTextColor = this.selectedTrain?.line?.textColor || null;
                }
            }
        },
    },
    mounted() {
        this.analyzeUrlParams().then(() => {
            if (!this.selectedTrain) {
                this.fetchData();
            }
        });

        window.addEventListener('popstate', () => {
            this.popstateListener();
        });
    },
    methods: {
        transChoice,
        getActiveLanguage,
        trans,
        showModal(selectedItem) {
            this.selectedDestination = null;
            this.selectedTrain = selectedItem;
            this.show = true;
            this.$refs.modal.show();

            const data = new URLSearchParams({
                tripId: selectedItem.tripId,
                lineName: selectedItem.line.name,
                start: this.useInternalIdentifiers ? this.selectedTrain.stop.id : this.meta.station.ibnr,
                departure: selectedItem.when,
                category: selectedItem.line.product,
            });

            this.pushHistory(data);
        },
        updateStation(station) {
            this.stationName = station.name;
            this.trwlStationId = station.id;
            this.data = [];
            this.fetchData();
        },
        updateTravelType(travelType) {
            this.travelType = travelType;
            this.data = [];
            this.fetchData();
        },
        updateTime(time) {
            this.fetchData(time);
        },
        fetchPrevious() {
            this.fetchData(
                this.meta?.times?.prev ? this.meta.times.prev : this.fetchTime.minus({ minutes: 15 }).toString(),
            );
        },
        fetchNext() {
            this.fetchData(
                this.meta?.times?.next ? this.meta.times.next : this.fetchTime.plus({ minutes: 15 }).toString(),
            );
        },
        fetchData(time = null, appendPosition = 0) {
            this.loading = true;
            if (time !== null) {
                this.fetchTime = DateTime.fromISO(time).setZone('UTC');
            } else {
                time = this.fetchTime.minus({ minutes: 5 }).toString();
            }
            if (this.trwlStationId === null) {
                return;
            }

            const travelType = this.travelType ? this.travelType : '';

            this.pushHistory(
                new URLSearchParams({
                    stationId: this.trwlStationId,
                    stationName: this.stationName,
                    when: time,
                    travelType: travelType,
                }),
            );

            fetch(`/api/v1/station/${this.trwlStationId}/departures?when=${time}&travelType=${travelType}`).then(
                (response) => {
                    this.loading = false;
                    if (response.ok) {
                        response.json().then((result) => {
                            if (appendPosition === 0) {
                                this.data = result.data;
                            } else if (appendPosition === 1) {
                                this.data = this.data.concat(result.data);
                            } else {
                                this.data = result.data.concat(this.data);
                            }
                            this.meta = result.meta;
                            this.stationName = result.meta.station.name;
                            this.removedLicenses = Array.isArray(result.meta.removedLicenses)
                                ? result.meta.removedLicenses
                                : [];

                            this.firstFetchTime = DateTime.fromISO(this.meta?.times?.now);
                        });
                    } else {
                        if (response.status === 502) {
                            window.notyf.error(trans('messages.exception.hafas.502'));
                        }
                    }
                },
            );
        },
        formatTime(time) {
            return DateTime.fromISO(time).toFormat('HH:mm');
        },
        isPast(item) {
            return DateTime.fromISO(item.when) < DateTime.now();
        },
        async analyzeUrlParams() {
            const urlParams = new URLSearchParams(window.location.search);
            this.fetchTime = DateTime.now().setZone('UTC');

            if (urlParams.has('tripId')) {
                if (!urlParams.has('destination')) {
                    this.selectedDestination = null;
                }
                this.selectedTrain = {
                    tripId: urlParams.get('tripId'),
                    line: {
                        name: urlParams.get('lineName'),
                        product: urlParams.get('category') || null,
                    },
                    stop: {
                        id: urlParams.get('start'),
                    },
                    plannedWhen: urlParams.get('departure'),
                };
                if (urlParams.has('destination')) {
                    this.fastCheckinIbnr = urlParams.get('destination');
                }
                if (urlParams.has('idType')) {
                    this.useInternalIdentifiers = urlParams.get('idType') === 'trwl';
                }
                this.show = true;
                this.$refs?.modal?.show();
                return new Promise((resolve) => resolve());
            }

            if (!urlParams.has('stationId')) {
                window.notyf.error('No station found!');
            }
            if (urlParams.has('when')) {
                const fetchTime = DateTime.fromISO(urlParams.get('when')).setZone('UTC');
                this.fetchTime = fetchTime.isValid ? fetchTime : this.fetchTime;
            }
            this.stationName = urlParams.get('stationName');
            this.trwlStationId = urlParams.get('stationId');
            this.show = false;
            this.$refs.modal.hide();
            return new Promise((resolve) => resolve());
        },
        popstateListener() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.toString() !== this.pushState.toString()) {
                this.analyzeUrlParams().then(() => {
                    if (!this.selectedTrain) {
                        this.fetchData();
                    }
                });
            }
        },
        pushHistory(data) {
            this.pushState = data;
            window.history.pushState({}, '', `?${data.toString()}`);
        },
        goBackToLineRun() {
            this.selectedDestination = null;
            this.fastCheckinIbnr = null;
        },
    },
};
</script>

<template>
    <StationAutocomplete
        :station-name="stationName"
        :station="meta?.station"
        :time="now"
        :show-filter-button="true"
        @update:time="updateTime"
        @update:station="updateStation"
        @update:travel-type="updateTravelType"
    />

    <div v-if="showHiddenSection" class="accordion mb-4">
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button
                    class="accordion-button collapsed bg-info"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#hidden-licenses"
                    aria-expanded="false"
                    aria-controls="hidden-licenses"
                >
                    <div class="row">
                        <div class="col-auto">
                            <i class="fa-solid fa-triangle-exclamation" />
                        </div>
                        <div class="col">
                            {{ transChoice('stationboard.hidden-departures', removedLicensesCount) }}
                        </div>
                    </div>
                </button>
            </h2>
            <div id="hidden-licenses" class="accordion-collapse collapse">
                <div class="accordion-body">
                    <p class="mb-2">
                        {{ trans('stationboard.hidden-departures.detail') }}
                    </p>
                    <p class="mb-2">
                        {{ trans('stationboard.hidden-departures.detail2') }}
                    </p>
                    <ul>
                        <li v-for="(license, index) in removedLicenses" :key="index">
                            <span v-if="license?.licenseName">{{ license.licenseName }}</span>
                            <span v-else>{{ license }}</span>
                        </li>
                    </ul>
                    <p class="mb-2">
                        {{ trans('stationboard.hidden-departures.detail3') }}
                        <a
                            v-if="getActiveLanguage().startsWith('de')"
                            target="_blank"
                            href="https://help.traewelling.de/features/timetable/licensing/"
                            >help.traewelling.de/features/timetable/licensing</a
                        >
                        <a v-else target="_blank" href="https://help.traewelling.de/en/features/timetable/licensing/"
                            >help.traewelling.de/en/features/timetable/licensing</a
                        >
                    </p>
                </div>
            </div>
        </div>
    </div>

    <FullScreenModal ref="modal" body-class="{{ showCheckinInterface ? 'p-0' : ''}}">
        <template v-if="selectedTrain" #header>
            <div class="col-1 align-items-center d-flex">
                <ProductIcon :product="selectedTrain.line.product" />
            </div>
            <div class="col-auto align-items-center d-flex me-3">
                <LineIndicator
                    :product-name="selectedTrain.line.product"
                    :number="selectedTrain.line.name !== null ? selectedTrain.line.name : selectedTrain.line.fahrtNr"
                    :background-color="selectedTrainBackgroundColor"
                    :color="selectedTrainTextColor"
                />
            </div>
            <template v-if="selectedDestination">
                <div class="col-auto align-items-center d-flex me-3">
                    <i class="fas fa-arrow-alt-circle-right" />
                </div>
                <div class="col-auto align-items-center d-flex me-3">
                    {{ selectedDestination.name }}
                </div>
            </template>
        </template>
        <template v-if="showLineRun" #body>
            <CheckinLineRun
                v-model:destination="selectedDestination"
                :selected-train="selectedTrain"
                :fast-checkin-id="fastCheckinIbnr"
                :use-internal-identifiers="useInternalIdentifiers"
            />
        </template>
        <template v-if="showCheckinInterface" #close>
            <button type="button" class="btn-close" aria-label="Back" @click="goBackToLineRun" />
        </template>
        <template v-if="showCheckinInterface" #body>
            <CheckinInterface
                :selected-train="selectedTrain"
                :selected-destination="selectedDestination"
                :use-internal-identifiers="useInternalIdentifiers"
            />
        </template>
    </FullScreenModal>

    <div class="text-center mb-2">
        <button type="button" class="btn btn-primary" :disabled="loading" @click="fetchPrevious">
            <i class="fa-solid fa-angle-up" />
        </button>
    </div>

    <LoadingSkeletonRows v-if="loading" :rows="10" :columns="1" />

    <template v-if="!loading && data.length === 0">
        <div class="card mb-1 dep-card mt-3 mb-3">
            <div class="text-center my-auto py-3">
                {{ trans('stationboard.no-departures') }}
                <div v-if="fetchTime">
                    <br />
                    {{ trans('requested-timestamp') }}: {{ formatTime(fetchTime) }}
                </div>
            </div>
        </div>
    </template>

    <StationBoardEntry
        v-for="item in data"
        v-show="!loading"
        :key="item.id"
        :item="item"
        :station="meta.station"
        @click="showModal(item)"
    />

    <div class="text-center mt-2">
        <button type="button" class="btn btn-primary" :disabled="loading" @click="fetchNext">
            <i class="fa-solid fa-angle-down" />
        </button>
    </div>
</template>

<style scoped lang="scss">
.product-icon {
    width: 1.25rem;
    height: 1.25rem;
}

.timeline {
    margin-left: -1rem;
}

.second-stop {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>
