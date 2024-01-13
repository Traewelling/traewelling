<script>
import FullScreenModal from "./FullScreenModal.vue";
import VisibilityDropdown from "./VisibilityDropdown.vue";
import {trans} from "laravel-vue-i18n";
import TagRow from "./TagRow.vue";

export default {
    name: "TagHelper",
    components: {
        TagRow,
        VisibilityDropdown,
        FullScreenModal
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
        trans,
        showModal() {
            this.$refs.modal.show();
        },
        addTag(value) {
            this.tags.push(value);
        },
        updateTag(event, tag) {
            if (event === null) {
                this.tags = this.tags.filter(item => item.key !== tag.key);
            } else {
                this.tags = this.tags.map(item => {
                    if (item.key === tag.key) {
                        return event;
                    }
                    return item;
                });
            }
        }
    },
    computed: {
        excludeTags() {
            return this.tags.map(key => key.key);
        }
    }
}
</script>

<template>
    <FullScreenModal ref="modal">
        <template #header>
            <div class="col-1 align-items-center d-flex">
                Tags
                {{ trans('export.title.status_tags') }}
            </div>
        </template>
        <template #body>
            <TagRow @update:model-value="addTag" :exclude="excludeTags"></TagRow>
            <hr>
            <TagRow @update:model-value="updateTag($event, tag)" class="mb-1" v-for="tag in tags" :key="tag.key" :value="tag" :list="true"></TagRow>
        </template>
    </FullScreenModal>

    <button class="btn btn-link btn-sm text-white badge bg-trwl" @click="showModal()">
        <i class="fa fa-plus"></i>
        New Tag
    </button>
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
