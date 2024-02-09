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

            .right {
                float: right;
            }

            .sum {
                font-weight: bold;
                border-top: 2px solid black;
            }
        </style>
    </head>
    <body>
        <div class="footer-wrapper">
            <div class="footer fixed-section">
                <div class="right">
                    <span class="page-number">{{ __('export.page') }} </span>
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
                        @foreach($columns as $column)
                            <th>{{ \App\Http\Controllers\Backend\Export\ExportController::getColumnTitle($column) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $key => $row)
                        <tr class="{{ $key === 'sum' ? 'sum' : '' }}">
                            @foreach($columns as $column)
                                <td>{{ $row[$column->value ?? $column] ?? '' }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </body>
</html>
