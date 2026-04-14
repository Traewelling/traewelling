@extends(request()->routeIs('embed.*') ? 'layouts.app-embed' : (!auth()->user()?->hasRole('open-beta') ? 'layouts.app' : 'layouts.tailwind-vue-layout'))

@section('title', 'Create trip manually')

@section('content')
    <div id="vue-content" class="mx-0">
        <trip-creation-form></trip-creation-form>
    </div>
@endsection
