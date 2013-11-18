An event with the highest possible logging level, ALERT, was registered.

Time: {{ $time }}
Message: {{ $logmsg }}
URL: {{ $url }}
Route: {{ $route }}

@if (!empty($context))
Context
=======
@foreach ($context as $key => $val)
{{ $key }}: {{ $val }}
@endforeach
@endif