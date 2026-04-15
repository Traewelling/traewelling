@extends(request()->routeIs('embed.*') ? 'layouts.app-embed' : (!auth()->user()?->hasRole('open-beta') ? 'layouts.app' : 'layouts.tailwind-vue-layout'))

@section('title', 'Dashboard')

@section('content')
    <div class="container">
        <div id="vue-content">
            <vue-dashboard></vue-dashboard>
        </div>
    </div>
@endsection
