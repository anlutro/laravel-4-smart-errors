@extends('anlutro/l4-smart-errors::layout')

@section('title', Lang::get('anlutro/l4-smart-errors::error.missingTitle'))

@section('content')

	<p>@lang('anlutro/l4-smart-errors::error.missingText')</p>
	<p>
		{{ HTML::link(Request::header('referer'), Lang::get('anlutro/l4-smart-errors::error.backLinkTitle')) }} - 
		{{ HTML::link('/', Lang::get('anlutro/l4-smart-errors::error.frontpageLinkTitle')) }}
	</p>

@stop
