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
			<h2>Update Profile</h5> <!--figure out way to get user name in here -->
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
		<div id="proForm" class="g3">
			<form id="updateForm">
				<br>
				<label></label><br> <input id="updateId" input type="hidden" name="updateId" type="text" value=<?php  echo $_SESSION['uID']?>  /><br>
				<br>
				<label><strong>Email:</strong></label> <input id="updateEmail" name="updateEmail" type="text"  /><br>
				<label><strong>Password:</strong></label> <input id="updatePassword" name="updatePassword" type="text"  /><br>
				<label><strong>Home Zip:</strong></label><input type="text" id="updateHomeZip" name="updateHomeZip" /><br>
				<label><strong>Work Zip:</strong></label><input type="text" id="updateWorkZip" name="updateWorkZip" /><br>
				<label><strong>School Zip:</strong></label><input type="text" id="updateSchoolZip" name="updateSchoolZip" /><br>
				<br>
				<label><strong>Cycling Frequency:</strong><br>
				<input type="radio" id="updateCycleFreq1" name="updateCycleFreq" value="1">Less than once a month<br>
				<input type="radio" id="updateCycleFreq2" name="updateCycleFreq" value="2">Several times per month<br>
				<input type="radio" id="updateCycleFreq3" name="updateCycleFreq" value="3">Several times per week<br>
				<input type="radio" id="updateCycleFreq4" name="updateCycleFreq" value="4">Daily<br>
				<br>
				<label><strong>Cycling Confidence:</strong><br>
				<input type="radio" id="updateCycleConf1" name="updateCycleConf" value="1">Strong and fearless<br>
				<input type="radio" id="updateCycleConf2" name="updateCycleConf" value="2">Enthused and confident<br>
				<input type="radio" id="updateCycleConf3" name="updateCycleConf" value="3">Comfortable, but cautious<br>
				<input type="radio" id="updateCycleConf4" name="updateCycleConf" value="4">Interested, but concerned<br>
				<br>
				<button id="submitUpdates">Submit Changes</button>
			</form>
		</div>
	<div id="deleteForm" class="g3">
		<form id="deleteUser">
		<br>
		Would you like to delete your profile?
		<label></label><br> <input id="deleteId" input type="hidden" name="deleteId" type="text" value=1439 /><br>
		
    	<button id="delProfile">Delete Profile</button>
    	</form>
		
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
