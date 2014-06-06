Uncaught exception: {{ $exception->renderInfoPlain() }}


Application information
=======================
{{ $info->renderPlain() }}


Exception stack trace
=====================
{{ $exception->renderTracePlain() }}
@if ($exception->previous)


Previous exception
==================
{{ $exception->previous->renderInfoPlain() }}
@endif
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