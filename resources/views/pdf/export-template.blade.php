<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Träwelling Export</title>
        <style>
            body {
                padding: 0;
                margin: 0;
            }

            .export-container {
                width: 100%;
                margin: 0;
                font-size: 16px;
                line-height: 24px;
                font-family: 'Helvetica Neue', 'Helvetica', 'Helvetica', 'Arial', sans-serif;
                color: #555;
            }

            .product-icon {
                width: 16px;
                height: 16px;
                margin-right: -100%;
            }

            td > .product-icon {
                padding: 0;
                max-width: 20px;
            }

            .export-container .top {
                page-break-after: avoid;
            }

            .export-container .heading {
                font-size: 2em;
                font-weight: bold;
                text-align: center;
                vertical-align: middle;
            }

            .export-container .username {
                text-align: center;
                vertical-align: middle;
            }

            .export-container table {
                width: 100%;
                line-height: inherit;
                text-align: left;
            }

            .export-container table thead tr {
                background: #CCC;
                border-bottom: 1px solid #DDD;
                font-weight: bold;
            }

            .export-container table td {
                padding: 5px;
                vertical-align: top;
            }

            .export-container table tr:nth-child(even) {
                background: #EEE
            }

            .footer .page-number:after {
                content: counter(page);
            }

            .footer {
                position: fixed;
                bottom: -60px;
                left: 0px;
                right: 0px;
                height: 50px;
            }

            .right {
                float: right;
            }

        </style>
    </head>
    <body>
        <div class="footer fixed-section">
            <div class="right">
                <span class="page-number">{{ __('export.page') }} </span>
            </div>
            <div class="left">
                <span class="promo">{!! __('export.guarantee', ['url' => url('/'), 'name' => config('app.name', 'Träwelling')]) !!}</span>
            </div>
        </div>
        <div class="export-container">
            <table class="top">
                <tr>
                    <td><img src="{{ public_path('images/icons/logo128.png') }}" height="64"></td>
                    <td class="heading">{{ config('app.name', 'Träwelling') }} {{ __('export.export') }}:
                        {{ $start_date }} &ndash; {{ $end_date }}</td>
                    <td class="username">{{ date('Y-m-d') }}<br>{{ $name }}</td>
                </tr>
            </table>
            <table cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <th>{{ __('export.type') }}</th>
                        <th>{{ __('export.number') }}</th>
                        <th>{{ __('export.origin') }}</th>
                        <th>{{ __('export.departure') }}</th>
                        <th>{{ __('export.destination') }}</th>
                        <th>{{ __('export.arrival') }}</th>
                        <th>{{ __('export.duration') }}</th>
                        <th>{{ __('export.kilometers') }}</th>
                        <th>{{ __('export.reason') }}</th>
                        {{--            TODO IMPORTANT: This translation has to be moved to the json, as soon as this branch is up to date --}}
                    </tr>
                </thead>
                <tbody>
                    @foreach($export as $e)
                        <tr>
                            <td>{{ __('transport_types.'.$e[1]) }}</td>
                            <td>{{ $e[2] }}</td>
                            <td>{{ $e[3] }}</td>
                            <td>{{ $e[5] }}</td>
                            <td>{{ $e[6] }}</td>
                            <td>{{ $e[8] }}</td>
                            <td>{{ $e[9] }}</td>
                            <td>{{ $e[10] }}</td>
                            <td><i class="fa fa-
                            @if($e[14] == 2)
                                        building
@elseif($e[14] == 1)
                                        briefcase
@else
                                        user
@endif
                                        "></i></td>
                            {{--                            ToDo This is not showing up yet... weird.--}}
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </body>
</html>
