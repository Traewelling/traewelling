<div class="card mt-3">
    <div class="card-header">{{ __('settings.title-ics') }}</div>

    <div class="card-body">
        @if(auth()->user()->icsTokens->count() == 0)
            <p class="text-danger">{{__('settings.no-ics-tokens')}}</p>
        @else
            <table class="table table-responsive">
                <thead>
                    <tr>
                        <th>{{ __('settings.token') }}</th>
                        <th>{{ __('settings.created') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(auth()->user()->icsTokens as $icsToken)
                        <tr>
                            <td>{{ substr($icsToken->token, 0, 8) }}<small>*****</small></td>
                            <td>{{ $icsToken->created_at->format('d.m.Y H:i') }}</td>
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
        @endif

        <form method="POST" action="{{route('ics.createToken')}}">
            @csrf
            <button type="submit" class="btn btn-sm btn-primary">
                {{__('settings.create-ics-token')}}
            </button>
        </form>
    </div>
</div>