<p>
	{{ $info }}
</p>

<p>
	<b>Exception thrown:</b> {{ nl2br($exception->info) }}
</p>

<p><b>Stack trace</b></p>
<p><pre style="white-space:pre-wrap;">{{ nl2br($exception->trace) }}</pre></p>

@if ($exception->previous)
<p>
	<b>Previous exception:</b> {{ nl2br($exception->previous->info) }}
</p>
@endif

@if ($input)
<p>
	<b>Input</b><br>
	<p>{{ $input }}</p>
</p>
@endif

@if ($queryLog)
	<b>Query log</b><br>
	{{ $queryLog }}
@endif