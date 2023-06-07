<script>
import {warn} from "vue";

export default {
    props: {
        "id": String,
        "type": String,
        "leadFormatted": String,
        "lead": String,
        "noticeFormatted": String,
        "notice": String,
        "link": String,
        "data": Object,
        "readAt": String,
        "createdAt": String,
        "createdAtForHumans": String
    },
    emits: ['toggle-read'],
    methods: {
        toggleUnread() {
            this.$emit('toggle-read')
        }
    },
    computed: {
        icon() {
            switch (this.type) {
                case 'EventSuggestionProcessed':
                    return 'fa-regular fa-calendar';
                case 'FollowRequestApproved':
                    return 'fas fa-user-plus';
                case 'FollowRequestIssued':
                    return 'fas fa-user-plus';
                case 'MastodonNotSent':
                    return 'fas fa-exclamation-triangle';
                case 'StatusLiked':
                    return 'fas fa-heart';
                case 'UserFollowed':
                    return 'fas fa-user-friends';
                case 'UserJoinedConnection':
                    return 'fa fa-train';
                default:
                    return 'far fa-envelope';
            }
        },
        warnType() {
            switch (this.type) {
                case 'MastodonNotSent':
                    return 'warning';
                default:
                    return 'neutral';
            }
        },
        read() {
            return this.readAt ?? false;
        }
    },
}
</script>

<template>
    <div class="row notification" :class="[warnType, read ? '' : 'unread']">
        <a class="col-1 col-sm-1 align-left lead" :href="link">
            <i :class="icon"></i>
        </a>
        <a class="col-7 col-sm-8 align-middle" :href="link">
            <p class="lead" v-html="leadFormatted"></p>
            <span v-html="noticeFormatted ?? ''"></span>
        </a>
        <div class="col col-sm-3 text-end">
            <button type="button" class="interact toggleReadState" @click="toggleUnread">
                <span aria-hidden="true">
                    <i class="far" :class="{'fa-envelope': !read, 'fa-envelope-open': read}"></i>
                </span>
            </button>
            <div class="text-muted">{{ createdAtForHumans }}</div>
        </div>
    </div>
</template>

<style scoped>

</style>
