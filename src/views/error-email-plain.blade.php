Uncaught exception: {{ $exception->renderInfoPlain() }}


Exception stack trace
=====================
{{ $exception->renderTracePlain() }}
@if ($exception->previous)


Previous exception
==================
{{ $exception->previous->renderInfoPlain() }}
@endif


Application information
=======================
{{ $info->renderPlain() }}
@if ($input)


Input
=====
{{ $input->renderPlain() }}
@endif
@if ($queryLog)


Query Log
=========
{{ $queryLog->renderPlain() }}
@endif