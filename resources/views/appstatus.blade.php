@extends('layouts.app')

@section('title')
    Application Status
@endsection

@section('content')
    <div class="container" id="appstatus">
        <div class="row">
            <div class="col-md-12">
                <h1>Application Status <code>{{ substr(get_current_git_commit(), 0, 6) }}</code></h1>
                
                <?php
                

                function fmt($i) {
                    return number_format($i, 0, ',', '.');
                }
                function formatNumber($i, $unit = "") {
                    $number = fmt($i);

                    return '<span class="' . ($i < 0 ? 'bad' : 'good' . '">+')  . $number . '<small>' . $unit . '</small></span>';
                }

                function fmtInterval($secs) {
                    // 5<small>h</small> 10<small>m</small>
                    $mins = $secs / 60;
                    $hours = floor($mins / 60);
                    $mins = $mins - 60 * $hours;

                    return (($hours > 0) ? $hours . "<small>h</small> " : "")
                      . $mins . "<small>m</small>";
                }
                function fmtIntervalSpan($secs) {
                    $begin = '<span class="good">+';
                    if ($secs < 0) {
                        $begin = '<span class="bad">-';
                    }
                    return $begin . fmtInterval(abs($secs)) . '</span>';
                }
                ?>

                <div class="row pb-4">
                    <div class="col">
                        <dd>Registered Users</dd>
                        <dt class="display-4">{{fmt($all_users)}} <small>users</small></dt>
                        {!! formatNumber($users_last_week) !!} last week
                    </div>
                    <div class="col">
                        <dd>Trips</dd>
                        <dt class="display-4">{{fmt($all_trips)}} <small>trips</small></dt>
                        {!! formatNumber($trips_last_week) !!} last week
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
                        <dt class="display-4">{{fmt($trips_last_day)}} <small>trips</small></dt>
                        {!! formatNumber($trips_day_before) !!} compared to day before
                    </div>
                    <div class="col-sm-4">
                        <dd>last 24 hours</dd>
                        <dt class="display-4">{{fmt($distance_last_day)}}<small>km</small></dt>
                        {!! formatNumber($distance_day_before, "km") !!}  compared to day before
                    </div>
                    <div class="col-sm-4">
                        <dd>last 24 hours</dd>
                        <dt class="display-4">{!! fmtInterval($time_last_day) !!}</dt>
                        {!! fmtIntervalSpan($time_last_day) !!} compared to day before
                    </div>
                </div>
                <div class="row pt-4 pb-4">
                    <div class="col-sm-4">
                        <dd>last week</dd>
                        <dt class="display-4">{{fmt($trips_last_week)}} <small>trips</small></dt>
                        {!! formatNumber($trips_week_before) !!} compared to week before
                    </div>
                    <div class="col-sm-4">
                        <dd>last week</dd>
                        <dt class="display-4">{{fmt($distance_last_week)}}<small>km</small></dt>
                        {!! formatNumber($distance_week_before, "km") !!}  compared to day before
                    </div>
                    <div class="col-sm-4">
                        <dd>last week</dd>
                        <dt class="display-4">{!! fmtInterval($time_last_week) !!}</dt>
                        {!! fmtIntervalSpan($time_last_week) !!} compared to week before
                    </div>
                </div>
                <div class="row pt-4 pb-4">
                    <div class="col-sm-4">
                        <dd>last month</dd>
                        <dt class="display-4">{{fmt($trips_last_month)}} <small>trips</small></dt>
                        {!! formatNumber($trips_month_before) !!} compared to month before
                    </div>
                    <div class="col-sm-4">
                        <dd>last month</dd>
                        <dt class="display-4">{{fmt($distance_last_month)}}<small>km</small></dt>
                        {!! formatNumber($distance_month_before, "km") !!}  compared to day before
                    </div>
                    <div class="col-sm-4">
                        <dd>last month</dd>
                        <dt class="display-4">{!! fmtInterval($time_last_month) !!}</dt>
                        {!! fmtIntervalSpan($time_last_month) !!} compared to month before
                    </div>
                </div>

                <div class="row pb-4">
                    <?php
                    function toMiB($in) {
                        if($in > 1024 * 1024 * 1024) {
                            return round(100 * $in / (1024 * 1024 * 1024)) / 100 . '<small>GiB</small>';
                        }
                        if($in > 1024 * 1024) {
                            return round(100 * $in / (1024 * 1024)) / 100 . '<small>MiB</small>';
                        }
                        if($in > 1024) {
                            return $in / 1024 . '<small>KiB</small>';
                        }
                    }
                    ?>
                    <div class="col">
                        <dd>Database size</dd>
                        <dt class="display-4">{!! toMiB($db_size) !!}</dt>
                    </div>
                    <div class="col">
                        <dd><code>hafas_trip</code> table</dd>
                        <dt class="display-4">{!! toMiB($hafas_trip_size) !!}</dt>
                    </div>
                </div>
            </div>
        </div>
    </div><!--- /container -->
@endsection
