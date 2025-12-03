<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Träwelling</title>
    @vite(['resources/js/year-in-review.js'])
    <style>
        html, body {
            margin: 0;
            padding: 0;
            overflow: hidden;
            height: 100%;
        }

        #year-in-review-app {
            height: 100vh;
        }
    </style>
</head>
<body>
<div id="year-in-review-app"></div>
</body>
</html>
