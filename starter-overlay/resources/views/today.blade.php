@extends('layouts.app')
@section('content')
  <h1>Quote of the Day</h1>
  <p>{{ $quote->cached ? '[cached] ' : '' }}{{ $quote->text }} — <em>{{ $quote->author }}</em></p>
  @if ($image)
    <p><img src="{{ $image }}" alt="Inspirational image" style="border-radius:8px;border:1px solid #e2e8f0;"></p>
  @endif
  <p><a class="btn" href="/today?new=1">Refresh</a></p>
@endsection
