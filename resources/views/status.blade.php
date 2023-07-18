@extends('layouts.app')

@section('title', $title)
@section('canonical', route('statuses.get', ['id' => $status->id]))

@if($status->user->prevent_index)
    @section('meta-robots', 'noindex')
@else
    @section('meta-description', $status->description)
@endif

@section('head')
    @parent
    <meta property="og:title" content="{{ $title }}"/>
    <meta property="og:type" content="website"/>
    <meta property="og:url" content="{{ url('/status/'.$status->id)  }}"/>
    <meta property="og:image" content="{{ $image }}?{{ $status->user->updated_at->timestamp }}"/>
    <meta property="og:description" content="{{ $description }}"/>

    <meta name="twitter:card" content="summary"/>
    <meta name="twitter:site" content="@traewelling"/>
    <meta name="twitter:title" content="{{ $title }}"/>
    <meta name="twitter:description" content="{{ $description }}"/>
    <meta name="twitter:image" content="{{ $image }}?{{ $status->user->updated_at->timestamp }}"/>
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                <h2 class="fs-5">{{ userTime($status->trainCheckin->departure,__('dateformat.with-weekday')) }}</h2>
                @include('includes.status')

                @if(isset($status->trainCheckin->HafasTrip->last_refreshed) && \Illuminate\Support\Facades\Date::now()->isBefore($status->created_at->clone()->addDay()))
                    <small class="text-muted">
                        {{__('real-time-last-refreshed')}}
                        {{$status->trainCheckin->HafasTrip->last_refreshed->diffForHumans()}}
                    </small>
                @endif

                @if($status->trainCheckin->alsoOnThisConnection->count() > 0)

                    <div class="card">
                        <div class="card-body">
                            <h3 class="fs-4 text-trwl">
                                <i class="fa-solid fa-users-between-lines"></i>
                                Also on this connection
                            </h3>
                            <div class="table-responsive">
                                <table class="table text-center align-middle">
                                    @foreach($status->trainCheckin->alsoOnThisConnection as $otherStatus)
                                        <tr>
                                            <td>
                                                <div class="image-box pe-0 d-lg-flex" style="width: 4em; height: 4em;">
                                                    <a href="{{ route('statuses.get', ['id' => $otherStatus->id]) }}">
                                                        <img
                                                            src="{{ \App\Http\Controllers\Backend\User\ProfilePictureController::getUrl($otherStatus->user) }}"
                                                            alt="{{$otherStatus->user->username}}"
                                                            loading="lazy"
                                                            decoding="async"
                                                        />
                                                    </a>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="{{ route('statuses.get', ['id' => $otherStatus->id]) }}">
                                                    {{ $otherStatus->user->name }} <br/>
                                                    <small>{{ '@' . $otherStatus->user->username }}</small>
                                                </a>
                                            </td>
                                            <td>
                                                {{$otherStatus->trainCheckin->originStation->name}}
                                            </td>
                                            <td><i class="fa-solid fa-arrow-right"></i></td>
                                            <td>
                                                {{$otherStatus->trainCheckin->destinationStation->name}}
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        @if(auth()->check() && auth()->user()->id == $status->user_id)
            @include('includes.edit-modal')
            @include('includes.delete-modal')
        @endif
    </div>
@endsection
