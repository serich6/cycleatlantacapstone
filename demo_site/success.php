<?php
//session_cache_limiter(false);
session_start();
if(!isset($_SESSION['uID'])) {
    header("Location: login.php");
    die;
}
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
			<h2>Success</h5> <!--figure out way to get user name in here -->
			<!-- This will be a header menu, with options: update profile, maps, logout -->
		</div>
		<nav class ="g2">
			<ul class="nav">
				<ul><a href="portal.php">Home</a></ul>
				<ul><a href="updateProfile.php">Update Profile</a></ul>
				<ul><a href="#Maps">View Your Maps</a></ul>
				<ul><a href="logout.php">Log Out</a></ul>
			</ul>
		</nav>
	</header>
	<div class="cf"></div>
	<div id="content">
		<div class="g3">
			<h5>Profile updated successfully</h5>
			<button type="button" id="showUserPutData">Show Submitted JSON</button>
			<div id="putUserData">
			<?php 
        
   				print_r( $_SESSION['userPutJSON'] ); 
    
			?>
			</div></br>
			<a href="portal.php">Return home</a>
					
			
		</div>
	
			<div id="dom-target" style="display: none;">
   				 <?php 
        		$user = $_SESSION['uID']; //Again, do some operation, get the output.
        		echo htmlspecialchars($user); /* You have to escape because the result
                                       will not be valid HTML otherwise. */
    		?>
			</div>
	</div>
	</div>


</body>
</html>


	<script src="js/jquery-1.7.1.min.js"></script>
	<script src="js/main.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>

	<!-- HTML5 IE Enabling Script -->
	<!--[if lt IE 9]><script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
	
	<!-- CSS3 Media Queries -->
	<script src="js/respond.min.js"></script>
	
<script>
$("#putUserData").hide();
$(document).ready(function(){
  		$("#showUserPutData").click(function(){
    		$("#putUserData").toggle(800);
  		});
	});
</script>
