<template>
    <LayoutBasicNoSidebar>
        <div class="row g-2">
            <div class="col-lg-6 col-sm-12">
                <Card :title="i18n.get('_.settings.picture')">
                    <div class="row text-center">
                        <img :alt="i18n.get('_.settings.picture')"
                             :src="`/profile/${$auth.user().username}/profilepicture`"
                             class="rounded-circle mx-auto w-25 d-block">
                    </div>
                    <div class="row mt-4">
                        <div class="col text-end">
                            <button class="btn btn-outline-danger float-start">
                                <i aria-hidden="true" class="fas fa-trash"></i>
                                <span class="sr-only">{{ i18n.get("_.settings.delete-profile-picture-btn") }}</span>
                            </button>
                            <button class="btn btn-primary">
                                {{ i18n.get("_.settings.upload-image") }}
                            </button>
                        </div>
                    </div>
                </Card>
            </div>
            <div class="col-lg-6 col-sm-12">
                <Card :title="i18n.get('_.settings.title-profile')">
                    <div class="row">
                        <div class="col-4">
                            <label for="username">
                                {{ i18n.get('_.user.username') }}
                            </label>
                        </div>
                        <div class="col-6">
                            <div class="input-group">
                                <span class="input-group-text">@</span>
                                <input id="username" :value="$auth.user().username"
                                       autofocus
                                       class="form-control" required type="text"/>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-4">
                            <label for="displayname">
                                {{ i18n.get("_.user.displayname") }}
                            </label>
                        </div>
                        <div class="col-6">
                            <input id="displayname" :value="$auth.user().displayName" class="form-control" type="text">
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-4">
                            <label for="mail">
                                {{ i18n.get("_.user.email") }}
                            </label>
                        </div>
                        <div class="col-6">
                            <input id="mail" class="form-control" type="text" value="">
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col text-end">
                            <button class="btn btn-primary">{{ i18n.get("_.settings.btn-update") }}</button>
                        </div>
                    </div>
                </Card>
            </div>
            <div class="col-lg-6 col-sm-12">
                <Card :title="i18n.get('_.settings.title-privacy')">
                    <div class="row">
                        <div class="col">
                            <label class="form-check-label" for="privateProfileSwitch">
                                {{ i18n.get('_.user.private-profile') }}
                            </label>
                        </div>
                        <div class="col form-check form-switch">
                            <input id="privateProfileSwitch" class="form-check-input float-end" type="checkbox"/>
                        </div>
                    </div>
                    <div class="row border-top mt-3 pt-3">
                        <div class="col">
                            <label class="form-check-label" for="preventIndexSwitch">
                                {{ i18n.get('_.settings.hide-search-engines') }}
                                <i :title="i18n.get('_.settings.search-engines.description')" aria-hidden="true"
                                   class="fas fa-info-circle" data-mdb-toggle="tooltip"></i>
                            </label>
                        </div>
                        <div class="col form-check form-switch">
                            <input id="preventIndexSwitch" class="form-check-input float-end" type="checkbox"/>
                        </div>
                    </div>
                    <div class="row border-top mt-3 pt-3">
                        <div class="col">
                            <label class="form-check-label" for="dblSwitch">
                                {{ i18n.get('_.user.always-dbl') }}
                            </label>
                        </div>
                        <div class="col form-check form-switch">
                            <input id="dblSwitch" class="form-check-input float-end" type="checkbox"/>
                        </div>
                    </div>
                    <div class="row border-top mt-3 pt-3">
                        <div class="col">
                            <label class="form-check-label" for="privateProfileSwitch">
                                {{ i18n.get('_.settings.visibility.default') }}
                            </label>
                        </div>
                        <div class="col">
                            <FADropdown :dropdown-content="visibility" :pre-select="0" class="float-end"
                                        showText="true"></FADropdown>
                        </div>
                    </div>
                </Card>
            </div>
            <div class="col-lg-6 col-sm-12">
                <Card :title="i18n.get('_.settings.title-password')">
                    <form>
                        <input autocomplete="username" name="username" type="hidden">

                        <div class="row">
                            <label class="col-md-4 col-form-label text-md-right" for="currentPassword">
                                {{ i18n.get("_.settings.current-password") }}
                            </label>
                            <div class="col-md-6">
                                <input id="currentPassword" autocomplete="current-password"
                                       class="form-control"
                                       type="password"/>
                            </div>
                        </div>

                        <div class="row">
                            <label class="col-md-4 col-form-label text-md-right" for="password">
                                {{ i18n.get("_.settings.new-password") }}
                            </label>

                            <div class="col-md-6">
                                <input id="password" autocomplete="new-password"
                                       class="form-control"
                                       required type="password"/>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4 col-form-label text-md-right" for="password-confirm">
                                {{ i18n.get("_.settings.confirm-password") }}
                            </label>
                            <div class="col-md-6">
                                <input id="password-confirm" autocomplete="new-password"
                                       class="form-control"
                                       name="password_confirmation" required type="password"/>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-6 offset-4">
                                <button class="btn btn-primary" type="submit">
                                    {{ i18n.get("_.settings.btn-update") }}
                                </button>
                            </div>
                        </div>
                    </form>
                </Card>
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
