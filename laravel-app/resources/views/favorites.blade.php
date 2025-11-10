@extends('layouts.app')
@section('content')
  <h1>My Favorites</h1>
  @if ($favorites->isEmpty())
    <p>You don't have favorite quotes yet. Visit <a href="/quotes">Quotes</a> and click "Add to favorites".</p>
  @else
    <ul>
      @foreach ($favorites as $quote)
        <li>
          {{ $quote->text }} — <em>{{ $quote->author }}</em>
          <form method="POST" action="/favorites/{{ $quote->id }}" style="margin-left:.5rem;">
            @csrf @method('DELETE')
            <button class="btn" type="submit">Delete</button>
          </form>
        </li>
      @endforeach
    </ul>
  @endif

  {{-- Attribution --}}
  <div style="margin-top: 2rem; padding: 1rem; background-color: #f8f9fa; border-top: 1px solid #dee2e6;">
    <p style="font-size: 0.875rem; color: #6c757d; margin: 0;">
      Quotes originally from <a href="https://api-ninjas.com/api/quotes" target="_blank" rel="noopener">API Ninjas</a>
    </p>
  </div>
@endsection
