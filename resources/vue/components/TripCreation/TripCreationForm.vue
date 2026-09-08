<script>
import { trans } from 'laravel-vue-i18n';
import _ from 'lodash';
import { DateTime } from 'luxon';
import { Api } from '../../../types/Api.gen';
import StationInput from './StationInput.vue';
import StopoversCsvImporter from './StopoversCsvImporter.vue';
import TripCreationMap from './TripCreationMap.vue';

const api = new Api({ baseUrl: window.location.origin + '/api' });

export default {
    name: 'TripCreationForm',
    components: { TripCreationMap, StationInput, StopoversCsvImporter },
    data() {
        return {
            form: {
                originId: '',
                originDeparturePlanned: '',
                destinationId: '',
                destinationArrivalPlanned: '',
                lineName: '',
                journeyNumber: 0,
                operatorId: null,
                category: {},
                stopovers: [],
            },
            tripDataActive: true,
            originTimezone: 'Europe/Berlin',
            destinationTimezone: 'Europe/Berlin',
            stopovers: [],
            origin: {},
            destination: {},
            originDepartureLocal: '',
            destinationArrivalLocal: '',
            journeyNumberInput: '',
            trainTypeInput: '',
            selectedCategory: {},
            selectedOperator: null,
            operatorQuery: '',
            operatorResults: [],
            operatorLoading: false,
            operatorDropdownVisible: false,
            categories: [
                { value: 'nationalExpress', text: 'nationalExpress', emoji: '🚄' },
                { value: 'national', text: 'national', emoji: '🚅' },
                { value: 'regionalExp', text: 'regionalExpress', emoji: '🚆' },
                { value: 'regional', text: 'regional', emoji: '🚞' },
                { value: 'suburban', text: 'suburban', emoji: '🚋' },
                { value: 'bus', text: 'bus', emoji: '🚌' },
                { value: 'ferry', text: 'ferry', emoji: '⛴' },
                { value: 'subway', text: 'subway', emoji: '🚇' },
                { value: 'tram', text: 'tram', emoji: '🚊' },
                { value: 'taxi', text: 'taxi', emoji: '🚖' },
                { value: 'plane', text: 'plane', emoji: '✈️' },
                { value: 'freightTrain', text: 'freightTrain', emoji: '🚂' },
            ],
            disallowed: ['fahrrad', 'auto', 'fuss', 'fuß', 'foot', 'car', 'bike'],
            showDisallowed: false,
            validation: {
                times: null,
            },
        };
    },
    mounted() {
        this.initForm();
        this.getOriginFromQuery();
    },
    methods: {
        trans,
        onCsvImported(imported) {
            if (!Array.isArray(imported) || imported.length < 2) {
                window?.notyf?.error?.(trans('trip_creation.csv_import.errors.min_two_rows'));
                return;
            }

            const oldLen = this.stopovers.length;
            for (let i = 0; i < oldLen; i++) {
                try {
                    if (this.stopovers[i]?.station?.id) this.$refs.map.removeMarker(i);
                } catch {
                    // ignore
                }
            }

            const first = imported[0]; //origin
            const last = imported[imported.length - 1]; //destination
            const middle = imported.slice(1, imported.length - 1); //stopovers

            if (first?.station?.id) {
                this.$refs.originInput?.setStation(first.station);
            }
            if (first?.departurePlanned) {
                this.setDeparture(first.departurePlanned);
            }

            if (last?.station?.id) {
                this.$refs.destinationInput?.setStation(last.station);
            }
            if (last?.arrivalPlanned) {
                this.setArrival(last.arrivalPlanned);
            }

            this.stopovers = middle.map((s) => ({
                station: { id: '', name: '' },
                arrivalPlanned: s.arrivalPlanned,
                departurePlanned: s.departurePlanned,
            }));

            this.$nextTick(() => {
                const refs = this.$refs.stopoverInputs;
                const children = Array.isArray(refs) ? refs : refs ? [refs] : [];
                middle.forEach((s, idx) => {
                    const child = children[idx];
                    if (child?.setStation && s.station?.id) {
                        child.setStation(s.station);
                    } else if (s.station?.id) {
                        this.setStopoverStation(s.station, idx);
                    }
                });
                this.validateTimes();
            });
        },
        addStopover() {
            const times = [];

            if (this.form.originDeparturePlanned) {
                times.push(DateTime.fromISO(this.form.originDeparturePlanned, this.originTimezone));
            }
            if (this.form.destinationArrivalPlanned) {
                times.push(DateTime.fromISO(this.form.destinationArrivalPlanned, this.destinationTimezone));
            }
            for (const stop of this.stopovers) {
                if (stop.arrivalPlanned) {
                    times.push(DateTime.fromISO(stop.arrivalPlanned, this.originTimezone));
                }
                if (stop.departurePlanned) {
                    times.push(DateTime.fromISO(stop.departurePlanned, this.originTimezone));
                }
            }

            let baseDate;
            if (times.length > 0) {
                baseDate = times.sort((a, b) => b.toMillis() - a.toMillis())[0];
            } else {
                baseDate = DateTime.now().setZone(this.originTimezone);
            }

            const dummyStopover = {
                station: {
                    name: '',
                    id: '',
                },
                departurePlanned: baseDate.toFormat("yyyy-MM-dd'T'HH:mm"),
                arrivalPlanned: '',
            };
            this.stopovers.push(dummyStopover);

            this.$nextTick(() => {
                const refs = this.$refs.stopoverInputs;
                // catch one vs. many stopovers:
                const last = Array.isArray(refs) ? refs[refs.length - 1] : refs;
                if (last) {
                    last.showModal();
                }
            });
        },
        showData() {
            this.tripDataActive = true;
        },
        showMap() {
            this.tripDataActive = false;
            this.$refs.map.invalidateSize();
        },
        removeStopover(index) {
            if (this.stopovers[index].station.id) {
                this.$refs.map.removeMarker(index);
            }
            this.stopovers.splice(index, 1);
            this.validateTimes();
        },
        setOrigin(item) {
            this.$refs.map.addMarker(item, 'origin', this.stopovers.length);
            this.origin = item;
            this.form.originId = item.id;
        },
        setDeparture(time) {
            this.originDepartureLocal = DateTime.fromISO(time, this.originTimezone).toFormat("yyyy-MM-dd'T'HH:mm");
            this.form.originDeparturePlanned = DateTime.fromISO(time, this.originTimezone).toISO();
            this.validateTimes();
        },
        setDestination(item) {
            this.$refs.map.addMarker(item, 'destination', this.stopovers.length);
            this.destination = item;
            this.form.destinationId = item.id;
        },
        setArrival(time) {
            this.destinationArrivalLocal = DateTime.fromISO(time, this.destinationTimezone).toFormat(
                "yyyy-MM-dd'T'HH:mm",
            );
            this.form.destinationArrivalPlanned = DateTime.fromISO(time, this.destinationTimezone).toISO();
            this.validateTimes();
        },
        validateTimes() {
            try {
                //iterate over stopovers and destination, check if time is valid
                let time = DateTime.fromISO(this.form.originDeparturePlanned, this.originTimezone);

                for (const stopover of this.stopovers) {
                    const arrivalStr = stopover.arrivalPlanned || stopover.departurePlanned;
                    const departureStr = stopover.departurePlanned || stopover.arrivalPlanned;

                    if (!arrivalStr && !departureStr) {
                        this.validation.times = false;
                        return false;
                    }

                    const arrival = DateTime.fromISO(arrivalStr, this.originTimezone);
                    const departure = DateTime.fromISO(departureStr, this.originTimezone);

                    if (arrival < time || departure < arrival) {
                        this.validation.times = false;
                        return false;
                    }
                    time = departure;
                }

                if (DateTime.fromISO(this.form.destinationArrivalPlanned, this.destinationTimezone) < time) {
                    this.validation.times = false;
                    return false;
                }

                this.validation.times = true;
                return true;
            } catch {
                this.validation.times = false;
                return false;
            }
        },
        sendForm() {
            if (this.showDisallowed) {
                notyf.error(trans('trip_creation.limitations.6'));
                return;
            }

            if (!this.validateTimes()) {
                notyf.error(trans('trip_creation.no-valid-times'));
                return;
            }

            this.form.lineName = this.trainTypeInput;
            this.form.journeyNumber =
                !isNaN(this.journeyNumberInput) && !isNaN(parseInt(this.journeyNumberInput))
                    ? parseInt(this.journeyNumberInput)
                    : null;
            this.form.stopovers = this.stopovers.map((stopover) => {
                const mapped = { stationId: stopover.station.id };
                if (stopover.departurePlanned) {
                    mapped.departure = DateTime.fromFormat(
                        stopover.departurePlanned,
                        "yyyy-MM-dd'T'HH:mm",
                        this.originTimezone,
                    ).toISO();
                }
                if (stopover.arrivalPlanned) {
                    mapped.arrival = DateTime.fromFormat(
                        stopover.arrivalPlanned,
                        "yyyy-MM-dd'T'HH:mm",
                        this.originTimezone,
                    ).toISO();
                }
                return mapped;
            });
            this.form.category = this.selectedCategory.value;
            this.form.operatorId = this.selectedOperator ? this.selectedOperator.uuid : null;

            api.trips
                .createTrip(this.form)
                .then((response) => {
                    const result = response.data?.data;
                    const query = {
                        tripId: result.id,
                        lineName: result.lineName,
                        start: result.origin.id,
                        departure: this.form.originDeparturePlanned,
                        idType: 'trwl',
                        category: result.category,
                    };

                    window.location.href = `/stationboard?${new URLSearchParams(query).toString()}`;
                })
                .catch((error) => {
                    if (error?.status === 403 || error?.status === 422 || error?.status === 400) {
                        notyf.error(error.error?.message ?? trans('messages.exception.general-values'));
                    } else {
                        notyf.error(trans('messages.exception.general-values'));
                    }
                });
        },
        setStopoverStation(item, key) {
            this.$refs.map.addMarker(item, key, this.stopovers.length);
            this.stopovers[key].station = item;
        },
        setStopoverDeparture(time, key) {
            const dt = DateTime.fromISO(time, this.originTimezone);
            this.stopovers[key].departurePlanned = dt.isValid ? dt.toFormat("yyyy-MM-dd'T'HH:mm") : '';
            this.validateTimes();
        },
        setStopoverArrival(time, key) {
            const dt = DateTime.fromISO(time, this.destinationTimezone);
            this.stopovers[key].arrivalPlanned = dt.isValid ? dt.toFormat("yyyy-MM-dd'T'HH:mm") : '';
            this.validateTimes();
        },
        checkDisallowed() {
            this.showDisallowed = this.disallowed.some((disallowed) => {
                return this.trainTypeInput.toLowerCase().includes(disallowed);
            });
        },
        guessModeOfTransport() {
            // todo: guess mode of transport based on line input
            // e.g.: if line starts with ICE or TGV, set category to nationalExpress
        },
        getOriginFromQuery() {
            const stationId = new URLSearchParams(window.location.search).get('from');

            if (stationId) {
                api.stations
                    .showStation(stationId)
                    .then((result) => {
                        this.$refs.originInput.setStation(result.data?.data);
                    })
                    .catch((error) => {
                        console.error(error);
                    });
            }
        },
        onLineInput() {
            this.checkDisallowed();
            this.guessModeOfTransport();
        },
        onOperatorInput: _.debounce(function () {
            this.searchOperators();
        }, 300),
        searchOperators() {
            const query = this.operatorQuery.trim();
            if (query.length < 2) {
                this.operatorResults = [];
                this.operatorDropdownVisible = false;
                return;
            }
            this.operatorLoading = true;
            api.operators
                .getOperators({ query })
                .then((result) => {
                    this.operatorResults = result.data?.data ?? [];
                    this.operatorDropdownVisible = true;
                })
                .catch((error) => {
                    console.error(error);
                })
                .finally(() => {
                    this.operatorLoading = false;
                });
        },
        selectOperator(operator) {
            this.selectedOperator = operator;
            this.operatorQuery = operator.name;
            this.operatorDropdownVisible = false;
            this.operatorResults = [];
        },
        clearOperator() {
            this.selectedOperator = null;
            this.operatorQuery = '';
            this.operatorResults = [];
            this.operatorDropdownVisible = false;
        },
        hideOperatorDropdown() {
            setTimeout(() => {
                this.operatorDropdownVisible = false;
            }, 150);
        },
        initForm() {
            this.selectedCategory = this.categories[0];
        },
    },
};
</script>

