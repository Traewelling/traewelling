<template>
    <div id="settingsTabs-account" aria-labelledby="settingsTab-account" class="tab-pane fade" role="tabpanel">
        <h2>{{ i18n.get("_.settings.heading.account") }}</h2>

        <h6 class="text-capitalize text-muted border-bottom my-5">{{ i18n.get('_.settings.tab.account') }}</h6>
        <div class="row">
            <div class="col">
                {{ i18n.get("_.user.email") }}<br>
                <span v-if="value.email" class="small text-muted">
                    {{ value.email }}
                </span>
                <span v-else class="small text-muted">{{ i18n.get("_.user.email.not-set") }}</span>
            </div>
            <div class="col">
                <button class="btn btn-outline-primary float-end" @click="$refs.mail.show()">
                    {{ i18n.get("_.generic.change") }}
                </button>
                <button v-if="value.email && !value.email_verified" class="btn btn-outline-info float-end me-1"
                        @click="resendMail">
                    {{ i18n.get("_.controller.status.email-resend-mail") }}
                </button>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col">
                {{ i18n.get("_.user.password") }}<br>
                <span class="small text-muted">{{ i18n.get("_.passwords.password") }}</span>
            </div>
            <div class="col-3">
                <button class="btn btn-outline-primary float-end" @click="$refs.password.show()">
                    {{ i18n.get("_.generic.change") }}
                </button>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col">
                {{ i18n.get("_.settings.language.set") }}
            </div>
            <div class="col">
                <ChangeLanguageButton class="float-end"></ChangeLanguageButton>
            </div>
        </div>

        <h6 class="text-capitalize text-danger border-bottom my-5">
            {{ i18n.get('_.settings.delete-account') }}
        </h6>
        <div class="row">
            <div class="col">
                {{ i18n.get("_.settings.delete-account.detail") }}
            </div>
            <div class="col-4 col-md-3">
                <button class="btn btn-outline-danger float-end" @click="$refs.delete.show()">
                    <i aria-hidden="true" class="fa fa-trash"></i>
                    {{ i18n.get("_.settings.delete-account") }}
                </button>
            </div>
        </div>
        <ModalConfirm ref="mail" :abort-text="i18n.get('_.menu.abort')" :body-text="i18n.get('_.email.change')"
                      :confirm-text="i18n.get('_.modals.edit-confirm')" :title-text="i18n.get('_.user.email.change')"
                      confirm-button-color="btn-primary" v-on:confirm="updateMail">
            <div class="form-floating mb-3">
                <input id="mail" v-model="email" class="form-control" placeholder="mail@example.com"
                       required type="email">
                <label for="mail">{{ i18n.get("_.user.email.new") }}</label>
            </div>
            <div class="form-floating mb-3">
                <input id="password" v-model="password" class="form-control" placeholder=""
                       required type="password">
                <label for="password">{{ i18n.get("_.settings.current-password") }}</label>
            </div>
        </ModalConfirm>
        <ModalConfirm ref="password" :abort-text="i18n.get('_.menu.abort')"
                      :confirm-text="i18n.get('_.modals.edit-confirm')"
                      :title-text="i18n.get('_.settings.title-change-password')"
                      confirm-button-color="btn-primary" v-on:confirm="updatePassword">
            <form>
                <div v-if="value.password" class="form-floating mb-3">
                    <input id="currentPassword" v-model="password" autocomplete="password" class="form-control"
                           placeholder="" required type="password">
                    <label for="currentPassword">{{ i18n.get("_.settings.current-password") }}</label>
                </div>
                <div class="form-floating mb-3">
                    <input id="newPassword" v-model="newPassword" autocomplete="new-password" class="form-control"
                           placeholder="" required type="password">
                    <label for="newPassword">{{ i18n.get("_.settings.new-password") }}</label>
                </div>
                <div class="form-floating mb-3">
                    <input id="newPassword_confirm" v-model="newPasswordConfirm" autocomplete="new-password"
                           class="form-control"
                           placeholder="" required type="password">
                    <label for="newPassword_confirm">{{ i18n.get("_.settings.confirm-password") }}</label>
                </div>
            </form>
        </ModalConfirm>
        <ModalConfirm ref="delete" :abort-text="i18n.get('_.settings.delete-account-btn-back')"
                      :confirm-text="i18n.get('_.settings.delete-account-btn-confirm')"
                      :title-text="i18n.get('_.settings.delete-account')"
                      confirm-button-color="btn-primary" v-on:confirm="deleteAccount">
            <span v-html="i18n.choice('_.settings.delete-account-verify', 1, {appname: $appName})"></span>
            <hr>
            <label v-html="i18n.choice('_.messages.account.please-confirm', 1, {delete: value.username})">
            </label>
            <input v-model="confirmDelete" :placeholder="value.username" class="form-control" name="confirmation"
                   required
                   type="text" @submit="$refs.delete.confirm()"/>
        </ModalConfirm>
    </div>
</template>

<script>
import ChangeLanguageButton from "../ChangeLanguageButton";
import ModalConfirm from "../ModalConfirm";
import Settings from "../../js/ApiClient/Settings";

export default {
    name: "AccountSettings",
    components: {ModalConfirm, ChangeLanguageButton},
    props: ["value"],
    inject: ["notyf"],
    model: {prop: "value", event: "input"},
    data() {
        return {
            password: null,
            newPassword: null,
            newPasswordConfirm: null,
            email: null,
            setValue: null,
            confirmDelete: null
        };
    },
    mounted() {
        this.setValue = this.$props.value;
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
        updatePassword() {
            Settings.updatePassword(this.password, this.newPassword, this.newPasswordConfirm)
                .then((data) => {
                    this.setValue = data.data;
                    this.$emit("input", this.setValue);
                    this.password = this.newPassword = this.newPasswordConfirm = null;
                    this.notyf.success(this.i18n.get("_.controller.user.password-changed-ok"));
                })
                .catch((error) => {
                    this.password = this.email = null;
                    this.apiErrorHandler(error);
                });
        },
        resendMail() {
            Settings.resendMail()
                .then(() => {
                    this.notyf.success(this.i18n.get("_.email.verification.sent"));
                })
                .catch((error) => {
                    this.password = this.email = null;
                    this.apiErrorHandler(error);
                });
        },
        updateMail() {
            Settings.updateMail(this.email, this.password)
                .then((data) => {
                    this.setValue = data.data;
                    this.$emit("input", this.setValue);
                    this.password = null;
                    this.email    = null;
                    this.notyf.success(this.i18n.get("_.email.verification.sent"));
                })
                .catch((error) => {
                    this.password = this.email = null;
                    this.apiErrorHandler(error);
                });
        }
    }
};
</script>

<style scoped>

</style>
