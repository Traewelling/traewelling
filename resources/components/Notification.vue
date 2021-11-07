<template>
    <div v-if="notification" :class="[severity, {'unread': !read}]" class="row" @click="clickMessage">
        <a class="col-1 col-sm-1 align-left lead" href="#">
            <i :class="icon" aria-hidden="true"></i>
        </a>
        <a class="col-7 col-sm-8 align-middle" href="#">
            <p class="lead"
               v-html="i18n.choice(i18nKey, 1, notification.detail.message.lead.values)"></p>
            <span v-if="notification.detail.message.notice.key"
                  v-html="i18n.choice(i18nNoticeKey, 1, notification.detail.message.notice.values)">
            </span>
        </a>
        <div class="col col-sm-3 text-end">
            <button :aria-label="readLabel" class="interact toggleReadState" type="button" @click.stop="readMessage">
                <i :class="{'fa-envelope-open': read, 'fa-envelope': !read}" aria-hidden="true" class="far"></i>
            </button>
            <div class="text-muted">{{ moment(notification.createdAt).format("LLL") }}</div>
        </div>
    </div>
</template>

<script>
import {profileNotifications, statusNotifications} from "../js/APImodels";
import Notifications from "../js/ApiClient/Notifications";

export default {
    name: "Notification",
    props: ["data"],
    inject: ["notyf"],
    data() {
        return {
            notification: null
        };
    },
    computed: {
        readLabel() {
            if (this.read) {
                return this.i18n.get("_.notifications.mark-as-unread");
            }
            return this.i18n.get("_.notifications.mark-as-read");
        },
        i18nKey() {
            return "_." + this.notification.detail.message.lead.key;
        },
        i18nNoticeKey() {
            return "_." + this.notification.detail.message.notice.key;
        },
        severity() {
            return this.notification.detail.message.severity;
        },
        read() {
            return this.notification.readAt;
        },
        icon() {
            return this.notification.detail.message.icon;
        }
    },
    mounted() {
        this.notification = this.$props.data;
    },
    watch: {
        data() {
            this.notification = this.$props.data;
        }
    },
    methods: {
        clickMessage() {
            if (!this.read) {
                this.readMessage();
            }
            this.goToSender();
        },
        readMessage() {
            Notifications.toggleRead(this.notification.id)
                .then((data) => {
                    this.notification = data;
                    if (this.read) {
                        this.$emit("decrease");
                    } else {
                        this.$emit("increase");
                    }
                })
                .catch((error) => {
                    this.apiErrorHandler(error);
                });
        },
        goToSender() {
            if (profileNotifications.indexOf(this.notification.type) >= 0) {
                this.$router.push({name: "profile", params: {username: this.notification.detail.sender.username}});
            }
            if (statusNotifications.indexOf(this.notification.type) >= 0) {
                this.$router.push({name: "singleStatus", params: {id: this.notification.detail.status.id}});
            }
            this.$emit("close");
        }
    }
};
</script>

<style scoped>
.unread.notice {
    background-color: #e2effa !important;
}

.unread.warning {
    background-color: #f2c9c5 !important;
}

</style>
