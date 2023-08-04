@extends('layouts.settings')

@section('content')
    <div class="card mt-3">
        <div class="card-header">{{ __('settings.title-tokens') }}</div>
        <div class="card-body table-responsive px-0">
            @if(count($tokens) == 0)
                <p class="text-danger mx-4">{{__('settings.no-tokens')}}</p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('settings.client-name') }}</th>
                            <th>{{ __('settings.created') }}</th>
                            <th>{{ __('settings.expires') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tokens as $token)
                            <tr>
                                <td>{{ $token->client->name }}</td>
                                <td>{{ userTime($token->created_at, __('datetime-format')) }}</td>
                                <td>{{ $token->expires_at->diffForHumans() }}</td>
                                <td>
                                    <form method="POST" action="{{ route('deltoken') }}">
                                        @csrf
                                        <input type="hidden" name="tokenId" value="{{$token->id}}"/>
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
