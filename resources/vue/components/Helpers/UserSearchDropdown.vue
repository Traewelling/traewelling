<script lang="ts">
import {defineComponent} from 'vue'
import {trans} from "laravel-vue-i18n";
import {Api, User} from "../../../types/Api";
import _ from "lodash";

export default defineComponent({
    setup() {
        const api = new Api({baseUrl: window.location.origin + '/api/v1'});

        return {api}
    },
    emits: ["select-event"],
    name: "UserSearchDropdown",
    data() {
        return {
            users: [] as User[],
            search: "" as string,
        }
    },
    mounted() {
    },
    methods: {
        trans,
        fetchFriends() {
            this.api.user.searchUsers(this.search)
                .then((data) => {
                    if (!data.ok || data.status === 404) {
                        this.users = [];
                        return;
                    }
                    data.json().then((data) => {
                        this.users = data.data;
                    })
                })
                .catch(() => {
                    this.users = [];
                });
        },
        selectFriend(user: User) {
            this.$emit("select-event", user);
        },
    },
    watch: {
        search: _.debounce(function () {
            this.fetchFriends();
        }, 500),
    },
})
</script>

<template>
    <div class="col btn-group me-1">
        <button class="btn btn-sm btn-outline-primary dropdown-toggle"
                type="button"
                id="friendDropdown"
                data-mdb-dropdown-animation="off"
                data-mdb-toggle="dropdown"
                aria-expanded="false"
        >
            <i class="fas fa-users" aria-hidden="true"></i>
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
                    :placeholder="trans('settings.find-users')"
                >
            </form>
            <ul class="list-unstyled mb-0" v-if="users.length > 0">
                <li v-for="user in users" :key="user?.id">
                    <a href="#"
                       class="dropdown-item d-flex align-items-center gap-2 py-2"
                       @click="selectFriend(user)"
                    >
                        <div class="flex-grow-1">
                            <div class="fw-bold">{{ user?.displayName }}</div>
                            <div class="text-muted small">{{ user?.username }}</div>
                        </div>
                    </a>
                </li>
            </ul>
            <div v-else class="p-2 mb-0 text-center text-muted">
            </div>
        </div>
    </div>
</template>

<style scoped lang="scss">

</style>
