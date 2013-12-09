@extends('smarterror::layout')

@section('title', Lang::get('smarterror::error.genericErrorTitle'))

@section('content')

	<p>@lang('smarterror::error.genericErrorParagraph1')</p>
	<p>@lang('smarterror::error.genericErrorParagraph2')</p>
	<p style="text-align:center;">
		{{ HTML::link(Request::header('referer'), Lang::get('smarterror::error.backLinkTitle')) }} - 
		{{ HTML::link('/', Lang::get('smarterror::error.frontpageLinkTitle')) }}
	</p>

@stop
