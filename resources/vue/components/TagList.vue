<script lang="ts">
import { defineComponent } from 'vue';
import TagRow from './TagRow.vue';
import { TrwlTag } from '../../types/TrwlTags';

export default defineComponent({
    name: 'TagList',
    components: { TagRow },
    props: {
        tags: {
            type: Array as () => TrwlTag[],
            required: false,
            default: [],
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
            _tags: [] as TrwlTag[],
            _statusId: null as null | number,
        };
    },
    computed: {
        excludeTags() {
            return this._tags.map(key => key.key);
        },
    },
    watch: {
        tags: {
            handler(tags: TrwlTag[]) {
                this._tags = tags;
            },
            immediate: true,
        },
        statusId: {
            handler(statusId: number) {
                this._statusId = statusId;
            },
            immediate: true,
        },
    },
    mounted() {
        this._tags = this.$props.tags;
        this._statusId = this.statusId;
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
                    this.$emit('update:model-value', this._tags);
                });
            } else {
                this.postUpdateTag(event, tag).then((data) => {
                    this._tags = this._tags.map((item) => {
                        if (item.key === tag.key) {
                            return data.data;
                        }
                        return item;
                    });
                    this.$emit('update:model-value', this._tags);
                });
            }
        },
        async postAllTags(statusId: number) {
            return Promise.all(this._tags.map(tag => this.postAddTag(tag, statusId)));
        },
        async postDeleteTag(tag: TrwlTag) {
            if (this.$props.cacheLocally) {
                return new Promise((resolve) => {
                    resolve({});
                });
            }
            return fetch(`/api/v1/status/${this._statusId}/tags/${tag.key}`, {
                method: 'DELETE',
            }).then(response => response.json());
        },
        async postUpdateTag(event: any, tag: TrwlTag) {
            if (this.$props.cacheLocally) {
                return new Promise((resolve) => {
                    resolve({ data: event });
                });
            }
            return fetch(`/api/v1/status/${this._statusId}/tags/${tag.key}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(event),
            }).then(response => response.json());
        },
        async postAddTag(value: string | TrwlTag, statusId: number|null = null) {
            if (this.$props.cacheLocally && statusId === null) {
                return new Promise((resolve) => {
                    resolve({ data: value });
                });
            }
            statusId = statusId || this._statusId;

            return fetch(`/api/v1/status/${statusId}/tags`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(value),
            })
                .then((response) => response.json());
        },
    },
});
</script>

<template>
    <TagRow :exclude="excludeTags" @update:model-value="addTag" />
    <hr v-if="tags.length">
    <TagRow
        v-for="tag in _tags"
        :key="tag.key"
        class="mb-1"
        :value="tag"
        :list="true"
        @update:model-value="updateTag($event, tag)"
    />
</template>

<style scoped lang="scss">

</style>
