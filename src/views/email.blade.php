<p>
	Time: {{ $time }}<br>
	URL: {{ $url }}<br>
	Route: {{ $route }}<br>
	Client: {{ $client }}
</p>

<p>
	{{ get_class($exception) }}<br>
	@if ($exception->getMessage())
	Exception message: {{ $exception->getMessage() }}<br>
	@endif
	@if ($exception->getCode() > 0)
	Exception code: {{ $exception->getCode() }}<br>
	@endif
	In {{ $exception->getFile() }} on line {{ $exception->getLine() }}
</p>

<p><b>Stack trace</b></p>
<p><pre style="white-space:pre-wrap;">
	{{ nl2br($exception->getTraceAsString()) }}
</pre></p>

@if ($previous = $exception->getPrevious())
<p>
	<b>Previous exception:</b> {{ get_class($previous) }}
	@if ($previous->getMessage())
	Exception message: {{ $previous->getMessage() }}<br>
	@endif
	@if ($previous->getCode() > 0)
	Exception code: {{ $previous->getCode() }}<br>
	@endif
	In {{ $previous->getFile() }} on line {{ $previous->getLine() }}
</p>
@endif


@if (!empty($input))
<p>
	<b>Input</b><br>
	<?php var_dump($input) ?>
</p>
@endif