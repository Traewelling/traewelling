@extends('layouts.settings')
@section('title', __('settings.title-loginservices'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card mb-3">
                <div class="card-header">{{ __('settings.title-loginservices') }}</div>

                <div class="card-body table-responsive px-0">
                    <table class="table">
                        <tbody>
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
                                        data-bs-target="#mastodon-auth"
                                        data-bs-toggle="modal"
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
        </div>
    </div>

    @include('includes.modals.mastodon-auth')
@endsection
