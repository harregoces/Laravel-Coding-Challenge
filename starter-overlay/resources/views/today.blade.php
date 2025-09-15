@extends('layouts.app')
@section('content')
  <h1>Quote of the Day</h1>
  <p>{{ $quote->cached ? '[cached] ' : '' }}{{ $quote->text }} — <em>{{ $quote->author }}</em></p>
  @if (!empty($image))
    <p><img src="{{ asset('images/inspiration/' . $image) }}" alt="inspiration" style="max-width:100%;height:auto;border:1px solid #eee;border-radius:6px;"></p>
  @endif
  <p><a class="btn" href="/today?new=1">Refresh</a></p>
@endsection
