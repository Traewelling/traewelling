<template>
    <LayoutBasicNoSidebar>
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-8 mb-2">
                <div class="card">
                    <div class="card-body">
                        <h2 class="fs-4" id="heading-live-upcoming">
                            <em class="far fa-calendar-alt"></em>
                            {{ i18n.get('_.events.live-and-upcoming') }}
                        </h2>
                        <hr/>
                        <p v-if="upcomingEvents.length === 0 && !loading" class="text-trwl">
                            {{ i18n.get('_.events.no-upcoming') }}
                            {{ i18n.get('_.events.request-question') }}
                        </p>
                        <div v-else-if="!loading" class="table-responsive">
                            <table class="table" aria-describedby="heading-live-upcoming">
                                <tbody>
                                    <tr v-for="event in upcomingEvents">
                                        <td>
                                            {{ event.name }}
                                            <small v-if="event.station" class="text-muted">
                                                <br/>
                                                {{ i18n.get('_.events.closestStation') }}:
                                                <router-link
                                                    :to="{name: 'trains.stationboard', query: {station: event.station.name}}">
                                                    {{ event.station.name }}
                                                </router-link>
                                            </small>
                                        </td>
                                        <td v-if="moment(event.begin).isSame(moment(event.end), 'day')">
                                            {{ moment(event.begin).format('LL') }}
                                        </td>
                                        <td v-else>
                                            {{ moment(event.begin).format('LL') }}
                                            - {{ moment(event.end).format('LL') }}
                                        </td>
                                        <td>
                                            <router-link :to="{name: 'event', params: {slug: event.slug}}"
                                                         class="btn btn-primary btn-sm">
                                                {{ i18n.get('_.menu.show-more') }}
                                                <em class="fas fa-angle-double-right"></em>
                                            </router-link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <spinner v-if="loading"></spinner>
                        <div v-if="links && links.next" class="text-center">
                            <button aria-label="i18n.get('_.menu.show-more')"
                                    class="btn btn-primary btn-lg btn-floating mt-4"
                                    @click.prevent="fetchMore">
                                <i aria-hidden="true" class="fas fa-caret-down"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h2 class="fs-4">
                            <em class="far fa-calendar-plus"></em>
                            {{ i18n.get('_.events.request') }}
                        </h2>
                        <hr/>
                        <form v-if="$auth.check()" @submit.prevent="submitProposal">
                            <div class="form-outline mb-4">
                                <input type="text" id="event-requester-name" v-model="suggest.name" class="form-control"
                                       required/>
                                <label class="form-label"
                                       for="event-requester-name">{{ i18n.get('_.events.name') }}</label>
                            </div>
                            <div class="form-outline mb-4">
                                <input type="text" id="event-requester-host" v-model="suggest.host"
                                       class="form-control"/>
                                <label class="form-label"
                                       for="event-requester-host">{{ i18n.get('_.events.host') }}</label>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-outline mb-4">
                                        <input type="date" id="event-requester-begin" v-model="suggest.begin"
                                               class="form-control" required/>
                                        <label class="form-label" for="event-requester-begin">
                                            {{ i18n.get('_.events.begin') }}
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-outline mb-4">
                                        <input type="date" id="event-requester-end" v-model="suggest.end"
                                               class="form-control"
                                               required/>
                                        <label class="form-label" for="event-requester-end">
                                            {{ i18n.get('_.events.end') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-outline mb-4">
                                <input type="string" id="event-requester-url" v-model="suggest.url"
                                       class="form-control"/>
                                <label class="form-label"
                                       for="event-requester-url">{{ i18n.get('_.events.url') }}</label>
                            </div>
                            <button class="btn btn-primary" :class="{'disabled':suggestLoading}">
                                {{ i18n.get('_.events.request-button') }}
                                <span v-if="suggestLoading" class="spinner-border spinner-border-sm" role="status"
                                      aria-hidden="true"></span>
                                <span v-if="suggestLoading"
                                      class="visually-hidden">{{ i18n.get("_.menu.loading") }}</span>
                            </button>
                            <hr/>
                            <small class="text-muted">{{ i18n.get('_.events.notice') }}</small>
                        </form>
                        <p v-else class="text-trwl bold">{{ i18n.get('_.auth.required') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </LayoutBasicNoSidebar>
</template>

<script>
import LayoutBasicNoSidebar from "../layouts/BasicNoSidebar";
import Event from "../../js/ApiClient/Event";
import Spinner from "../Spinner";
import moment from "moment";

export default {
    name: "Events",
    components: {Spinner, LayoutBasicNoSidebar, moment},
    inject: ["notyf"],
    metaInfo() {
        return {
            title: this.i18n.get("_.events.live"),
            meta: [
                {name: "robots", content: "index", vmid: "robots"}
            ]
        };
    },
    data() {
        return {
            upcomingEvents: [],
            loading: true,
            links: null,
            suggestLoading: false,
            suggest: {}
        };
    },
    created() {
        this.fetchData();
    },
    methods: {
        fetchMore() {
            this.loading = true;
            this.fetchMoreData(this.links.next)
                .then((data) => {
                    this.upcomingEvents = this.upcomingEvents.concat(data.data);
                    this.links          = data.links;
                    this.loading        = false;
                });
        },
        fetchData() {
            Event
                .upcoming()
                .then((data) => {
                    this.upcomingEvents = data.data;
                    this.links          = data.links;
                    this.loading        = false;
                });
        },
        submitProposal() {
            this.suggestLoading = true;
            const formData      = {};
            Object.assign(formData, this.suggest);
            Event
                .suggest(formData)
                .then(() => {
                    this.suggestLoading = false;
                    this.notyf.success(this.i18n.get("_.settings.saved"));
                    this.suggest = {};
                })
                .catch((error) => {
                    this.suggestLoading = false;
                    this.apiErrorHandler(error);
                });

        }
    }
};
</script>
