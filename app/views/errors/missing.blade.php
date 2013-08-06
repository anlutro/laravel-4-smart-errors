@extends('layouts.main')

@section('title', Lang::get('error.missingTitle'))

@section('content')

<h1>@lang('error.missingTitle')</h1>

<p>@lang('error.missingText')</p>
<p style="text-align:center;">
	{{ HTML::link(Request::header('referer'), Lang::get('error.backLinkTitle')) }} - {{ HTML::link('/', Lang::get('frontpageLinkTitle')) }}
</p>

@stop