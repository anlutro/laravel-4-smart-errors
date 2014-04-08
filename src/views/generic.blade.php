@extends('smarterror::layout')

@section('title', trans('smarterror::error.genericErrorTitle'))

@section('content')

	<p>@lang('smarterror::error.genericErrorParagraph1')</p>
	<p>@lang('smarterror::error.genericErrorParagraph2')</p>
	<p style="text-align:center;">
	@if ($referer)
		<a href="{{ $referer }}">{{ trans('smarterror::error.backLinkTitle') }}</a> - 
	@endif
		<a href="/">{{ trans('smarterror::error.frontpageLinkTitle') }}</a>
	</p>

@stop
