@if($user->id == Auth::user()->id)
    <a href="{{ route('settings') }}" class="btn btn-sm btn-primary">{{ __('profile.settings') }}</a>
@elseif(($user->private_profile || $user->follower_approval) && $user->followRequests->contains('user_id', Auth::user()->id))
    <a href="#" class="btn btn-sm btn-primary disabled" aria-disabled="true">{{ __('profile.follow_req.pending') }}</a>
@elseif(($user->private_profile || $user->follower_approval) && !auth()->user()->follows->contains('id', $user->id))
    <a href="#" class="btn btn-sm btn-primary follow" data-userid="{{ $user->id }}"
       data-following="no" data-private="yes">{{ __('profile.follow_req') }}</a>
@else
    @if(!auth()->user()->follows->contains('id', $user->id))
        <a href="#" class="btn btn-sm btn-primary follow" data-userid="{{ $user->id }}"
           data-following="no" data-private="{{ $user->private_profile || $user->follower_approval ? 'yes' : 'no' }}">
            {{__('profile.follow')}}
        </a>
    @else
        <a href="#" class="btn btn-sm btn-danger follow" data-userid="{{ $user->id }}" data-following="yes"
           data-private="{{ $user->private_profile || $user->follower_approval ? 'yes' : 'no' }}">
            {{__('profile.unfollow')}}
        </a>
    @endif
@endif
<script>
    window.translFollow   = "{{__('profile.follow')}}";
    window.translUnfollow = "{{__('profile.unfollow')}}";
    window.translPending  = "{{__('profile.follow_req.pending')}}";
</script>
