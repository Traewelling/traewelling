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
        <!-- ToDo -->
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
        <!-- ToDo -->
        <h6 class="text-capitalize text-muted border-bottom my-5">{{
                i18n.get("_.settings.title-sessions")
            }}</h6>
        <table aria-label="i18n.get('_.settings.title-sessions')" class="table table-responsive">
            <thead>
                <tr>
                    <th scope="col">{{ i18n.get("_.settings.client-name") }}</th>
                    <th scope="col">{{ i18n.get("_.settings.created") }}</th>
                    <th scope="col">{{ i18n.get("_.settings.expires") }}</th>
                    <th scope="col"></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>$token->client->name</td>
                    <td>(i18n.get("_.datetime-format"))</td>
                    <td>$token->expires_at->diffForHumans()</td>
                    <td>
                        <form>
                            <input name="tokenId" type="hidden" value="$token->id"/>
                            <button class="btn btn-block btn-danger mx-0">
                                <i aria-hidden="true" class="fas fa-trash"></i>
                                <span class="sr-only">{{ i18n.get("_.modals.delete-confirm") }}</span>
                            </button>
                        </form>
                    </td>
                </tr>
            </tbody>
        </table>


        <ModalConfirm ref="newIcs"
                      :abort-text="i18n.get('_.menu.abort')"
                      :confirm-text="i18n.get('_.modals.edit-confirm')"
                      :title-text="i18n.get('_.settings.create-ics-token')"
                      confirm-button-color="btn-primary">
            <input :placeholder="i18n.get('_.settings.ics.name-placeholder')" class="form-control"
                   name="name" required
                   type="text"/>
        </ModalConfirm>
        <ModalConfirm ref="allIcs"
                      :confirm-text="i18n.get('_.menu.close')"
                      :large="true"
                      :title-text="i18n.get('_.settings.ics.modal')"
                      body-class="p-0"
                      confirm-button-color="btn-primary">
            <Spinner v-if="icsLoading"></Spinner>
            <p v-else-if="icsTokens == []" class="text-danger">{{ i18n.get("_.settings.no-ics-tokens") }}</p>
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
    </div>
</template>

<script>

import ModalConfirm from "../ModalConfirm";
import Spinner from "../Spinner";

export default {
    name: "ConnectivitySettings",
    components: {Spinner, ModalConfirm},
    props: ["value"],
    inject: ["notyf"],
    model: {prop: "value", event: "input"},
    data() {
        return {
            icsLoading: false,
            icsTokens: null,
            newIcsName: null
        }
    },
    methods: {
        notimplemented() {
            this.notyf.error("Not yet implemented");
        },
        fetchIcsTokens() {
            this.icsLoading = true;
            this.$refs.allIcs.show()
            axios
                .get('/settings/ics-tokens')
                .then((response) => {
                    this.icsLoading = false;
                    this.icsTokens  = response.data.data;
                })
                .catch((error) => {
                    this.icsLoading = false;
                    this.catchError(error);
                });
        },
        deleteIcsToken(token) {
            axios
                .delete('/settings/ics-token', {data: {id: token.id}})
                .then(() => {
                    const index = this.icsTokens.indexOf(token);
                    if (index > -1) {
                        this.icsTokens.splice(index, 1);
                    }
                    this.notyf.success(this.i18n.get("_.settings.revoke-ics-token-success"));
                })
                .catch((error) => {
                    this.catchError(error);
                });
        },
        catchError(error) {
            if (error.response) {
                if (error.response.data.errors) {
                    Object.entries(error.response.data.errors).forEach((err) => {
                        this.notyf.error(err[1][0]);
                    });
                } else {
                    this.notyf.error(error.response.data.message);
                }
            } else {
                this.notyf.error(this.i18n.get("_.messages.exception.general"));
            }
        }
    }
};
</script>

<style scoped>

</style>
