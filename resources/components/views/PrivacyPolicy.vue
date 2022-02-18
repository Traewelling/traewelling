<template>
    <LayoutBasicNoSidebar>
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                <div class="card mb-3" v-if="$route.query.validFrom && !$route.query.acceptedAt">
                    <p class="card-body mb-0" v-html="i18n.get('_.privacy.not-signed-yet')">
                    </p>
                </div>
                <div class="card mb-3" v-else-if="$route.query.acceptedAt">
                    <p class="card-body mb-0" v-html="i18n.get('_.privacy.we-changed')">
                    </p>
                </div>

                <form v-if="$route.query.validFrom" class="fixed-bottom text-end" style="background-color: hsl(216, 25%, 95.1%);">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-md-8 col-lg-7 my-2">
                                <a class="btn btn-link pr-0" data-mdb-target="#deleteUserModal" data-mdb-toggle="modal"
                                   href="javascript:void(0)" role="button">
                                    {{ i18n.get('_.settings.delete-account') }}
                                </a>
                                <input class="btn btn-success" type="submit" :value="i18n.get('_.privacy.sign')"/>
                            </div>
                        </div>
                    </div>
                </form>

                @include('settings.modals.deleteUserModal')
                @endauth

                <div class="privacy" v-html="policy">
                </div>
            </div>
        </div>
    </LayoutBasicNoSidebar>
</template>

<script>
import LayoutBasic from "../layouts/Basic";
import LayoutBasicNoSidebar from "../layouts/BasicNoSidebar";
import PrivacyPolicy from "../../js/ApiClient/PrivacyPolicy";

export default {
    name: "PrivacyPolicy",
    components: {LayoutBasicNoSidebar, LayoutBasic},
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
        };
    },
    created() {
        this.fetchData();
        console.log(this.intercepted)
    },
    methods: {
        fetchData() {
            PrivacyPolicy
                .getPolicy()
                .then((data) => {
                    if (this.i18n.getLocale().startsWith("DE")) {
                        this.policy = data.de;
                    } else {
                        this.policy = data.en;
                    }
                })
        }
    }
};
</script>

<style scoped>

</style>
