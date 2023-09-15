<!DOCTYPE html>
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
                font-size: 0.8em;
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
                background: #EEE;
            }

            .export-container table tfoot {
                border-top: 4px double black;
            }

            .footer .page-number:after {
                content: counter(page);
            }

            .footer {
                font-size: 9px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0;
            }

            .footer-wrapper {
                position: fixed;
                bottom: -60px;
                left: 0;
                right: 0;
                height: 50px;
            }

            .number-field {
                text-align: right;
                white-space: nowrap;
            }

            .right {
                float: right;
            }

            .center {
                text-align: center;
                font-style: italic;
            }
        </style>
    </head>
    <body>
        <div class="footer-wrapper">
            <div class="footer fixed-section">
                <div class="right">
                    <span class="page-number">{{ __('export.page') }} </span>
                </div>
                <div class="center">
                    *: 0- {{ __('export.reason.private') }} | 1- {{ __('export.reason.business') }} |
                    2- {{ __('export.reason.commute') }}
                </div>
                <div class="left">
                    <span class="promo">
                        {!! __('export.guarantee', ['url' => url('/'), 'name' => config('app.name', 'Träwelling')]) !!}
                    </span>
                </div>
            </div>
        </div>
        <div class="export-container">
            <table class="top">
                <tr>
                    <td>
                        <img src="{{ public_path('images/icons/logo128.png') }}" height="64"/>
                    </td>
                    <td class="heading">
                        {{ config('app.name', 'Träwelling') }} {{ __('export.export') }}:
                        {{ userTime($begin, __('date-format')) }} &ndash; {{ userTime($end, __('date-format')) }}
                    </td>
                    <td class="username">
                        {{ userTime(now(), __('date-format')) }}
                        <br>
                        {{ auth()->user()->username }}
                    </td>
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
                        <th>{{ __('export.reason') }}*</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($statuses as $status)
                        <tr>
                            <td>{{ __('transport_types.' . $status->trainCheckin->HafasTrip->category->value) }}</td>
                            <td>{{ $status->trainCheckin->HafasTrip->linename }}</td>
                            <td>{{ $status->trainCheckin->originStation->name }}</td>
                            <td>
                                @if(isset($status->trainCheckin->manual_departure))
                                    {{ userTime($status->trainCheckin->manual_departure, __('datetime-format')) }}
                                @elseif($status->trainCheckin->origin_stopover->isDepartureDelayed)
                                    <span style="text-decoration: line-through;">
                                        {{ userTime($status->trainCheckin->origin_stopover->departure_planned, __('datetime-format')) }}
                                    </span>
                                    <br/>
                                    {{ userTime($status->trainCheckin->origin_stopover->departure_real, __('datetime-format')) }}
                                @else
                                    {{ userTime($status->trainCheckin->origin_stopover->departure_planned, __('datetime-format')) }}
                                @endif
                            </td>
                            <td>{{ $status->trainCheckin->destinationStation->name }}</td>
                            <td>
                                @if(isset($status->trainCheckin->manual_arrival))
                                    {{ userTime($status->trainCheckin->manual_arrival, __('datetime-format')) }}
                                @elseif($status->trainCheckin->origin_stopover->isArrivalDelayed)
                                    <span style="text-decoration: line-through;">
                                        {{ userTime($status->trainCheckin->destination_stopover->arrival_planned, __('datetime-format')) }}
                                    </span>
                                    <br/>
                                    {{ userTime($status->trainCheckin->destination_stopover->arrival_real, __('datetime-format')) }}
                                @else
                                    {{ userTime($status->trainCheckin->destination_stopover->arrival_planned, __('datetime-format')) }}
                                @endif
                            </td>
                            <td class="number-field">{{ $status->trainCheckin->duration }} min</td>
                            <td class="number-field">{{ number($status->trainCheckin->distance / 1000) }} km</td>
                            <td class="number-field"><i>{{ $status->business->value }}</i></td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style="font-style: italic;">{{ __('export.total') }}:</td>
                        <td class="number-field">{{ $sum_duration }} min</td>
                        <td class="number-field">{{ number($sum_distance) }} km</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

    </body>
</html>
