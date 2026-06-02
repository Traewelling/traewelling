@extends(appLayout())

@section('title', html_entity_decode(__('privacy.title')))
@section('meta-robots', 'noindex')

@section('content')
    <style>
        .cookiealert { /* Wir wollen keine doppelten Bottom-Bars auf der Privacy-Seite. */
            display: none;
        }
    </style>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                <div id="vue-privacy-intercept">
                    <privacy-intercept username="{{ auth()->user()?->username ?? '' }}"></privacy-intercept>
                </div>
            </div>
        </div>
    </div>
@endsection
