<script setup lang="ts">
import { Plus, Trash2 } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { inject, ref, watch } from 'vue';
import { Api, StatusTagKey, StatusTagResource, StatusVisibility } from '../../../types/Api.gen';
import { getEnumValues, getTitle } from '../../helpers/StatusTag';
import { ALL_VISIBILITIES, VISIBILITY_ICONS } from '../../helpers/visibility';

const props = defineProps<{
    statusId: number;
    tags: StatusTagResource[];
    editable: boolean;
}>();

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const localTags = ref<StatusTagResource[]>([...props.tags]);
const notyf = inject('notyf') as Notyf;

// add modal
const showAddModal = ref(false);
const newKey = ref<string>(StatusTagKey.TrwlJourneyNumber);
const isCustomKey = ref(false);
const customKeyInput = ref('');
const customKeyError = ref(false);
const newValue = ref('');
const newVisibility = ref<number>(StatusVisibility.Value0);
const saving = ref(false);

// edit modal
const showEditModal = ref(false);
const editTag = ref<StatusTagResource | null>(null);
const editValue = ref('');
const editVisibility = ref<number>(StatusVisibility.Value0);
const editSaving = ref(false);
const deletingKey = ref<string | null>(null);

const CUSTOM_SENTINEL = '__custom__';

function closeDropdown() {
    (document.activeElement as HTMLElement)?.blur();
}

function enumValues(key: string) {
    return getEnumValues(key);
}

function displayValue(tag: StatusTagResource): string {
    const opts = enumValues(tag.key);
    return opts ? (opts.find((o) => o.value === tag.value)?.label ?? tag.value) : tag.value;
}

const availableKeys = () => Object.values(StatusTagKey).filter((k) => !localTags.value.some((t) => t.key === k));

function openAddModal() {
    const available = availableKeys();
    if (available.length > 0) {
        newKey.value = available[0];
        isCustomKey.value = false;
        const vals = enumValues(newKey.value);
        newValue.value = vals ? vals[0].value : '';
    } else {
        newKey.value = CUSTOM_SENTINEL;
        isCustomKey.value = true;
        newValue.value = '';
    }
    customKeyInput.value = '';
    newVisibility.value = StatusVisibility.Value0;
    showAddModal.value = true;
}

function onKeyChange() {
    isCustomKey.value = newKey.value === CUSTOM_SENTINEL;
    customKeyInput.value = '';
    if (isCustomKey.value) {
        newValue.value = '';
    } else {
        const vals = enumValues(newKey.value);
        newValue.value = vals ? vals[0].value : '';
    }
}

function openEditModal(tag: StatusTagResource) {
    if (!props.editable) return;
    editTag.value = tag;
    editValue.value = tag.value;
    editVisibility.value = tag.visibility ?? StatusVisibility.Value0;
    showEditModal.value = true;
}

async function addTag() {
    const actualKey = isCustomKey.value ? customKeyInput.value.trim() : newKey.value;
    if (!actualKey || !newValue.value) return;
    saving.value = true;
    try {
        const res = await api.status.createSingleStatusTag(
            { key: actualKey, value: newValue.value, visibility: newVisibility.value } as StatusTagResource,
            props.statusId,
        );
        localTags.value.push(res.data.data as StatusTagResource);
        showAddModal.value = false;
    } catch (e: unknown) {
        if (e.status === 422 && e.error.message) {
            notyf.error(e.error.message);
        }
    } finally {
        saving.value = false;
    }
}

async function saveEditTag() {
    if (!editTag.value || !editValue.value) return;
    editSaving.value = true;
    try {
        const res = await api.status.updateSingleStatusTag(
            { key: editTag.value.key, value: editValue.value, visibility: editVisibility.value } as StatusTagResource,
            props.statusId,
            editTag.value.key,
        );
        const updated = res.data.data as StatusTagResource;
        localTags.value = localTags.value.map((t) => (t.key === updated.key ? updated : t));
        showEditModal.value = false;
    } catch (e: unknown) {
        if (e.status === 422 && e.error.message) {
            notyf.error(e.error.message);
        }
    } finally {
        editSaving.value = false;
    }
}

