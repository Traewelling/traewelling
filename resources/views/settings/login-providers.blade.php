@extends('layouts.settings')

@section('content')
    <div class="card mt-3">
        <div class="card-header">{{ __('settings.title-loginservices') }}</div>

        <div class="card-body">
            <table class="table table-responsive">
                <tbody>
                    <tr>
                        <td>
                            <i class="fab fa-twitter"></i>
                            Twitter
                        </td>
                        @if (auth()->user()?->socialProfile?->twitter_id != null)
                            <td class="align-middle d-none d-md-table-cell">
                                <a href="https://blog.traewelling.de/posts/twitter-deprecation/" target="_blank">
                                    <i class="fa fa-link"></i> {{ __('settings.twitter-deprecated') }}
                                </a>
                            </td>
                            <td class="align-middle">
                                <a href="javascript:void(0)" data-provider="twitter"
                                   class="btn btn-sm btn-outline-danger disconnect">
                                    {{ __('settings.disconnect') }}
                                </a>
                            </td>
                        @else
                            <td class="align-middle">
                                <a href="https://blog.traewelling.de/posts/twitter-deprecation/" target="_blank">
                                    <i class="fa fa-link"></i> {{ __('settings.twitter-deprecated') }}
                                </a>
                            </td>
                            <td class="align-middle d-none d-md-table-cell"></td>
                        @endif
                    </tr>
                    <tr>
                        <td>
                            <i class="fab fa-mastodon"></i>
                            Mastodon
                        </td>
                        @if (auth()->user()?->socialProfile?->mastodon_id != null)
                            <td class="text-success d-none d-md-table-cell">
                                <i class="fas fa-check"></i>
                                {{ __('settings.connected') }}
                            </td>
                            <td>
                                <a href="javascript:void(0)" data-provider="mastodon"
                                   class="btn btn-sm btn-outline-danger disconnect">
                                    {{ __('settings.disconnect') }}
                                </a>
                            </td>
                        @else
                            <td class="text-danger d-none d-md-table-cell">
                                <i class="fas fa-times"></i>
                                {{ __('settings.notconnected') }}
                            </td>
                            <td>
                                <a
                                        href="#"
                                        data-mdb-target="#mastodon-auth"
                                        data-mdb-toggle="modal"
                                        class="btn btn-md btn-primary m-0 px-3"
                                >
                                    <i class="fab fa-mastodon"></i> {{ __('settings.connect') }}
                                </a>
                            </td>
                        @endif
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    @include('includes.modals.mastodon-auth')
@endsection
