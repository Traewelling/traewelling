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
                font-size: 9px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0;
            }

            .footer-wrapper {
                position: fixed;
                bottom: -60px;
                left: 0px;
                right: 0px;
                height: 50px;
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
                    <span class="page-number">{{ __('export.page') }}</span>
                </div>
                <div class="center">
                    0 {{ __('export.reason.private') }} |
                    1 {{ __('export.reason.business') }} |
                    2 {{ __('export.reason.commute') }}
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
                        {{ $begin->isoFormat(__('date-format')) }} &ndash; {{ $end->isoFormat(__('date-format')) }}
                    </td>
                    <td class="username">
                        {{ \Carbon\Carbon::now()->isoFormat(__('date-format')) }}
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
                        <th>{{ __('export.reason') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($statuses as $status)
                        <tr>
                            <td>{{ __('transport_types.'. $status->trainCheckin->HafasTrip->category) }}</td>
                            <td>{{ $status->trainCheckin->HafasTrip->linename }}</td>
                            <td>{{ $status->trainCheckin->Origin->name }}</td>
                            <td>{{ $status->trainCheckin->origin_stopover->departure_planned?->isoFormat(__('datetime-format')) }}</td>
                            <td>{{ $status->trainCheckin->Destination->name }}</td>
                            <td>{{ $status->trainCheckin->destination_stopover->arrival_planned?->isoFormat(__('datetime-format')) }}</td>
                            <td>{{ $status->trainCheckin->duration }} min</td>
                            <td>{{ number($status->trainCheckin->distance / 1000) }} km</td>
                            <td><i>{{ $status->business }}</i></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </body>
</html>
