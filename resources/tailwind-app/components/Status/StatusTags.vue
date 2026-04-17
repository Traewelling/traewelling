<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Eye, Globe, Lock, Plus, Trash2, UserCheck } from 'lucide-vue-next';
import { ref } from 'vue';
import { Api, StatusTagResource, StatusVisibility } from '../../../types/Api.gen';
import { getEnumValues, getTitle, keys } from '../../../vue/helpers/StatusTag';

const props = defineProps<{
    statusId: number;
    tags: StatusTagResource[];
    editable: boolean;
}>();

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const localTags = ref<StatusTagResource[]>([...props.tags]);

// add modal
const showAddModal = ref(false);
const newKey = ref<string>(keys[0]);
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

const availableKeys = () => keys.filter((k) => !localTags.value.some((t) => t.key === k));

function openAddModal() {
    const available = availableKeys();
    newKey.value = available[0] ?? keys[0];
    const vals = enumValues(newKey.value);
    newValue.value = vals ? vals[0].value : '';
    newVisibility.value = StatusVisibility.Value0;
    showAddModal.value = true;
}

function onKeyChange() {
    const vals = enumValues(newKey.value);
    newValue.value = vals ? vals[0].value : '';
}

function openEditModal(tag: StatusTagResource) {
    if (!props.editable) return;
    editTag.value = tag;
    editValue.value = tag.value;
    editVisibility.value = tag.visibility ?? StatusVisibility.Value0;
    showEditModal.value = true;
}

async function addTag() {
    if (!newValue.value) return;
    saving.value = true;
    try {
        const res = await api.status.createSingleStatusTag(
            { key: newKey.value, value: newValue.value, visibility: newVisibility.value },
            props.statusId,
        );
        localTags.value.push(res.data.data as StatusTagResource);
        showAddModal.value = false;
    } finally {
        saving.value = false;
    }
}

