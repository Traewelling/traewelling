<div class="card mt-3">
    <div class="card-header">{{ __('settings.title-tokens') }}</div>
    <div class="card-body">
        @if(count($tokens) == 0)
            <p class="text-danger">{{__('settings.no-tokens')}}</p>
        @else
            <table class="table table-responsive">
                <thead>
                    <tr>
                        <th>{{ __('settings.client-name') }}</th>
                        <th>{{ __('settings.created') }}</th>
                        <th>{{ __('settings.updated') }}</th>
                        <th>{{ __('settings.expires') }}</th>
                        <th></th>
                    </tr>
                </thead>
                @foreach($tokens as $token)
                    <tr>
                        <td>{{ $token['clientName'] }}</td>
                        <td>{{ $token['created_at'] }}</td>
                        <td>{{ $token['updated_at'] }}</td>
                        <td>{{ $token['expires_at'] }}</td>
                        <td>
                            <form method="POST" action="{{ route('deltoken') }}">
                                @csrf
                                <input type="hidden" name="tokenId" value="{{$token['id']}}"/>
                                <button class="btn btn-block btn-danger mx-0">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>

                        </td>
                    </tr>
                @endforeach
            </table>
        @endif
    </div>
</div>