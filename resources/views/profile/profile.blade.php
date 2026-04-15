@extends(request()->routeIs('embed.*') ? 'layouts.app-embed' : (!auth()->user()?->hasRole('open-beta') ? 'layouts.app' : 'layouts.tailwind-vue-layout'))

@section('title', $user->name)
@section('canonical', route('profile', ['username' => $user->username]))

@if($user->prevent_index)
    @section('meta-robots', 'noindex')
@else
    @section('meta-description', __('description.profile', [
        'username' => $user->name,
        'kmAmount' => number($user->train_distance / 1000, 0),
        'hourAmount' => number($user->train_duration / 60, 0)
    ]))
@endif

@section('content')
    @php /** @var \App\Models\User $user */ @endphp
        <div id="vue-user-profile">
            <profile username="{{$user->username}}"/>
        </div>
@endsection
