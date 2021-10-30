<template>
    <LayoutBasicNoSidebar footerclass="pt-5">
        <h1>{{ i18n.get("_.menu.settings") }}</h1>
        <ul id="settingsTabs" class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item" role="presentation">
                <a id="settingsTab-profile" aria-controls="settingsTabs-profile" class="nav-link active"
                   data-mdb-toggle="tab"
                   href="#settingsTabs-profile"
                   role="tab"
                   aria-selected="true">
                    {{ i18n.get("_.settings.tab.profile") }}
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a
                    class="nav-link"
                    id="settingsTab-account"
                    data-mdb-toggle="tab"
                    aria-controls="settingsTabs-account"
                    role="tab"
                    href="#settingsTabs-account"
                    aria-selected="false"
                >
                    {{ i18n.get("_.settings.tab.account") }}
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a
                    class="nav-link"
                    id="settingsTab-connectivity"
                    data-mdb-toggle="tab"
                    aria-controls="settingsTabs-connectivity"
                    role="tab"
                    href="#settingsTabs-connectivity"
                    aria-selected="false"
                >
                    {{ i18n.get("_.settings.tab.connectivity") }}
                </a>
            </li>
        </ul>
        <div id="settingsTabs-content" class="tab-content col-md-12 col-lg-8">
            <div id="settingsTabs-profile" aria-labelledby="settingsTab-profile" class="tab-pane fade show active"
                 role="tabpanel">
                <h2>{{ i18n.get("_.settings.heading.profile") }}</h2>
                <!-- ToDo -->
                <h6 class="text-capitalize text-muted border-bottom my-5">{{ i18n.get('_.settings.picture') }}</h6>
                <div class="row text-start">
                    <div class="col-3">
                        <img ref="profilepicture" :alt="i18n.get('_.settings.picture')"
                             :src="`/profile/${$auth.user().username}/profilepicture?${Date.now()}`"
                             class="rounded-circle w-100 d-block">
                    </div>
                    <div class="col-8 d-flex align-items-center">
                        <button class="btn btn-primary me-1">
                            {{ i18n.get("_.settings.upload-image") }}
                        </button>
                        <button v-if="userProfileSettings.profile_picture_set" class="btn btn-outline-danger"
                                @click="$refs.deleteModal.show()">
                            <i aria-hidden="true" class="fas fa-trash"></i>
                            <span class="sr-only">{{ i18n.get("_.settings.delete-profile-picture-btn") }}</span>
                        </button>
                    </div>
                </div>

                <h6 class="text-capitalize text-muted border-bottom my-5">{{
                        i18n.get('_.settings.title-profile')
                    }}</h6>
                <div class="form-floating mb-3">
                    <input id="username" v-model="userProfileSettings.username" class="form-control"
                           placeholder="@Gertrud" type="text" @change="profileSettingsChange">
                    <label for="username">{{ i18n.get('_.user.username') }}</label>
                </div>
                <div class="form-floating mb-3">
                    <input id="displayname" v-model="userProfileSettings.name" class="form-control"
                           placeholder="Gertrud" type="text" @change="profileSettingsChange">
                    <label for="displayname">{{ i18n.get("_.user.displayname") }}</label>
                </div>

                <h6 class="text-capitalize text-muted border-bottom my-5">{{
                        i18n.get('_.settings.title-privacy')
                    }}</h6>

                <div class="row">
                    <div class="col">
                        <label aria-label="visibilityDropdown" class="form-check-label">
                            {{ i18n.get('_.settings.visibility.default') }}
                        </label>
                    </div>
                    <div class="col">
                        <FADropdown id="visibilityDropdown" v-model="userProfileSettings.default_status_visibility"
                                    :dropdown-content="visibility"
                                    :pre-select="userProfileSettings.default_status_visibility"
                                    class="float-end" showText="true" @input="profileSettingsChange"></FADropdown>
                    </div>
                </div>
                <div class="row mt-3 pt-3">
                    <div class="col-9 col-md-11">
                        <label class="form-check-label" for="privateProfileSwitch">
                            {{ i18n.get('_.user.private-profile') }}<br>
                            <span class="small text-muted">{{ i18n.get("_.settings.visibility.disclaimer") }}</span>
                        </label>
                    </div>
                    <div class="col form-check form-switch">
                        <input id="privateProfileSwitch" v-model="userProfileSettings.private_profile"
                               class="form-check-input float-end"
                               type="checkbox" @change="profileSettingsChange"/>
                    </div>
                </div>
                <div class="row mt-3 pt-3">
                    <div class="col-9 col-md-11">
                        <label class="form-check-label" for="preventIndexSwitch">
                            {{ i18n.get('_.settings.prevent-indexing') }}<br>
                            <span class="text-muted small">
                                {{ i18n.get('_.settings.search-engines.description') }}
                            </span>
                        </label>
                    </div>
                    <div class="col form-check form-switch">
                        <input id="preventIndexSwitch" v-model="userProfileSettings.prevent_index"
                               class="form-check-input float-end"
                               type="checkbox" @change="profileSettingsChange"/>
                    </div>
                </div>
                <div class="row mt-3 pt-3">
                    <div class="col">
                        <label class="form-check-label" for="dblSwitch">
                            {{ i18n.get('_.user.always-dbl') }}
                        </label>
                    </div>
                    <div class="col form-check form-switch">
                        <input id="dblSwitch" v-model="userProfileSettings.always_dbl"
                               class="form-check-input float-end"
                               type="checkbox" @change="profileSettingsChange"/>
                    </div>
                </div>

            </div>
            <div id="settingsTabs-account" aria-labelledby="settingsTab-account" class="tab-pane fade" role="tabpanel">
                <h2>{{ i18n.get("_.settings.heading.account") }}</h2>
                <!-- ToDo -->
                <h6 class="text-capitalize text-muted border-bottom my-5">{{ i18n.get('_.settings.picture') }}</h6>
                <div class="row">
                    <div class="col">
                        {{ i18n.get("_.user.email") }}<br>
                        <span class="small text-muted">{{ i18n.get("_.user.email.not-set") }}</span>
                    </div>
                    <div class="col">
                        <button class="btn btn-outline-primary float-end">{{ i18n.get("_.settings.change") }}</button>
                        <button class="btn btn-outline-info float-end me-1">{{
                                i18n.get("_.user.email-verify")
                            }}
                        </button>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col">
                        {{ i18n.get("_.user.password") }}<br>
                        <span class="small text-muted">{{ i18n.get("_.passwords.password") }}</span>
                    </div>
                    <div class="col">
                        <button class="btn btn-outline-primary float-end">{{ i18n.get("_.settings.change") }}</button>
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

                <h6 class="text-capitalize text-muted border-bottom my-5">{{
                        i18n.get('_.settings.delete-account')
                    }}</h6>
                <div class="row">
                    <div class="col">

                        <button class="btn btn-outline-danger float-end">
                            <i aria-hidden="true" class="fa fa-trash"></i>
                            {{ i18n.get("_.settings.delete-account") }}
                        </button>
                    </div>
                </div>
            </div>
            <div id="settingsTabs-connectivity" aria-labelledby="settingsTab-connectivity" class="tab-pane fade"
                 role="tabpanel">
                <h2>{{ i18n.get("_.settings.tab.connectivity") }}</h2>
                <!-- ToDo -->
                <h6 class="text-capitalize text-muted border-bottom my-5">{{
                        i18n.get('_.settings.title-loginservices')
                    }}</h6>
                <div class="row">
                    <div class="col">
                        <i aria-hidden="true" class="fab fa-twitter"></i> Twitter<br>
                        <span class="small text-success">
                            <i aria-hidden="true" class="fa fa-check"></i>
                            {{ i18n.get("_.settings.connected") }}
                        </span>
                    </div>
                    <div class="col">
                        <button class="btn btn-outline-danger float-end">{{
                                i18n.get("_.settings.disconnect")
                            }}
                        </button>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col">
                        <i aria-hidden="true" class="fab fa-mastodon"></i> Mastodon<br>
                        <span class="small text-danger">
                            <i aria-hidden="true" class="fa fa-times"></i>
                            {{ i18n.get("_.settings.notconnected") }}
                        </span>
                    </div>
                    <div class="col">
                        <div class="input-group">
                            <input :placeholder="i18n.get('_.user.mastodon-instance-url')" class="form-control"
                                   type="text">
                            <button class="btn btn-primary float-end">{{ i18n.get("_.settings.connect") }}</button>
                        </div>
                    </div>
                </div>
                <!-- ToDo -->
                <h6 class="text-capitalize text-muted border-bottom my-5">{{ i18n.get('_.settings.title-ics') }}</h6>
                <table :aria-label="i18n.get('_.settings.title-ics')" class="table table-responsive">
                    <thead>
                        <tr>
                            <th colspan="2" scope="col">{{ i18n.get("_.settings.token") }}</th>
                            <th scope="col">{{ i18n.get("_.settings.created") }}</th>
                            <th scope="col">{{ i18n.get("_.settings.last-accessed") }}</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td> $name</td>
                            <td> $token<small>*****</small></td>
                            <td> DateTime</td>
                            <td> {{ i18n.get("_.settings.never") }}</td>
                            <td>
                                <button class="btn btn-sm btn-danger" type="submit">
                                    {{ i18n.get("_.settings.revoke-token") }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <form>
                    <div class="input-group mt-0 w-75">
                        <input :placeholder="i18n.get('_.settings.ics.name-placeholder')" class="form-control"
                               name="name" required
                               type="text"/>
                        <button class="btn btn-sm btn-primary m-0 px-3" type="submit">
                            <i aria-hidden="true" class="fas fa-plus"></i>
                            {{ i18n.get("_.settings.create-ics-token") }}
                        </button>
                    </div>
                </form>
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
            </div>
        </div>
        <ModalConfirm
            ref="deleteModal"
            :abort-text="i18n.get('_.settings.delete-profile-picture-no')"
            :body-text="i18n.get('_.settings.delete-profile-picture-desc')"
            :confirm-text="i18n.get('_.settings.delete-profile-picture-yes')"
            :title-text="i18n.get('_.settings.delete-profile-picture')"
            confirm-button-color="btn-danger"
            v-on:confirm="deleteProfilePicture"
        ></ModalConfirm>
    </LayoutBasicNoSidebar>
</template>

<script>
import LayoutBasic from "../layouts/Basic";
import LayoutBasicNoSidebar from "../layouts/BasicNoSidebar";
import Card from "../Card";
import {userProfileSettings, visibility} from "../../js/APImodels";
import FADropdown from "../FADropdown";
import ChangeLanguageButton from "../ChangeLanguageButton";
import _debounce from 'lodash/debounce'
import ModalConfirm from "../ModalConfirm";

export default {
    name: "Settings",
    inject: ["notyf"],
    data() {
        return {
            visibility: visibility,
            //Profile Settings
            userProfileSettings: userProfileSettings
        };
    },
    components: {ModalConfirm, ChangeLanguageButton, FADropdown, Card, LayoutBasicNoSidebar, LayoutBasic},
    watch: {
        userProfileSettings: function (val, oldVal) {
            console.log(val);
            console.log(oldVal);
        }
    },
    mounted() {
        this.fetchProfileSettings();
    },
    methods: {
        fetchProfileSettings() {
            axios
                .get("/settings/profile")
                .then((response) => {
                    this.userProfileSettings = response.data.data;
                })
                .catch((error) => {
                    this.loading = false;
                    if (error.response) {
                        this.notyf.error(error.response.data.message);
                    } else {
                        this.notyf.error(this.i18n.get("_.messages.exception.general"));
                    }
                });

        },
        updateProfileSettings() {
            axios
                .put("/settings/profile", this.userProfileSettings)
                .then((response) => {
                    this.userProfileSettings = response.data.data;
                    this.notyf.success(this.i18n.get("_.settings.saved"));
                    this.$auth.fetch();
                })
                .catch((error) => {
                    this.loading = false;
                    if (error.response) {
                        this.notyf.error(error.response.data.message);
                    } else {
                        this.notyf.error(this.i18n.get("_.messages.exception.general"));
                    }
                });
        },
        deleteProfilePicture() {
            axios
                .delete("/settings/profilePicture")
                .then(() => {
                    this.notyf.success(this.i18n.get("_.settings.saved"));
                    //ToDo implement twitter-like profilepicture links
                    this.$refs.profilepicture.src = "/profile/" + this.$auth.user().username + "/profilepicture?" + Date.now();
                })
                .catch((error) => {
                    this.loading = false;
                    if (error.response) {
                        this.notyf.error(error.response.data.message);
                    } else {
                        this.notyf.error(this.i18n.get("_.messages.exception.general"));
                    }
                });
        },
        profileSettingsChange: _debounce(function () {
            this.updateProfileSettings();
        }, 300),
    }
}
</script>

<style scoped>

</style>
