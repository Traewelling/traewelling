<template>
    <ModalConfirm ref="delete" :abort-text="i18n.get('_.settings.delete-account-btn-back')"
                  :confirm-text="i18n.get('_.settings.delete-account-btn-confirm')"
                  :title-text="i18n.get('_.settings.delete-account')"
                  confirm-button-color="btn-primary" v-on:confirm="deleteAccount">
        <span v-html="i18n.choice('_.settings.delete-account-verify', 1, {appname: $appName})"></span>
        <hr>
        <label v-html="i18n.choice('_.messages.account.please-confirm', 1, {delete: username})">
        </label>
        <input v-model="confirmDelete" :placeholder="username" class="form-control" name="confirmation"
               required
               type="text" @submit="$refs.delete.confirm()"/>
    </ModalConfirm>
</template>

<script>
import ModalConfirm from "./ModalConfirm";
import Settings from "../js/ApiClient/Settings";
export default {
    name: "DeleteAccountModal",
    components: {ModalConfirm},
    props: ["username"],
    data() {
        return {
            confirmDelete: null
        };
    },
    methods: {
        deleteAccount() {
            Settings.deleteAccount(this.confirmDelete)
                .then(() => {
                    this.confirmDelete = null;
                    this.$auth.logout();
                    this.notyf.success(this.i18n.get("_.settings.delete-account-completed"));
                })
                .catch((error) => {
                    this.confirmDelete = null;
                    this.apiErrorHandler(error);
                });
        },
        show() {
            this.$refs.delete.show();
        }
    }
};
</script>

<style scoped>

</style>
