<template>
    <nav class="navbar fixed-bottom navbar-dark bg-trwl px-2 d-md-none">
        <router-link :to="{ name: 'dashboard' }" active-class="text-white" class="navbar-brand nav-link">
            <i class="fa fa-home" aria-hidden="true"></i>
            <span class="sr-only">{{ i18n.get('_.menu.dashboard') }}</span>
        </router-link>
        <router-link :to="{ name: 'leaderboard' }" class="navbar-brand nav-link">
            <i class="fa fa-crown" aria-hidden="true"></i>
            <span class="sr-only">{{ i18n.get('_.menu.leaderboard') }}</span>
        </router-link>
        <router-link :to="{ name: 'trains.stationboard' }" class="navbar-brand nav-link">
            <i aria-hidden="true" class="fas fa-ticket-alt"></i>
            <span class="sr-only">{{ i18n.get('_.menu.leaderboard') }}</span>
        </router-link>
        <router-link :to="{ name: 'statuses.active'}" class="navbar-brand nav-link">
            <i class="fas fa-map-marked-alt" aria-hidden="true"></i>
            <span class="sr-only">{{ i18n.get('_.menu.active') }}</span>
        </router-link>
        <router-link :to="{ name: 'statistics'}" class="navbar-brand nav-link">
            <i class="fas fa-chart-pie" aria-hidden="true"></i>
            <span class="sr-only">{{ i18n.get('_.stats') }}</span>
        </router-link>
    </nav>
</template>

<script>
export default {
    name: "MobileFooterNav"
}
</script>

<style scoped>

</style>
