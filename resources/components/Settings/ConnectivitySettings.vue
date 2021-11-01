<template>
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
</template>

<script>

export default {
    name: "ConnectivitySettings",
    props: ["value"],
};
</script>

<style scoped>

</style>
