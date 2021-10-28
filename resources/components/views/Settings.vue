<template>
    <LayoutBasicNoSidebar>
        <h1>{{ i18n.get("_.menu.settings") }}</h1>
        <ul class="nav nav-tabs mb-3" id="ex1" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="ex1-tab-1" data-mdb-toggle="tab" href="#ex1-tabs-1" role="tab" aria-controls="ex1-tabs-1"
                    aria-selected="true">
                    {{ i18n.get("_.settings.tab.profile") }}
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a
                    class="nav-link"
                    id="ex1-tab-2"
                    data-mdb-toggle="tab"
                    href="#ex1-tabs-2"
                    role="tab"
                    aria-controls="ex1-tabs-2"
                    aria-selected="false"
                >
                    {{ i18n.get("_.settings.tab.account") }}
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a
                    class="nav-link"
                    id="ex1-tab-3"
                    data-mdb-toggle="tab"
                    href="#ex1-tabs-3"
                    role="tab"
                    aria-controls="ex1-tabs-3"
                    aria-selected="false"
                >
                    {{ i18n.get("_.settings.tab.connectivity") }}
                </a>
            </li>
        </ul>
        <div class="tab-content" id="ex1-content">
            <div
                class="tab-pane fade show active"
                id="ex1-tabs-1"
                role="tabpanel"
                aria-labelledby="ex1-tab-1"
            >
                <h2>{{ i18n.get("_.settings.heading.profile") }}</h2>

                <h6 class="text-capitalize text-muted border-bottom my-5">{{ i18n.get('_.settings.picture') }}</h6>
                <div class="row text-start">
                    <div class="col-3">
                        <img :alt="i18n.get('_.settings.picture')"
                             :src="`/profile/${$auth.user().username}/profilepicture`"
                             class="rounded-circle w-100 d-block">
                    </div>
                    <div class="col-8 d-flex align-items-center">
                        <button class="btn btn-primary me-1">
                            {{ i18n.get("_.settings.upload-image") }}
                        </button>
                        <button class="btn btn-outline-danger">
                            <i aria-hidden="true" class="fas fa-trash"></i>
                            <span class="sr-only">{{ i18n.get("_.settings.delete-profile-picture-btn") }}</span>
                        </button>
                    </div>
                </div>

                <h6 class="text-capitalize text-muted border-bottom my-5">{{
                        i18n.get('_.settings.title-profile')
                    }}</h6>
                <div class="form-floating mb-3">
                    <input id="username" :value="$auth.user().username" class="form-control" placeholder="@Gertrud"
                           type="text">
                    <label for="username">{{ i18n.get('_.user.username') }}</label>
                </div>
                <div class="form-floating mb-3">
                    <input id="displayname" :value="$auth.user().displayName" class="form-control" placeholder="Gertrud"
                           type="text">
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
                        <FADropdown id="visibilityDropdown" :dropdown-content="visibility" :pre-select="0"
                                    class="float-end" showText="true"></FADropdown>
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
                        <input id="privateProfileSwitch" class="form-check-input float-end" type="checkbox"/>
                    </div>
                </div>
                <div class="row mt-3 pt-3">
                    <div class="col-9 col-md-11">
                        <label class="form-check-label" for="preventIndexSwitch">
                            {{ i18n.get('_.settings.hide-search-engines') }}<br>
                            <span class="text-muted small">{{
                                    i18n.get('_.settings.search-engines.description')
                                }}</span>
                        </label>
                    </div>
                    <div class="col form-check form-switch">
                        <input id="preventIndexSwitch" class="form-check-input float-end" type="checkbox"/>
                    </div>
                </div>
                <div class="row mt-3 pt-3">
                    <div class="col">
                        <label class="form-check-label" for="dblSwitch">
                            {{ i18n.get('_.user.always-dbl') }}
                        </label>
                    </div>
                    <div class="col form-check form-switch">
                        <input id="dblSwitch" class="form-check-input float-end" type="checkbox"/>
                    </div>
                </div>

            </div>
            <div class="tab-pane fade" id="ex1-tabs-2" role="tabpanel" aria-labelledby="ex1-tab-2">
                Tab 2 content
            </div>
            <div class="tab-pane fade" id="ex1-tabs-3" role="tabpanel" aria-labelledby="ex1-tab-3">
                Tab 3 content
            </div>
        </div>
    </LayoutBasicNoSidebar>
</template>

<script>
import LayoutBasic from "../layouts/Basic";
import LayoutBasicNoSidebar from "../layouts/BasicNoSidebar";
import Card from "../Card";
import {visibility} from "../../js/APImodels";
import FADropdown from "../FADropdown";

export default {
    name: "Settings",
    data() {
        return {
            visibility: visibility
        };
    },
    components: {FADropdown, Card, LayoutBasicNoSidebar, LayoutBasic}
}
</script>

<style scoped>

</style>
