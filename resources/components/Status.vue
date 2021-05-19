<template>
  <div class="card status mt-3"
       v-if="status"
       :id="`status-${status.id}`"
       :data-trwl-status-body="status.body"
       :data-date="status.train.departure"
       :data-trwl-business-id="status.business">
    <div class="card-img-top" v-if="polyline">
      <div :id="`map-${status.id}`" class="map statusMap embed-responsive embed-responsive-16by9"
           :data-polygon="polyline"></div>
    </div>

    <div class="card-body row">
      <div class="col-2 image-box pe-0 d-none d-lg-flex">
        <router-link :to="{ name: 'profile', params: {username: status.username}}">
          <img :src="`/profile/${status.username}/profilepicture`">
        </router-link>
      </div>

      <div class="col ps-0">
        <ul class="timeline">
          <li>
            <i>&nbsp;</i>
            <span class="text-trwl float-end">
<!--              ToDo: fix this mess-->
              <!--                        @if($status->trainCheckin?->origin_stopover?->isDepartureDelayed)-->
              <!--                            <small style="text-decoration: line-through;"-->
              <!--                                   class="text-muted">{{-->
              <!--                                $status->trainCheckin->origin_stopover->departure_planned->isoFormat(__('time-format'))-->
              <!--                              }}</small>-->
              <!--                            &nbsp;-->
              <!--                            {{ $status->trainCheckin->origin_stopover->departure_real->isoFormat(__('time-format')) }}-->
              <!--                        @else-->
              <!--                            {{-->
              <!--                $status->trainCheckin ?->origin_stopover ?->departure->isoFormat(__('time-format')) ?? $status->trainCheckin->departure->isoFormat(__('time-format'))-->
              <!--              }}-->
              <!--                        @endif-->
              {{ moment(status.train.departure).format('LT') }}
                    </span>
            <!--            ToDo: Add router-url, add better station-shit (like the helper method)-->
            <a :href="`/trains/stationboard?provider=train&station=${status.train.originName}`"
               class="text-trwl clearfix">{{ status.train.originName }}</a>
            <p class="train-status text-muted">
              <span>
                <img v-if="categories.indexOf(status.train.category) > -1 " class="product-icon"
                     :src="`/img/${status.train.category}.svg`" :alt="status.train.category">
                <i v-else class="fa fa-train d-inline"></i>
                {{ status.train.lineName }}
              </span>
              <span class="ps-2">
                <i class="fa fa-route d-inline"></i>&nbsp;{{ status.train.distance.toFixed(0) }}<small>km</small>
              </span>
              <span class="ps-2"><i class="fa fa-stopwatch d-inline"></i>&nbsp;{{ duration }}</span>
              <!--                            {!! durationToSpan(secondsToDuration($status->trainCheckin->duration * 60)) !!}-->
              <span v-if="status.business === 1" class="pl-sm-2">
                <i class="fa fa-briefcase" data-mdb-toggle="tooltip" data-mdb-placement="top"></i>
                <!--title="{{ __('stationboard.business.business') }}"-->
              </span>
              <span v-else-if="status.business === 2" class="pl-sm-2">
                <i class="fa fa-building" data-mdb-toggle="tooltip" data-mdb-placement="top"></i>
                <!--                                   title="{{ __('stationboard.business.commute') }}">-->
              </span>
              <br>
              <span v-if="status.event != null" class="pl-sm-2">
                <i class="fa fa-calendar-day"></i>
                &nbsp;
                <a :href="`/event/${status.event.slug}`">{{ status.event.name }}</a>
              </span>
            </p>
            <p v-if="status.body" class="status-body"><i class="fas fa-quote-right"></i> {{ status.body }}</p>
            <!--            @if($status->trainCheckin->departure->isPast() && $status->trainCheckin->arrival->isFuture())-->
            <!--            <p class="text-muted font-italic">-->
            <!--              {{ __('stationboard.next-stop') }}-->
            <!--              {!! stationLink(\App\Http\Controllers\FrontendStatusController::nextStation($status)) !!}-->
            <!--            </p>-->
            <!--            @endif-->
          </li>
          <li>
            <i>&nbsp;</i>
            <span class="text-trwl float-end">
<!--              ToDo: Fix this mess-->
              <!--                        @if($status->trainCheckin?->destination_stopover?->isArrivalDelayed)-->
              <!--                            <small style="text-decoration: line-through;" class="text-muted">-->
              <!--                                {{-->
              <!--                                $status->trainCheckin->destination_stopover->arrival_planned->isoFormat(__('time-format'))-->
              <!--                              }}-->
              <!--                            </small>-->
              <!--                            &nbsp;-->
              <!--                            {{-->
              <!--                $status->trainCheckin->destination_stopover->arrival_real->isoFormat(__('time-format'))-->
              <!--              }}-->
              <!--                        @else-->
              <!--                            {{-->
              <!--                $status->trainCheckin ?->destination_stopover ?->arrival ?->isoFormat(__('time-format')) ?? $status->trainCheckin->arrival->isoFormat(__('time-format'))-->
              <!--              }}-->
              <!--                        @endif-->
              {{ moment(status.train.arrival).format('LT') }}
            </span>
            <!--            {!! stationLink($status->trainCheckin->Destination->name) !!}-->
            <a :href="`/trains/stationboard?provider=train&station=${status.train.destinationName}`"
               class="text-trwl clearfix">{{ status.train.destinationName }}</a>
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
          <router-link :to="{name: 'profile', params: {username: status.username}}">
            {{ status.username }}
          </router-link>
          <!--          {{ __('dates.-on-') }}-->
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

export default {
  name: "Status.vue",
  data() {
    return {
      moment: moment,
      isSingleStatus: false,
      categories: ["bus", "suburban", "subway", "tram"],
      loading: false,
      error: false
    };
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
        departure: "",
        arrival: "",
        delay: null,
        duration: 0,
        speed: 0,
        origin: 0,
        originName: "",
        destination: 0,
        destinationName: "",
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
    polyline: ""
  },
  computed: {
    duration () {
      const duration = moment.duration(this.status.train.duration, 'minutes').asMinutes();
      let minutes    = duration % 60;
      let hours      = Math.floor(duration / 60);

      return hours + "h " + minutes + "m";
    },
    percentage () {
      const start = moment(this.status.train.departure);
      const end   = moment(this.status.train.arrival);
      const now   = moment();
      //ToDo: Add delays
      if (now < start) {
        return 0;
      } else if (now > end) {
        return 100;
      } else {
        return 100 * ((now - start) / (end - start));
      }
    }
  }
}
</script>

<style scoped>

</style>