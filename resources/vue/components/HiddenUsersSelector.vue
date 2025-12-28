<script setup lang="ts">
import {ref, onMounted, onUnmounted, PropType} from "vue";
import {trans} from "laravel-vue-i18n";
import {Api, UserResource} from "../../types/Api.gen";
import {useUserStore} from "../stores/user";

const userStore = useUserStore();

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
const sectionRef = ref<HTMLElement | null>(null);

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
    // Filter out already hidden users and the current user
    const hiddenIds = hiddenUsers.value.map(u => u.id);
    const currentUserId = userStore.user?.id;
    searchResults.value = (data.data || []).filter((u: UserResource) => 
      !hiddenIds.includes(u.id) && u.id !== currentUserId
    );
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

function toggleExpanded(event: MouseEvent) {
  event.stopPropagation();
  
  // Close all other Bootstrap dropdowns
  const openDropdowns = document.querySelectorAll('.dropdown-menu.show');
  openDropdowns.forEach(dropdown => {
    dropdown.classList.remove('show');
  });
  
  expanded.value = !expanded.value;
  if (expanded.value && hiddenUsers.value.length === 0) {
    fetchHiddenUsers();
  }
}

// Close dropdown when clicking outside
function handleClickOutside(event: MouseEvent) {
  const target = event.target as HTMLElement;
  if (sectionRef.value && !sectionRef.value.contains(target) && expanded.value) {
    expanded.value = false;
  }
}

// Debounce search
let searchTimeout: ReturnType<typeof setTimeout> | null = null;
function onSearchInput() {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(searchUsers, 300);
}

onMounted(() => {
  // Fetch hidden users on mount to show the badge count immediately
  fetchHiddenUsers();
  
  // Add event listener after a small delay to prevent immediate closing
  setTimeout(() => {
    document.addEventListener('click', handleClickOutside, false);
  }, 0);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside, false);
});
</script>

<template>
  <div ref="sectionRef" class="hidden-users-section col btn-group">
    <button
        type="button"
        class="btn btn-outline-primary dropdown-toggle"
        @click="toggleExpanded"
        :class="{ 'active': hiddenUsers.length > 0 }"
        style="padding-left: 0.57rem; padding-right: 0.57rem;"
    >
      <i class="fas fa-eye-slash"></i>
      <span v-if="hiddenUsers.length > 0" class="badge bg-secondary ms-1 position-absolute top-0 start-100 translate-middle" style="font-size: 0.6rem;">{{ hiddenUsers.length }}</span>
    </button>

    <div v-if="expanded" class="dropdown-menu show mt-2 p-2 position-absolute start-0"
         style="z-index: 1000; min-width: 350px; max-width: 400px;">
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
            <span class="text-body">{{ user.displayName }} (@{{ user.username }})</span>
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
        <i class="fa fa-spinner fa-spin text-body"></i>
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
            <span class="text-body">{{ user.displayName }} (@{{ user.username }})</span>
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
  position: relative;
}

.hidden-users-section button {
  position: relative;
}

.dropdown-menu {
  max-height: 400px;
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
