An event with the highest possible logging level, ALERT, was registered.

Message: {{ $logmsg }}
{{ $info->renderLines() }}

Context
=======
{{ $context->render() }}