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
			<h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h5> <!--figure out way to get user name in here -->
			<!-- This will be a header menu, with options: update profile, maps, logout -->
		</div>
		<nav class ="g2">
			<ul class="nav">
				<ul><a href="portal.php">Home</a></ul>
				<ul><a href="updateProfile">Update Profile</a></ul>
				<ul><a href="#Maps">View Your Maps</a></ul>
				<ul><a href="logout.php">Log Out</a></ul>
			</ul>
		</nav>
	</header>
	<div class="cf"></div>
	<div id="content">
		<div class="g1">
			<h3>Notes</h3>
			<button type="button" id="noteInfoButton">Notes Main View</button>
		</div>
		<div class="g1">
			<h3>Trips</h3>
			<div>
			<strong>Trip Purpose:</strong> <p id="tripPurpose"></p> 
			<strong>Trip Notes:</strong> <p id="tripNotes"></p> 
			<strong>Trip Start:</strong> <p id="tripStart"></p>
			<strong>Trip End:</strong> <p id="tripEnd"></p>
			<strong>Trip Length:</strong> <p id="tripLength"></p> 
			<strong>Select a different trip:</strong>
			<select id="tripList" onchange="changeTripDetails(this)"></select><br>
			<button type="button" id="tripInfoButton">Trips Main View</button>
		</div>
		</div>
		<div class="g1">
			<h3>User Data</h3>
		<div>
			<strong>Name:</strong> Chris <br>
			<strong>Email:</strong> <p id="email"></p> <br>
			<strong>Home Zipcode:</strong> <p id="hZIP"></p> <br>
			<strong>Work Zipcode:</strong> <p id="wZIP"></p> <br>
			<strong>School Zipcode:</strong> <p id="sZIP">No data available</p> <br>
			<strong>Cycling Frequency:</strong> <p id="cFREQ"></p> <br>
			<strong>Cycling Confidence:</strong> <p id="cCONF"></p> <br>
			<button type="button" id="updateButton">Update Profile</button>
		</div>
		</div>			
	</div>
	<div id="dom-target" style="display: none;">
    <?php 
        $user = $_SESSION['uID']; //Again, do some operation, get the output.
        echo htmlspecialchars($user); /* You have to escape because the result
                                       will not be valid HTML otherwise. */
    ?>
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


<script>
var div = document.getElementById("dom-target");
var user_id = div.textContent;

console.log(user_id);
getUserData(user_id);


getTripData(user_id);


//var data = JSON.parse(user);


  document.getElementById("updateButton").onclick = function () {
        location.href = "updateProfile";
    };
    
     document.getElementById("tripInfoButton").onclick = function () {
        location.href = "tripMainView";
    };
    
     document.getElementById("noteInfoButton").onclick = function () {
        location.href = "noteMainView";
    };


</script>