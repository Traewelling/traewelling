<script>
import FullScreenModal from "../FullScreenModal.vue";
import _ from "lodash";
import {trans} from "laravel-vue-i18n";

export default {
    name: "StationRow",
    components: {FullScreenModal},
    props: {
        placeholder: {
            type: String,
        },
        arrival: {
            type: Boolean,
            default: true
        },
        departure: {
            type: Boolean,
            default: true
        }
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
            id: "",
        };
    },
    computed: {
        timeFieldALabel() {
            if (this.arrival && this.departure) {
                return trans("trip_creation.form.arrival");
            }
            return this.arrival ? trans("trip_creation.form.arrival") : trans("trip_creation.form.departure");
        },
        timeFieldBLabel() {
            if (this.arrival && this.departure) {
                return trans("trip_creation.form.departure");
            }
            return this.arrival ? trans("trip_creation.form.arrival") : trans("trip_creation.form.departure");
        },
        timeFieldAId() {
            return "timeFieldA" + this.id;
        },
        timeFieldBId() {
            return "timeFieldB" + this.id;
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
                this.loading          = false;
                return;
            }
            let query = this.stationInput.replace(/%2F/, ' ').replace(/\//, ' ');
            fetch(`/api/v1/stations/?query=${query}`).then((response) => {
                response.json().then((result) => {
                    console.log(result.data);
                    this.autocompleteList = result.data;
                    this.loading          = false;
                });
            });
        }
    },
    mounted() {
        // I hate it, it's extremely ugly, but it works
        // see https://github.com/vuejs/vue/issues/5886
        // There is a plugin for this, but it's not worth it with only one component
        this.id = Math.random().toString().substring(2);
    },
    watch: {
        stationInput: _.debounce(function () {
            this.autocomplete();
        }, 500),
    },
};
</script>

<template>
    <div :class="departure && arrival ? 'col-12 col-md-4' : 'col'">
        <FullScreenModal ref="modal">
            <template #header>
                <input v-model="stationInput"
                       :placeholder="placeholder"
                       class="form-control mobile-input-fs-16"
                       name="station"
                       type="text"
                />
            </template>
            <template #body>
                <ul class="list-group list-group-light list-group-small">
                    <li v-for="item in autocompleteList" class="list-group-item" @click="setStation(item)">
                        <a class="text-trwl" href="#">
                            {{ item.name }} <span v-if="item.rilIdentifier">({{ item.rilIdentifier }})</span>
                        </a>
                    </li>
                </ul>
            </template>
        </FullScreenModal>
        <label :for="timeFieldBId" class="form-label">{{ placeholder }}</label>
        <input v-model="stationInput" :placeholder="placeholder" class="form-control mobile-input-fs-16"
               type="text" @focusin="showModal">
    </div>
    <div v-if="departure && arrival" :class="departure && arrival ? 'col col-md-4' : 'col-4'">
        <label :for="timeFieldAId" class="form-label">{{ timeFieldALabel }}</label>
        <input
            :id="timeFieldAId"
            :aria-label="timeFieldALabel"
            :placeholder="timeFieldALabel"
            class="form-control mobile-input-fs-16"
            type="datetime-local"
            @input="$emit('update:timeFieldA', $event.target.value)"
        >
    </div>
    <div :class="departure && arrival ? 'col col-md-4' : 'col-4'">
        <label :for="timeFieldBId" class="form-label">{{ timeFieldBLabel }}</label>
        <input
            :id="timeFieldBId"
            :aria-label="timeFieldBLabel"
            :placeholder="timeFieldBLabel"
            class="form-control mobile-input-fs-16"
            type="datetime-local"
            @input="$emit('update:timeFieldB', $event.target.value)"
        >
    </div>
</template>
