@extends('layouts.app')

@section('title')
    Dashboard
@endsection

@section('content')
<div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="home-tab" data-toggle="tab" href="#top20" role="tab" aria-controls="home" aria-selected="true">{{ __('Top 20') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="profile-tab" data-toggle="tab" href="#top20k" role="tab" aria-controls="profile" aria-selected="false">{{ __('Top 20 Kilometers') }}</a>
                    </li>
                    @if($friends != null)
                    <li class="nav-item">
                        <a class="nav-link" id="contact-tab" data-toggle="tab" href="#top20f" role="tab" aria-controls="contact" aria-selected="false">{{ __('Top 20 Friends') }}</a>
                    </li>
                    @endif
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="top20" role="tabpanel" aria-labelledby="home-tab">
                        <table class="table">
                            <thead>
                            <tr>
                                <td>{{ __('Rank') }}</td>
                                <td>{{ __('User') }}</td>
                                <td>{{ __('Duration') }}</td>
                                <td>{{ __('Distance') }}</td>
                                <td>{{ __('Points') }}</td>
                            </tr>
                            </thead>
                            @foreach($users as $key=>$user)
                                <tr>
                                    <td>{{ $key+1 }}</td>
                                    <td><a href="{{ route('account.show', ['username' => $user->username]) }}">{{ $user->username }}</a></td>
                                    <td>{{ date('H:i', mktime(0,$user->train_duration)) }}</td>
                                    <td>{{ $user->train_distance }} km</td>
                                    <td>{{ $user->points }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                    <div class="tab-pane fade" id="top20k" role="tabpanel" aria-labelledby="profile-tab">
                        <table class="table">
                            <thead>
                            <tr>
                                <td>{{ __('Rank') }}</td>
                                <td>{{ __('User') }}</td>
                                <td>{{ __('Duration') }}</td>
                                <td>{{ __('Distance') }}</td>
                                <td>{{ __('Points') }}</td>
                            </tr>
                            </thead>
                            @foreach($kilometers as $key=>$user)
                                <tr>
                                    <td>{{ $key+1 }}</td>
                                    <td><a href="{{ route('account.show', ['username' => $user->username]) }}">{{ $user->username }}</a></td>
                                    <td>{{ date('H:i', mktime(0,$user->train_duration)) }}</td>
                                    <td>{{ $user->train_distance }} km</td>
                                    <td>{{ $user->points }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                    @if($friends != null)
                    <div class="tab-pane fade" id="top20f" role="tabpanel" aria-labelledby="contact-tab">
                        <table class="table">
                            <thead>
                            <tr>
                                <td>{{ __('Rank') }}</td>
                                <td>{{ __('User') }}</td>
                                <td>{{ __('Duration') }}</td>
                                <td>{{ __('Distance') }}</td>
                                <td>{{ __('Points') }}</td>
                            </tr>
                            </thead>
                            @foreach($friends as $key=>$user)
                                <tr>
                                    <td>{{ $key+1 }}</td>
                                    <td><a href="{{ route('account.show', ['username' => $user->username]) }}">{{ $user->username }}</a></td>
                                    <td>{{ date('H:i', mktime(0,$user->train_duration)) }}</td>
                                    <td>{{ $user->train_distance }} km</td>
                                    <td>{{ $user->points }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                    @endif
                </div>



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
