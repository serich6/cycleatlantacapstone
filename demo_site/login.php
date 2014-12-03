<?php
//session_cache_limiter(false);
session_start();

?>
<!DOCTYPE html>

<html>
	<head>
	<!--
	*****************************************************************
	Fluid Baseline Grid v1.0.0
	Designed & Built by Josh Hopkins and 40 Horse, http://40horse.com
	Licensed under Unlicense, http://unlicense.org/
	*****************************************************************
	-->
	<title>Cycle Atlanta Portal</title>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
	<meta name="description" content="" />
	<meta name="author" content="">
	<meta name="keywords" content="" />

	<!-- Optimized mobile viewport -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	
	<!-- Place favicon.ico and apple-touch-icon.png in root directory -->
		
	<link href="css/style.css" rel="stylesheet" />
	</head>
<!-- use g3 to span across entire page, g2 to span across two columns, g1 to span one column -->
		<body>
			<header>
				<div class="g1">
					<h2>User Login</h5> 
					<p id="incorrect"></p>
				</div>
			</header>
			
			<div class="cf"></div>
			
			<div id="content">
				<form action="../index.php/login" method="post" name="myform">
				Email:
				<input name="email" value="none" /> <br>
				Password:
				<input name="password" input type= "password" value="password" /> <br>
				<input type="submit" value="Submit">

			</form>
			<p>Or register <a href="register.php">here</a></p>
			</div>
	<footer class="g3 cf">
		<small>2011 <span class="license">Created by <a href="http://twitter.com/thedayhascome">Josh Hopkins</a> <span class="amp">&amp;</span> <a href="http://40horse.com">40 Horse</a></span>. Released under <a href="http://unlicense.org">Unlicense</a>. </small>
	</footer>

	<!-- JavaScript at the bottom for fast page loading -->

	<!-- Minimized jQuery from Google CDN -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>

	<!-- HTML5 IE Enabling Script -->
	<!--[if lt IE 9]><script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
	
	<!-- CSS3 Media Queries -->
	<script src="js/respond.min.js"></script>
	<script src="js/main.js"></script>
	<script src="js/tripProcessing.js"></script>

	
	
</body>
</html>


