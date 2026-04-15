@extends(request()->routeIs('embed.*') ? 'layouts.app-embed' : (!auth()->user()?->hasRole('open-beta') ? 'layouts.app' : 'layouts.tailwind-vue-layout'))

@section('title', __('tickets.title'))

@section('content')
    <div class="container">
        <div id="vue-content">
            <ticket-detail :ticket-id="{{ json_encode(request()->route('id')) }}"></ticket-detail>
        </div>
    </div>
@endsection
