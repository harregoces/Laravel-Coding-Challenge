@extends('layouts.app')
@section('content')
  <h1>Quotes</h1>
  <p><a class="btn" href="/quotes?new=1">Refresh</a></p>
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
@endsection
