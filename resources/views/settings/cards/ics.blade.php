<div class="card mt-3" id="ics">
    <div class="card-header">{{ __('settings.title-ics') }}</div>

    <div class="card-body">
        @if(session()->has('ics-success'))
            <div class="alert alert-success">
                {!! session()->get('ics-success') !!}
            </div>
        @endif

        @if(auth()->user()->icsTokens->count() === 0)
            <p class="text-danger">{{__('settings.no-ics-tokens')}}</p>
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th colspan="2">{{ __('settings.token') }}</th>
                            <th>{{ __('settings.created') }}</th>
                            <th>{{ __('settings.last-accessed') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(auth()->user()->icsTokens as $icsToken)
                            <tr>
                                <td>{{$icsToken->name}}</td>
                                <td>{{ substr($icsToken->token, 0, 8) }}<small>*****</small></td>
                                <td>{{ $icsToken->created_at->isoFormat(__('datetime-format')) }}</td>
                                <td>{{ $icsToken?->last_accessed?->isoFormat(__('datetime-format')) ?? __('settings.never') }}</td>
                                <td>
                                    <form method="POST" action="{{route('ics.revokeToken')}}">
                                        @csrf
                                        <input type="hidden" name="id" value="{{$icsToken->id}}"/>
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            {{__('settings.revoke-token')}}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <form method="POST" action="{{route('ics.createToken')}}">
            @csrf
            <div class="input-group mt-0">
                <input type="text" name="name" class="form-control" required
                       placeholder="{{__('settings.ics.name-placeholder')}}"/>
                <button class="btn btn-sm btn-primary m-0 px-3" type="submit">
                    <i class="fas fa-plus"></i>
                    {{__('settings.create-ics-token')}}
                </button>
            </div>
        </form>
    </div>
</div>
