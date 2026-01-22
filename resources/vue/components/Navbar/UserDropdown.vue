<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { useUserStore } from '../../stores/user';

const user = useUserStore();
user.fetchSettings(false, true);

function logout() {
    // get csrf token from meta tag
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!token) {
        console.error('CSRF token not found');
        return;
    }

    user.invalidateUser();
    fetch('/logout', {
        method: 'POST',
        headers: {
            'Content-Type': 'multipart/form-data',
            'X-CSRF-TOKEN': token,
        },
        body: JSON.stringify({}),
    })
        .then(() => {
            window.location.href = '/';
        })
        .catch((error) => {
            console.error('Error logging out:', error);
        });
}
</script>

<template>
    <li class="nav-item dropdown">
        <button
            id="navbarDropdown"
            class="nav-link dropdown-toggle select"
            data-bs-dropdown-animation="off"
            data-bs-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false"
        >
            {{ user.getDisplayName }}
            <span class="caret" />
        </button>

        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
            <li>
                <a class="dropdown-item" :href="`/@${user.getUsername}`">
                    <i class="fas fa-user" /> {{ trans('menu.profile') }}
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="/export"> <i class="fas fa-save" /> {{ trans('menu.export') }} </a>
            </li>
            <li>
                <a class="dropdown-item" href="/settings"> <i class="fas fa-cog" /> {{ trans('menu.settings') }} </a>
            </li>
            <li>
                <a class="dropdown-item" href="https://help.traewelling.de/faq/" target="_blank">
                    <i class="fa-solid fa-bug" aria-hidden="true" />
                    {{ trans('help') }}
                </a>
            </li>
            <li v-if="user.isAdmin || user.isEventModerator">
                <a class="dropdown-item" href="/admin"> <i class="fas fa-tools" /> Backend </a>
            </li>
            <li>
                <hr class="dropdown-divider" />
            </li>

            <button type="submit" class="dropdown-item" @click="logout">
                <i class="fas fa-sign-out-alt" /> {{ trans('menu.logout') }}
            </button>
        </ul>
    </li>
</template>
