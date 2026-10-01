@extends('layouts.frontend')
@section('title', 'DMCA — '.config('app.name'))
@section('content')
<section class="mx-auto max-w-3xl px-6 py-10 sm:py-14 text-slate-300 space-y-4">
    <h1 class="text-3xl font-extrabold text-white">DMCA Copyright Policy</h1>
    <p>Last updated: October 2026</p>
    <p>We respect intellectual property rights. If you believe your copyright has been infringed, please send a notice to legal@{{ parse_url(config('app.url'), PHP_URL_HOST) }} with:</p>
    <ul class="list-disc pl-5 space-y-2">
        <li>A description of the copyrighted work</li>
        <li>The URL of the infringing material</li>
        <li>Your contact information</li>
        <li>A statement of good faith belief</li>
    </ul>
</section>
@endsection
