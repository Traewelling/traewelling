@extends('layouts.app')

@section('title')
    Dashboard
@endsection

@section('content')
@include('includes.station-autocomplete')
<div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                @foreach($statuses as $status)
                            <div class="card status mt-3" data-statusid="{{ $status->id }}">
                                <div class="card-img-top">
                                    {{$status->trainCheckin->getMapLines()}}
                                </div>
                                <div class="card-body">
                                    <ul class="timeline">
                                        <li>
                                            <span class="text-trwl">{{ $status->trainCheckin->getOrigin->name }} </span>
                                            <span class="text-trwl float-right">{{ date('H:i', strtotime($status->trainCheckin->departure)) }} Uhr</span>
                                            <p class="train-status">{{ $status->trainCheckin->getHafasTrip->linename }}</p>
                                            @if(!empty($status->body))
                                                <p class="status-body">{{ $status->body }}</p>
                                            @endif
                                        </li>
                                        <li>
                                            <span class="text-trwl">{{ $status->trainCheckin->getDestination->name }}</span>
                                            <span class="text-trwl float-right">{{ date('H:i', strtotime($status->trainCheckin->arrival)) }} Uhr</span>
                                        </li>
                                    </ul>
                                </div>
                                <div class="card-footer text-muted interaction">
                                    <span class="float-right"><a href="{{ route('account.show', ['username' => $status->user->username]) }}">{{ $status->user->username }}</a> on {{ $status->created_at }}</span>
                                    <a href="#" class="like">{{ $status->likes->where('user_id', Auth::user()->id)->first() === null ? 'Like' : 'Dislike'}}</a>
                                    @if(Auth::user() == $status->user)
                                        |
                                        <a href="#" class="edit">Edit</a> |
                                        <a href="#" class="delete">Delete</a>
                                    @endif
                                </div>
                            </div>
                @endforeach
            </div>
        </div>

        <div class="modal fade" tabindex="-1" role="dialog" id="edit-modal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Edit status</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="form-group">
                                <label for="status-body">Edit the status</label>
                                <textarea class="form-control" name="status-body" id="status-body" rows="5"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="modal-save">Save changes</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
    </div><!--- /container -->
@endsection
