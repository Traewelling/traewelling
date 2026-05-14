<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Plus, Tag, Trash2 } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import { Api, StatusTagResource, StatusTagSuggestionResource } from '../../../types/Api.gen';
import { TrwlTag } from '../../../types/TrwlTags';
import { getEnumValues, getTitle, keys } from '../../../vue/helpers/StatusTag';
import { getVisibilityOptions } from '../../helpers/visibility';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const CUSTOM_SENTINEL = '__custom__';

const tags = ref<TrwlTag[]>([]);
const showAddForm = ref(false);
const suggestions = ref<StatusTagSuggestionResource[]>([]);

const availableSuggestions = computed(() => suggestions.value.filter((s) => !tags.value.some((t) => t.key === s.key)));

onMounted(async () => {
    try {
        const res = await api.tags.getTagSuggestions();
        suggestions.value = (res.data?.data ?? []) as StatusTagSuggestionResource[];
    } catch {
        // ignore
    }
});

function applySuggestion(suggestion: StatusTagSuggestionResource): void {
    if (tags.value.some((t) => t.key === suggestion.key)) return;
    tags.value.push({ key: suggestion.key!, value: suggestion.value!, visibility: 0 });
}

const selectedKey = ref<string>(keys[0]);
const isCustomKey = ref(false);
const customKeyInput = ref('');
const inputValue = ref('');
const tagVisibility = ref(0);

const enumValues = computed(() => getEnumValues(selectedKey.value));
const isEnum = computed(() => enumValues.value !== null && !isCustomKey.value);
const availableKeys = computed(() => keys.filter((k) => !tags.value.some((t) => t.key === k)));

const visibilityOptions = computed(getVisibilityOptions);

function onKeyChange(): void {
    isCustomKey.value = selectedKey.value === CUSTOM_SENTINEL;
    customKeyInput.value = '';
    if (!isCustomKey.value) {
        const vals = getEnumValues(selectedKey.value);
        inputValue.value = vals ? vals[0].value : '';
    } else {
        inputValue.value = '';
    }
}

function addTag(): void {
    const actualKey = isCustomKey.value ? customKeyInput.value.trim() : selectedKey.value;
    if (!actualKey || !inputValue.value) return;
    if (tags.value.some((t) => t.key === actualKey)) return;

    tags.value.push({ key: actualKey, value: inputValue.value, visibility: tagVisibility.value });
    tagVisibility.value = 0;

    const nextKey = availableKeys.value[0];
    if (nextKey) {
        selectedKey.value = nextKey;
        isCustomKey.value = false;
        const vals = getEnumValues(nextKey);
        inputValue.value = vals ? vals[0].value : '';
    } else {
        selectedKey.value = CUSTOM_SENTINEL;
        isCustomKey.value = true;
        customKeyInput.value = '';
        inputValue.value = '';
    }
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
    <div class="flex flex-col gap-2">
        <!-- Suggestions -->
        <div v-if="availableSuggestions.length" class="flex flex-wrap gap-1">
            <button
                v-for="s in availableSuggestions"
                :key="s.key + ':' + s.value"
                type="button"
                class="badge badge-outline gap-1 cursor-pointer hover:badge-primary transition-colors"
                @click="applySuggestion(s)"
            >
                <Plus class="w-2.5 h-2.5" />
                <span class="text-xs">{{ getTitle(s.key!) }}: {{ s.value }}</span>
            </button>
        </div>

        <!-- Current tags -->
        <div v-if="tags.length" class="flex flex-col gap-1">
            <div v-for="(tag, i) in tags" :key="tag.key" class="flex items-center gap-1">
                <span class="text-base-content/60 text-xs flex-shrink-0 w-20 truncate" :title="getTitle(tag.key)">
                    {{ getTitle(tag.key) }}
                </span>
                <select
                    v-if="getEnumValues(tag.key)"
                    v-model="tags[i].value"
                    class="select select-bordered select-xs flex-1 min-w-0"
                >
                    <option v-for="opt in getEnumValues(tag.key)" :key="opt.value" :value="opt.value">
                        {{ opt.label }}
                    </option>
                </select>
                <input
                    v-else
                    v-model="tags[i].value"
                    type="text"
                    class="input input-bordered input-xs flex-1 min-w-0"
                />
                <select v-model="tags[i].visibility" class="select select-bordered select-xs w-24 flex-shrink-0">
                    <option v-for="opt in visibilityOptions" :key="opt.value" :value="opt.value">
                        {{ opt.label }}
                    </option>
                </select>
                <button type="button" class="btn btn-ghost btn-xs btn-square flex-shrink-0" @click="removeTag(tag.key)">
                    <Trash2 class="w-3 h-3" />
                </button>
            </div>
        </div>

        <!-- Add tag toggle -->
        <button
            v-if="!showAddForm"
            type="button"
            class="btn btn-sm btn-ghost btn-outline w-full"
            @click="showAddForm = true"
        >
            <Tag class="w-4 h-4" />
            {{ trans('tag.add') }}
        </button>

        <!-- Add tag form (inline) -->
        <div v-else class="flex flex-col gap-2">
            <div class="flex gap-2 flex-wrap">
                <select
                    v-model="selectedKey"
                    class="select select-bordered select-sm flex-1 min-w-[130px]"
                    @change="onKeyChange"
                >
                    <option v-for="key in availableKeys" :key="key" :value="key">
                        {{ getTitle(key) }}
                    </option>
                    <option :value="CUSTOM_SENTINEL">{{ trans('tag.custom_key') }}</option>
                </select>

                <template v-if="!isCustomKey">
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
                </template>

                <select v-model="tagVisibility" class="select select-bordered select-sm w-[100px]">
                    <option v-for="opt in visibilityOptions" :key="opt.value" :value="opt.value">
                        {{ opt.label }}
                    </option>
                </select>
            </div>

            <!-- Custom key row -->
            <div v-if="isCustomKey" class="flex gap-2">
                <input
                    v-model="customKeyInput"
                    type="text"
                    class="input input-bordered input-sm flex-1"
                    :placeholder="trans('tag.custom_key.placeholder')"
                    @keydown.enter.prevent="addTag"
                />
                <input
                    v-model="inputValue"
                    type="text"
                    class="input input-bordered input-sm flex-1"
                    :placeholder="trans('tag.value')"
                    @keydown.enter.prevent="addTag"
                />
            </div>

            <button
                type="button"
                class="btn btn-sm btn-outline w-full"
                :disabled="!inputValue || (isCustomKey && !customKeyInput.trim())"
                @click="addTag"
            >
                <Plus class="w-4 h-4" />
                {{ trans('tag.add') }}
            </button>
        </div>
    </div>
</template>
