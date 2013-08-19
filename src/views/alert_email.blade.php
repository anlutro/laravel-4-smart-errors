<p>An event with the highest possible logging level, ALERT, was registered.</p>
<p>
	Time: {{ $time }}<br>
	Message: {{ $logmsg }}<br>
	URL: {{ $url }}<br>
	Route: {{ $route }}
</p>
<?php if (!empty($context)) var_dump($context) ?>