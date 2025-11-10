@extends('layouts.app')
@section('content')
  <h1>Quotes</h1>

  {{-- Client Mode Banner --}}
  <div style="background-color: {{ $client === 'stub' ? '#fff3cd' : '#d1ecf1' }}; padding: 0.75rem 1rem; margin-bottom: 1rem; border: 1px solid {{ $client === 'stub' ? '#ffc107' : '#17a2b8' }}; border-radius: 4px;">
    <strong>Mode:</strong> {{ strtoupper($client) }}
    @if($client === 'stub')
      <span style="margin-left: 0.5rem;">(Using local fixtures)</span>
    @else
      <span style="margin-left: 0.5rem;">(Using API Ninjas API)</span>
    @endif
  </div>

  <p>
    <a class="btn" href="/quotes?new=1">Refresh</a>
    @if(isset($count))
      <span style="margin-left: 1rem;">Showing {{ $count }} quotes</span>
    @endif
  </p>

  <ul>
    @foreach ($quotes as $q)
    <li>
      {{ $q->cached ? '[cached] ' : '' }}{{ $q->text }} — <em>{{ $q->author }}</em>
      @auth
        <form method="POST" action="/favorites" style="margin-left:.5rem;">
          @csrf
          <input type="hidden" name="text" value="{{ $q->text }}">
          <input type="hidden" name="author" value="{{ $q->author }}">
          <button class="btn" type="submit">Add to favorites</button>
        </form>
      @endauth
    </li>
    @endforeach
  </ul>

  {{-- Attribution --}}
  <div style="margin-top: 2rem; padding: 1rem; background-color: #f8f9fa; border-top: 1px solid #dee2e6;">
    <p style="font-size: 0.875rem; color: #6c757d; margin: 0;">
      Quotes powered by <a href="https://api-ninjas.com/api/quotes" target="_blank" rel="noopener">API Ninjas</a>
    </p>
  </div>
@endsection
