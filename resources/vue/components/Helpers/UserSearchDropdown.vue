<script lang="ts">
import { trans } from 'laravel-vue-i18n';
import _ from 'lodash';
import { defineComponent } from 'vue';
import { Api, UserResource } from '../../../types/Api.gen';

export default defineComponent({
    name: 'UserSearchDropdown',
    emits: ['select-event'],
    setup() {
        const api = new Api({ baseUrl: window.location.origin + '/api' });

        return { api };
    },
    data() {
        return {
            users: [] as UserResource[],
            search: '' as string,
            showResults: false,
        };
    },
    watch: {
        search: _.debounce(function () {
            this.fetchFriends();
        }, 500),
    },
    mounted() {},
    methods: {
        trans,
        fetchFriends() {
            if (!this.search.trim()) {
                this.users = [];
                this.showResults = false;
                return;
            }
            this.api.user
                .searchUsers(this.search)
                .then((data) => {
                    if (!data.ok || data.status === 404) {
                        this.users = [];
                        this.showResults = false;
                        return;
                    }
                    data.json().then((data) => {
                        this.users = data.data;
                        this.showResults = this.users.length > 0;
                    });
                })
                .catch(() => {
                    this.users = [];
                    this.showResults = false;
                });
        },
        selectFriend(user: UserResource) {
            this.$emit('select-event', user);
            this.search = '';
            this.users = [];
            this.showResults = false;
        },
        handleBlur() {
            // Delay to allow click on result items to fire first
            setTimeout(() => {
                this.showResults = false;
            }, 200);
        },
    },
});
</script>

<template>
    <div class="position-relative">
        <input
            v-model="search"
            type="search"
            class="form-control mobile-input-fs-16"
            autocomplete="off"
            :placeholder="trans('settings.find-users')"
            @focus="showResults = users.length > 0"
            @blur="handleBlur"
        />
        <ul
            v-if="showResults && users.length > 0"
            class="dropdown-menu show w-100 shadow rounded-3 overflow-hidden mt-1"
        >
            <li v-for="user in users" :key="user?.id">
                <a
                    href="#"
                    class="dropdown-item d-flex align-items-center gap-2 py-2"
                    @click.prevent="selectFriend(user)"
                >
                    <div class="flex-grow-1">
                        <div class="fw-bold">{{ user?.displayName }}</div>
                        <div class="text-muted small">{{ user?.username }}</div>
                    </div>
                </a>
            </li>
        </ul>
    </div>
</template>

<style scoped lang="scss"></style>
