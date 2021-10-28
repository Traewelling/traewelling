<template>
    <LayoutBasicNoSidebar footerclass="pt-5">
        <h1>{{ i18n.get("_.menu.settings") }}</h1>
        <ul class="nav nav-tabs mb-3" id="ex1" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="ex1-tab-1" data-mdb-toggle="tab" href="#ex1-tabs-1" role="tab"
                   aria-controls="ex1-tabs-1"
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
                class="tab-pane fade show active col-md-12 col-lg-8"
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
            <div id="ex1-tabs-2" aria-labelledby="ex1-tab-2" class="tab-pane fade  col-md-12 col-lg-8" role="tabpanel">
                <h2>{{ i18n.get("_.settings.heading.account") }}</h2>

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
            <div class="tab-pane fade" id="ex1-tabs-3" role="tabpanel" aria-labelledby="ex1-tab-3">
                <h2>{{ i18n.get("_.settings.tab.connectivity") }}</h2>

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

                <h6 class="text-capitalize text-muted border-bottom my-5">{{ i18n.get('_.settings.title-ics') }}</h6>
                <table class="table table-responsive">
                    <thead>
                        <tr>
                            <th colspan="2">{{ i18n.get("_.settings.token") }}</th>
                            <th>{{ i18n.get("settings.created") }}</th>
                            <th>{{ i18n.get("settings.last-accessed") }}</th>
                            <th></th>
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
                    <div class="input-group mt-0">
                        <input :placeholder="i18n.get('_.settings.ics.name-placeholder')" class="form-control"
                               name="name" required
                               type="text"/>
                        <button class="btn btn-sm btn-primary m-0 px-3" type="submit">
                            <i aria-hidden="true" class="fas fa-plus"></i>
                            {{ i18n.get("_.settings.create-ics-token") }}
                        </button>
                    </div>
                </form>
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
import ChangeLanguageButton from "../ChangeLanguageButton";

export default {
    name: "Settings",
    data() {
        return {
            visibility: visibility
        };
    },
    components: {ChangeLanguageButton, FADropdown, Card, LayoutBasicNoSidebar, LayoutBasic}
}
</script>

<style scoped>

</style>
