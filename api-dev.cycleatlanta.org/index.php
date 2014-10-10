


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

$app->get('/retrieve/:gender', function ($gender) {


	

echo "<br>";

 
  


	if($gender=="male")
	{
	    	$con=mysqli_connect("mysql.govathon.cycleatlanta.org","govathon12db","7Jk3WYNt","catl_govathon");
	    	$result = mysqli_query($con,"SELECT * FROM user WHERE gender = '2'");
	    		while($row = mysqli_fetch_array($result)) {
  					echo $row['id'] . " " . $row['gender'];
  					echo "<br>";
				}	
	    	mysqli_close($con);
	  	}
	
	  	if($gender == "female")
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