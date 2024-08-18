<script lang="ts">
import {defineComponent} from 'vue'
import {trans} from "laravel-vue-i18n";
import {Api, TrustedUserResource} from "../../../types/Api";

export default defineComponent({
  setup() {
    const api = new Api({baseUrl: window.location.origin + '/api/v1'});

    return {api}
  },
  emits: ["select-user"],
  name: "FriendDropdown",
  data() {
    return {
      friends: [] as TrustedUserResource[],
      filteredFriends: [] as TrustedUserResource[],
      search: "" as string,
      selectedUsers: [] as TrustedUserResource[],
    }
  },
  mounted() {
    this.fetchFriends();
  },
  methods: {
    trans,
    fetchFriends() {
      this.api.user.trustedByUserIndex().then((data) => {
        data.json().then((data) => {
          this.friends = data.data;
          this.filterFriends();
        });
      });
    },
    filterFriends() {
      this.filteredFriends = this.friends.filter((user) => {
        return user.user?.displayName?.toLowerCase().includes(this.search.toLowerCase())
            || user.user?.username?.toLowerCase().includes(this.search.toLowerCase());
      });
    },
    selectFriend(friend: TrustedUserResource) {
      if (this.selectedUsers.some(user => user.user?.id === friend.user?.id)) {
        this.selectedUsers = this.selectedUsers.filter(user => user.user?.id !== friend.user?.id);
      } else {
        this.selectedUsers.push(friend);
      }

      this.$emit("select-user", this.selectedUsers);
    },
    isSelected(friend: TrustedUserResource) {
      return this.selectedUsers.some(user => user.user?.id === friend.user?.id);
    }
  },
  watch: {
    search() {
      this.filterFriends();
    }
  },
})
</script>

<template>
  <div class="col btn-group">
    <button class="btn btn-sm dropdown-toggle btn-link px-2"
            type="button"
            id="friendDropdown"
            data-mdb-dropdown-animation="off"
            data-mdb-toggle="dropdown"
            data-mdb-auto-close="outside"
            aria-expanded="false"
    >
      <i class="fas"
         aria-hidden="true"
         :class="{'fa-users': selectedUsers.length === 0, 'fa-user-check':selectedUsers.length > 0}"
      ></i>
    </button>
    <div aria-labelledby="friendDropdown"
         class="dropdown-menu pt-0 mx-0 rounded-3 shadow overflow-hidden"
    >
      <form class="p-2 mb-2 border-bottom">
        <input
            v-model="search"
            type="search"
            class="form-control mobile-input-fs-16"
            autocomplete="off"
            :placeholder="trans('stationboard.friend-filter')"
        >
      </form>
      <ul class="list-unstyled mb-0" v-if="filteredFriends.length > 0">
        <li v-for="user in filteredFriends" :key="user.user?.id">
          <a href="#"
             class="dropdown-item d-flex align-items-center gap-2 py-2"
             @click="selectFriend(user)"
             :class="{'active': isSelected(user)}"
          >
            <i class="fas" :class="{'fa-check': isSelected(user)}"></i>
            <div class="flex-grow-1">
              <div class="fw-bold">{{ user.user?.displayName }}</div>
              <div class="text-muted small">{{ user.user?.username }}</div>
            </div>
          </a>
        </li>
      </ul>
      <div v-else class="p-2 mb-0 text-center text-muted">
        <p>{{ trans("stationboard.friends-none") }}</p>
        <p>
          {{ trans("stationboard.friends-set") }}
          <a href="/settings/privacy" target="_blank">traewelling.de/settings/privacy</a>
        </p>
      </div>
    </div>
  </div>
</template>
