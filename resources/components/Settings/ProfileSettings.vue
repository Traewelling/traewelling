<template>

    <div id="settingsTabs-profile" aria-labelledby="settingsTab-profile" class="tab-pane fade show active"
         role="tabpanel">
        <h2>{{ i18n.get("_.settings.heading.profile") }}</h2>

        <h6 class="text-capitalize text-muted border-bottom my-5">{{ i18n.get('_.settings.picture') }}</h6>
        <div class="row text-start">
            <div class="col-3">
                <img ref="profilepicture" :alt="i18n.get('_.settings.picture')"
                     :src="`/profile/${$auth.user().username}/profilepicture?${Date.now()}`"
                     class="rounded-circle w-100 d-block">
            </div>
            <div class="col-8 d-flex align-items-center">
                <button class="btn btn-primary me-1" @click="toggleShowUpload">
                    {{ i18n.get("_.settings.upload-image") }}
                </button>
                <button v-if="value.profile_picture_set" class="btn btn-outline-danger"
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
            <input id="username" v-model="value.username" class="form-control"
                   placeholder="@Gertrud" type="text" @change="profileSettingsChange">
            <label for="username">{{ i18n.get('_.user.username') }}</label>
        </div>
        <div class="form-floating mb-3">
            <input id="displayname" v-model="value.name" class="form-control"
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
                <FADropdown id="visibilityDropdown" v-model="value.default_status_visibility"
                            :dropdown-content="visibility"
                            :pre-select="value.default_status_visibility"
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
                <input id="privateProfileSwitch" v-model="value.private_profile"
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
                <input id="preventIndexSwitch" v-model="value.prevent_index"
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
                <input id="dblSwitch" v-model="value.always_dbl"
                       class="form-check-input float-end"
                       type="checkbox" @change="profileSettingsChange"/>
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
        <my-upload v-if="showUpload"
                   v-model="showUpload"
                   :height="300"
                   :langType="i18n.getLocale()"
                   :width="300"
                   field="img"
                   img-format="png"
                   @crop-success="cropSuccess"></my-upload>
    </div>
</template>

<script>
import ModalConfirm from "../ModalConfirm";
import FADropdown from "../FADropdown";
import myUpload from "vue-image-crop-upload";
import {visibility} from "../../js/APImodels";
import _debounce from "lodash/debounce";

export default {
    name: "ProfileSettings",
    inject: ["notyf"],
    props: ["value"],
    model: {
        prop: "value",
        event: "input"
    },
    components: {ModalConfirm, FADropdown, myUpload},
    data() {
        return {
            visibility: visibility,
            showUpload: false,
        };
    },
    methods: {
        cropSuccess(val) {
            axios
                .post("/settings/profilePicture", {image: val})
                .then(() => {
                    this.toggleShowUpload();
                    this.refreshProfilePicture();
                    this.notyf.success(this.i18n.get("_.settings.saved"));
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
        toggleShowUpload() {
            this.showUpload = !this.showUpload;
        },
        refreshProfilePicture() {
            this.$refs.profilepicture.src = "/profile/" + this.$auth.user().username + "/profilepicture?" + Date.now();
        },
        updateProfileSettings() {
            axios
                .put("/settings/profile", this.value)
                .then((response) => {
                    this.value = response.data.data;
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
                    this.refreshProfilePicture();
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
