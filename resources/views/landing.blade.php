<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>welcome.</title>
        <meta name="description" content="Login Page">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <meta name="csrf-token" content="{{ csrf_token() }}">

    </head>
    <body>

        <div id="app">
            <app></app>
        </div>

        <script src="{{ mix('js/app.js') }}"></script>

    </body>
</html>
