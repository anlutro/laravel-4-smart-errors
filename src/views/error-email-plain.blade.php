Uncaught exception: {{ $exception->info }}


Application information
=======================
{{ $info->render() }}


Exception stack trace
=====================
{{ $exception->trace }}
@if ($exception->previous)


Previous exception
==================
{{ $exception->previous->info }}
@endif
@if ($input)


Input
=====
{{ $input->render() }}
@endif
@if ($queryLog)


Query Log
=========
{{ $queryLog->render() }}
@endif