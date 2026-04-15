@extends(request()->routeIs('embed.*') ? 'layouts.app-embed' : (!auth()->user()?->hasRole('open-beta') ? 'layouts.app' : 'layouts.tailwind-vue-layout'))

@section('title', __('tickets.title'))

@section('content')
    <div class="container">
        <div id="vue-content">
            <tickets></tickets>
        </div>
    </div>
@endsection
