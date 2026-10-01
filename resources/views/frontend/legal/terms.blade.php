@extends('layouts.frontend')
@section('title', 'Terms of Service — '.config('app.name'))
@section('content')
<section class="mx-auto max-w-3xl px-6 py-10 sm:py-14 text-slate-300 space-y-4">
    <h1 class="text-3xl font-extrabold text-white">Terms of Service</h1>
    <p>Last updated: October 2026</p>
    <p>Welcome to {{ config('app.name') }}. By accessing our platform, you agree to these terms.</p>
    <h2 class="mt-8 text-xl font-bold text-white">1. Content</h2>
    <p>You retain ownership of the content you upload, but grant us a license to distribute it on the platform.</p>
    <h2 class="mt-8 text-xl font-bold text-white">2. Conduct</h2>
    <p>You agree not to use the platform for any illegal or unauthorized purpose.</p>
</section>
@endsection
