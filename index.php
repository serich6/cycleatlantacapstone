<?php
echo "Page loaded: good";
echo "<br>";

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


$app->get('/users/:id/workZIP', function ($id)
{
$con=mysqli_connect("mysql.govathon.cycleatlanta.org","govathon12db","7Jk3WYNt","catl_govathon");
	    	$result = mysqli_query($con,"SELECT * FROM user WHERE id = 10");
	    		while($row = mysqli_fetch_array($result)) {
  					echo $row['id'] . " " . $row['workZIP'];
  					echo "<br>";
				}	
	    	mysqli_close($con);

	
});

$app->get('/users/:id/homeZIP', function ($id)
{
$con=mysqli_connect("mysql.govathon.cycleatlanta.org","govathon12db","7Jk3WYNt","catl_govathon");
	    	$result = mysqli_query($con,"SELECT * FROM user WHERE id = 10");
	    		while($row = mysqli_fetch_array($result)) {
  					echo $row['id'] . " " . $row['homeZIP'];
  					echo "<br>";
				}	
	    	mysqli_close($con);

	
});

$app->get('/users/:id/schoolZIP', function ($id)
{
$con=mysqli_connect("mysql.govathon.cycleatlanta.org","govathon12db","7Jk3WYNt","catl_govathon");
	    	$result = mysqli_query($con,"SELECT * FROM user WHERE id = 10");
	    		while($row = mysqli_fetch_array($result)) {
  					echo $row['id'] . " " . $row['schoolZIP'];
  					echo "<br>";
				}	
	    	mysqli_close($con);

	
});

$app->get('/users/:id/email', function ($id)
{
$con=mysqli_connect("mysql.govathon.cycleatlanta.org","govathon12db","7Jk3WYNt","catl_govathon");
	    	$result = mysqli_query($con,"SELECT * FROM user WHERE id = 10");
	    		while($row = mysqli_fetch_array($result)) {
  					echo $row['id'] . " " . $row['email'];
  					echo "<br>";
				}	
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