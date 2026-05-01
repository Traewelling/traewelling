<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Plus, Tag, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Api, StatusTagResource } from '../../../types/Api.gen';
import { TrwlTag } from '../../../types/TrwlTags';
import { getEnumValues, getTitle, keys } from '../../../vue/helpers/StatusTag';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const tags = ref<TrwlTag[]>([]);
const open = ref(false);

const selectedKey = ref<string>(keys[0]);
const inputValue = ref('');
const tagVisibility = ref(0);

const enumValues = computed(() => getEnumValues(selectedKey.value));
const isEnum = computed(() => enumValues.value !== null);
const availableKeys = computed(() => keys.filter((k) => !tags.value.some((t) => t.key === k)));

const visibilityOptions = computed(() => [
    { value: 0, label: trans('status.visibility.0') },
    { value: 2, label: trans('status.visibility.2') },
    { value: 3, label: trans('status.visibility.3') },
]);

function onKeyChange(): void {
    const vals = getEnumValues(selectedKey.value);
    inputValue.value = vals ? vals[0].value : '';
}

function addTag(): void {
    if (!inputValue.value || !selectedKey.value) return;
    tags.value.push({ key: selectedKey.value, value: inputValue.value, visibility: tagVisibility.value });
    selectedKey.value = availableKeys.value.find((k) => k !== selectedKey.value) ?? availableKeys.value[0] ?? keys[0];
    const vals = getEnumValues(selectedKey.value);
    inputValue.value = vals ? vals[0].value : '';
    tagVisibility.value = 0;
    if (!availableKeys.value.length) open.value = false;
}

function removeTag(key: string): void {
    tags.value = tags.value.filter((t) => t.key !== key);
}

async function postTags(statusId: number): Promise<void> {
    await Promise.all(tags.value.map((tag) => api.status.createSingleStatusTag(tag as StatusTagResource, statusId)));
}

defineExpose({ postTags });
</script>

<template>
    <div class="relative">
        <div v-if="open" class="fixed inset-0 z-40" @click="open = false" />
        <button
            type="button"
            class="btn btn-sm gap-1"
            :class="tags.length ? 'btn-primary' : 'btn-ghost'"
            :disabled="availableKeys.length === 0 && !tags.length"
            @click="open = !open"
        >
            <Tag class="w-4 h-4" />
            <span v-if="tags.length" class="text-xs">{{ tags.length }}</span>
        </button>

        <div
            v-if="open"
            class="absolute bottom-full left-0 z-50 mb-1 w-80 border border-base-300 rounded-box p-3 flex flex-col gap-3 bg-base-100 shadow-lg"
        >
            <!-- Existing tags -->
            <div v-if="tags.length" class="flex flex-col gap-1">
                <div
                    v-for="tag in tags"
                    :key="tag.key"
                    class="flex items-center gap-2 text-sm bg-base-200 rounded px-2 py-1"
                >
                    <span class="text-base-content/60 flex-shrink-0">{{ getTitle(tag.key) }}</span>
                    <span class="flex-1 truncate font-medium">{{ tag.value }}</span>
                    <button type="button" class="btn btn-ghost btn-xs btn-square" @click="removeTag(tag.key)">
                        <Trash2 class="w-3 h-3" />
                    </button>
                </div>
            </div>

            <!-- Add tag form -->
            <div v-if="availableKeys.length" class="flex flex-col gap-2">
                <div class="flex gap-2 flex-wrap">
                    <select
                        v-model="selectedKey"
                        class="select select-bordered select-sm flex-1 min-w-[130px]"
                        @change="onKeyChange"
                    >
                        <option v-for="key in availableKeys" :key="key" :value="key">
                            {{ getTitle(key) }}
                        </option>
                    </select>

                    <select
                        v-if="isEnum"
                        v-model="inputValue"
                        class="select select-bordered select-sm flex-1 min-w-[130px]"
                    >
                        <option v-for="opt in enumValues" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                    <input
                        v-else
                        v-model="inputValue"
                        type="text"
                        class="input input-bordered input-sm flex-1 min-w-[130px]"
                        :placeholder="trans('tag.value')"
                        @keydown.enter.prevent="addTag"
                    />

                    <select v-model="tagVisibility" class="select select-bordered select-sm w-[100px]">
                        <option v-for="opt in visibilityOptions" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                </div>

                <button type="button" class="btn btn-sm btn-outline w-full" :disabled="!inputValue" @click="addTag">
                    <Plus class="w-4 h-4" />
                    {{ trans('tag.add') }}
                </button>
            </div>
        </div>
    </div>
</template>
