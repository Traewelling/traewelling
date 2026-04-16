@extends(appLayout())

@section('title', $username ? __('status.ogp-title', ['name' => $username]) : 'Träwelling')

@section('content')
    <div id="vue-content" class="container">
        <single-status></single-status>
    </div>
@endsection
