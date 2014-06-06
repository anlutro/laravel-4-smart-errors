<p>
	<strong>Uncaught exception:</strong> {{ $exception->renderInfoHtml() }}
</p>

<p><b>Application information</b></p>
<p>
	{{ $info->renderHtml() }}
</p>

<p><b>Exception stack trace</b></p>
<p><pre style="white-space:pre-wrap;">{{ $exception->renderTraceHtml() }}</pre></p>

@if ($exception->previous)
<p>
	<b>Previous exception:</b> {{ $exception->previous->renderInfoHtml() }}
</p>
@endif

@if ($input)
<hr>
<p>
	<b>Input</b><br>
	<p>{{ $input->renderHtml() }}</p>
</p>
@endif

@if ($queryLog)
<hr>
<p>
	<b>Query log</b><br>
	{{ $queryLog->renderHtml() }}
</p>
@endif