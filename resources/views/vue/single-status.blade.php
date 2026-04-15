@extends(request()->routeIs('embed.*') ? 'layouts.app-embed' : (!auth()->user()?->hasRole('open-beta') ? 'layouts.app' : 'layouts.tailwind-vue-layout'))

@section('title', $username ? __('status.ogp-title', ['name' => $username]) : 'Träwelling')

@section('content')
    <div id="vue-content" class="container">
        <single-status></single-status>
    </div>
@endsection
