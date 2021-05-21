<template>
  <div class="card status mt-3"
       v-if="status"
       :id="`status-${status.id}`"
       :data-trwl-status-body="status.body"
       :data-date="status.train.origin.departure"
       :data-trwl-business-id="status.business">
    <div class="card-img-top" v-if="polyline">
      <Map class="map embed-responsive embed-responsive-16by9" :poly-lines="polyline"></Map>
    </div>

    <div class="card-body row">
      <div class="col-2 image-box pe-0 d-none d-lg-flex">
        <router-link :to="{ name: 'profile', params: {username: status.username}}">
          <img :src="`/profile/${status.username}/profilepicture`" :alt="status.username">
        </router-link>
      </div>

      <div class="col ps-0">
        <ul class="timeline">
          <li>
            <i class="trwl-bulletpoint" aria-hidden="true"></i>
            <span class="text-trwl float-end">
              <small v-if="status.train.origin.isDepartureDelayed"
                     style="text-decoration: line-through;"
                     class="text-muted">{{ moment(status.train.origin.departurePlanned).format('LT') }}
              </small>
              &nbsp; {{ departure.format('LT') }}
            </span>
            <!--            ToDo: Add router-url, add better station-shit (like the helper method)-->
            <a :href="`/trains/stationboard?provider=train&station=${status.train.origin.name}`"
               class="text-trwl clearfix">{{ status.train.origin.name }}</a>
            <p class="train-status text-muted">
              <span>
                <img v-if="categories.indexOf(status.train.category) > -1 " class="product-icon"
                     :src="`/img/${status.train.category}.svg`" :alt="status.train.category">
                <i v-else class="fa fa-train d-inline" aria-hidden="true"></i>
                {{ status.train.lineName }}
              </span>
              <span class="ps-2">
                <i class="fa fa-route d-inline" aria-hidden="true"></i>&nbsp;{{
                  status.train.distance.toFixed(0)
                }}<small>km</small>
              </span>
              <span class="ps-2"><i class="fa fa-stopwatch d-inline" aria-hidden="true"></i>&nbsp;{{ duration }}</span>
              <span v-if="status.business === 1" class="pl-sm-2">
                <i class="fa fa-briefcase" data-mdb-toggle="tooltip" data-mdb-placement="top"
                   title="__('stationboard.business.business')" aria-hidden="true"></i>
              </span>
              <span v-else-if="status.business === 2" class="pl-sm-2">
                <i class="fa fa-building" data-mdb-toggle="tooltip" data-mdb-placement="top"
                   title="__('stationboard.business.commute')" aria-hidden="true"></i>
              </span>
              <br>
              <span v-if="status.event != null" class="pl-sm-2">
                <i class="fa fa-calendar-day" aria-hidden="true"></i>
                <a :href="`/event/${status.event.slug}`">{{ status.event.name }}</a>
              </span>
            </p>
            <p v-if="status.body" class="status-body"><i class="fas fa-quote-right" aria-hidden="true"></i>
              {{ status.body }}</p>
            <div v-if="nextStop != null">
              <p class="text-muted font-italic">
                <!--                ToDo: fix with router link.-->
                __('stationboard.next-stop')
                <a :href="`/trains/stationboard?provider=train&station=${nextStop.name}`" class="text-trwl clearfix">{{
                    nextStop.name
                  }}</a>
              </p>
            </div>
          </li>
          <li>
            <i class="trwl-bulletpoint" aria-hidden="true"></i>
            <span class="text-trwl float-end">
              <small v-if="status.train.destination.isArrivalDelayed"
                     style="text-decoration: line-through;"
                     class="text-muted">{{ moment(status.train.destination.arrivalPlanned).format('LT') }}
              </small>
              &nbsp; {{ arrival.format('LT') }}
            </span>
            <!--            {!! stationLink($status->trainCheckin->Destination->name) !!}-->
            <a :href="`/trains/stationboard?provider=train&station=${status.train.destination.name}`"
               class="text-trwl clearfix">{{ status.train.destination.name }}</a>
          </li>
        </ul>
      </div>
    </div>
    <div class="progress">
      <div class="progress-bar"
           role="progressbar"
           v-bind:style="{width: percentage + '%'}"></div>
    </div>
    <div class="card-footer text-muted interaction">
        <span class="float-end like-text">
