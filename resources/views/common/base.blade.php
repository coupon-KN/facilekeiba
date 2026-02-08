<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('pageTitle')</title>
  <link rel="stylesheet" href="{{ asset('/lib/bootstrap/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('/style.css') }}">
  @yield('pageCss')
  <link href="https://fonts.googleapis.com/earlyaccess/nicomoji.css" rel="stylesheet">
  <script src="{{ asset('/lib/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('/common.js') }}"></script>
  @yield('pageJs')
</head>
<body>
  @yield('pageContents')
</body>
</html>
