@extends(appLayout())

@section('title', __('stats'))

@section('head')
    @parent
@endsection

@section('content')
    <div class="container">
        <div id="vue-content">
            <div id="vue-stats">
                <stats-dashboard></stats-dashboard>
            </div>
        </div>
    </div>
@endsection
