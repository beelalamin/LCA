@php
    $description = __('An advanced platform for registering, tracking, auditing, and managing organizational assets across their full lifecycle — built for precision, security, and scale.');
    $image = asset('images/adda-og.png');
@endphp

<meta name="description" content="{{ $description }}">

<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ config('app.name') }}">
<meta property="og:title" content="{{ config('app.name') }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:image" content="{{ $image }}">
<meta property="og:image:type" content="image/png">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="{{ config('app.name') }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ config('app.name') }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $image }}">
