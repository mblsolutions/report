<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $report->name }} - {{ config('app.name') }}</title>

    <link href="https://fonts.googleapis.com/css?family=Inconsolata:400,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <style>
        @page { size: landscape; }

        body, html {
            font-size: 1em;
            font-family: 'Inconsolata', monospace;
            padding: 0 4px;
        }

        h1 {
            margin: 0;
            padding: 0;
            font-size: 1.3em;
            font-weight: 600;
            text-decoration: underline;
        }

        .qc-header {
            margin: 8px 0;
        }

        .qc-header > div {
            margin: 0;
            padding: 0;
        }

        .qc-order-info {
            font-weight: 700;
            border-bottom: 1px solid grey;
        }

        .table {
            border-top: 2px solid black;
            margin-bottom: 2px;
        }

        .table > thead > tr > th, .table > tbody > tr > td {
            padding: 0 4px;
            vertical-align: bottom;
            border: 0;
        }

        .table > thead {
            font-size: 0.85em;
        }

        .table > tbody {
            font-size: 0.7em;
        }

        .qc-titles {
            font-style: italic;
            border-bottom: 1px dotted black;
        }

        .qc-totals {
            font-weight: 600;
            background-color: #ececec;
        }

        .qc-signature {
            display: block;
            width: 100%;
            border-bottom: 1px solid black;
        }

        .qc-date-user {
            font-size: 0.8em;
        }

        .print-qc-signature {
            display: none;
        }

        @media print {
            body, html {
                font-size: 0.8em !important;
            }

            .table > thead {
                font-size: 0.6em !important;
            }

            .table > tbody {
                font-size: 0.5em !important;
            }

            .print-qc-button {
                display: none;
            }

            .print-qc-signature {
                display: inline;
            }
        }
    </style>
</head>
<body>
    <div id="report-results-print">

        <div class="row qc-header">
            <div class="col-6 qc-date-user">
                <p><strong>{{ config('app.name') }} {{ $report->name }}</strong></p>
                User: {{ $user->name }}<br />
                Date: {{ $date }}
            </div>
            <div class="col-6 text-right">
                <p><strong>Report Parameters</strong></p>
                <table class="table table-striped table-hover">
                    <tbody>
                    @foreach ($params as $key => $param)
                        <tr>
                            <td><strong>{{ $key }}</strong></td>
                            <td class="text-right">{{ $param }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    @foreach ($headings  as $heading)
                        <th>{{ $heading }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $row)
                    <tr>
                        @foreach ($row as $key => $column)
                            <td>
                                {{ $column }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>
</body>
</html>