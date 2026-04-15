@extends(request()->routeIs('embed.*') ? 'layouts.app-embed' : (!auth()->user()?->hasRole('open-beta') ? 'layouts.app' : 'layouts.tailwind-vue-layout'))

@section('title', __('menu.active'))

@section('meta-robots', 'index')
@section('meta-description', __('description.en-route'))

@section('content')
    <div class="container">
        <div id="vue-content">
            <active-journeys></active-journeys>
        </div>
    </div>
@endsection