async function deleteTag(tag: StatusTagResource) {
    deletingKey.value = tag.key;
    showEditModal.value = false;
    try {
        await api.status.destroySingleStatusTag(props.statusId, tag.key);
        localTags.value = localTags.value.filter((t) => t.key !== tag.key);
    } finally {
        deletingKey.value = null;
    }
}

watch(customKeyInput, () => {
    const startsWithError = new RegExp(/^\w[^/\n\r%?\\<>]*$/);
    if (customKeyInput.value.length === 0) {
        customKeyError.value = false;
        return;
    }
    if (!startsWithError.test(customKeyInput.value)) {
        customKeyError.value = true;
        return;
    }
    customKeyError.value = false;
});
</script>

<template>
    <div v-if="localTags.length || editable" class="flex flex-wrap items-center gap-1.5">
        <button
            v-if="editable"
            class="badge badge-outline gap-1 text-xs cursor-pointer hover:badge-primary transition-colors"
            @click="openAddModal"
        >
            <Plus class="inline-block size-3" />
            {{ trans('modals.tags.new') }}
        </button>

        <span
            v-for="tag in localTags"
            :key="tag.key"
            :data-tip="getTitle(tag.key)"
            class="badge badge-outline gap-1 text-xs tooltip"
            :class="editable ? 'cursor-pointer hover:badge-primary transition-colors' : ''"
            @click="editable && openEditModal(tag)"
        >
            {{ displayValue(tag) }}
            <span v-if="deletingKey === tag.key" class="loading loading-spinner loading-xs" />
        </span>
    </div>

    <dialog class="modal" :class="{ 'modal-open': showAddModal }">
        <div class="modal-box overflow-visible">
            <h3 class="font-bold text-lg mb-4">{{ trans('export.title.status_tags') }}</h3>

            <div class="grid grid-cols-2 gap-3 mb-3">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">
                        {{ trans('tag.key') }}
                    </legend>
                    <select v-model="newKey" class="select select-bordered" @change="onKeyChange">
                        <option v-for="k in availableKeys()" :key="k" :value="k">{{ getTitle(k) }}</option>
                        <option :value="CUSTOM_SENTINEL">{{ trans('tag.custom_key') }}</option>
                    </select>
                </fieldset>

                <fieldset v-if="!isCustomKey" class="fieldset">
                    <legend class="fieldset-legend">
                        {{ trans('tag.value') }}
                    </legend>
                    <select v-if="enumValues(newKey)" v-model="newValue" class="select select-bordered">
                        <option v-for="opt in enumValues(newKey)" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                    <input v-else v-model="newValue" type="text" class="input" @keydown.enter="addTag" />
                </fieldset>
            </div>

            <div v-if="isCustomKey" class="grid grid-cols-2 gap-3 mb-3">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ trans('tag.custom_key') }}</legend>
                    <input
                        v-model="customKeyInput"
                        type="text"
                        class="input"
                        :class="customKeyError ? 'input-error' : ''"
                        :placeholder="trans('tag.custom_key.placeholder')"
                        @keydown.enter="addTag"
                    />
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">
                        {{ trans('tag.value') }}
                    </legend>
                    <input v-model="newValue" type="text" class="input" @keydown.enter="addTag" />
                </fieldset>
                <p v-if="customKeyError" class="text-error col-span-2">{{ trans('validation.tag.format') }}</p>
            </div>

            <div class="mb-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ trans('settings.visibility') }}</legend>
                    <div class="dropdown w-full">
                        <button tabindex="0" class="btn btn-md btn-outline gap-1 w-full justify-start">
                            <component :is="VISIBILITY_ICONS[newVisibility]" class="w-4 h-4" />
                            {{ trans('status.visibility.' + newVisibility) }}
                        </button>
                        <ul
                            tabindex="0"
                            class="dropdown-content menu bg-base-100 rounded-box z-10 w-full p-2 shadow-lg border border-base-200"
                        >
                            <li
                                v-for="v in ALL_VISIBILITIES"
                                :key="v"
                                @click="
                                    newVisibility = v as StatusVisibility;
                                    closeDropdown();
                                "
                            >
                                <a :class="newVisibility === v ? 'active' : ''">
                                    <component :is="VISIBILITY_ICONS[v]" class="w-4 h-4 shrink-0" />
                                    <span>
                                        {{ trans('status.visibility.' + v) }}
                                        <span class="block text-xs text-base-content/50">{{
                                            trans('status.visibility.' + v + '.detail')
                                        }}</span>
                                    </span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </fieldset>
            </div>

            <div class="modal-action">
                <button class="btn btn-ghost" @click="showAddModal = false">{{ trans('cancel') }}</button>
                <button
                    class="btn btn-primary"
                    :disabled="saving || !newValue || (isCustomKey && (!customKeyInput.trim() || customKeyError))"
                    @click="addTag"
                >
                    <span v-if="saving" class="loading loading-spinner loading-xs" />
                    {{ trans('save') }}
                </button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop" @submit.prevent="showAddModal = false">
            <button>close</button>
        </form>
    </dialog>

    <!-- Edit modal -->
    <dialog class="modal" :class="{ 'modal-open': showEditModal }">
        <div v-if="editTag" class="modal-box overflow-visible">
            <h3 class="font-bold text-lg mb-4">{{ getTitle(editTag.key) }}</h3>

            <div class="grid grid-cols-2 gap-3 mb-3">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ trans('tag.key') }}</legend>
                    <input type="text" class="input" :value="getTitle(editTag.key)" disabled />
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ trans('tag.value') }}</legend>
                    <select v-if="enumValues(editTag.key)" v-model="editValue" class="select select-bordered">
                        <option v-for="opt in enumValues(editTag.key)" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                    <input v-else v-model="editValue" type="text" class="input" @keydown.enter="saveEditTag" />
                </fieldset>
            </div>

            <div class="mb-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ trans('settings.visibility') }}</legend>
                    <div class="dropdown w-full">
                        <button tabindex="0" class="btn btn-sm btn-outline gap-1 w-full justify-start">
                            <component :is="VISIBILITY_ICONS[editVisibility]" class="w-4 h-4" />
                            {{ trans('status.visibility.' + editVisibility) }}
                        </button>
                        <ul
                            tabindex="0"
                            class="dropdown-content menu bg-base-100 rounded-box z-10 w-full p-2 shadow-lg border border-base-200"
                        >
                            <li
                                v-for="v in ALL_VISIBILITIES"
                                :key="v"
                                @click="
                                    editVisibility = v as StatusVisibility;
                                    closeDropdown();
                                "
                            >
                                <a :class="editVisibility === v ? 'active' : ''">
                                    <component :is="VISIBILITY_ICONS[v]" class="w-4 h-4 shrink-0" />
                                    <span>
                                        {{ trans('status.visibility.' + v) }}
                                        <span class="block text-xs text-base-content/50">{{
                                            trans('status.visibility.' + v + '.detail')
                                        }}</span>
                                    </span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </fieldset>
            </div>

            <div class="modal-action justify-between">
                <button
                    class="btn btn-ghost text-error"
                    :disabled="deletingKey === editTag.key"
                    @click="deleteTag(editTag)"
                >
                    <span v-if="deletingKey === editTag.key" class="loading loading-spinner loading-xs" />
                    <Trash2 v-else class="w-4 h-4" />
                    {{ trans('delete') }}
                </button>
                <div class="flex gap-2">
                    <button class="btn btn-ghost" @click="showEditModal = false">{{ trans('cancel') }}</button>
                    <button class="btn btn-primary" :disabled="editSaving || !editValue" @click="saveEditTag">
                        <span v-if="editSaving" class="loading loading-spinner loading-xs" />
                        {{ trans('save') }}
                    </button>
                </div>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop" @submit.prevent="showEditModal = false">
            <button>close</button>
        </form>
    </dialog>
</template>
