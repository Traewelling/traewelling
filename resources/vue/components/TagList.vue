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
        }
    },
    data() {
        return {
            _tags: [] as TrwlTag[],
            _statusId: null as null | number,
        }
    },
    methods: {
        updateTag(event: any, tag: TrwlTag) {
            if (event === null) {
                fetch(`/api/v1/status/${this._statusId}/tags/${tag.key}`, {
                    method: "DELETE",
                })
                    .then(response => response.json())
                    .then(() => {
                        this._tags = this._tags.filter((item) => item.key !== tag.key);
                    })
            } else {
                fetch(`/api/v1/status/${this.statusId}/tags/${tag.key}`, {
                    method: "PUT",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify(event)
                })
                    .then(response => response.json())
                    .then(data => {
                        this._tags = this._tags.map((item) => {
                            if (item.key === tag.key) {
                                return data.data;
                            }
                            return item;
                        });
                    })
            }
        },
        addTag(value: string) {
            fetch(`/api/v1/status/${this.$props.statusId}/tags`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(value)
            })
                .then((response) => response.json())
                .then((data) => {
                    this._tags.push(data.data);
                    this.$emit("update:model-value", this._tags);
                });
        },

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
