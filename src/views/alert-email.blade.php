<p>An event with the highest possible logging level, ALERT, was registered.</p>
<p>
	Message: {{ $logmsg }}<br>
	{{ $info->renderHtml() }}
</p>
<p>
	<strong>Context:</strong><br>
	{{ $context->renderHtml() }}
</p>