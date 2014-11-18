<?php
//session_cache_limiter(false);
session_start();
if(!isset($_SESSION['uID'])) {
    header("Location: login.php");
    die;
}

//print_r($_SESSION['uID']);
?>

<!DOCTYPE html>

<html>

<head>
<style>
.bar {
  fill: steelblue;
}
.bar:hover {
  fill: turquoise;
}

.chart text {
  font: 10px sans-serif;
}
.chart .title {
  font: 15px sans-serif;
}

.axis path,
.axis line {
  fill: none;
  stroke: #000;
  shape-rendering: crispEdges;
}

.x.axis path {
  display: none;
}
 </style>
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
			<h2>Trip View</h5> <!--figure out way to get user name in here -->
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
			<table id="myTable"></table>
			<br>
		</div>
	</div>
	
	<div class = "g3" id="yearChart"> <br></div>
	<svg class = "chart"></svg>
	<div id="dom-target" style="display: none;">
    <?php 
        $user = $_SESSION['uID']; //Again, do some operation, get the output.
        echo htmlspecialchars($user); /* You have to escape because the result
                                       will not be valid HTML otherwise. */
    ?>
	</div>



	<!-- JavaScript at the bottom for fast page loading -->

	<!-- Minimized jQuery from Google CDN -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>

	<!-- HTML5 IE Enabling Script -->
	<!--[if lt IE 9]><script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
	
	<!-- CSS3 Media Queries -->
	<script src="js/respond.min.js"></script>
	<script src="js/main.js"></script>
	<script src="js/tripProcessing.js"></script>
	<script type="text/javascript" src="http://d3js.org/d3.v2.js"></script>
	<script type="text/javascript" src="js/chartData.js"></script>

	
	
</body>
</html>
<script>
	var div = document.getElementById("dom-target");
	var user_id = div.textContent;
	
	populateTripTable(user_id);
</script>	
<script type="text/javascript">
	var data = testTrips();
	//var dataset = Object.keys(arr).map(function(k) { return arr[k] });
	
	//console.log(dataset);
		
			
			
			
			
			
			
			


var margin = {top: 20, right: 30, bottom: 40, left: 40},
    width = 480 - margin.left - margin.right,
    height = 250 - margin.top - margin.bottom;

var x = d3.scale.ordinal()
    .domain(data.map(function(d) { return d.year; }))
    .rangeRoundBands([0, width], .1);

var y = d3.scale.linear()
    .domain([0, d3.max(data, function(d) { return d.total; })])
    .range([height, 0]);

var xAxis = d3.svg.axis()
    .scale(x)
    .orient("bottom");

var yAxis = d3.svg.axis()
    .scale(y)
    .orient("left");

var chart = d3.select(".chart")
    .attr("width", width + margin.left + margin.right)
    .attr("height", height + margin.top + margin.bottom)
  .append("g")
    .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

// Add data
chart.selectAll(".bar")
      .data(data)
      .enter()
      .append("rect")
      .attr("class", "bar")
      .attr("x", function(d) { return x(d.year); })
      .attr("y", function(d) { return y(d.total); })
      .attr("height", function(d) { return height - y(d.total); })
      .attr("width", x.rangeBand());

// y axis and label
chart.append("g")
    .attr("class", "y axis")
    .call(yAxis)
  .append("text")
    .attr("transform", "rotate(-90)")
    .attr("x", -height/2)
    .attr("y", -margin.bottom)
    .attr("dy", ".71em")
    .style("text-anchor", "end")
    .text("YAxis");
// x axis and label
chart.append("g")
    .attr("class", "x axis")
    .attr("transform", "translate(0," + height + ")")
    .call(xAxis)
  .append("text")
    .attr("x", width / 2)
    .attr("y", margin.bottom - 10)
    .attr("dy", ".71em")
    .style("text-anchor", "end")
    .text("XAxis");
// chart title
chart.append("text")
  .text("Yearly Trip Totals")
  .attr("x", width / 2)
  .attr("class","title");
			
	
		
	</script>

