@extends('layouts.frontend')
@section('title', 'Acceptable Use — '.config('app.name'))
@section('content')
<section class="mx-auto max-w-3xl px-6 py-10 sm:py-14 text-slate-300 space-y-4">
    <h1 class="text-3xl font-extrabold text-white">Acceptable Use Policy</h1>
    <p>Last updated: October 2026</p>
    <p>To keep {{ config('app.name') }} safe, you agree to not upload content that is:</p>
    <ul class="list-disc pl-5 space-y-2">
        <li>Illegal or promoting illegal acts</li>
        <li>Hate speech or harassment</li>
        <li>Spam or deceptive</li>
        <li>Sexually explicit</li>
    </ul>
    <p>We reserve the right to remove any content violating these rules.</p>
</section>
@endsection
