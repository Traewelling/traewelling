<script>
import FullScreenModal from "./FullScreenModal.vue";
import VisibilityDropdown from "./VisibilityDropdown.vue";
import {trans} from "laravel-vue-i18n";
import TagRow from "./TagRow.vue";
import {getIcon, getTitle} from "../helpers/StatusTag";
import TagList from "./TagList.vue";

export default {
    name: "TagHelper",
    components: {
        TagList,
        TagRow,
        VisibilityDropdown,
        FullScreenModal
    },
    props: {
        statusId: {
            type: Number,
        },
        editable: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            tags: [],
            newTag: {
                key: null,
                value: null,
                visibility: 0
            }
        };
    },
    methods: {
        getTitle,
        getIcon,
        trans,
        showModal(tag) {
            this.$refs.modal.show();
            let input = "input";
            if (tag) {
                input = "#input-" + tag.key.replace(":", "");
            }
            // automatically focus the input field of the tag
            setTimeout(() => {
                this.$refs.modal.$el.querySelector(input).focus();
            }, 100);
        },
        fetchTags() {
            fetch(`/api/v1/status/${this.$props.statusId}/tags`)
                .then(response => response.json())
                .then(data => {
                    this.tags = data.data;
                })
        },
        updateTags(tags) {
            this.tags = tags;
        }
    },
    mounted() {
        this.fetchTags();
    },
};
</script>

<template>
    <FullScreenModal ref="modal">
        <template #header>
            {{ trans("export.title.status_tags") }}
        </template>
        <template #body>
            <TagList @update:model-value="updateTags" :tags="tags" :status-id="statusId"/>
        </template>
    </FullScreenModal>

    <button v-show="editable" class="btn btn-link btn-sm text-white badge bg-trwl" @click="showModal()"
        style="text-transform: none;"
    >
        <i class="fa fa-plus"></i>
        {{ trans("modals.tags.new") }}
    </button>

    <button
        v-if="editable"
        v-for="tag in tags"
        :key="tag.key"
        class="btn btn-link btn-sm text-white badge bg-trwl ms-1"
        style="text-transform: none;"
        @click="showModal(tag)"
    >
        <i v-show="getIcon(tag.key) !== 'fa-fw'" :class="[getIcon(tag.key), 'fa']"></i>
        {{ tag.value }}
    </button>
    <span
        v-else
        v-for="tag in tags"
        :key="tag.key"
        class="text-white badge bg-trwl ms-1"
        data-bs-toggle="tooltip"
        data-bs-placement="top"
        :title="getTitle(tag.key)"
        :ref="tag.key"
    >
        <i v-show="getIcon(tag.key) !== 'fa-fw'" :class="[getIcon(tag.key), 'fa']"></i>
        {{ tag.value }}
    </span>
    &nbsp;
    <slot/>
</template>

<style scoped lang="scss">
@import "../../sass/_variables.scss";

.btn-outline-trwl {
    border-color: $trwlRot;
    color: $trwlRot;
}

</style>
