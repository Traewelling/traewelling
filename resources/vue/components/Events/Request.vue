<script>
import {trans} from "laravel-vue-i18n";
import {DateTime} from "luxon";
import {Event} from "../../../js/api/Event.js";
import HelpPageLink from "../Helpers/HelpPageLink.vue";

export default {
  name: 'Suggest',
  components: {HelpPageLink},
  data: function () {
    return {
      event: {
        name: '',
        host: '',
        begin: '',
        end: '',
        url: '',
        hashtag: '',
        nearestStation: ''
      },
      errors: [],
    }
  },
  methods: {
    trans,
    submit() {
      this.errors = [];
      const now = DateTime.now();
      const begin = DateTime.fromISO(this.event.begin);
      const end = DateTime.fromISO(this.event.end);
      const diff = begin.diff(now, 'days').days;
      const duration = end.diff(begin, 'days').days;

      if (diff < 14) {
        this.errors.push(trans('events.request.warn.date'));
      }
      if (duration > 3) {
        this.errors.push(trans('events.request.warn.duration'));
      }
      if (this.event.url.length < 5) {
        this.errors.push(trans('events.request.warn.url'));
      }


      if (this.errors.length > 0) {
        const message = this.errors.join('\n\n') +
            "\n\n" +
            trans('events.request.warn.confirm');

        if (confirm(message)) {
          this.errors = [];
        } else {
          return;
        }
      }

      this.submitEvent();
    },
    clear() {
      this.event.name = '';
      this.event.host = '';
      this.event.begin = '';
      this.event.end = '';
      this.event.url = '';
      this.event.hashtag = '';
      this.event.nearestStation = '';
    },
    submitEvent() {
      Event.suggest(
          this.event.name,
          this.event.host,
          this.event.begin,
          this.event.end,
          this.event.url,
          this.event.hashtag,
          this.event.nearestStation
      );
      this.clear();
    },
    updateBegin() {
      if (this.event.end === '') {
        this.event.end = this.event.begin;
      }
    }
  },
}
</script>

<template>
  <div class="card">
    <div class="card-body">
      <h2 class="fs-4" id="heading-live-upcoming">
        <em class="far fa-calendar-plus"></em>
        {{ trans('events.request') }}
      </h2><small class="text-muted">
      {{ trans('events.notice.help') }}
      <HelpPageLink help-page="/features/events">{{ trans('events.notice.helppage') }}</HelpPageLink>
    </small>
      <hr/>

      <form @submit.prevent="submit">
        <div class="form-floating mb-2">
          <input v-model="event.name" id="event-name" type="text" class="form-control" required/>
          <label class="form-label" for="event-name">{{ trans('events.name') }} *</label>
        </div>
        <div class="form-floating mb-2">
          <input v-model="event.host" id="event-host" type="text" class="form-control"/>
          <label class="form-label" for="event-host">{{ trans('events.host') }}</label>
        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="form-floating mb-2 datepicker">
              <input v-model="event.begin" id="event-begin" type="date" class="form-control" required
                     @focusout="updateBegin"/>
              <label class="form-label" for="event-begin">
                {{ trans('events.begin') }} *
              </label>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-floating mb-2 datepicker">
              <input v-model="event.end" id="event-end" type="date" class="form-control"
                     required/>
              <label class="form-label" for="event-end">
                {{ trans('events.end') }} *
              </label>
            </div>
          </div>
        </div>
        <div class="form-floating mb-2">
          <input v-model="event.url" type="url" id="event-url" class="form-control"/>
          <label class="form-label" for="event-url">{{ trans('events.url') }}</label>
        </div>
        <div class="form-floating mb-2">
          <input v-model="event.hashtag" type="text" id="event-hashtag" class="form-control"/>
          <label class="form-label" for="event-requester-hashtag">{{ trans('events.hashtag') }}</label>
        </div>
        <div class="form-floating mb-2" id="station-autocomplete-container">
          <!-- todo: use new autocomplete -->
          <input v-model="event.nearestStation" type="text" id="station-autocomplete" name="nearestStation"
                 class="form-control"/>
          <label class="form-label" for="station-autocomplete">{{ trans('events.closestStation') }}</label>
        </div>
        <button type="submit" class="btn btn-primary">{{ trans('events.request-button') }}</button>
      </form>

      <hr/>
      <small class="text-muted">{{ trans('events.notice') }}</small>
    </div>
  </div>

</template>
