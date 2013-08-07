@extends('error.layout')

@section('title', Lang::get('error.genericErrorTitle'))

@section('content')

	<p>@lang('error.genericErrorParagraph1')</p>
	<p>@lang('error.genericErrorParagraph2')</p>
	<p style="text-align:center;">
		{{ HTML::link(Request::header('referer'), Lang::get('error.backLinkTitle')) }} - {{ HTML::link('/', Lang::get('error.frontpageLinkTitle')) }}
	</p>

@stop
