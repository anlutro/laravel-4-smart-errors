<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>@lang('smarterror::error.genericErrorTitle')</title>
	<style>
		* {
			font-family: sans-serif;
			line-height: 1.4em;
		}
		
		body {
			background: #fcfcfc;
			-webkit-font-smoothing: antialiased;
			-moz-font-smoothing: antialiased;
			-ms-font-smoothing: antialiased;
			-o-font-smoothing: antialiased;
			font-smoothing: antialiased;
		}
		
		h1 {
			text-align: center;
			font-size: 3.5em;
		}
		
		.error-message {
			padding: 30px;
			background: #fff;
			color: black;
		}
		
		a {
			color: black;
		}
		
		@media screen and (min-width: 800px) {
			h1 {
				margin-top: 75px;
			}
			.error-message {
				max-width: 700px;
				margin: 50px auto;
				border-radius: 10px;
				border: 1px solid #ddd;
			}
		}
	</style>
</head>
<body>

<h1>@yield('title')</h1>

<section class="error-message">
	@yield('content')
</section>

</body>
</html>
