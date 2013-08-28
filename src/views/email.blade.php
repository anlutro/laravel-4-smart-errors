<p>
	Time: {{ $time }}<br>
	URL: {{ $url }}<br>
	Route: {{ $route }}
</p>

<p>
	Error message: {{ $e->getMessage() }}
	@if ($e->getCode() > 0)
	 - code: {{ $e->getCode() }}
	@endif
	<br>In {{ $e->getFile() }} on line {{ $e->getLine() }}
</p>

<p>
	<b>Stack trace</b><br>
	{{ nl2br($e->getTraceAsString()) }}
</p>

@if ($pe = $e->getPrevious())
<p>
	<b>Previous exception:</b> {{ $pe->getMessage() }}
	@if ($pe->getCode() > 0)
	 - code: {{ $pe->getCode() }}
	@endif
	<br>In {{ $pe->getFile() }} on line {{ $pe->getLine() }}
</p>
@endif


@if ($input)
<p>
	<b>Input</b><br>
	<?php var_dump($input) ?>
</p>
@endif