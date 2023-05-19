@php
    use Carbon\Carbon;
    use App\Enum\WebhookEvent;
@endphp
@extends('layouts.settings')

@section('content')
    <div class="card mt-3">
        <div class="card-header">{{ __('settings.title-webhooks') }}</div>
        <div class="card-body">
            <p>
                {{ __('settings.webhook-description') }}
            </p>
            @if(count($webhooks) == 0)
                <p class="text-danger">{{__('settings.no-webhooks')}}</p>
            @else
                <table class="table table-responsive">
                    <thead>
                        <tr>
                            <th>{{ __('settings.client-name') }}</th>
                            <th>{{ __('settings.created') }}</th>
                            <th>{{ __('settings.webhook-event-notifications-description') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($webhooks as $webhook)
                            <tr>
                                <td>{{ $webhook->client->name }}</td>
                                <td>{{ Carbon::parse($webhook->created_at)->isoFormat(__('datetime-format')) }}</td>
                                <td>
                                    <ul>
                                        @foreach(WebhookEvent::cases() as $event)
                                            @if(inBitmask($event->value, $webhook->events))
                                                <li>{{ __('settings.webhook_event.' . $event->name())}}</li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </td>
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
@endsection
