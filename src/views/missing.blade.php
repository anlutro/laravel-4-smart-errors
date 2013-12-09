@extends('smarterror::layout')

@section('title', Lang::get('smarterror::error.missingTitle'))

@section('content')

	<p>@lang('smarterror::error.missingText')</p>
	<p>
		{{ HTML::link(Request::header('referer'), Lang::get('smarterror::error.backLinkTitle')) }} - 
		{{ HTML::link('/', Lang::get('smarterror::error.frontpageLinkTitle')) }}
	</p>

@stop
