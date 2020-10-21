@extends('layouts.app')

@section('title')
    Dashboard
@endsection

@section('content')
{{--    @include('includes.station-autocomplete')--}}
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card status mt-3" id="status-1" data-body="" data-date="Mittwoch, 07. Oktober 2020">

                        <div class="card-body row">
                            <div class="col-2 image-box d-none d-lg-flex">
                                <a href="http://localhost:8000/profile/Gertrud123">
                                    <img src="http://localhost:8000/profile/Gertrud123/profilepicture">
                                </a>
                            </div>

                            <div class="col pl-0">
                                <h3>User XYZ</h3>
                                <small>
                                    <span class="font-weight-bold"><i class="fa fa-route d-inline"></i>&nbsp;15.907,94</span><span class="small font-weight-lighter">km</span>
                                    <span class="font-weight-bold pl-sm-2"><i class="fa fa-stopwatch d-inline"></i>&nbsp;1<small>h</small>&nbsp;30<small>min</small></span>
                                    <span class="font-weight-bold pl-sm-2"><i class="fa fa-dice-d20 d-inline"></i>&nbsp;1593</span><span class="small font-weight-lighter">Pkt</span>
                                </small>
                                <button type="button" class="btn btn-outline-primary float-right">FOLGEN</button>
                            </div>
                        </div>
                        <div class="progress">
                            <div class="progress-bar progress-time" role="progressbar" style="width: 22342.2%;" data-valuenow="1603269554" data-valuemin="1602063077" data-valuemax="1602068477" data-now="1603269558"></div>
                        </div>
                    </div>
                <div class="card status mt-3" id="status-1" data-body="" data-date="Mittwoch, 07. Oktober 2020">

                    <div class="card-body row">
                        <div class="col-2 image-box d-none d-lg-flex">
                            <a href="http://localhost:8000/profile/Gertrud123">
                                <img src="http://localhost:8000/profile/Gertrud123/profilepicture">
                            </a>
                        </div>

                        <div class="col pl-0">
                            <h3>User XYZ</h3>
                            <small>
                                <span class="font-weight-bold"><i class="fa fa-route d-inline"></i>&nbsp;15.907,94</span><span class="small font-weight-lighter">km</span>
                                <span class="font-weight-bold pl-sm-2"><i class="fa fa-stopwatch d-inline"></i>&nbsp;1<small>h</small>&nbsp;30<small>min</small></span>
                                <span class="font-weight-bold pl-sm-2"><i class="fa fa-dice-d20 d-inline"></i>&nbsp;1593</span><span class="small font-weight-lighter">Pkt</span>
                            </small>
                            <button type="button" class="btn btn-outline-primary float-right">FOLGEN</button>
                        </div>
                    </div>
                    <div class="progress">
                        <div class="progress-bar progress-time" role="progressbar" style="width: 22342.2%;" data-valuenow="1603269554" data-valuemin="1602063077" data-valuemax="1602068477" data-now="1603269558"></div>
                    </div>
                </div>
                <div class="card status mt-3" id="status-1" data-body="" data-date="Mittwoch, 07. Oktober 2020">

                    <div class="card-body row">
                        <div class="col-2 image-box d-none d-lg-flex">
                            <a href="http://localhost:8000/profile/Gertrud123">
                                <img src="http://localhost:8000/profile/Gertrud123/profilepicture">
                            </a>
                        </div>

                        <div class="col pl-0">
                            <h3>User XYZ</h3>
                            <small>
                                <span class="font-weight-bold"><i class="fa fa-route d-inline"></i>&nbsp;15.907,94</span><span class="small font-weight-lighter">km</span>
                                <span class="font-weight-bold pl-sm-2"><i class="fa fa-stopwatch d-inline"></i>&nbsp;1<small>h</small>&nbsp;30<small>min</small></span>
                                <span class="font-weight-bold pl-sm-2"><i class="fa fa-dice-d20 d-inline"></i>&nbsp;1593</span><span class="small font-weight-lighter">Pkt</span>
                            </small>
                            <button type="button" class="btn btn-outline-primary float-right">FOLGEN</button>
                        </div>
                    </div>
                    <div class="progress">
                        <div class="progress-bar progress-time" role="progressbar" style="width: 22342.2%;" data-valuenow="1603269554" data-valuemin="1602063077" data-valuemax="1602068477" data-now="1603269558"></div>
                    </div>
                </div>


{{--                @include('includes.statuses', ['statuses' => $statuses, 'showDates' => true])--}}
            </div>
        </div>
        <div class="row justify-content-center mt-5">
{{--            {{ $statuses->links() }}--}}
        </div>
        @include('includes.edit-modal')
        @include('includes.delete-modal')
    </div><!--- /container -->
@endsection
