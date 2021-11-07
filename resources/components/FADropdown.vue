<template>
    <div class="btn-group">
        <button ref="dropdownButton" :aria-label="dropDown[selected].desc"
                :class="buttonClass"
                aria-expanded="false"
                class="btn btn-sm dropdown-toggle"
                data-mdb-toggle="dropdown"
                type="button"
        >
            <i :class="dropDown[selected].icon" aria-hidden="true"></i>
            <span v-if="showText">{{ i18n.get(dropDown[selected].desc) }}</span>
        </button>
        <ul :aria-labelledby="$refs.dropdownButton" class="dropdown-menu">
            <li v-for="(item, key) in dropDown" class="dropdown-item" @click="selectItem(key)">
                <i :class="item.icon" aria-hidden="true"></i> {{ i18n.get(item.desc) }}
                <span v-if="item.detail" class="text-muted"><br/>{{ i18n.get(item.detail) }}</span>
            </li>
        </ul>
    </div>
</template>

<script>
export default {
    name: "FADropdown",
    data() {
        return {
            selectedValue: null
        };
    },
    model: {
        prop: "value",
        event: "input"
    },
    props: ["btnClass", "dropdownContent", "preSelect", "showText", "value"],
    computed: {
        buttonClass() {
            if (this.$props.btnClass) {
                return this.$props.btnClass;
            }
            return "btn-outline-primary";
        },
        dropDown() {
            return this.$props.dropdownContent;
        },
        selected() {
            if (!this.selectedValue && this.$props.preSelect) {
                return this.$props.preSelect;
            }
            if (this.selectedValue) {
                return this.selectedValue;
            }
            return 0;
        }
    },
    methods: {
        selectItem(key) {
            this.selectedValue = key;

            this.$emit("input", this.selectedValue);
        }
    }
};
</script>

<style scoped>

</style>
