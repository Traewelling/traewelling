<script setup lang="ts">
import {ref, onMounted, PropType} from "vue";
import {trans} from "laravel-vue-i18n";
import {Api, UserResource} from "../../types/Api.gen";

const props = defineProps({
  statusId: {
    type: Number,
    required: true
  }
});

const emit = defineEmits<{
  (e: "updated"): void
}>();

const api = new Api({baseUrl: window.location.origin + '/api/v1'});
const hiddenUsers = ref<UserResource[]>([]);
const searchQuery = ref('');
const searchResults = ref<UserResource[]>([]);
const loading = ref(false);
const searching = ref(false);
const expanded = ref(false);

async function fetchHiddenUsers() {
  loading.value = true;
  try {
    const response = await fetch(`/api/v1/status/${props.statusId}/hidden-users`, {
      credentials: 'same-origin',
      headers: {'Accept': 'application/json'}
    });
    const data = await response.json();
    hiddenUsers.value = data.data || [];
  } catch (error) {
    console.error('Error fetching hidden users:', error);
  } finally {
    loading.value = false;
  }
}

async function searchUsers() {
  if (searchQuery.value.length < 2) {
    searchResults.value = [];
    return;
  }

  searching.value = true;
  try {
    const response = await fetch(`/api/v1/user/search/${encodeURIComponent(searchQuery.value)}`, {
      credentials: 'same-origin',
      headers: {'Accept': 'application/json'}
    });
    const data = await response.json();
    // Filter out already hidden users
    const hiddenIds = hiddenUsers.value.map(u => u.id);
    searchResults.value = (data.data || []).filter((u: UserResource) => !hiddenIds.includes(u.id));
  } catch (error) {
    console.error('Error searching users:', error);
    searchResults.value = [];
  } finally {
    searching.value = false;
  }
}

async function addHiddenUser(user: UserResource) {
  try {
    const response = await fetch(`/api/v1/status/${props.statusId}/hidden-users`, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({userId: user.id})
    });

    if (response.ok) {
      hiddenUsers.value.push(user);
      searchResults.value = searchResults.value.filter(u => u.id !== user.id);
      searchQuery.value = '';
      emit('updated');
    }
  } catch (error) {
    console.error('Error adding hidden user:', error);
  }
}

async function removeHiddenUser(user: UserResource) {
  try {
    const response = await fetch(`/api/v1/status/${props.statusId}/hidden-users/${user.id}`, {
      method: 'DELETE',
      credentials: 'same-origin',
      headers: {'Accept': 'application/json'}
    });

    if (response.ok) {
      hiddenUsers.value = hiddenUsers.value.filter(u => u.id !== user.id);
      emit('updated');
    }
  } catch (error) {
    console.error('Error removing hidden user:', error);
  }
}

function toggleExpanded() {
  expanded.value = !expanded.value;
  if (expanded.value && hiddenUsers.value.length === 0) {
    fetchHiddenUsers();
  }
}

// Debounce search
let searchTimeout: ReturnType<typeof setTimeout> | null = null;
function onSearchInput() {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(searchUsers, 300);
}

onMounted(() => {
  // Optionally fetch on mount
});
</script>

<template>
  <div class="hidden-users-section">
    <button
        type="button"
        class="btn btn-outline-secondary btn-sm w-100 d-flex justify-content-between align-items-center"
        @click="toggleExpanded"
    >
      <span>
        <i class="fa fa-user-slash me-2"></i>
        {{ trans('status.hidden-users') }}
        <span v-if="hiddenUsers.length > 0" class="badge bg-secondary ms-1">{{ hiddenUsers.length }}</span>
      </span>
      <i :class="expanded ? 'fa fa-chevron-up' : 'fa fa-chevron-down'"></i>
    </button>

    <div v-if="expanded" class="hidden-users-content mt-2 p-2 border rounded">
      <small class="text-muted d-block mb-2">
        {{ trans('status.hidden-users.description') }}
      </small>

      <!-- Search Input -->
      <div class="input-group input-group-sm mb-2">
        <input
            type="text"
            class="form-control"
            v-model="searchQuery"
            @input="onSearchInput"
            :placeholder="trans('status.hidden-users.search')"
        />
        <span class="input-group-text">
          <i v-if="searching" class="fa fa-spinner fa-spin"></i>
          <i v-else class="fa fa-search"></i>
        </span>
      </div>

      <!-- Search Results -->
      <div v-if="searchResults.length > 0" class="search-results mb-2">
        <div
            v-for="user in searchResults"
            :key="user.id"
            class="d-flex align-items-center justify-content-between py-1 px-2 border-bottom search-result-item"
        >
          <div class="d-flex align-items-center">
            <img
                v-if="user.profilePicture"
                :src="user.profilePicture"
                class="rounded-circle me-2"
                width="24"
                height="24"
                alt=""
            />
            <span>{{ user.displayName }} (@{{ user.username }})</span>
          </div>
          <button
              type="button"
              class="btn btn-sm btn-outline-danger"
              @click="addHiddenUser(user)"
              :title="trans('status.hidden-users.add')"
          >
            <i class="fa fa-user-slash"></i>
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-2">
        <i class="fa fa-spinner fa-spin"></i>
      </div>

      <!-- Hidden Users List -->
      <div v-else-if="hiddenUsers.length > 0" class="hidden-users-list">
        <div
            v-for="user in hiddenUsers"
            :key="user.id"
            class="d-flex align-items-center justify-content-between py-1 px-2 border-bottom"
        >
          <div class="d-flex align-items-center">
            <img
                v-if="user.profilePicture"
                :src="user.profilePicture"
                class="rounded-circle me-2"
                width="24"
                height="24"
                alt=""
            />
            <span>{{ user.displayName }} (@{{ user.username }})</span>
          </div>
          <button
              type="button"
              class="btn btn-sm btn-outline-secondary"
              @click="removeHiddenUser(user)"
              :title="trans('status.hidden-users.remove')"
          >
            <i class="fa fa-times"></i>
          </button>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="!loading && searchQuery.length < 2" class="text-muted text-center py-2">
        <small>{{ trans('status.hidden-users.empty') }}</small>
      </div>
    </div>
  </div>
</template>

<style scoped>
.hidden-users-section {
  margin-top: 0.5rem;
}

.hidden-users-content {
  background-color: var(--bs-body-bg);
  max-height: 300px;
  overflow-y: auto;
}

.search-result-item:hover {
  background-color: var(--bs-tertiary-bg);
}

.hidden-users-list > div:last-child {
  border-bottom: none !important;
}

.search-results > div:last-child {
  border-bottom: none !important;
}
</style>