<template>
    <div class="row mt-n4 mb-4 border-bottom d-block d-md-none">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link" :class="{ active: tripDataActive }" @click="showData">
                    {{ trans('trip_creation.form.trip_data') }}
                </button>
            </li>
            <li class="nav-item" role="presentation" @click="showMap">
                <button class="nav-link" :class="{ active: !tripDataActive }">
                    {{ trans('trip_creation.form.map') }}
                </button>
            </li>
        </ul>
    </div>
    <div class="row full-height mt-n4 mx-0">
        <div class="col d-md-block col-md-5 col-lg-4 col-xl-3 p-0 h-100" :class="{ 'd-none': !tripDataActive }">
            <div id="TripCreationMetaDataAccordion" class="accordion accordion-flush border-bottom">
                <div class="accordion-item">
                    <h2 id="accordionTripInfo" class="accordion-header">
                        <button
                            class="accordion-button collapsed"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapseTripInfo"
                            aria-expanded="false"
                            aria-controls="collapseTripInfo"
                        >
                            <div class="d-flex justify-start w-100">
                                <i class="fa-solid fa-list-check" />
                                <span class="d-flex justify-content-between w-100 px-2">
                                    <span v-if="!trainTypeInput.length" class="fw-bold">
                                        {{ trans('trip_creation.form.trip_data') }}
                                    </span>
                                    <span v-else class="fw-bold">
                                        {{ trainTypeInput }}
                                        <span class="fw-lighter fst-italic text-secondary">{{
                                            journeyNumberInput
                                        }}</span>
                                    </span>
                                </span>
                            </div>
                        </button>
                    </h2>
                    <div
                        id="collapseTripInfo"
                        class="accordion-collapse collapse"
                        aria-labelledby="accordionTripInfo"
                        data-bs-parent="#accordionTripInfo"
                    >
                        <div class="accordion-body">
                            <input
                                v-model="trainTypeInput"
                                type="text"
                                class="form-control mb-2"
                                :placeholder="trans('trip_creation.form.line')"
                                :aria-label="trans('trip_creation.form.line')"
                                aria-describedby="basic-addon1"
                                @focusout="onLineInput"
                            />
                            <input
                                v-model="journeyNumberInput"
                                type="text"
                                class="form-control"
                                :placeholder="trans('trip_creation.form.number')"
                                :aria-label="trans('trip_creation.form.number')"
                                aria-describedby="basic-addon1"
                            />
                            <div v-show="showDisallowed" class="alert alert-danger mt-2">
                                <i class="fas fa-triangle-exclamation" />
                                {{ trans('trip_creation.limitations.6') }}
                                <a :href="trans('trip_creation.limitations.6.link')" target="_blank">
                                    {{ trans('trip_creation.limitations.6.rules') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 id="accordionTripCategory" class="accordion-header">
                        <button
                            class="accordion-button collapsed"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapseTripCategory"
                            aria-expanded="false"
                            aria-controls="collapseTripCategory"
                        >
                            <div class="d-flex justify-start w-100">
                                {{ selectedCategory.emoji }}
                                <span class="d-flex justify-content-between w-100 px-2">
                                    <span class="fw-bold">{{ trans('trip_creation.form.travel_type') }}</span>
                                    <span>{{ trans('transport_types.' + selectedCategory.value) }}</span>
                                </span>
                            </div>
                        </button>
                    </h2>
                    <div
                        id="collapseTripCategory"
                        class="accordion-collapse collapse"
                        aria-labelledby="accordionTripCategory"
                        data-bs-parent="#accordionTripCategory"
                    >
                        <div class="accordion-body">
                            <ul class="list-group">
                                <li v-for="item in categories" :key="item.value" class="list-group-item">
                                    <input
                                        :id="item.value"
                                        v-model="selectedCategory"
                                        type="radio"
                                        class="form-check-input me-1"
                                        name="categoryRadio"
                                        :value="item"
                                    />
                                    <label class="form-check-label stretched-link" :for="item.value">
                                        {{ item.emoji }} {{ trans('transport_types.' + item.value) }}
                                    </label>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 id="accordionTripOperator" class="accordion-header">
                        <button
                            class="accordion-button collapsed"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapseTripOperator"
                            aria-expanded="false"
                            aria-controls="collapseTripOperator"
                        >
                            <div class="d-flex justify-start w-100">
                                <i class="fa-solid fa-building" />
                                <span class="d-flex justify-content-between w-100 px-2">
                                    <span v-if="selectedOperator == null" class="fw-bold">
                                        {{ trans('export.title.operator') }}
                                    </span>
                                    <span v-else class="fw-bold">
                                        {{ selectedOperator.name }}
                                    </span>
                                </span>
                            </div>
                        </button>
                    </h2>
                    <div
                        id="collapseTripOperator"
                        class="accordion-collapse collapse"
                        aria-labelledby="accordionTripOperator"
                        data-bs-parent="#accordionTripOperator"
                    >
                        <div class="accordion-body">
                            <div class="position-relative">
                                <div class="input-group">
                                    <input
                                        v-model="operatorQuery"
                                        type="text"
                                        class="form-control"
                                        :placeholder="trans('trip_creation.form.operator_search')"
                                        :aria-label="trans('trip_creation.form.operator_search')"
                                        @input="onOperatorInput"
                                        @blur="hideOperatorDropdown"
                                        @focus="operatorQuery.length >= 2 && (operatorDropdownVisible = true)"
                                    />
                                    <button
                                        v-if="selectedOperator"
                                        class="btn btn-outline-secondary"
                                        type="button"
                                        @click="clearOperator"
                                    >
                                        <i class="fa-solid fa-xmark" />
                                    </button>
                                    <span v-else-if="operatorLoading" class="input-group-text">
                                        <i class="fa-solid fa-spinner fa-spin" />
                                    </span>
                                </div>
                                <ul
                                    v-if="operatorDropdownVisible && operatorResults.length > 0"
                                    class="list-group position-absolute w-100 shadow-sm"
                                    style="z-index: 1000; max-height: 240px; overflow-y: auto"
                                >
                                    <li
                                        v-for="operator in operatorResults"
                                        :key="operator.id"
                                        class="list-group-item list-group-item-action"
                                        style="cursor: pointer"
                                        @mousedown.prevent="selectOperator(operator)"
                                    >
                                        {{ operator.name }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <form class="px-4 mt-4" @submit.prevent="sendForm">
                <StationInput
                    ref="originInput"
                    :placeholder="trans('trip_creation.form.origin')"
                    :arrival="false"
                    :departure-time="originDepartureLocal"
                    @update:station="setOrigin"
                    @update:time-field-b="setDeparture"
                />

                <div v-for="(stopover, key) in stopovers" :key="key" class="row g-3 mt-1">
                    <div class="d-flex align-items-center w-100">
                        <div class="flex-grow-1 d-flex">
                            <StationInput
                                ref="stopoverInputs"
                                :placeholder="trans('trip_creation.form.stopover')"
                                :arrival-time="stopover.arrivalPlanned"
                                :departure-time="stopover.departurePlanned"
                                @update:station="setStopoverStation($event, key)"
                                @update:time-field-b="setStopoverDeparture($event, key)"
                                @update:time-field-a="setStopoverArrival($event, key)"
                                @delete="removeStopover(key)"
                            />
                        </div>
                    </div>
                </div>

                <div class="mb-2 px-3 d-flex align-items-center">
                    <a href="#" @click="addStopover">
                        {{ trans('trip_creation.form.add_stopover') }}
                        <i class="fa fa-plus" aria-hidden="true" />
                    </a>
                </div>

                <StationInput
                    ref="destinationInput"
                    :placeholder="trans('trip_creation.form.destination')"
                    :arrival="true"
                    :departure="false"
                    :departure-time="destinationArrivalLocal"
                    @update:station="setDestination"
                    @update:time-field-b="setArrival"
                />

                <div class="mt-4 border-top pt-4 d-flex justify-content-between align-items-center">
                    <a
                        href="#"
                        class="small link-secondary text-decoration-none"
                        data-bs-toggle="offcanvas"
                        data-bs-target="#stopoversCsvImporterOffcanvas"
                        :title="trans('trip_creation.csv_import.button')"
                    >
                        <i class="fa-solid fa-file-csv me-1" aria-hidden="true" />
                        <span class="d-none d-sm-inline">{{ trans('trip_creation.csv_import.button') }}</span>
                    </a>

                    <button type="submit" class="btn btn-primary">
                        {{ trans('trip_creation.form.save') }}
                    </button>
                </div>
            </form>

            <div class="alert alert-warning m-2">
                <h2 class="fs-5">
                    <i class="fa fa-exclamation-triangle" aria-hidden="true" />
                    {{ trans('trip_creation.limitations') }}
                </h2>

                <ul>
                    <li>
                        {{ trans('trip_creation.limitations.2') }}
                        <small
                            >(<a href="https://help.traewelling.de/features/map/" target="_blank">{{
                                trans('messages.cookie-notice-learn')
                            }}</a
                            >)</small
                        >
                    </li>
                    <li>{{ trans('trip_creation.limitations.3') }}</li>
                </ul>

                <p class="fw-bold text-danger">
                    {{ trans('trip_creation.limitations.6') }}
                    <a :href="trans('trip_creation.limitations.6.link')" target="_blank">
                        {{ trans('trip_creation.limitations.6.rules') }}
                    </a>
                </p>
            </div>
        </div>
        <div class="col d-md-block bg-warning px-0" :class="{ 'd-none': tripDataActive }">
            <TripCreationMap ref="map" />
        </div>
    </div>
    <StopoversCsvImporter offcanvas-id="stopoversCsvImporterOffcanvas" :max-items="50" @imported="onCsvImported" />
</template>

<style scoped>
.full-height {
    min-height: 90vh;
}
</style>
