<script>
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
        internalLink() {
            if (this.link) {
                return this.link;
            }
            return "#";
        },
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
    <div class="row notification" :class="[warnType, { unread: !read }]">
        <a class="col-1 col-sm-1 align-left lead" :href="internalLink">
            <i :class="icon"></i>
        </a>
        <a class="col-7 col-sm-8 align-middle" :href="internalLink">
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

<style scoped lang="scss">
@import "../../sass/_variables.scss";

div {
    font-size: var(--mdb-body-font-size);
    line-height: var(--mdb-body-line-height);
}

a {
    color: $text-color;
}

.unread {
    &.warning {
        background-color: lighten($bahnrot, 40%);
    }

    &.neutral {
        background-color: lighten($blue, 40%);
    }
}

.col-1 i,
.interact {
    font-weight: 700;
    line-height: 1;
    color: $dark;
    text-shadow: 0 1px 0 #fff;
    padding: 0;
    background-color: transparent;
    border: 0;
    -webkit-appearance: none;
    -moz-appearance: none;
    font-size: 1.25rem;
}

p.lead {
    margin-bottom: 0.5rem;

    i {
        padding-right: 0.5rem;
    }
}

a ::v-deep(b) {
    font-weight: bold;
}

.dark {
    .fas,
    .far,
    .fa {
        filter: invert(1);
    }
    a {
        color: $dm-body;
    }
    .unread {
        &.warning {
            background-color: mix($dm-base, $bahnrot, 78%);
        }

        &.neutral {
            background-color: mix($dm-base, $blue, 78%);
        }
    }
}
</style>
