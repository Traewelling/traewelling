<script setup lang="ts">
import { Building2 } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { ref } from 'vue';
import { Api } from '../../../types/Api.gen';

type Operator = { uuid: string; name: string };

const props = defineProps<{ modelValue: Operator | null }>();
const emit = defineEmits<{ 'update:modelValue': [op: Operator | null] }>();

const api = new Api({ baseUrl: window.location.origin + '/api' });

const query = ref(props.modelValue?.name ?? '');
const results = ref<Operator[]>([]);
const loading = ref(false);
const dropdownVisible = ref(false);
let debounce: ReturnType<typeof setTimeout> | null = null;

function onInput(): void {
    if (debounce) clearTimeout(debounce);
    debounce = setTimeout(search, 300);
}

async function search(): Promise<void> {
    const q = query.value.trim();
    if (q.length < 2) {
        results.value = [];
        dropdownVisible.value = false;
        return;
    }
    loading.value = true;
    try {
        const result = await api.operators.getOperators({ query: q });
        results.value = (result.data?.data ?? []) as Operator[];
        dropdownVisible.value = true;
    } catch {
        // ignore
    } finally {
        loading.value = false;
    }
}

function select(op: Operator): void {
    query.value = op.name;
    results.value = [];
    dropdownVisible.value = false;
    emit('update:modelValue', op);
}

function clear(): void {
    query.value = '';
    results.value = [];
    dropdownVisible.value = false;
    emit('update:modelValue', null);
}

function hideDropdown(): void {
    setTimeout(() => {
        dropdownVisible.value = false;
    }, 150);
}
</script>

<template>
    <fieldset class="fieldset p-0">
        <legend class="fieldset-legend">
            <Building2 class="w-3.5 h-3.5 inline mr-1" />
            {{ trans('export.title.operator') }}
        </legend>
        <div class="relative">
            <div class="flex gap-2">
                <input
                    v-model="query"
                    type="text"
                    class="input input-bordered input-sm flex-1"
                    :placeholder="trans('trip_creation.form.operator_search')"
                    @input="onInput"
                    @blur="hideDropdown"
                    @focus="query.length >= 2 && (dropdownVisible = true)"
                />
                <button v-if="modelValue" class="btn btn-sm btn-ghost" type="button" @click="clear">✕</button>
                <span v-else-if="loading" class="loading loading-spinner loading-xs self-center" />
            </div>
            <ul
                v-if="dropdownVisible && results.length > 0"
                class="absolute z-50 mt-1 w-full bg-base-100 border border-base-300 rounded-box shadow-lg max-h-48 overflow-y-auto"
            >
                <li v-for="op in results" :key="op.uuid">
                    <button
                        class="w-full text-left px-4 py-2 hover:bg-base-200 text-sm"
                        @mousedown.prevent="select(op)"
                    >
                        {{ op.name }}
                    </button>
                </li>
            </ul>
        </div>
    </fieldset>
</template>
