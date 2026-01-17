<script lang="ts">
import { defineComponent } from 'vue';
import { TrwlTag } from '../../types/TrwlTags';
import TagRow from './TagRow.vue';

export default defineComponent({
    name: 'TagList',
    components: { TagRow },
    props: {
        tags: {
            type: Array as () => TrwlTag[],
            required: false,
            default: () => [],
        },
        statusId: {
            type: Number,
            required: false,
            default: null,
        },
        cacheLocally: {
            type: Boolean,
            required: false,
            default: false,
        },
    },
    emits: ['update:model-value'],
    data() {
        return {
            i_tags: [] as TrwlTag[],
            i_statusId: null as null | number,
        };
    },
    computed: {
        excludeTags() {
            return this.i_tags.map((key) => key.key);
        },
    },
    watch: {
        tags: {
            handler(tags: TrwlTag[]) {
                this.i_tags = tags;
            },
            immediate: true,
        },
        statusId: {
            handler(statusId: number) {
                this.i_statusId = statusId;
            },
            immediate: true,
        },
    },
    mounted() {
        this.i_tags = this.$props.tags;
        this.i_statusId = this.statusId;
    },
    methods: {
        addTag(value: string) {
            this.postAddTag(value).then((data) => {
                this.i_tags.push(data.data);
            });
        },
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        updateTag(event: any, tag: TrwlTag) {
            if (event === null) {
                this.postDeleteTag(tag).then(() => {
                    this.i_tags = this.i_tags.filter((item) => item.key !== tag.key);
                    this.$emit('update:model-value', this.i_tags);
                });
            } else {
                this.postUpdateTag(event, tag).then((data) => {
                    this.i_tags = this.i_tags.map((item) => {
                        if (item.key === tag.key) {
                            return data.data;
                        }
                        return item;
                    });
                    this.$emit('update:model-value', this.i_tags);
                });
            }
        },
        async postAllTags(statusId: number) {
            return Promise.all(this.i_tags.map((tag) => this.postAddTag(tag, statusId)));
        },
        async postDeleteTag(tag: TrwlTag) {
            if (this.$props.cacheLocally) {
                return new Promise((resolve) => {
                    resolve({});
                });
            }
            return fetch(`/api/v1/status/${this.i_statusId}/tags/${tag.key}`, {
                method: 'DELETE',
            }).then((response) => response.json());
        },
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        async postUpdateTag(event: any, tag: TrwlTag) {
            if (this.$props.cacheLocally) {
                return new Promise((resolve) => {
                    resolve({ data: event });
                });
            }
            return fetch(`/api/v1/status/${this.i_statusId}/tags/${tag.key}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(event),
            }).then((response) => response.json());
        },
        async postAddTag(value: string | TrwlTag, statusId: number | null = null) {
            if (this.$props.cacheLocally && statusId === null) {
                return new Promise((resolve) => {
                    resolve({ data: value });
                });
            }
            statusId = statusId || this.i_statusId;

            return fetch(`/api/v1/status/${statusId}/tags`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(value),
            }).then((response) => response.json());
        },
    },
});
</script>

<template>
    <TagRow :exclude="excludeTags" @update:model-value="addTag" />
    <hr v-if="tags.length" />
    <TagRow
        v-for="tag in i_tags"
        :key="tag.key"
        class="mb-1"
        :value="tag"
        :list="true"
        @update:model-value="updateTag($event, tag)"
    />
</template>

<style scoped lang="scss"></style>
