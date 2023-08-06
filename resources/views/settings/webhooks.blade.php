@php
    use Carbon\Carbon;
    use App\Enum\WebhookEvent;
@endphp
@extends('layouts.settings')
@section('title', __('settings.title-webhooks'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card mb-3">
                <div class="card-header">{{ __('settings.title-webhooks') }}</div>
                <div class="card-body table-responsive px-0">
                    <p class="mx-4">
                        {{ __('settings.webhook-description') }}
                    </p>
                    @if(count($webhooks) == 0)
                        <p class="text-danger mx-4">{{__('settings.no-webhooks')}}</p>
                    @else
                        <table class="table">
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
        </div>
    </div>
@endsection
