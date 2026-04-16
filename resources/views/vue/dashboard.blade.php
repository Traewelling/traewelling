@extends(app_layout())

@section('title', 'Dashboard')

@section('content')
    <div class="container">
        <div id="vue-content">
            <vue-dashboard></vue-dashboard>
        </div>
    </div>
@endsection
