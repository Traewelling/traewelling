<template>
    <LayoutBasicNoSidebar>
        <div class="row g-2">
            <div class="col-lg-6 col-sm-12">
                <Card title="Test">
                    aaa
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
                        <div class="col form-check form-switch float-end">
                            <input id="privateProfileSwitch" class="form-check-input" type="checkbox"/>
                        </div>
                    </div>
                    <div class="row border-top mt-1 pt-1">
                        <div class="col">
                            <label class="form-check-label" for="preventIndexSwitch">
                                {{ i18n.get('_.settings.hide-search-engines') }}
                                <i :title="i18n.get('_.settings.search-engines.description')" aria-hidden="true"
                                   class="fas fa-info-circle" data-mdb-toggle="tooltip"></i>
                            </label>
                        </div>
                        <div class="col form-check form-switch float-end">
                            <input id="preventIndexSwitch" class="form-check-input" type="checkbox"/>
                        </div>
                    </div>
                    <div class="row border-top mt-1 pt-1">
                        <div class="col">
                            <label class="form-check-label" for="privateProfileSwitch">
                                {{ i18n.get('_.settings.visibility.default') }}
                            </label>
                        </div>
                        <div class="col">
                            <FADropdown :dropdown-content="visibility" :pre-select="0" showText="true"></FADropdown>
                        </div>
                    </div>
                </Card>
            </div>
            <div class="col-lg-6 col-sm-12">
                <Card title="Test">
                    bbb
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
