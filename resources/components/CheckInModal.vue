<template>
  <div class="modal fade" tabindex="-1" role="dialog" ref="checkinModal">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">{{ this.lineName }}
            <i class="fas fa-arrow-alt-circle-right" aria-hidden="true"></i> {{ this.dest }}</h4>
          <button type="button" class="close" v-on:click="hide" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form method="POST" id="checkinForm">
            <div class="form-group">
              <label for="message-text" class="col-form-label">
                {{ i18n.get('_.stationboard.label-message') }}
              </label>
              <textarea name="body" class="form-control" v-model="status.body"></textarea>
            </div>

            <div class="mt-2">
              <div class="btn-group" v-if="!edit && $auth.user().twitterUrl != null">
                <input type="checkbox" class="btn-check" id="tweet_check" autocomplete="off"
                       v-model="status.tweet"/>
                <label class="btn btn-sm btn-outline-twitter" for="tweet_check">
                  <i class="fab fa-twitter" aria-hidden="true" :title="i18n.get('stationboard.check-tweet')"></i>
                  <span class="visually-hidden-focusable">{{ i18n.get('_.stationboard.check-tweet') }}</span>
                </label>
              </div>
              <div class="btn-group" v-if="!edit && $auth.user().mastodonUrl != null">
                <input type="checkbox" class="btn-check" id="toot_check" autocomplete="off"
                       v-model="status.toot"/>
                <label class="btn btn-sm btn-outline-mastodon" for="toot_check">
                  <i class="fab fa-mastodon" aria-hidden="true" :title="i18n.get('stationboard.check-toot')"></i>
                  <span class="visually-hidden-focusable">{{ i18n.get('_.stationboard.check-toot') }}</span>
                </label>
              </div>

              <div class="float-end">
                <FADropdown :pre-select="status.business" :dropdown-content="travelReason"
                            v-model="status.business"></FADropdown>
                <!-- @todo Add features from PR#463 (Use default visibility) -->
                <FADropdown :pre-select="status.visibility" :dropdown-content="visibility"
                            v-model="status.visibility"></FADropdown>
              </div>
            </div>

            <!--            @if($events->count() == 1)-->
            <!--            <div class="custom-control custom-checkbox mt-2">-->
            <!--              <input type="checkbox" class="custom-control-input" id="event_check" name="event"-->
            <!--                     value="{{ $events[0]->id }}"/>-->
            <!--              <label class="custom-control-label" for="event_check">-->
            <!--                {{ i18n.get('_.events.on-my-way-to', ['name' => $events[0]->name]) }}-->
            <!--              </label>-->
            <!--            </div>-->
            <!--            @elseif($events->count() > 1)-->
            <!--            <div class="form-group">-->
            <!--              <label for="event-dropdown" class="col-form-label">-->
            <!--                {{i18n.get('_.events.on-my-way-dropdown')}}-->
            <!--              </label>-->
            <!--              <select class="form-control" id="event-dropdown" name="event">-->
            <!--                <option value="0" selected>{{ i18n.get('_.events.no-event-dropdown') }}</option>-->
            <!--                @foreach($events as $event)-->
            <!--&lt;!&ndash;                <option value="{{ $event->id }}">{{ $event->name }}</option>&ndash;&gt;-->
            <!--                @endforeach-->
            <!--              </select>-->
            <!--            </div>-->
            <!--            @else-->
            <input type="hidden" name="event" value="0"/>
            <!--            <input type="hidden" id="input-tripID" name="tripID" value=""/>-->
            <!--            <input type="hidden" id="input-destination" name="destination" value=""/>-->
            <!--            <input type="hidden" name="start" value="{{request()->start}}"/>-->
            <!--            <input type="hidden" name="departure" value="{{request()->departure}}"/>-->
            <!--            <input type="hidden" id="input-arrival" name="arrival"/>-->
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" v-on:click="hide">{{ i18n.get('_.menu.abort') }}</button>
          <button v-if="edit" type="button" class="btn btn-primary" v-on:click="editCheckin">
            {{ i18n.get("_.modals.edit-confirm") }}
          </button>
          <button v-else type="button" class="btn btn-primary" v-on:click="submitCheckin">
            {{ i18n.get('_.stationboard.btn-checkin') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
import {Modal} from "bootstrap";

<script>
import {Modal} from "bootstrap";
import FADropdown from "./FADropdown";
import {travelReason, visibility} from "../js/APImodels";
import Checkin from "../js/ApiClient/Checkin";

export default {
    name: "CheckInModal",
    inject: ["notyf"],
    components: {FADropdown},
    data() {
        return {
            modal: null,
            notifications: null,
            status: {
                body: "",
                business: 0,
                visibility: 0,
                eventId: null,
                tweet: false,
                toot: false,
            },
            travelReason: travelReason,
            visibility: visibility,
            result: null
        };
  },
  props: {
    destination: null,
    statusData: {
      type: Object
    },
    trainData: {
      type: Object
    }
  },
  mounted() {
    this.modal = new Modal(this.$refs.checkinModal);
    if (this.edit) {
      this.status.body       = this.$props.statusData.body;
      this.status.business   = this.$props.statusData.business;
      this.status.visibility = this.$props.statusData.visibility;
    }
  },
  computed: {
    edit() {
      if (this.$props.statusData) {
        return true;
      }
      return false;
    },
    dest() {
      if (this.edit) {
        return this.$props.statusData.train.destination.name;
      }
      return this.$props.destination;
    },
    lineName() {
      if (this.edit) {
        return this.$props.statusData.train.lineName;
      }
      return this.$props.trainData.lineName;
    }
  },
  methods: {
    show() {
      this.modal.show();
    },
    hide() {
      this.modal.hide();
    },
    submitCheckin() {
        const formData = {};
        Object.assign(formData, this.status);
        Object.assign(formData, this.$props.trainData);
        Checkin
            .checkIn(formData)
            .then((data) => {
                this.$router.push({name: "dashboard"});
                this.hide();
                //ToDo Better success modal
                this.notyf.success(data.status.train.points + " points");
            })
            .catch((error) => {
                this.apiErrorHandler(error);
            });
    },
    editCheckin() {
        const formData = {};
        Object.assign(formData, this.status);
        Checkin
            .editCheckin(this.statusData.id, formData)
            .then((data) => {
                this.result = data;
                this.notyf.success(this.i18n.get("_.settings.saved"));
                this.$emit("updated");
                this.hide();
            })
            .catch((error) => {
                this.apiErrorHandler(error);
            });

    }
  }
};
</script>

<style scoped>

</style>
