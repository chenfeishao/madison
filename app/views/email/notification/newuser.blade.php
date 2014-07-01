<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta charset="utf-8" />
		<style>
			blockquote {
				background: #f9f9f9;
				border-left: 10px solid #ccc;
				margin: 1.5em 10px;
				padding: 0.5em 10px;
				quotes: "\201C""\201D""\2018""\2019";
			}
			blockquote:before {
				color:#ccc;
				content: open-quote;
				font-size: 4em;
				line-height: 0.1em;
				margin-right: 0.25em;
				vertical-align: -0.4em;
			}
			blockquote p{
				display:inline;
			}
		</style>
	</head>
	<body>
		<p>A new User {{ $user.fname }} {{ $user.lname }} has signed up!</p>
		
		<p>&ndash; The OpenGov Foundation Team</p>
	</body>
</html>


