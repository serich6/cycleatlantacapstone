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

  .odd{background-color: white;} 

  .even{background-color: rgba(70,130,180, .50);} 
  
  
.bar {
  fill: steelblue;
}
.bar:hover {
  fill: turquoise;
}

.arc:hover {
  fill: turquoise;
}


.chart text {
  font: 12px Futura, sans-serif;
}
.chart .title {
  font: 15px "Century Gothic", sans-serif;
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

.d3-tip {
  line-height: 1;
  font-weight: bold;
  padding: 12px;
  background: rgba(0, 0, 0, 0.8);
  color: #fff;
  border-radius: 2px;
}

/* Creates a small triangle extender for the tooltip */
.d3-tip:after {
  box-sizing: border-box;
  display: inline;
  font-size: 10px;
  width: 100%;
  line-height: 1;
  color: rgba(0, 0, 0, 0.8);
  content: "\25BC";
  position: absolute;
  text-align: center;
}

/* Style northward tooltips differently */
.d3-tip.n:after {
  margin: -1px 0 0 0;
  top: 100%;
  left: 0;
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
	
	<div class="g1" id="yearChart"><svg class = "chart"></svg></div>
	
	
	<div class="g3" id ="pieChart">
		<strong>2012 Breakdown by Purpose &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2013 Breakdown by Purpose  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2014 Breakdown by Purpose </strong>
		
	</div>
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
	<script src="http://labratrevenge.com/d3-tip/javascripts/d3.tip.v0.6.3.js"></script>
	

	
	
</body>
</html>
<script>
	var div = document.getElementById("dom-target");
	var user_id = div.textContent;
	
	populateTripTable(user_id);
</script>	
<script type="text/javascript">
	//the data
	var data = yearFreq(user_id);
	
	//var data = data;
	
	/*******
	BAR CHART
	********/
	var margin = {top: 20, right: 30, bottom: 40, left: 40},
		width = 900 - margin.left - margin.right,
		height = 400 - margin.top - margin.bottom;

	var x = d3.scale.ordinal()
		.domain(data.map(function(d) { return d.year; }))
		.rangeRoundBands([0, width], .1);

	var y = d3.scale.linear()
		.domain([0, d3.max(data, function(d) { return +d.total; })])
		.range([height, 0]);

	var xAxis = d3.svg.axis()
		.scale(x)
		.orient("bottom");

	var yAxis = d3.svg.axis()
		.scale(y)
		.orient("left");

	var chart = d3.selectAll(".chart")
		.attr("width", width + margin.left + margin.right)
		.attr("height", height + margin.top + margin.bottom)
	  .append("g")
		.attr("transform", "translate(" + margin.left + "," + margin.top + ")");
		
	var tip = d3.tip()
 		 .attr('class', 'd3-tip')
  		.offset([-10, 0])
  		.html(function(d) {
    		return "<strong>Commute Trips:</strong>"+d.commute+" <br> Social Trips:"+d.social+"<br> Exercise Trips:"+d.exercise+"<br>Shopping Trips:"+d.shopping+"<br> Errand Trips:"+d.errand+"<br><span style='color:red'></span>";
 	 })
 	 
	chart.call(tip);
	// Add data
	chart.selectAll(".bar")
		  .data(data)
		  .enter()
		  .append("rect")
		  .attr("class", "bar")
		  .attr("x", function(d) { return x(d.year); })
		  .attr("y", function(d) { return y(+d.total); })
		  .attr("height", function(d) { return height - y(+d.total); })
		  .attr("width", x.rangeBand())
		  .on('mouseover', tip.show)
      	  .on('mouseout', tip.hide);

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
		.text("Trip Totals");
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
		.text("Years");
	// chart title
	chart.append("text")
	  .text("Yearly Trip Totals")
	  .attr("x", width / 2)
	  .attr("y", margin.top - 29)
	  .attr("class","title");
			
			
			
	/*********
	PIE CHARTS
	**********/		
	var dataset = [{"purpose":"commute", "total":+data[0]["commute"]}, 
				  {"purpose":"social", "total":+data[0]["social"]}, 
				  {"purpose":"errand", "total":+data[0]["errand"]},
				  {"purpose":"shopping", "total": +data[0]["shopping"]}, 
				  {"purpose":"exercise", "total": +data[0]["exercise"]}];

	var d2013 = [{"purpose":"commute", "total":+data[1]["commute"]}, 
				  {"purpose":"social", "total":+data[1]["social"]}, 
				  {"purpose":"errand", "total":+data[1]["errand"]},
				  {"purpose":"shopping", "total": +data[1]["shopping"]}, 
				  {"purpose":"exercise", "total": +data[1]["exercise"]}];
				  
	var d2014 = [{"purpose":"commute", "total":+data[2]["commute"]}, 
				  {"purpose":"social", "total":+data[2]["social"]}, 
				  {"purpose":"errand", "total":+data[2]["errand"]},
				  {"purpose":"shopping", "total": +data[2]["shopping"]}, 
				  {"purpose":"exercise", "total": +data[2]["exercise"]}];
				  
	console.log(d2014);

 	var color = d3.scale.ordinal()
           .range(["#e53517","#6b486b","#ffbb78","#7ab51d","#6b486b",
                  "#e53517","#7ab51d","#ff7f0e","#ffc400"]);  
	var width = 420,
		height = 300,
		radius = Math.min(width, height) / 2;

	var color = d3.scale.category20();

	var pie = d3.layout.pie()
		.sort(null)
		.value(function(d){return d.total;});

	var arc = d3.svg.arc()
		.innerRadius(radius - 100)
		.outerRadius(radius - 50);

	var pieChart = d3.selectAll("#pieChart").append("svg")
		.attr("width", width)
		.attr("height", height)
		.append("g")
		.attr("transform", "translate(" + width / 2 + "," + height / 2 + ")");
		
		
	
   
      
    var otherPieChart = d3.selectAll("#pieChart").append("svg")
		.attr("width", width)
		.attr("height", height)
		.append("g")
		.attr("transform", "translate(" + width / 2 + "," + height / 2 + ")");  
		
	var thirdPieChart = d3.selectAll("#pieChart").append("svg")
		.attr("width", width)
		.attr("height", height)
		.append("g")
		.attr("transform", "translate(" + width / 2 + "," + height / 2 + ")");  
        
    var pieTip = d3.tip()
  		.attr('class', 'd3-tip')
  		.html(function(d) {
    		return "<strong>Purpose:</strong>"+d.data.purpose+" <br> Total:"+d.data.total+"<br> <span style='color:red'></span>";
    		})
    		
 	 pieChart.call(pieTip);
 	 otherPieChart.call(pieTip);
 	 thirdPieChart.call(pieTip);
        
    var g = pieChart.selectAll(".arc")
    	.data(pie(dataset))
    	.enter()
    	.append("g")
    	.attr("class","arc")
    	.on('mouseover', pieTip.show)
    	.on('mouseout', pieTip.hide);
    


    g.append("path")
    	.attr("d",arc)
    	.style("fill",function(d){return color(d.data.purpose);});

    pieChart.selectAll("text").data(pie(dataset)).enter()
         .append("text")
         .attr("class","label1")
         .attr("transform", function(d) {
                   
                   
          var dist=radius+15;
          var winkel=(d.startAngle+d.endAngle)/2;
          var x=dist*Math.sin(winkel)-4;
          var y=-dist*Math.cos(winkel)-4;

          return "translate(" + x + "," + y + ")";
                })
                .attr("dy", "0.35em")
                .attr("text-anchor", "middle")

                .text(function(d){
                  return d.data.total;
                });
                
                
    var g = otherPieChart.selectAll(".arc")
    	.data(pie(d2013))
    	.enter()
    	.append("g")
    	.attr("class","arc")
    	
    	.on('mouseover', pieTip.show)
    	.on('mouseout', pieTip.hide);
    


    g.append("path")
    	.attr("d",arc)
    	.style("fill",function(d){return color(d.data.purpose);});

    otherPieChart.selectAll("text").data(pie(d2013)).enter()
         .append("text")
         .attr("class","label1")
         .attr("transform", function(d) {
                   
                   
          var dist=radius+15;
          var winkel=(d.startAngle+d.endAngle)/2;
          var x=dist*Math.sin(winkel)-4;
          var y=-dist*Math.cos(winkel)-4;

          return "translate(" + x + "," + y + ")";
                })
                .attr("dy", "0.35em")
                .attr("text-anchor", "middle")

                .text(function(d){
                  return d.data.total;
                });    
                
	 var g = thirdPieChart.selectAll(".arc")
    	.data(pie(d2014))
    	.enter()
    	.append("g")
    	.attr("class","arc")
    	
    	.on('mouseover', pieTip.show)
    	.on('mouseout', pieTip.hide);
    


    g.append("path")
    	.attr("d",arc)
    	.style("fill",function(d){return color(d.data.purpose);});

    thirdPieChart.selectAll("text").data(pie(d2014)).enter()
         .append("text")
         .attr("class","label1")
         .attr("transform", function(d) {
                   
                   
          var dist=radius+15;
          var winkel=(d.startAngle+d.endAngle)/2;
          var x=dist*Math.sin(winkel)-4;
          var y=-dist*Math.cos(winkel)-4;

          return "translate(" + x + "," + y + ")";
                })
                .attr("dy", "0.35em")
                .attr("text-anchor", "middle")

                .text(function(d){
                  return d.data.total;
                });            
        

		
	</script>

