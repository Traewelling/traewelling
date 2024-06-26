<script>
import VisibilityDropdown from "./VisibilityDropdown.vue";
import _ from "lodash";
import {getIcon, getTitle, keys} from "../helpers/StatusTag";

export default {
    name: "TagRow",
    props: {
        value: {
            type: Object,
            default: null
        },
        list: {
            type: Boolean,
            default: false
        },
        exclude: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            baseKeys: keys,
            selectedKey: this.value?.key,
            input: this.value?.value,
            visibility: 0
        };
    },
    components: {VisibilityDropdown},
    methods: {
        getTitle,
        getIcon,
        selectKey(key) {
            if (key) {
                this.selectedKey = key;
            } else {
                this.selectedKey = this.tagKeys[0];
            }
        },
        addTag() {
            if (this.updateTag()) {
                this.input = null;
                this.selectKey();
            }
        },
        deleteTag() {
            this.$emit("update:model-value", null);
        },
        updateTag() {
            if (this.input) {
                this.$emit("update:model-value", {
                    key: this.selectedKey,
                    value: this.input,
                    visibility: this.visibility
                });
                return true;
            }

            return false;
        },
        setVisibility(visibility) {
            this.visibility = visibility;

            if (this.list) {
                this.updateTag();
            }
        }
    },
    mounted() {
        this.visibility = this.value?.visibility ?? 0;
        if (!this.selectedKey) {
            this.selectKey();
        }
    },
    computed: {
        tagKeys() {
            return this.baseKeys.filter((key) => !this.exclude.includes(key));
        },
        disabled() {
            return this.tagKeys.length === 0;
        }
    },
    watch: {
        exclude() {
            this.selectKey();
        },
        input: _.debounce(function(){
            if (this.list) {
                this.updateTag();
            }
        }, 1000)
    },
    emits: ["update:model-value"]
};
</script>

<template>
    <div class="input-group">
        <button
            class="btn btn-outline-secondary dropdown-toggle"
            type="button"
            data-mdb-toggle="dropdown"
            aria-expanded="false"
            :disabled="list || disabled"
        >
            <i v-if="getIcon(selectedKey) !== 'fa-fw'" :class="[getIcon(selectedKey), 'fa']" aria-hidden="true"></i>
            <span v-show="getIcon(selectedKey) === 'fa-fw'">{{ getTitle(selectedKey) }}</span>
        </button>
        <ul class="dropdown-menu">
            <li v-for="tagKey in tagKeys">
                <a class="dropdown-item" href="#" @click="selectKey(tagKey)">
                    <i :class="[getIcon(tagKey), 'fa']" class="w-0125 text-center" aria-hidden="true"></i>
                    {{ getTitle(tagKey) }}
                </a>
            </li>
        </ul>
        <input
            :id="`input-${selectedKey?.replace(':', '')}`"
            type="text"
            class="form-control border-secondary mobile-input-fs-16"
            v-model="input"
            :disabled="disabled"
            @keydown.enter="addTag"
        >
        <VisibilityDropdown :start-value="visibility" @update:model-value="setVisibility" :disabled="disabled"></VisibilityDropdown>
        <button v-if="!list" class="btn btn-primary" @click="addTag" :disabled="disabled">Add</button>
        <button v-if="list" class="btn btn-outline-danger" @click="deleteTag"><i class="fa fa-trash"></i></button>
    </div>
</template>

<style scoped lang="scss">
.w-0125 {
    width: 12.5%;
}
</style>
