@php use Carbon\Carbon; @endphp
<div class="card mt-3">
    <div class="card-header">{{ __('settings.title-webhooks') }}</div>
    <div class="card-body">
        @if(count($webhooks) == 0)
            <p class="text-danger">{{__('settings.no-webhooks')}}</p>
        @else
            <table class="table table-responsive">
                <thead>
                    <tr>
                        <th>{{ __('settings.client-name') }}</th>
                        <th>{{ __('settings.created') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($webhooks as $webhook)
                        <tr>
                            <td>{{ $webhook->client->name }}</td>
                            <td>{{ Carbon::parse($webhook->created_at)->isoFormat(__('datetime-format')) }}</td>
                            <td>
                                <form method="post" action="{{ route('delwebhook') }}">
                                    @csrf
                                    <input type="hidden" name="webhookId" value="{{$webhook->id}}"/>
                                    <button class="btn btn-block btn-danger mx-0">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
