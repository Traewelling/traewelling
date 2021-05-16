<template>
  <div class="card status mt-3" :id="`status-${status.id}`" :data-trwl-status-body="status.body"
       :data-date="status.train.departure"
       :data-trwl-business-id="status.business"
  >

    <div class="card-img-top" v-if="isSingleStatus">
      <div :id="`map-${status.id}`" class="map statusMap embed-responsive embed-responsive-16by9"
           :data-polygon="status.train"></div>
    </div>

    <div class="card-body row">
      <div class="col-2 image-box pe-0 d-none d-lg-flex">
        <!-- ToDo: fix route -->
        <a :href="`/profile/${status.username}`">
          <img :src="`/profile/${status.username}/profilepicture`">
        </a>
      </div>

      <div class="col ps-0">
        <ul class="timeline">
          <li>
            <!-- ToDo: Is this i-tag necessary? -->
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
              {{ status.train.departure }}
                    </span>
            <!--            ToDo: Add router-url, add better station-shit (like the helper method)-->
            <a :href="`/trains/stationboard?provider=train&station=${status.train.origin_name}`"
               class="text-trwl clearfix">{{ status.train.origin_name }}</a>
            <p class="train-status text-muted">
              <span>
                <img v-if="categories.indexOf(status.train.category) > -1 " class="product-icon"
                   :src="`/img/${status.train.category}.svg`">
                <i v-else class="fa fa-train d-inline"></i>
                {{ status.train.linename }}
              </span>
              <span class="ps-2">
                <i class="fa fa-route d-inline"></i>&nbsp;{{ status.train.distance.toFixed(0) }}<small>km</small>
              </span>
<!--              ToDo: This should be properly rendered in sth. like moment.js-->
              <span class="ps-2"><i class="fa fa-stopwatch d-inline"></i>&nbsp;{{ status.train.duration*60 }}</span>
<!--                            {!! durationToSpan(secondsToDuration($status->trainCheckin->duration * 60)) !!}-->

              <span v-if="status.business === 1" class="pl-sm-2">
                                <i class="fa fa-briefcase" data-mdb-toggle="tooltip" data-mdb-placement="top"></i>
                                   <!--title="{{ __('stationboard.business.business') }}"-->
                            </span>
              <span v-else-if="status.business === 2" class="pl-sm-2">
                                <i class="fa fa-building" data-mdb-toggle="tooltip" data-mdb-placement="top"></i>
<!--                                   title="{{ __('stationboard.business.commute') }}">-->
                            </span>


              <div v-if="status.event !== null">
              <br/>
              <span class="pl-sm-2">
                                <i class="fa fa-calendar-day"></i>
<!--                                <a href="{{ route('statuses.byEvent', ['eventSlug' => $status->event->slug]) }}">-->
<!--                                    {{ $status->event->name }}-->
<!--                                </a>-->
                            </span>

              </div>
            </p>


            <p v-if="status.body !== ''" class="status-body"><i class="fas fa-quote-right"></i> {{ status.body }}</p>


<!--            @if($status->trainCheckin->departure->isPast() && $status->trainCheckin->arrival->isFuture())-->
<!--            <p class="text-muted font-italic">-->
<!--              {{ __('stationboard.next-stop') }}-->
<!--              {!! stationLink(\App\Http\Controllers\FrontendStatusController::nextStation($status)) !!}-->
<!--            </p>-->
<!--            @endif-->
          </li>
          <li>
<!--            <span class="text-trwl float-end">-->
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
<!--                    </span>-->
<!--            {!! stationLink($status->trainCheckin->Destination->name) !!}-->

              {{ status.train.destination_name }}
          </li>
        </ul>
      </div>
    </div>
    <div class="progress">
      <div
          class="progress-bar progress-time"
          role="progressbar"
          style="width: 0"
          :data-valuenow="new Date()"
          :data-valuemin="status.train.departure"
          :data-valuemax="status.train.arrival"
      ></div>
      <!--          data-valuemax="{{ $status->trainCheckin?->destination_stopover?->arrival->timestamp ?? $status->trainCheckin->arrival->timestamp }}"-->
      <!--          data-valuemin="{{ $status->trainCheckin?->origin_stopover?->departure->timestamp ?? $status->trainCheckin->departure->timestamp }}"-->
  </div>
    <div class="card-footer text-muted interaction">
        <span class="float-end like-text">
<!--            <a href="{{ route('account.show', ['username' => $status->user->username]) }}">-->
            <a :href="`/profile/${status.username}`">
<!--                @if(auth()?->user()?->id == $status->user_id)-->
<!--                    {{ __('user.you') }}-->
<!--                @else-->
                    {{ status.username }}
<!--                @endif-->
            </a>
<!--          {{ __('dates.-on-') }}-->
            <a :href="`/status/${status.id}`">
                {{ status.created_at }}
            </a>
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
export default {
  name: "Status.vue",
  props: ['status'],
  data() {
    return {
      isSingleStatus: false,
      categories: ["bus", "suburban", "subway", "tram"],
      status: {
        id: 0,
        body: '',
        type: '',
        created_at: '',
        user: 0,
        username: '',
        business: 0,
        train: {
          trip: 0,
          category: '',
          number: '',
          linenumber: null,
          distance: 0,
          points: 0,
          departure: '',
          arrival: '',
          delay: null,
          duration: 0,
          speed: 0,
          origin: 0,
          origin_name: '',
          destination: 0,
          destination_name: ''
        },
        event: null
      }
    }
  }
}
</script>

<style scoped>

</style>