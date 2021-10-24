<template>
    <div :class="{severity, 'unread': !read}" class="row">
        <a class="col-1 col-sm-1 align-left lead" href="#">
            <i :class="icon" aria-hidden="true"></i>
        </a>
        <a class="col-7 col-sm-8 align-middle" href="#">
            <p class="lead"
               v-html="i18n.choice(i18nKey, 1, data.detail.message.lead.values)"></p>
            <span v-if="data.detail.message.notice"
                  v-html="i18n.choice(i18nNoticeKey, 1, data.detail.message.notice.values)">
            </span>
        </a>
        <div class="col col-sm-3 text-end">
            <button :aria-label="readLabel" class="interact toggleReadState" type="button">
                <i :class="{'fa-envelope-open': read, 'fa-envelope': !read}" aria-hidden="true" class="far"></i>
            </button>
            <div class="text-muted">{{ moment(data.createdAt).format("LLL") }}</div>
        </div>
    </div>
</template>

<script>
export default {
    name: "Notification",
    props: ["data"],
    computed: {
        readLabel() {
            if (this.read) {
                return this.i18n.get("_.notifications.mark-as-unread");
            }
            return this.i18n.get("_.notifications.mark-as-read");
        },
        i18nKey() {
            return "_." + this.$props.data.detail.message.lead.key;
        },
        i18nNoticeKey() {
            return "_." + this.$props.data.detail.message.notice.key;
        },
        severity() {
            return this.$props.data.detail.severity;
        },
        read() {
            return this.$props.data.readAt;
        },
        icon() {
            return this.$props.data.detail.message.icon;
        }
    },
    mounted() {
        console.log(this.$props.data.detail.message);
    }
}
</script>

<style scoped>

</style>
