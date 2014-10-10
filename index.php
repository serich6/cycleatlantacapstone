<?php
echo "Page loaded: good";
echo "<br>";
 $con=mysqli_connect("mysql.govathon.cycleatlanta.org","govathon12db","7Jk3WYNt","catl_govathon");
require 'Slim/Slim.php';

\Slim\Slim::registerAutoloader();
echo "Registered AutoLoader: good";
echo "<br>";
$app = new \Slim\Slim();
echo "New Slim Object: good";
echo "<br>";
 

//include '../Include/UserFactory.php';
//$ufact = new UserFactory();
//echo $ufact;        

//kelley: users/<id>/homeZIP, users/<id>/workZIP, users/<id>/schoolZIP, users/<id>/email


$app->get('/users/:id/workZIP', function ($id) use($app, $con) 
{


	    	$result = mysqli_query($con,"SELECT * FROM user WHERE id = '$id'");
	    		while($row = mysqli_fetch_array($result)) {
  					echo $row['id'] . " " . $row['workZIP'];
  					echo "<br>";
				}	
	    	mysqli_close($con);

	
});





$app->get('/users/:id/homeZIP', function ($id) use($app, $con) 
{

	    	$result = mysqli_query($con,"SELECT * FROM user WHERE id = '$id'");
	    		while($row = mysqli_fetch_array($result)) {
  					echo $row['id'] . " " . $row['homeZIP'];
  					echo "<br>";
				}	
	    	mysqli_close($con);

	
});

$app->get('/users/:id/schoolZIP', function ($id) use($app, $con) 
{

	    	$result = mysqli_query($con,"SELECT * FROM user WHERE id = '$id'");
	    		while($row = mysqli_fetch_array($result)) {
  					echo $row['id'] . " " . $row['schoolZIP'];
  					echo "<br>";
				}	
	    	mysqli_close($con);

	
});

$app->get('/users/:id/email', function ($id) use($app, $con) 
{

	    	$result = mysqli_query($con,"SELECT * FROM user WHERE id = '$id'");
	    		while($row = mysqli_fetch_array($result)) {
  					echo $row['id'] . " " . $row['email'];
  					echo "<br>";
				}	
	    	mysqli_close($con);

	
});

//Dhruv: income, rider type, rider history,rider frequency
$app->get('/users/:id/income', function ($id) use($app, $con) 
{

	    	$result = mysqli_query($con,"SELECT * FROM user WHERE id = '$id'");
	    		while($row = mysqli_fetch_array($result)) {
  					echo $row['id'] . " " . $row['income'];
  					echo "<br>";
				}	
	    	mysqli_close($con);
});

$app->get('/users/:id/rider_type', function ($id) use($app, $con) 
{

	    	$result = mysqli_query($con,"SELECT * FROM user WHERE id = '$id'");
	    		while($row = mysqli_fetch_array($result)) {
  					echo $row['id'] . " " . $row['rider_type'];
  					echo "<br>";
				}	
	    	mysqli_close($con);

});

$app->get('/users/:id/rider_history', function ($id) use($app, $con) 
{

	    	$result = mysqli_query($con,"SELECT * FROM user WHERE id = '$id'");
	    		while($row = mysqli_fetch_array($result)) {
  					echo $row['id'] . " " . $row['rider_history'];
  					echo "<br>";
				}	
	    	mysqli_close($con);

});

$app->get('/users/:id/cycling_freq', function ($id) use($app, $con) 
{

	    	$result = mysqli_query($con,"SELECT * FROM user WHERE id = '$id'");
	    		while($row = mysqli_fetch_array($result)) {
  					echo $row['id'] . " " . $row['cycling_freq'];
  					echo "<br>";
				}	
	    	mysqli_close($con);

});




//Kelley: filtering methods
//$paramValue = $app->request()->params('paramName');

$app->get('/users',  function () use($app, $con)  {
		 
          $paramValue = $app->request()->params(); //this gets the params, in an array
          //what I want to do is to loop through the array, it doesn't care about 
          //? or &, it gets the params just fine
          //loop through the array, and for each filter, find the data, and add it to the
          //return format
          foreach($paramValue as $type=>$val)
          {
          	if($type == "age")
          	{
          		$result = mysqli_query($con,"SELECT * FROM user WHERE Age = '$val'");
	    		while($row = mysqli_fetch_array($result)) {
  					echo $row['id'] . " " . $row['age'];
  					echo "<br>";
				}	
          	}
          	if($type == "gender")
          	{
          		$result = mysqli_query($con,"SELECT * FROM user WHERE gender = '$val'");
	    		while($row = mysqli_fetch_array($result)) {
  					echo $row['id'] . " " . $row['gender'];
  					echo "<br>";
				}	
          	}
          	if($type == "ethnicity")
          	{
          		$result = mysqli_query($con,"SELECT * FROM user WHERE ethnicity = '$val'");
	    		while($row = mysqli_fetch_array($result)) {
  					echo $row['id'] . " " . $row['ethnicity'];
  					echo "<br>";
				}	
          	}
          	if($type == "income")
          	{
          		$result = mysqli_query($con,"SELECT * FROM user WHERE income = '$val'");
	    		while($row = mysqli_fetch_array($result)) {
  					echo $row['id'] . " " . $row['income'];
  					echo "<br>";
				}	
          	}
          }
          var_dump( $paramValue );
         
	    	mysqli_close($con);
});







$app->get('/users:?gender=:value', function ($value) {


	

echo "<br>";

 //testing github push :D (Sam)
  


	if($value=="male")
	{
	    	$con=mysqli_connect("mysql.govathon.cycleatlanta.org","govathon12db","7Jk3WYNt","catl_govathon");
	    	$result = mysqli_query($con,"SELECT * FROM user WHERE gender = '2'");
	    		while($row = mysqli_fetch_array($result)) {
  					echo $row['id'] . " " . $row['gender'];
  					echo "<br>";
				}	
	    	mysqli_close($con);
	  	}
	
	  	if($value == "female")
	{

	    	$con=mysqli_connect("mysql.govathon.cycleatlanta.org","govathon12db","7Jk3WYNt","catl_govathon");
	    	$result = mysqli_query($con,"SELECT * FROM user WHERE gender = '1'");
	    		while($row = mysqli_fetch_array($result)) {
  					echo $row['id'] . " " . $row['gender'];
  					echo "<br>";
				}	
	    	mysqli_close($con);
	  	}
	//  }
	  	
  
});





$app->get('/hello/:name', function ($name) {



	 $con=mysqli_connect("mysql.govathon.cycleatlanta.org","govathon12db","7Jk3WYNt","catl_govathon");
	    $result = mysqli_query($con,"SELECT * FROM trip");
	    while($row = mysqli_fetch_array($result)) {
  			echo $row['user_id'] . " " . $row['purpose'];
  			echo "<br>";
		}	
	    mysqli_close($con);
    echo "Hello, $name";
});




$app->run();
?>