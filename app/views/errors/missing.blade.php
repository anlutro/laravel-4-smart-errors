@extends('layouts.main')

@section('title', Lang::get('error.missingTitle'))

@section('content')

	<div class="page-header">
		<h1>@lang('error.missingTitle')</h1>
	</div>

	<p>@lang('error.missingText')</p>
	<p>
		{{ HTML::link(Request::header('referer'), Lang::get('error.backLinkTitle')) }} - 
		{{ HTML::link('/', Lang::get('frontpageLinkTitle')) }}
	</p>

@stop