async function saveEditTag() {
    if (!editTag.value || !editValue.value) return;
    editSaving.value = true;
    try {
        const res = await api.status.updateSingleStatusTag(
            { value: editValue.value, visibility: editVisibility.value },
            props.statusId,
            editTag.value.key,
        );
        const updated = res.data.data as StatusTagResource;
        localTags.value = localTags.value.map((t) => (t.key === updated.key ? updated : t));
        showEditModal.value = false;
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
</script>

<template>
    <div v-if="localTags.length || editable" class="flex flex-wrap items-center gap-1.5">
        <button
            v-if="editable && availableKeys().length > 0"
            class="badge badge-outline gap-1 text-xs cursor-pointer hover:badge-primary transition-colors"
            @click="openAddModal"
        >
            <Plus class="w-3 h-3" />
            {{ trans('modals.tags.new') }}
        </button>

        <div v-for="tag in localTags" :key="tag.key" class="tooltip" :data-tip="getTitle(tag.key)">
            <span
                class="badge badge-outline gap-1 text-xs"
                :class="editable ? 'cursor-pointer hover:badge-primary transition-colors' : ''"
                @click="editable && openEditModal(tag)"
            >
                {{ displayValue(tag) }}
                <span v-if="deletingKey === tag.key" class="loading loading-spinner loading-xs" />
            </span>
        </div>
    </div>

    <dialog class="modal" :class="{ 'modal-open': showAddModal }">
        <div class="modal-box overflow-visible">
            <h3 class="font-bold text-lg mb-4">{{ trans('export.title.status_tags') }}</h3>

            <div class="grid grid-cols-2 gap-3 mb-3">
                <div class="form-control">
                    <label class="label"
                        ><span class="label-text">{{ trans('tag.key') }}</span></label
                    >
                    <select v-model="newKey" class="select select-bordered" @change="onKeyChange">
                        <option v-for="k in availableKeys()" :key="k" :value="k">{{ getTitle(k) }}</option>
                    </select>
                </div>

                <div class="form-control">
                    <label class="label"
                        ><span class="label-text">{{ trans('tag.value') }}</span></label
                    >
                    <select v-if="enumValues(newKey)" v-model="newValue" class="select select-bordered">
                        <option v-for="opt in enumValues(newKey)" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                    <input v-else v-model="newValue" type="text" class="input input-bordered" @keydown.enter="addTag" />
                </div>
            </div>

            <div class="mb-4">
                <label class="label"
                    ><span class="label-text">{{ trans('settings.visibility') }}</span></label
                >
                <div class="dropdown w-full">
                    <button tabindex="0" class="btn btn-sm btn-outline gap-1 w-full justify-start">
                        <Globe v-if="newVisibility === 0" class="w-4 h-4" />
                        <Eye v-else-if="newVisibility === 1" class="w-4 h-4" />
                        <UserCheck v-else-if="newVisibility === 2" class="w-4 h-4" />
                        <Lock v-else class="w-4 h-4" />
                        {{ trans('status.visibility.' + newVisibility) }}
                    </button>
                    <ul
                        tabindex="0"
                        class="dropdown-content menu bg-base-100 rounded-box z-10 w-full p-2 shadow-lg border border-base-200"
                    >
                        <li
                            v-for="v in [0, 1, 2, 3]"
                            :key="v"
                            @click="
                                newVisibility = v as StatusVisibility;
                                closeDropdown();
                            "
                        >
                            <a :class="newVisibility === v ? 'active' : ''">
                                <Globe v-if="v === 0" class="w-4 h-4 shrink-0" />
                                <Eye v-else-if="v === 1" class="w-4 h-4 shrink-0" />
                                <UserCheck v-else-if="v === 2" class="w-4 h-4 shrink-0" />
                                <Lock v-else class="w-4 h-4 shrink-0" />
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
            </div>

            <div class="modal-action">
                <button class="btn btn-ghost" @click="showAddModal = false">{{ trans('cancel') }}</button>
                <button class="btn btn-primary" :disabled="saving || !newValue" @click="addTag">
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
                <div class="form-control">
                    <label class="label"
                        ><span class="label-text">{{ trans('tag.key') }}</span></label
                    >
                    <input type="text" class="input input-bordered" :value="getTitle(editTag.key)" disabled />
                </div>

                <div class="form-control">
                    <label class="label"
                        ><span class="label-text">{{ trans('tag.value') }}</span></label
                    >
                    <select v-if="enumValues(editTag.key)" v-model="editValue" class="select select-bordered">
                        <option v-for="opt in enumValues(editTag.key)" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                    <input
                        v-else
                        v-model="editValue"
                        type="text"
                        class="input input-bordered"
                        @keydown.enter="saveEditTag"
                    />
                </div>
            </div>

            <div class="mb-4">
                <label class="label"
                    ><span class="label-text">{{ trans('settings.visibility') }}</span></label
                >
                <div class="dropdown w-full">
                    <button tabindex="0" class="btn btn-sm btn-outline gap-1 w-full justify-start">
                        <Globe v-if="editVisibility === 0" class="w-4 h-4" />
                        <Eye v-else-if="editVisibility === 1" class="w-4 h-4" />
                        <UserCheck v-else-if="editVisibility === 2" class="w-4 h-4" />
                        <Lock v-else class="w-4 h-4" />
                        {{ trans('status.visibility.' + editVisibility) }}
                    </button>
                    <ul
                        tabindex="0"
                        class="dropdown-content menu bg-base-100 rounded-box z-10 w-full p-2 shadow-lg border border-base-200"
                    >
                        <li
                            v-for="v in [0, 1, 2, 3]"
                            :key="v"
                            @click="
                                editVisibility = v as StatusVisibility;
                                closeDropdown();
                            "
                        >
                            <a :class="editVisibility === v ? 'active' : ''">
                                <Globe v-if="v === 0" class="w-4 h-4 shrink-0" />
                                <Eye v-else-if="v === 1" class="w-4 h-4 shrink-0" />
                                <UserCheck v-else-if="v === 2" class="w-4 h-4 shrink-0" />
                                <Lock v-else class="w-4 h-4 shrink-0" />
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
