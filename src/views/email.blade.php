<p>
	Time: {{ $time }}<br>
	URL: {{ $url }}<br>
	Route: {{ $route }}
</p>

<p>
	Error message: {{ $exception->getMessage() }}
	@if ($exception->getCode() > 0)
	 - code: {{ $exception->getCode() }}
	@endif
	<br>In {{ $exception->getFile() }} on line {{ $exception->getLine() }}
</p>

<p>
	<b>Stack trace</b><br>
	{{ nl2br($exception->getTraceAsString()) }}
</p>

@if ($previous = $exception->getPrevious())
<p>
	<b>Previous exception:</b> {{ $previous->getMessage() }}
	@if ($previous->getCode() > 0)
	 - code: {{ $previous->getCode() }}
	@endif
	<br>In {{ $previous->getFile() }} on line {{ $previous->getLine() }}
</p>
@endif


@if (!empty($input))
<p>
	<b>Input</b><br>
	<?php var_dump($input) ?>
</p>
@endif