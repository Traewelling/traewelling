<template>
    <LayoutBasicNoSidebar>
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                <div v-if="userProfileSettings && userProfileSettings.email" class="card mb-4">
                    <div class="card-header">{{ i18n.get('_.support.create') }}</div>
                    <div class="card-body">
                        <form @submit.prevent="submitForm">
                            <div class="form-outline mb-4">
                                <input id="form-subject" v-model="form.subject" class="form-control" type="text"/>
                                <label class="form-label" for="form-subject">{{ i18n.get('_.subject') }}</label>
                            </div>

                            <div class="form-outline mb-4">
                                <textarea id="form-message" v-model="form.message" class="form-control"
                                          rows="4"></textarea>
                                <label class="form-label" for="form-message">{{ i18n.get('_.how-can-we-help') }}</label>
                            </div>

                            <button class="btn btn-primary btn-block mb-4" type="submit">
                                {{ i18n.get('_.support.submit') }}
                            </button>
                            <hr/>
                            <small>
                                {{ i18n.choice('_.support.answer', 1, {'address': userProfileSettings.email}) }}
                            </small>
                        </form>
                    </div>
                </div>

                <div class="alert alert-info" v-if="userProfileSettings && userProfileSettings.email">
                    <h5 class="fw-bold"><i class="fas fa-user-shield"></i> {{ i18n.get('_.support.privacy') }}</h5>
                    {{ i18n.get('_.support.privacy.description') }}
                    {{ i18n.get('_.support.privacy.description2') }}
                </div>
                <spinner v-else-if="loading"></spinner>

                <div v-else>
                    <h4>{{ i18n.get('support.create') }}</h4>
                    <hr/>
                    <div class="alert alert-danger">
                        <p>{{ i18n.get('support.email-required') }}</p>
                        <router-link class="btn btn-sm btn-primary" :to="{name: 'settings'}">
                            <i class="fas fa-user-cog"></i>
                            {{ i18n.get('go-to-settings') }}
                        </router-link>
                    </div>
                </div>
            </div>
        </div>
    </LayoutBasicNoSidebar>
</template>

<script>
import LayoutBasicNoSidebar from "../layouts/BasicNoSidebar";
import Spinner from "../Spinner";
import moment from "moment";
import Support from "../../js/ApiClient/Support";
import Settings from "../../js/ApiClient/Settings";

export default {
    name: "Support",
    components: {Spinner, LayoutBasicNoSidebar, moment},
    inject: ["notyf"],
    metaInfo() {
        return {
            title: this.i18n.get("_.support.create"),
            meta: [
                {name: "robots", content: "index", vmid: "robots"}
            ]
        };
    },
    data() {
        return {
            userProfileSettings: null,
            loading: true,
            links: null,
            formLoading: false,
            form: {}
        };
    },
    created() {
        this.fetchData();
    },
    methods: {
        fetchData() {
            Settings.getProfileSettings()
                .then((data) => {
                    this.userProfileSettings = data;
                    this.loading = false;
                })
                .catch((error) => {
                    this.apiErrorHandler(error);
                });
        },
        submitForm() {
            this.suggestLoading = true;
            const formData      = {};
            Object.assign(formData, this.form);
            Support
                .createTicket(formData)
                .then((data) => {
                    this.suggestLoading = false;
                    this.notyf.success(this.i18n.choice("_.support.success", 1, {'ticketNumber': data.ticket}));
                    this.form = {};
                })
                .catch((error) => {
                    this.suggestLoading = false;
                    this.apiErrorHandler(error);
                });
        }
    }
};
</script>