<!--            <a href="{{ route('account.show', ['username' => $status->user->username]) }}">-->
          <!--            <a :href="`/profile/${status.username}`">-->
          <!--                @if(auth()?->user()?->id == $status->user_id)-->
          <!--                    {{ __('user.you') }}-->
          <!--                @else-->
          <!--                    {{ status.username }}-->
          <!--                @endif-->
          <!--            </a>-->
          <router-link :to="{name: 'profile', params: {username: status.username}}">{{ status.username }}</router-link>,
          <router-link :to="{ name: 'status', params: {id: status.id}}">
            {{ moment(status.createdAt).fromNow() }}
          </router-link>
        </span>
      <ul class="list-inline">
        <!--        @auth-->
        <!--        <li class="-->
        <!--                @if(auth()->user()->id == $status->user_id && $status->likes->count() !== 0)d-none @endif list-inline-item d-lg-none"-->
        <!--            id="avatar-small-{{ $status->id }}" data-trwl-selflike="{{ auth()->user()->id == $status->user_id }}">-->
        <!--          <a href="{{ route('account.show', ['username' => $status->user->username]) }}">-->
        <!--            <img src="{{ route('account.showProfilePicture', ['username' => $status->user->username]) }}"-->
        <!--                 class="profile-image" alt="{{__('settings.picture')}}">-->
        <!--          </a>-->
        <!--        </li>-->

        <!--        <li class="list-inline-item like-text">-->
        <!--                    <span-->
        <!--                        class="like {{ $status->likes->where('user_id', auth()->user()->id)->first() === null ? 'far fa-star' : 'fas fa-star'}}"-->
        <!--                        data-statusid="{{ $status->id }}"></span>-->
        <!--          <span class="pl-1 @if($status->likes->count() == 0) d-none @endif"-->
        <!--                id="like-count-{{ $status->id }}">{{ $status->likes->count() }}</span>-->
        <!--        </li>-->
        <!--        @if(auth()->user()->id == $status->user_id)-->
        <!--        <li class="list-inline-item like-text">-->
        <!--          <a href="#" class="edit" data-trwl-status-id="{{ $status->id }}"><i class="fas fa-edit"></i></a>-->
        <!--        </li>-->

        <!--        <li class="list-inline-item like-text">-->
        <!--          <a href="#" class="delete" data-trwl-status-id="{{ $status->id }}"><i class="fas fa-trash"></i></a>-->
        <!--        </li>-->
        <!--        @endif-->
        <!--        @else-->
        <!--        <li class="list-inline-item d-lg-none" id="avatar-small-{{ $status->id }}">-->
        <!--          <a href="{{ route('account.show', ['username' => $status->user->username]) }}">-->
        <!--            <img src="{{ route('account.showProfilePicture', ['username' => $status->user->username]) }}"-->
        <!--                 class="profile-image" alt="{{__('settings.picture')}}">-->
        <!--          </a>-->
        <!--        </li>-->
        <!--        @endauth-->
      </ul>
    </div>

    <!--    <div v-if="isSingleStatus" v-for="[] as likes" class="card-footer text-muted clearfix">-->
    <!--      <div class="col-xs-2">-->
    <!--        <a href="{{ route('account.show', ['username' => $like->user->username]) }}">-->
    <!--          <img src="{{ route('account.showProfilePicture', ['username' => $like->user->username]) }}"-->
    <!--               class="profile-image float-left" alt="{{__('settings.picture')}}">-->
    <!--        </a>-->
    <!--      </div>-->
    <!--      <div class="col-xs-10">-->
    <!--                <span class="like-text pl-2 d-table-cell">-->
    <!--                    <a href="{{ route('account.show', ['username' => $like->user->username]) }}">-->
    <!--                        {{ $like->user->username }}-->
    <!--                    </a>-->
    <!--                    @if($like->user == $status->user)-->
    <!--                        {{ __('user.liked-own-status') }}-->
    <!--                    @else-->
    <!--                        {{ __('user.liked-status') }}-->
    <!--                    @endif-->
    <!--                </span>-->
    <!--      </div>-->
    <!--    </div>-->
  </div>

