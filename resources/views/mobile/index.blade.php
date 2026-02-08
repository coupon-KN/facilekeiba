<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>facilekeiba</title>
    <link rel="stylesheet" href="{{ asset('/lib/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/style.css') . '?ver=240328' }}">
    <link href="https://fonts.googleapis.com/earlyaccess/nicomoji.css" rel="stylesheet">
    {{-- vite react --}}
    @viteReactRefresh
    {{-- @vite(['resources/sass/app.scss', 'resources/ts/index.tsx']) --}}
    @vite(['resources/ts/index.tsx'])
</head>
<body>
    <div id="reactContents"></div>
</body>
</html>
