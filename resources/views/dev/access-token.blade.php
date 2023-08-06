<div class="card mb-3">
    <div class="card-header">{{__('your-access-token')}}</div>
    <div class="card-body">

        @if(request()->session()->has('token'))
            <p class="text-success">{{__('access-token-generated-success')}}</p>
            <p class="text-warning">{{__('access-token-remove-at')}}</p>
            <hr/>
            <small><code>{{ request()->session()->get('token') }}</code></small>
        @else
            <p>{{__('your-access-token-description')}}</p>
            <form method="POST" action="{{route('dev.apps.createPersonalAccessToken')}}"
                  class="text-center">
                @csrf
                <button class="btn btn-lg btn-outline-primary">
                    <i class="fa-regular fa-square-plus"></i>
                    {{__('generate-token')}}
                </button>
            </form>
        @endif
    </div>
    <div class="card-footer">
        <small>{{__('access-token-is-private')}}</small>
    </div>
</div>
