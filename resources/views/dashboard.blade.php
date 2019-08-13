@extends('layouts.app')

@section('title')
    Dashboard
@endsection

@section('content')
@include('includes.station-autocomplete')
        <div class="row justify-content-center">
            <div class="col-md-8">
                <header><h3>What do you have to say?</h3></header>
                <form action="{{ route('status.create') }}" method="post">
                    <div class="form-group">
                        <textarea class="form-control" name="body" id="new-status" rows="5" placeholder="Your status"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Create status</button>
                    <input type="hidden" value="{{ Session::token() }}" name="_token">
                </form>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <header><h3>What other people say...</h3></header>
                @foreach($statuses as $status)
                    <div class="row">
                        <div class="col">
                            <div class="card status" data-statusid="{{ $status->id }}">
                                <div class="card-body">
                                    <p class="card-text">{{ $status->body }}</p>
                                </div>
                                <div class="card-footer text-muted interaction">
                                    <a href="{{ route('account.show', ['username' => $status->user->username]) }}">{{ $status->user->username }}</a> on {{ $status->created_at }} <br>
                                    <a href="#" class="like">{{ $status->likes->where('user_id', Auth::user()->id)->first() === null ? 'Like' : 'Dislike'}}</a>
                                    @if(Auth::user() == $status->user)
                                        |
                                        <a href="#" class="edit">Edit</a> |
                                        <a href="#" class="delete">Delete</a>
                                    @endif
                                </div>
                            </div>
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
