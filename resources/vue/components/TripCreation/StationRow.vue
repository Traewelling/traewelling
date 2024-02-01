<script>
import FullScreenModal from "../FullScreenModal.vue";
import _ from "lodash";

export default {
    name: "StationRow",
    components: {FullScreenModal},
    props: {
        placeholder: {
            type: String,
            default: "Zwischenhalt"
        },
        arrival: {
            type: Boolean,
            default: true
        },
        departure: {
            type: Boolean,
            default: true
        },
        primary: {
            default: "start"
        },
    },
    emits: ['update:station', 'update:timeFieldA', 'update:timeFieldB'],
    data() {
        return {
            timeFieldA: "",
            timeFieldB: "",
            station: null,
            stationInput: "",
            loading: false,
            autocompleteList: [],
        };
    },
    computed: {
        timeFieldALabel() {
            if (this.arrival && this.departure) {
                return this.primary === 'start' ? 'Abfahrt' : 'Ankunft';
            }
            return this.arrival ? 'Ankunft' : 'Abfahrt';
        },
        timeFieldBLabel() {
            if (this.arrival && this.departure) {
                return this.primary === 'start' ? 'Ankunft' : 'Abfahrt';
            }
            return this.arrival ? 'Ankunft' : 'Abfahrt';
        }
    },
    methods: {
        showModal() {
            this.$refs.modal.show();
        },
        setStation(item) {
            this.stationInput = item.name;
            this.$emit('update:station', item);
            this.$refs.modal.hide();
        },
        autocomplete() {
            this.loading = true;
            if (!this.stationInput || this.stationInput.length < 3) {
                this.autocompleteList = [];
                this.loading = false;
                return;
            }
            let query = this.stationInput.replace(/%2F/, ' ').replace(/\//, ' ');
            fetch(`/api/v1/trains/station/autocomplete/${query}`).then((response) => {
                response.json().then((result) => {
                    this.autocompleteList = result.data;
                    this.loading = false;
                });
            });
        }
    },
    watch: {
        stationInput: _.debounce(function() {
            this.autocomplete();
        }, 500),
    },
};
</script>

<template>
    <div :class="departure && arrival ? 'col-12 col-md-4' : 'col'">
        <FullScreenModal ref="modal">
            <template #header>
                <input type="text"
                       name="station"
                       class="form-control"
                       :placeholder="placeholder"
                       v-model="stationInput"
                />
            </template>
            <template #body>
                <ul class="list-group list-group-light list-group-small">
                    <li class="list-group-item" v-for="item in autocompleteList" @click="setStation(item)">
                        <a href="#" class="text-trwl">
                            {{item.name}} <span v-if="item.rilIdentifier">({{item.rilIdentifier}})</span>
                        </a>
                    </li>
                </ul>
            </template>
        </FullScreenModal>
        <label for="timeFieldB" class="form-label">{{placeholder}}</label>
        <input type="text" class="form-control" :placeholder="placeholder" @focusin="showModal" v-model="stationInput">
    </div>
    <div :class="departure && arrival ? 'col col-md-4' : 'col-4'" v-if="departure && arrival">
        <label for="timeFieldA" class="form-label">{{timeFieldALabel}}</label>
        <input
            id="timeFieldA"
            type="datetime-local"
            class="form-control"
            :placeholder="timeFieldALabel"
            :aria-label="timeFieldALabel"
            @input="$emit('update:timeFieldA', $event.target.value)"
        >
    </div>
    <div :class="departure && arrival ? 'col col-md-4' : 'col-4'">
        <label for="timeFieldB" class="form-label">{{timeFieldBLabel}}</label>
        <input
            id="timeFieldB"
            type="datetime-local"
            class="form-control"
            :placeholder="timeFieldBLabel"
            :aria-label="timeFieldBLabel"
            @input="$emit('update:timeFieldB', $event.target.value)"
        >
    </div>
</template>

<style scoped lang="scss">

</style>
