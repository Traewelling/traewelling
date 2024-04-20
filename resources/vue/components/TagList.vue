<script lang="ts">
import {defineComponent} from 'vue'
import TagRow from "./TagRow.vue";
import {TrwlTag} from "../../types/TrwlTags";

export default defineComponent({
    name: "TagList",
    components: {TagRow},
    props: {
        tags: {
            type: Array as () => TrwlTag[],
            required: false,
            default: []
        },
        statusId: {
            type: Number,
            required: false,
            default: null
        },
        cacheLocally: {
            type: Boolean,
            required: false,
            default: false
        }
    },
    data() {
        return {
            _tags: [] as TrwlTag[],
            _statusId: null as null | number,
        }
    },
    methods: {
        addTag(value: string) {
            this.postAddTag(value).then((data) => {
                this._tags.push(data.data);
            });
        },
        updateTag(event: any, tag: TrwlTag) {
            if (event === null) {
                this.postDeleteTag(tag).then(() => {
                    this._tags = this._tags.filter((item) => item.key !== tag.key);
                    this.$emit("update:model-value", this._tags);
                });
            } else {
                this.postUpdateTag(event, tag).then((data) => {
                    this._tags = this._tags.map((item) => {
                        if (item.key === tag.key) {
                            return data.data;
                        }
                        return item;
                    });
                    this.$emit("update:model-value", this._tags);
                });
            }
        },
        postAllTags() {
            for (const tag of this._tags) {
                this.postAddTag(tag);
            }
        },
        async postDeleteTag(tag: TrwlTag) {
            if (this.$props.cacheLocally) {
                return new Promise((resolve) => {
                    resolve({})
                });
            }
            return fetch(`/api/v1/status/${this.statusId}/tags/${tag.key}`, {
                method: "DELETE",
            }).then(response => response.json())
        },
        async postUpdateTag(event: any, tag: TrwlTag) {
            if (this.$props.cacheLocally) {
                return new Promise((resolve) => {
                    resolve({data: event})
                });
            }
            return fetch(`/api/v1/status/${this.statusId}/tags/${tag.key}`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(event)
            }).then(response => response.json())
        },
        async postAddTag(value: string | TrwlTag) {
            if (this.$props.cacheLocally) {
                new Promise((resolve) => {
                    resolve({data: value})
                });
            }
            return fetch(`/api/v1/status/${this.$props.statusId}/tags`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(value)
            })
                .then((response) => response.json())
        }
    },
    computed: {
        excludeTags() {
            return this._tags.map(key => key.key);
        }
    },
    mounted() {
        this._tags = this.$props.tags;
        this._statusId = this.statusId;
    },
    watch: {
        tags: {
            handler(tags: TrwlTag[]) {
                this._tags = tags;
            },
            immediate: true
        },
        statusId: {
            handler(statusId: number) {
                this._statusId = statusId;
            },
            immediate: true
        }
    },
    emits: ["update:model-value"]
})
</script>

<template>
    <TagRow @update:model-value="addTag" :exclude="excludeTags"></TagRow>
    <hr>
    <TagRow @update:model-value="updateTag($event, tag)" class="mb-1"
            v-for="tag in _tags" :key="tag.key" :value="tag" :list="true"></TagRow>
</template>

<style scoped lang="scss">

</style>
