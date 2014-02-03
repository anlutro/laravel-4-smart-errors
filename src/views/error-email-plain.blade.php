{{ $info->render() }}

{{ $exception->info }}

Stack trace
===========
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