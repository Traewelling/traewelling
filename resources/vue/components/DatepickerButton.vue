<template>
    <button :class="buttonClass  + ' ' + addButtonClass" :title="title">
        <i v-bind:class="buttonIconClass"></i>

        <input :type="type" class="datepicker-button-input"
               :value="datePickerBind(dateValue)"
               :min="normalizedMin"
               :max="normalizedMax"
               @input="datePickerUnbind($event.target.valueAsDate,dateValue)"
        />
    </button>
</template>

<script>
/*
Native Date Picker Button
-------------------------

This is a native date picker button.

properties:
title - title text for the button
dateValue - initial date (note this value is not updated)
dateId - optional unique ID for this date so you can differentiate for the update event
buttonClass - full class string for the button (default: btn btn-secondary btn-sm datepicker-button)
addButtonClass - adds these classes to the default class
buttonStyle - additional style overrides for the button
buttonIconClass - full class for icon (default: fad fa-calendar-alt fa-fw)
min, max  - Minimum date to display (optional). String (2022-10-01), number of days, or date value

emits:
update:dateValue
- fired when the date is changed.
 */
export default {
    name: "DatePickerButton",

    props: {
        title: {type: String, default: "Select a date"},
        dateValue: {type: Date, default: new Date()},
        dateId: {type: String, default: new Date().getTime().toString()},
        type: {type: String, default: "datetime-local"},

        buttonClass: {type: String, default: "btn btn-secondary btn-sm"},
        addButtonClass: {type: String, default: "datepicker-button"},
        buttonStyle: {type: String, default: ""},
        buttonIconClass: {type: String, default: "fas fa-calendar-alt fa-fw"},
        min: {type: [String, Date, Number], default: "1970-01-01"},
        max: {type: [String, Date, Number], default: "2100-00-01"}
    },
    methods: {
        datePickerBind(dt) {
            return this.localToGmtDate(dt);
        },

        datePickerUnbind(dt) {
            let newDate = this.utcToLocalDate(dt);
            this.$emit("update:dateValue", newDate, this.dateId);
        },

        localToGmtDate(localDate) {
            let dt = localDate && new Date(localDate.getTime() - (localDate.getTimezoneOffset() * 60 * 1000)).toISOString().split('T')[0];
            console.log("localtoutc", dt);
            return dt;
        },

        utcToLocalDate(utcDate) {
            let dt = new Date(utcDate.getTime() + (utcDate.getTimezoneOffset() * 60 * 1000));
            console.log("utctolocal", dt);
            return dt;
        },
    },
    computed: {
        normalizedMin(inst) {
            if (!inst?.min) return inst;

            let minVal = inst.min;
            if (typeof minVal === "string")
                return minVal;


            if (typeof minVal === "number") {
                let dt = new Date();
                dt     = new Date(dt.setDate(dt.getDate() - minVal));
                minVal = dt;
            }

            return this.localToGmtDate(minVal);
        },
        normalizedMax(inst) {
            if (!inst?.min) return inst;

            let maxVal = inst.max;
            if (typeof maxVal === "string")
                return maxVal;

            let dt = new Date();
            if (typeof maxVal === "number") {
                dt     = new Date();
                dt     = new Date(dt.setDate(dt.getDate() + maxVal));
                maxVal = dt;
            }

            return this.localToGmtDate(maxVal);
        }

    }
}
</script>

<style scoped>
.datepicker-button {
    position: relative;
}

.datepicker-button-input {
    position: absolute;
    overflow: hidden;
    width: 100%;
    height: 100%;
    right: 0;
    top: 0;
    opacity: 0;
}

.datepicker-button-input::-webkit-calendar-picker-indicator {
    position: absolute;
    right: 0;
    width: 100%;
    height: 100%;
    margin: 0;
    padding: 0;
    opacity: 0;
    cursor: pointer;
}
</style>
