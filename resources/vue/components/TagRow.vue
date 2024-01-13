<script>
import VisibilityDropdown from "./VisibilityDropdown.vue";
import {trans} from "laravel-vue-i18n";
import _ from "lodash";

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
            baseKeys: [
                'trwl:seat',
                'trwl:ticket',
                'trwl:role',
                'trwl:passenger_rights',
                'trwl:wagon',
                'trwl:travel_class',
                'trwl:locomotive_class',
                'trwl:wagon_class',
                'trwl:vehicle_number'
            ],
            selectedKey: this.value?.key,
            input: this.value?.value,
            visibility: this.value?.visibility ?? 0
        };
    },
    components: {VisibilityDropdown},
    methods: {
        getIcon(key) {
            switch (key) {
                case "trwl:seat":
                    return 'fa-couch';
                case 'trwl:role':
                    return 'fa-briefcase';
                case 'trwl:ticket':
                    return 'fa-qrcode';
                case 'trwl:passenger_rights':
                    return 'fa-user-shield';
            }
            return 'fa-fw';
        },
        getTitle(key) {
            let translate = trans('tag.title.' + key);

            if (translate === 'tag.title.' + key) {
                return key;
            }
            return translate;
        },
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
            if (this.input === null || this.input === "") {
                return false;
            }
            this.$emit("update:model-value", {
                key: this.selectedKey,
                value: this.input,
                visibility: this.visibility
            });
            return true;
        }
    },
    mounted() {
        if (!this.selectedKey) {
            this.selectKey();
        }
    },
    computed: {
        tagKeys() {
            return this.baseKeys.filter(key => !this.exclude.includes(key));
        },
        disabled() {
            return this.tagKeys.length === 0;
        }
    },
    watch: {
        exclude() {
            this.selectKey();
        },
        visibility() {
            if (this.list) {
                this.updateTag();
            }
        },
        input: _.debounce(function(){
            if (this.list) {
                this.updateTag();
            }
        }, 1000)
    },
    emits: ["update:model-value"]
}
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
            {{ getTitle(selectedKey) }}
        </button>
        <ul class="dropdown-menu">
            <li v-for="tagKey in tagKeys">
                <a class="dropdown-item" href="#" @click="selectKey(tagKey)">
                    <i :class="[getIcon(tagKey), 'fa']" aria-hidden="true"></i>
                    {{ getTitle(tagKey) }}
                </a>
            </li>
        </ul>
        <input :id="`input-${selectedKey?.replace(':', '')}`" type="text" class="form-control" v-model="input" :disabled="disabled">
        <VisibilityDropdown v-model="visibility" :disabled="disabled"></VisibilityDropdown>
        <button v-if="!list" class="btn btn-primary" @click="addTag" :disabled="disabled">Add</button>
        <button v-if="list" class="btn btn-outline-danger" @click="deleteTag"><i class="fa fa-trash"></i></button>
    </div>
</template>

<style scoped lang="scss">

</style>
