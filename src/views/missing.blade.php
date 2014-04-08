@extends('smarterror::layout')

@section('title', trans('smarterror::error.missingTitle'))

@section('content')

	<p>@lang('smarterror::error.missingText')</p>
	<p style="text-align:center;">
	@if ($referer)
		<a href="{{ $referer }}">{{ trans('smarterror::error.backLinkTitle') }}</a> - 
	@endif
		<a href="/">{{ trans('smarterror::error.frontpageLinkTitle') }}</a>
	</p>

@stop