</template>

<script>
import moment from "moment";
import Map from "../components/Map";

export default {
  name: "Status.vue",
  data() {
    return {
      moment: moment,
      isSingleStatus: false,
      categories: ["bus", "suburban", "subway", "tram"],
      loading: false,
      error: false,
      now: moment()
    };
  },
  components: {
    Map
  },
  props: {
    status: {
      id: 0,
      body: "",
      type: "",
      createdAt: "",
      user: 0,
      username: "",
      business: 0,
      train: {
        trip: 0,
        category: "",
        number: "",
        lineName: null,
        distance: 0,
        points: 0,
        delay: null,
        duration: 0,
        speed: 0,
        origin: {
          name: "",
          trainStationId: 0,
          arrival: "",
          arrivalPlanned: "",
          arrivalReal: null,
          arrivalPlatformPlanned: null,
          arrivalPlatformReal: null,
          departure: "",
          departurePlanned: "",
          departureReal: null,
          departurePlatformPlanned: null,
          departurePlatformReal: null,
          plattform: null,
          isArrivalDelayed: false,
          isDepartureDelayed: false
        },
        destination: {
          name: "",
          trainStationId: 0,
          arrival: "",
          arrivalPlanned: "",
          arrivalReal: null,
          arrivalPlatformPlanned: null,
          arrivalPlatformReal: null,
          departure: "",
          departurePlanned: "",
          departureReal: null,
          departurePlatformPlanned: null,
          departurePlatformReal: null,
          plattform: null,
          isArrivalDelayed: false,
          isDepartureDelayed: false
        },
        polyline: ""
      },
      event: {
        id: 0,
        name: "",
        slug: "",
        hashtag: "",
        host: "",
        url: "",
        begin: "",
        end: "",
        trainstation: 0
      }
    },
    polyline: null,
    stopovers: null
  },
  computed: {
    departure() {
      return moment(this.status.train.origin.departure);
    },
    arrival() {
      return moment(this.status.train.destination.arrival);
    },
    duration() {
      // ToDo: This needs localization, currently handled in `durationToSpan`
      const duration = moment.duration(this.status.train.duration, 'minutes').asMinutes();
      let minutes    = duration % 60;
      let hours      = Math.floor(duration / 60);

      return hours + "h " + minutes + "m";
    },
    percentage() {
      const start = moment(this.status.train.origin.departure);
      const end   = moment(this.status.train.destination.arrival);
      let percent;
      if (this.now > start && this.now < end) {
        percent = 100 * ((this.now - start) / (end - start));
      } else if (this.now >= end) {
        percent = 100;
      }
      return percent;
    },
    showStopOvers() {
      return this.departure.isBefore() && this.arrival.isAfter() && this.nextStop() !== null;
    },
    nextStop() {
      if (this.stopovers != null && this.percentage < 100 && this.percentage > 0) {
        let stopOvers = this.stopovers[this.status.train.trip];
        if (stopOvers && stopOvers.length > 0) {
          let future = stopOvers.filter((stopover) => {
            return moment(stopover.arrival).isAfter(this.now);
          });
          return future[0];
        }
      }
      return null;
    }
  },
  methods: {
    startRefresh() {
      setInterval(() => (this.now = moment()), 1000);
    }
  },
  created() {
    this.startRefresh();
  }
}
</script>

<style scoped>

</style>