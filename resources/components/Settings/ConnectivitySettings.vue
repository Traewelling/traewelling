<template>
    <div id="settingsTabs-connectivity" aria-labelledby="settingsTab-connectivity" class="tab-pane fade"
         role="tabpanel">
        <h2>{{ i18n.get("_.settings.tab.connectivity") }}</h2>
        <!-- ToDo -->
        <h6 class="text-capitalize text-muted border-bottom my-5">
            {{ i18n.get('_.settings.title-loginservices') }}</h6>
        <div class="row">
            <div class="col">
                <i aria-hidden="true" class="fab fa-twitter"></i> Twitter<br>
                <span v-if="value.twitter" class="small text-success">
                    <i aria-hidden="true" class="fa fa-check"></i>
                    {{ i18n.get("_.settings.connected") }}
                </span>
                <span v-else class="small text-danger">
                    <i aria-hidden="true" class="fa fa-times"></i>
                    {{ i18n.get("_.settings.notconnected") }}
                </span>
            </div>
            <div class="col">
                <button v-if="value.twitter" class="btn btn-outline-danger float-end" @click="notimplemented">
                    {{ i18n.get("_.settings.disconnect") }}
                </button>
                <button v-else class="btn btn-primary float-end" @click="notimplemented">
                    {{ i18n.get("_.settings.connect") }}
                </button>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col">
                <i aria-hidden="true" class="fab fa-mastodon"></i> Mastodon<br>
                <span v-if="value.mastodon" class="small text-success">
                    <i aria-hidden="true" class="fa fa-check"></i>
                    {{ i18n.get("_.settings.connected") }}
                </span>
                <span v-else class="small text-danger">
                    <i aria-hidden="true" class="fa fa-times"></i>
                    {{ i18n.get("_.settings.notconnected") }}
                </span>
            </div>
            <div class="col">
                <button v-if="value.mastodon" class="btn btn-outline-danger float-end" @click="notimplemented">
                    {{ i18n.get("_.settings.disconnect") }}
                </button>
                <div v-else class="input-group">
                    <input :placeholder="i18n.get('_.user.mastodon-instance-url')" class="form-control"
                           type="text">
                    <button class="btn btn-primary float-end" @click="notimplemented">
                        {{ i18n.get("_.settings.connect") }}
                    </button>
                </div>
            </div>
        </div>

        <h6 class="text-capitalize text-muted border-bottom my-5">{{ i18n.get('_.settings.title-ics') }}</h6>
        <div class="row">
            <div class="col">
                {{ i18n.get("_.settings.ics.descriptor") }}
            </div>
            <div class="col-4 col-md-3">
                <button class="btn btn-primary float-end" @click="fetchIcsTokens">
                    {{ i18n.get("_.menu.show-all") }}
                </button>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col">
                <button class="btn btn-sm btn-primary float-end" @click="$refs.newIcs.show()">
                    <i aria-hidden="true" class="fas fa-plus"></i>
                    {{ i18n.get("_.settings.create-ics-token") }}
                </button>
            </div>
        </div>

        <h6 class="text-capitalize text-muted border-bottom my-5">{{
                i18n.get("_.settings.title-sessions")
            }}</h6>
        <div class="row">
            <div class="col">
                {{ i18n.get("_.settings.title-sessions") }}
            </div>
            <div class="col">
                <button class="btn btn-primary float-end" @click="fetchSessions">
                    {{ i18n.get("_.menu.show-all") }}
                </button>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col">
                {{ i18n.get("_.settings.title-tokens") }}
            </div>
            <div class="col">
                <button class="btn btn-primary float-end" @click="fetchTokens">
                    {{ i18n.get("_.menu.show-all") }}
                </button>
            </div>
        </div>

        <ModalConfirm ref="newIcs"
                      :abort-text="i18n.get('_.menu.abort')"
                      :confirm-text="i18n.get('_.modals.edit-confirm')"
                      :title-text="i18n.get('_.settings.create-ics-token')"
                      confirm-button-color="btn-primary"
                      v-on:confirm="createIcsToken">
            <div class="form-floating mb-3">
                <input id="newIcsName" v-model="newIcsName" :placeholder="i18n.get('_.settings.ics.name-placeholder')"
                       class="form-control"
                       required type="text">
                <label for="newIcsName">{{ i18n.get("_.settings.ics.name-placeholder") }}</label>
            </div>
        </ModalConfirm>
        <ModalConfirm
            ref="allIcs"
            :confirm-text="i18n.get('_.menu.close')"
            :large="true"
            :title-text="i18n.get('_.settings.ics.modal')"
            body-class="p-0"
            confirm-button-color="btn-primary">
            <Spinner v-if="icsLoading"></Spinner>
            <p v-else-if="icsTokens.length === 0" class="text-danger text-center pt-3">
                {{ i18n.get("_.settings.no-ics-tokens") }}
            </p>
            <div v-else class="table-responsive p-0">
                <table :aria-label="i18n.get('_.settings.title-ics')" class="table">
                    <thead>
                        <tr>
                            <th colspan="2" scope="col">{{ i18n.get("_.settings.token") }}</th>
                            <th scope="col">{{ i18n.get("_.settings.created") }}</th>
                            <th scope="col">{{ i18n.get("_.settings.last-accessed") }}</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="token in icsTokens" v-bind:key="icsTokens.id">
                            <td> {{ token.name }}</td>
                            <td> {{ token.token }}<small>*****</small></td>
                            <td> {{ moment(token.created).format("lll") }}</td>
                            <td v-if="token.lastAccessed"> {{ moment(token.lastAccessed).format("lll") }}</td>
                            <td v-else> {{ i18n.get("_.settings.never") }}</td>
                            <td>
                                <button class="btn btn-sm btn-danger" type="submit" @click="deleteIcsToken(token)">
                                    <i aria-hidden="true" class="fas fa-trash"></i>
                                    <span class="sr-only">{{ i18n.get("_.settings.revoke-token") }}</span>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </ModalConfirm>
        <ModalConfirm
            ref="sessions"
            :abort-text="i18n.get('_.menu.abort')"
            :confirm-text="i18n.get('_.settings.deleteallsessions')"
            :large="true"
            :title-text="i18n.get('_.settings.title-sessions')"
            body-class="p-0"
            confirm-button-color="btn-danger"
            v-on:confirm="deleteSessions">
            <Spinner v-if="sessionLoading"></Spinner>
            <p v-else-if="sessions.length === 0" class="text-danger text-center pt-3">
                {{ i18n.get("_.settings.no-tokens") }}
            </p>
            <div v-else class="table-responsive p-0">
                <table :aria-label="i18n.get('_.settings.title-sessions')" class="table">
                    <thead>
                        <tr>
                            <th scope="col">{{ i18n.get("_.settings.device") }}</th>
                            <th scope="col">{{ i18n.get("_.settings.platform") }}</th>
                            <th scope="col">{{ i18n.get("_.settings.ip") }}</th>
                            <th scope="col">{{ i18n.get("_.settings.lastactivity") }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="session in sessions" v-bind:key="session.id">
                            <td><i :class="`fas fa-${session.deviceIcon}`" aria-hidden="true"></i></td>
                            <td> {{ session.platform }}</td>
                            <td> {{ session.ip }}</td>
                            <td v-if="session.lastActivity"> {{ moment(session.lastActivity).format("lll") }}</td>
                            <td v-else> {{ i18n.get("_.settings.never") }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </ModalConfirm>
        <ModalConfirm
            ref="tokens"
            :abort-text="i18n.get('_.menu.abort')"
            :confirm-text="i18n.get('_.settings.delete-all-tokens')"
            :large="true"
            :title-text="i18n.get('_.settings.title-tokens')"
            body-class="p-0"
            confirm-button-color="btn-danger"
            v-on:confirm="revokeTokens">
            <Spinner v-if="tokensLoading"></Spinner>
            <p v-else-if="tokens.length === 0" class="text-danger text-center pt-3">
                {{ i18n.get("_.settings.no-tokens") }}
            </p>
            <div v-else class="table-responsive p-0">
                <table :aria-label="i18n.get('_.settings.title-sessions')" class="table">
                    <thead>
                        <tr>
                            <th scope="col">{{ i18n.get("_.settings.client-name") }}</th>
                            <th scope="col">{{ i18n.get("_.settings.created") }}</th>
                            <th scope="col">{{ i18n.get("_.settings.expires") }}</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="token in tokens" v-bind:key="token.id">
                            <td>{{ token.client }}</td>
                            <td>{{ moment(token.createdAt).format("lll") }}</td>
                            <td>{{ moment(token.expiresAt).fromNow() }}</td>
                            <td>
                                <button class="btn btn-danger" @click="revokeToken(token)">
                                    <i aria-hidden="true" class="fas fa-trash"></i>
                                    <span class="sr-only">{{ i18n.get("_.settings.revoke-token") }}</span>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </ModalConfirm>
    </div>
</template>

<script>

import ModalConfirm from "../ModalConfirm";
import Spinner from "../Spinner";
import Settings from "../../js/ApiClient/Settings";

export default {
    name: "ConnectivitySettings",
    components: {Spinner, ModalConfirm},
    props: ["value"],
    inject: ["notyf"],
    model: {prop: "value", event: "input"},
    data() {
        return {
            icsLoading: false,
            icsTokens: [],
            newIcsName: null,
            sessionLoading: false,
            sessions: [],
            tokensLoading: false,
            tokens: []
        };
    },
    methods: {
        notimplemented() {
            this.notyf.error("Not yet implemented");
        },
        createIcsToken() {
            Settings.createIcsToken(this.newIcsName)
                .then((data) => {
                    this.notyf.success({
                        message: this.i18n.choice("_.settings.create-ics-token-success", 1, {link: data}),
                        duration: 10000
                    });
                })
                .catch((error) => {
                    this.icsLoading = false;
                    this.apiErrorHandler(error);
                });
        },
        fetchIcsTokens() {
            this.icsLoading = true;
            this.$refs.allIcs.show();
            Settings.fetchIcsTokens()
                .then((data) => {
                    this.icsLoading = false;
                    this.icsTokens  = data;
                })
                .catch((error) => {
                    this.icsLoading = false;
                    this.apiErrorHandler(error);
                });
        },
        deleteIcsToken(token) {
            Settings.deleteIcsToken(token.id)
                .then(() => {
                    const index = this.icsTokens.indexOf(token);
                    if (index > -1) {
                        this.icsTokens.splice(index, 1);
                    }
                    this.notyf.success(this.i18n.get("_.settings.revoke-ics-token-success"));
                })
                .catch((error) => {
                    this.apiErrorHandler(error);
                });
        },
        fetchSessions() {
            this.sessionLoading = true;
            this.$refs.sessions.show();
            Settings.fetchSessions()
                .then((data) => {
                    this.sessionLoading = false;
                    this.sessions       = data;
                })
                .catch((error) => {
                    this.sessionLoading = false;
                    this.apiErrorHandler(error);
                });
        },
        deleteSessions() {
            Settings.deleteSessions()
                .then(() => {
                    this.sessions = [];
                    this.notyf.success(this.i18n.get("_.settings.saved"));
                })
                .catch((error) => {
                    this.catchError(error);
                });
        },
        revokeToken(token) {
            Settings.revokeApiToken(token.id)
                .then(() => {
                    const index = this.tokens.indexOf(token);
                    if (index > -1) {
                        this.tokens.splice(index, 1);
                    }
                    this.notyf.success(this.i18n.get("_.settings.revoke-token.success"));
                })
                .catch((error) => {
                    this.apiErrorHandler(error);
                });
        },
        fetchTokens() {
            this.tokensLoading = true;
            this.$refs.tokens.show();
            Settings.fetchApiTokens()
                .then((data) => {
                    this.tokensLoading = false;
                    this.tokens        = data;
                })
                .catch((error) => {
                    this.tokensLoading = false;
                    this.apiErrorHandler(error);
                });
        },
        revokeTokens() {
            Settings.revokeAllApiTokens()
                .then(() => {
                    this.sessions = [];
                    this.$auth.logout();
                    this.notyf.success(this.i18n.get("_.settings.revoke-token.success"));
                })
                .catch((error) => {
                    this.apiErrorHandler(error);
                });
        }
    }
};
</script>

<style scoped>

</style>
