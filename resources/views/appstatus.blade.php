@extends('layouts.app')

@section('title')
    Application Status
@endsection

@section('content')
    <div class="container" id="appstatus">
        <div class="row">
            <div class="col-md-12">
                <h1>Application Status <code>{{ substr(get_current_git_commit(), 0, 6) }}</code></h1>
                
                <div class="row pb-4">
                    <div class="col">
                        <dd>Registered Users</dd>
                        <dt class="display-4">{{$users}} <small>users</small></dt>
                        <span class="good">+{{$users_last_week}}</span> last week
                    </div>
                    <div class="col">
                        <dd>Trips</dd>
                        <dt class="display-4">3.200 <small>trips</small></dt>
                        <span class="good">+58</span> last week
                    </div>
                </div>
                <div class="row d-none d-sm-flex">
                    <div class="col">
                        <dd><strong># Trips</strong></dd>
                    </div>
                    <div class="col">
                        <dd><strong>&Sigma; Distance</strong></dd>
                    </div>
                    <div class="col">
                        <dd><strong>&Sigma; Time</strong></dd>
                    </div>
                </div>
                <div class="row pt-4 pb-4">
                    <div class="col-sm-4">
                        <dd>last 24 hours</dd>
                        <dt class="display-4">15 <small>trips</small></dt>
                        <span class="bad">-2</span> compared to day before
                    </div>
                    <div class="col-sm-4">
                        <dd>last 24 hours</dd>
                        <dt class="display-4">1.259<small>km</small></dt>
                        <span class="good">+30km</span> compared to day before
                    </div>
                    <div class="col-sm-4">
                        <dd>last 24 hours</dd>
                        <dt class="display-4">5<small>h</small> 10<small>m</small></dt>
                        <span class="bad">-43m</span> compared to day before
                    </div>
                </div>
                <div class="row pt-4 pb-4">
                    <div class="col-sm-4">
                        <dd>last week</dd>
                        <dt class="display-4">58 <small>trips</small></dt>
                        <span class="good">+13</span> compared to week before
                    </div>
                    <div class="col-sm-4">
                        <dd>last week</dd>
                        <dt class="display-4">5.801<small>km</small></dt>
                        <span class="good">+94km</span> compared to week before
                    </div>
                    <div class="col-sm-4">
                        <dd>last week</dd>
                        <dt class="display-4">34<small>h</small> 20<small>m</small></dt>
                        <span class="bad">-2h 54m</span> compared to week before
                    </div>
                </div>
                <div class="row pt-4 pb-4">
                    <div class="col-sm-4">
                        <dd>last month</dd>
                        <dt class="display-4">238 <small>trips</small></dt>
                        <span class="good">+67</span> compared to month before
                    </div>
                    <div class="col-sm-4">
                        <dd>last month</dd>
                        <dt class="display-4">35.794<small>km</small></dt>
                        <span class="good">+308km</span> compared to month before
                    </div>
                    <div class="col-sm-4">
                        <dd>last month</dd>
                        <dt class="display-4">156<small>h</small> 12<small>m</small></dt>
                        <span class="good">+44h 38m</span> compared to month before
                    </div>
                </div>

                <div class="row pb-4">
                    <div class="col">
                        <dd>Database size</dd>
                        <dt class="display-4">4.2 <small>MiB</small></dt>
                    </div>
                    <div class="col">
                        <dd><code>hafas_trip</code> table</dd>
                        <dt class="display-4">2.4 <small>MiB</small></dt>
                    </div>
                </div>
            </div>
        </div>
    </div><!--- /container -->
@endsection
