<script>
import FullScreenModal from "./FullScreenModal.vue";
import _ from "lodash";

export default {
    name: "StationAutocomplete",
    components: {FullScreenModal},
    props: {
        station: {
            type: Object,
            required: false
        }
    },
    data() {
        return {
            recent: [],
            loading: false,
            autocompleteList: [],
            stationInput: '',
        };
    },
    methods: {
        showModal() {
            this.$refs.modal.show();
        },
        getRecent() {
            fetch(`/api/v1/trains/station/history`).then((response) => {
                response.json().then((result) => {
                    this.recent = result.data;
                });
            });
        },
        autocomplete() {
            this.loading = true;
            let query = this.stationInput.replace(/%2F/, ' ').replace(/\//, ' ');
            fetch(`/api/v1/trains/station/autocomplete/${query}`).then((response) => {
                response.json().then((result) => {
                    this.autocompleteList = result.data;
                    this.loading = false;
                });
            });
        },
        setStation(item) {
            this.stationInput = item.name;
            this.$emit('update:station', item);
            this.$refs.modal.hide();
        }
    },
    watch: {
        stationInput: _.debounce(function() {
            this.autocomplete();
        }, 500)
    },
    mounted() {
        this.stationInput = this.station ? this.station.name : '';
        this.getRecent();
    }
}
</script>

<template>
    <FullScreenModal ref="modal">
        <template #header>
            <input type="text" name="station" class="form-control"
                   placeholder="Station or Ril 100 identifier"
                   v-model="stationInput"
            />
        </template>
        <template #body>
            <ul class="list-group list-group-light list-group-small">
                <li class="list-group-item" v-for="item in recent" v-show="autocompleteList.length === 0">
                    <a href="#" class="text-trwl" @click="setStation(item)">
                        {{item.name}} <span v-if="item.rilIdentifier">({{item.rilIdentifier}})</span>
                    </a>
                </li>
                <li class="list-group-item" v-for="item in autocompleteList" @click="setStation(item)">
                    <a href="#" class="text-trwl">
                        {{item.name}} <span v-if="item.rilIdentifier">({{item.rilIdentifier}})</span>
                    </a>
                </li>
            </ul>
        </template>
    </FullScreenModal>
    <div class="card mb-4">
        <div class="card-header">Where are you?</div>
        <div class="card-body">
                <div id="station-autocomplete-container" style="z-index: 3;">
                    <div class="input-group mb-2 mr-sm-2">
                        <input type="text" name="station" class="form-control"
                               placeholder="Station or Ril 100 identifier"
                               v-model="stationInput" @focusin="showModal"
                        />
                        <button type="button"
                                class="btn btn-outline-dark stationSearchButton"
                                id="gps-button"
                                data-mdb-ripple-color="dark"
                                >
                            <i class="fa fa-map-marker-alt"></i>
                            <div class="spinner-border d-none" role="status" style="height: 1rem; width: 1rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </button>
                    </div>
                </div>
        </div>
    </div>

</template>

<style scoped lang="scss">

</style>
