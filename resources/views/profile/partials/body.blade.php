@if($user->muted)
    <div class="row justify-content-center mt-4">
        <div class="col-md-8 col-lg-7 text-center mb-5">
            <header><h3>{{__('user.muted.heading')}}</h3></header>
            <h5>{{__('user.muted.text', ["username" => $user->username])}}</h5>

            <x-mute-button :user="$user" :showText="true"/>
        </div>
    </div>
@elseif($user->isAuthUserBlocked)
    <div class="row justify-content-center mt-4">
        <div class="col-md-8 col-lg-7 text-center mb-5">
            <span class="fs-3">{{__('profile.youre-blocked-text')}}</span>
            <br/>
            <span class="fs-5">
                        {{__('profile.youre-blocked-information-text', ['username' => $user->username])}}
                    </span>
        </div>
    </div>
@elseif($user->isBlockedByAuthUser)
    <div class="row justify-content-center mt-4">
        <div class="col-md-8 col-lg-7 text-center mb-5">
            <span class="fs-3">{{__('profile.youre-blocking-text', ['username' => $user->username])}}</span>
            <br/>
            <span class="fs-5">
                        {{__('profile.youre-blocking-information-text')}}
                    </span>
        </div>
    </div>
@elseif($user->private_profile && !$user->following && (!auth()->check() || $user->id !== auth()->id()))
    <div class="row justify-content-center mt-4">
        <div class="col-md-8 col-lg-7 text-center mb-5">
            <span class="fs-3">{{__('profile.private-profile-text')}}</span>
            <br/>
            <span class="fs-5">
                        {{__('profile.private-profile-information-text', ['username' => $user->username, 'request' => __('profile.follow_req')])}}
                    </span>
        </div>
    </div>
@else
    <div id="vue-user-profile">
        <profile username="{{$user->username}}"/>
    </div>
@endif

