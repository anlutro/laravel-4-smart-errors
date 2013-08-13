@extends('anlutro/l4-smart-errors::layout')

@section('title', Lang::get('anlutro/l4-smart-errors::error.genericErrorTitle'))

@section('content')

	<p>@lang('anlutro/l4-smart-errors::error.genericErrorParagraph1')</p>
	<p>@lang('anlutro/l4-smart-errors::error.genericErrorParagraph2')</p>
	<p style="text-align:center;">
		{{ HTML::link(Request::header('referer'), Lang::get('anlutro/l4-smart-errors::error.backLinkTitle')) }} - 
		{{ HTML::link('/', Lang::get('anlutro/l4-smart-errors::error.frontpageLinkTitle')) }}
	</p>

@stop
