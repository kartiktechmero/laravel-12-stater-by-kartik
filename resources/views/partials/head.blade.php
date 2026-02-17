<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>

<title>{{ config('app.name').' - '.($title ?? config('app.name')) }}</title>
@include('partials.favicon')

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet"/>

@vite(['resources/css/app.css', 'resources/js/app.js'])
@livewireStyles
{{--@fluxAppearance--}}

<meta name="title" content="{{ $title ?? config('app.name') }}">
<meta name="description" content="Desc"/>
<meta name="robots" content="index, follow">
<link rel="canonical" href="{{ url()->current() }}">

<meta property="og:type" content="website"/>
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:site_name" content="{{ $title ?? config('app.name') }}">
<meta property="og:title" content="{{ $title ?? config('app.name') }}">
<meta property="og:description" content="Desc"/>
<meta property="og:image" content="{{ asset('assets/images/logo/logo.png') }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:locale" content="en_US">

<meta name="twitter:card" content="summary_large_image"/>
<meta name="twitter:url" content="{{ url()->current() }}">
<meta name="twitter:title" content="{{ $title ?? config('app.name') }}">
<meta name="twitter:description" content="Desc"/>
<meta name="twitter:image" content="{{ asset('assets/images/logo/logo.png') }}">

<meta itemprop="name" content="{{ $title ?? config('app.name') }}"/>
<meta itemprop="description" content="desc"/>
<meta itemprop="image" content="{{ asset('assets/images/logo/logo.png') }}"/>

<meta name="theme-color" content="#E6007A">
