@extends('layouts.app')
@section('content')
  <h1>Quote of the Day</h1>
  <p>{{ $quote->cached ? '[cached] ' : '' }}{{ $quote->text }} — <em>{{ $quote->author }}</em></p>
  @if (!empty($image))
    <p><img src="{{ asset('images/inspiration/' . $image) }}" alt="inspiration" style="max-width:100%;height:auto;border:1px solid #eee;border-radius:6px;"></p>
  @endif
  <p><a class="btn" href="/today?new=1">Refresh</a></p>

  {{-- Attribution --}}
  <div style="margin-top: 2rem; padding: 1rem; background-color: #f8f9fa; border-top: 1px solid #dee2e6;">
    <p style="font-size: 0.875rem; color: #6c757d; margin: 0;">
      Quote powered by <a href="https://api-ninjas.com/api/quotes" target="_blank" rel="noopener">API Ninjas</a>
    </p>
  </div>
@endsection
