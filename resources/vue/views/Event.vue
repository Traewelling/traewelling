<!--
ToDo: Event.vue does currenly only load statuses. Put the whole Page in here later.
ToDo: Cursor based pagination (API changes needed)
-->
<script setup lang="ts">
import {computed, onMounted, ref} from "vue";
import StatusCard from "../components/Status/StatusCard.vue";
import type {StatusResource} from "../../types/Api.gen";
import {trans} from "laravel-vue-i18n";

const props = defineProps<{
  eventSlug: string;
}>();

const statuses = ref<StatusResource[]>([]);
const loading = ref(false);
const errorMsg = ref<string | null>(null);
const currentPage = ref(1);
const lastPage = ref<number | null>(null);

const canLoadMore = computed(() => {
  if (lastPage.value === null) return false;
  return currentPage.value < lastPage.value;
});

async function fetchStatuses(append = false) {
  loading.value = true;
  errorMsg.value = null;

  const nextPage = append ? currentPage.value + 1 : 1;
  const url = `/api/v1/event/${encodeURIComponent(props.eventSlug)}/statuses?page=${nextPage}`;

  try {
    const res = await fetch(url, {
      headers: {Accept: "application/json"},
      credentials: "same-origin",
    });

    if (!res.ok) {
      throw new Error(`HTTP ${res.status}`);
    }

    const json = await res.json();
    const list: StatusResource[] = json.data ?? [];

    if (append) statuses.value.push(...list);
    else statuses.value = list;

    const meta = json.meta ?? {};
    currentPage.value = meta.current_page ?? nextPage;
    lastPage.value = meta.last_page ?? null;
  } catch (e: any) {
    errorMsg.value = e?.message || "Unknown error occurred";
  } finally {
    loading.value = false;
  }
}

function loadMore() {
  if (!loading.value && canLoadMore.value) {
    fetchStatuses(true);
  }
}

onMounted(() => {
  fetchStatuses(false);
});
</script>

<template>
  <div class="container mt-3">
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-7">

        <div v-if="errorMsg" class="alert alert-danger my-3">
          {{ errorMsg }}
        </div>

        <template v-for="s in statuses" :key="s.id">
          <StatusCard :status="s"/>
        </template>

        <div v-if="loading" class="text-center my-4">
          <i class="fa-solid fa-spinner fa-spin"></i>
        </div>

        <div v-if="!loading && canLoadMore" class="text-center my-4">
          <button class="btn btn-primary" @click="loadMore">
            <i class="fa-solid fa-arrow-down"></i>
          </button>
        </div>

        <div v-if="!loading && !canLoadMore && statuses.length" class="text-center text-muted my-4">
          Final stop. All change, please!
        </div>
      </div>
    </div>

    <div class="row justify-content-center mt-5">
      <small class="text-muted">
        <sup>1</sup> {{ trans('events.disclaimer.organizer') }}
        <sup>2</sup> {{ trans('events.disclaimer.source') }}
        <sup>3</sup> {{ trans('events.disclaimer.warranty') }}
      </small>
    </div>
  </div>
</template>
