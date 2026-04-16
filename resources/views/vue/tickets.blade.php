@extends(app_layout())

@section('title', __('tickets.title'))

@section('content')
    <div class="container">
        <div id="vue-content">
            <tickets></tickets>
        </div>
    </div>
@endsection
