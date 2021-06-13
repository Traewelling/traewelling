<template>
  <div class="modal fade" tabindex="-1" role="dialog" ref="checkinModal">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">{{ this.$props.lineName }}
            <i class="fas fa-arrow-alt-circle-right" aria-hidden="true"></i> {{ this.$props.destination }}</h4>
          <button type="button" class="close" v-on:click="hide" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form method="POST" id="checkinForm">
            <div class="form-group">
              <label for="message-text" class="col-form-label">
                {{i18n.get('_.stationboard.label-message')}}
              </label>
              <textarea name="body" class="form-control" id="message-text"></textarea>
            </div>

            <div class="mt-2">
              <div class="btn-group" v-if="$auth.user.twitterUrl != null">
                <input type="checkbox" class="btn-check" id="tweet_check" autocomplete="off"
                       name="tweet_check"/>
                <label class="btn btn-sm btn-outline-twitter" for="tweet_check">
                  <i class="fab fa-twitter" aria-hidden="true" :title="i18n.get('stationboard.check-tweet')"></i>
                  <span class="visually-hidden-focusable">{{ i18n.get('_.stationboard.check-tweet') }}</span>
                </label>
              </div>

              <div class="btn-group" v-if="$auth.user.mastodonUrl != null">
                <input type="checkbox" class="btn-check" id="toot_check" autocomplete="off"
                       name="toot_check"/>
                <label class="btn btn-sm btn-outline-mastodon" for="toot_check">
                  <i class="fab fa-mastodon" aria-hidden="true" :title="i18n.get('stationboard.check-toot')"></i>
                  <span class="visually-hidden-focusable">{{ i18n.get('_.stationboard.check-toot') }}</span>
                </label>
              </div>
              @include('includes.business-dropdown')
              @include('includes.visibility-dropdown')
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
          <button type="button" class="btn btn-primary" v-on:click="hide">{{ i18n.get('_.stationboard.btn-checkin') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
import {Modal} from "bootstrap";

<script>
import {Modal} from "bootstrap";

export default {
  name: "CheckInModal",
  data() {
    return {
      modal: null,
      notifications: null,
    };
  },
  props: {
    lineName: null,
    destination: null,
    edit: null
  },
  mounted() {
    this.modal = new Modal(this.$refs.checkinModal);
  },
  methods: {
    show() {
      this.modal.show();
    },
    hide() {
      this.modal.hide();
    },
    submitCheckin() {
      if (this.edit == null) {

      }
    }
  }
};
</script>

<style scoped>

</style>
