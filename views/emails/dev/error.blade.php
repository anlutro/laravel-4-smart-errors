<p>
	Site: {{ $site }}<br>
	URL: {{ $url }}<br>
	Route: {{ $route }}
</p>
<p>
	{{ $exception }}
</p>

@if ($input)
<?php var_dump($input) ?>
@endif