<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin')</title>

    <link rel="stylesheet" href="/css/admin.css">

    {{-- Page Specific Styles --}}
    @stack('styles')

    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">
</head>
<body>

@include('partials.admin-navbar')

<!-- PAGE CONTENT -->
@yield('content')
@stack('scripts')

</body>
</html>