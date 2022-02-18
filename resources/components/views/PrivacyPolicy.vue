<template>
    <LayoutBasicNoSidebar>
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                <div v-if="$route.query.validFrom && !$route.query.acceptedAt" class="card mb-3">
                    <p class="card-body mb-0" v-html="i18n.get('_.privacy.not-signed-yet')">
                    </p>
                </div>
                <div v-else-if="$route.query.acceptedAt" class="card mb-3">
                    <p class="card-body mb-0" v-html="i18n.get('_.privacy.we-changed')">
                    </p>
                </div>

                <form v-if="$route.query.validFrom" class="fixed-bottom text-end"
                     style="background-color: hsl(216, 25%, 95.1%);" @submit.prevent="acceptPrivacyPolicy">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-md-8 col-lg-7 my-2">
                                <a class="btn btn-link pr-0" role="button" @click.prevent="$refs.delete.show()">
                                    {{ i18n.get('_.settings.delete-account') }}
                                </a>
                                <LoadingButton :disabled="loadingSubmit" class="btn btn-success"
                                               type="submit">
                                    {{ i18n.get('_.privacy.sign') }}
                                </LoadingButton>
                            </div>
                        </div>
                    </div>
                </form>

                <spinner v-if="loading"></spinner>
                <div class="privacy" v-html="policy">
                </div>
            </div>
        </div>
        <DeleteAccountModal ref="delete" :username="$auth.user().username"></DeleteAccountModal>
    </LayoutBasicNoSidebar>
</template>

<script>
import LayoutBasic from "../layouts/Basic";
import LayoutBasicNoSidebar from "../layouts/BasicNoSidebar";
import PrivacyPolicy from "../../js/ApiClient/PrivacyPolicy";
import DeleteAccountModal from "../DeleteAccountModal";
import Spinner from "../Spinner";
import LoadingButton from "../LoadingButton";
import {marked} from "marked";

export default {
    name: "PrivacyPolicy",
    components: {LoadingButton, Spinner, DeleteAccountModal, LayoutBasicNoSidebar, LayoutBasic},
    metaInfo() {
        return {
            title: this.i18n.get("_.privacy.title"),
            meta: [
                {name: "robots", content: "noindex", vmid: "robots"}
            ]
        };
    },
    data() {
        return {
            policy: null,
            loading: true,
            loadingSubmit: false,
        };
    },
    created() {
        this.fetchData();
    },
    methods: {
        fetchData() {
            PrivacyPolicy
                .getPolicy()
                .then((data) => {
                    if (this.i18n.getLocale().startsWith("de")) {
                        this.policy = marked(data.de);
                    } else {
                        this.policy = marked(data.en);
                    }
                    this.loading = false;
                });
        },
        acceptPrivacyPolicy() {
            this.loadingSubmit = true;
            PrivacyPolicy
                .acceptPolicy()
                .then(() => {
                    this.$router.back();
                    this.loadingSubmit = false;
                })
                .catch((error) => {
                    this.apiErrorHandler(error);
                    this.loadingSubmit = false;
                })
        }
    }
};
</script>

<style scoped>

</style>
