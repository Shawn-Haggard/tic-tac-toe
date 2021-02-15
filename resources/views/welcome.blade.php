<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link href="{{ asset('css/app.css') }}" rel="stylesheet" type="text/css" >
        <title>Laravel</title>

        <!-- Fonts -->
    </head>
    <body>
        <span id="app">
            <board></board>
        </span>
        <script src="{{ asset('/js/app.js') }}"></script>
    </body>
</html>