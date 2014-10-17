<?php
echo "Page loaded: good";
echo "<br>";
 $con=mysqli_connect("mysql.govathon.cycleatlanta.org","govathon12db","7Jk3WYNt","catl_govathon");
require 'Slim/Slim.php';
require_once('../Include/UserFactory.php');

\Slim\Slim::registerAutoloader();
echo "Registered AutoLoader: good";
echo "<br>";
$app = new \Slim\Slim();
$app->add(new \Slim\Middleware\ContentTypes());
echo "New Slim Object: good";
echo "<br>";
 

//kelley: post user
//kelley's iphone uuid: a0d546c1224dfe5fb192e28837ab0447f01be3d6
//user fields: device = ^, email = none, age = 2, gender = 1, income = 1, ethnicity = 1, homeZIP = 30032,
//schoolZIP = 30032, workZIP = 30032, cycling_freq = 1, rider_history = 1, rider_type = 1, app_version = 1.0


$app->post('/users/user', function () use($app, $con) 
{


    $body = $app->request()->params();
    
    
	$query = "Insert INTO user (".''.") VALUES (".''.")";
	$values = '';
	$keys = '';
	foreach($body as $k=>$v)
	{
		$keys .= $k.",";
        $values .= '"'.$v.'"'.",";
	
    }
    $keys = substr($keys, 0, -1);
    $values = substr($values, 0, -1);
         
          $query = "Insert INTO user (".$keys.") VALUES (".$values.")";
          		
          		
        mysqli_query($con, $query);
	    	
          	
    echo $query;
  /*  try {
        $db = $con;
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $db->lastInsertId());
        $stmt->bindParam("device", $newUser->device);
        $stmt->bindParam("email", $newUser->email);
        $stmt->bindParam("age", $newUser->age);
        $stmt->bindParam("gender", $newUser->gender);
        $stmt->bindParam("income", $newUser->income);
        $stmt->bindParam("ethnicity", $newUser->ethnicity);
        $stmt->bindParam("homeZIP", $newUser->homeZIP);
        $stmt->bindParam("schoolZIP", $newUser->schoolZIP);
        $stmt->bindParam("workZIP", $newUser->workZIP);
        $stmt->bindParam("cycling_freq", $newUser->cycling_freq);
        $stmt->bindParam("rider_history", $newUser->rider_history);
        $stmt->bindParam("rider_type", $newUser->rider_type);
        $stmt->bindParam("app_version", $newUser->app_version);
        $stmt->execute();
        
        $db = null;
        echo json_encode($newUser);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }*/
    });

//kelley: users/<id>/homeZIP, users/<id>/workZIP, users/<id>/schoolZIP, users/<id>/email


$app->get('/users/:id/workZIP', function ($id) use($app, $con) 
{
			$user = UserFactory::getUser($id); //how to access methods in factory files
			var_dump($user);
	
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


//Yan
//users/<id>/ethnicity, users/<id>/created, users/<id>/device

$app->get('/users/:id/ethnicity', function ($id) use($app, $con) 
{

	    	$result = mysqli_query($con,"SELECT * FROM user WHERE id = '$id'");
			$ethnicityID;
			
	    		while($row = mysqli_fetch_array($result)) {
  					echo $row['id'] . " " . $row['ethnicity'];
					$ethnicityID = $row['ethnicity'];
				}	
				
			$result = mysqli_query($con,"SELECT * FROM ethnicity WHERE id = '$ethnicityID'");
	    		while($row = mysqli_fetch_array($result)) {
  					echo " (" . $row['text'] . ") ";
				}
			mysqli_close($con);
});				


$app->get('/users/:id/device', function ($id) use($app, $con) 
{

	    	$result = mysqli_query($con,"SELECT * FROM user WHERE id = '$id'");
		
			
	    		while($row = mysqli_fetch_array($result)) {
  					echo $row['id'] . " " . $row['device'];
					echo "<br>";
				}	
				
			
	    	mysqli_close($con);

	
});

$app->get('/users/:id/created', function ($id) use($app, $con) 
{

	    	$result = mysqli_query($con,"SELECT * FROM user WHERE id = '$id'");
		
			
	    		while($row = mysqli_fetch_array($result)) {
  					echo $row['id'] . " " . $row['created'];
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

//Sam  users/<id>, users/<id>/age, users/<id>/gender

//Get a specific user's information for authentication
$app->get('/users/:id', function ($id) use($app, $con) 
{
			//need to use this for authentication purposes, hopefully will later pull back a password as well?
	    	$result = mysqli_query($con,"SELECT * FROM user WHERE id = '$id'");
	    		while($row = mysqli_fetch_array($result)) {
  					echo $row['id'] . " " . $row['email'];
  					echo "<br>";
				}	
	    	mysqli_close($con);

});
//Get a specific user's age
$app->get('/users/:id/age', function ($id) use($app, $con) 
{
	    	$result = mysqli_query($con,"SELECT * FROM user WHERE id = '$id'");
	    		while($row = mysqli_fetch_array($result)) {
  					echo $row['id'] . " " . $row['age'];
  					echo "<br>";
				}	
	    	mysqli_close($con);

});
//Get a specific user's gender
$app->get('/users/:id/gender', function ($id) use($app, $con) 
{
			$result = mysqli_query($con,"SELECT * FROM user WHERE id = '$id'");
	    		while($row = mysqli_fetch_array($result)) {
  					echo $row['id'] . " " . $row['gender'];
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
          //return format (kelley)
          
          
          /*******************************
          
          
          NOTE: (kelley)
          This, as it stands, does not check for duplicates. These calls
          simply find ALL results matching that query. To truly do filtering,
          we will need to do more with the code in order to check for duplicate data.
          
          For example, if we did a filter call checking for users in a certain income
          bracket and for users who are female, right now you could get a list
          of ALL users who are in an income bracket and ALL users who are female.
          
          
          When we get to a point to do JSON creation, we will need to make sure
          filtering finds us users with the right attributes, not just a dump of
          all users of a certain criteria.
          
          
          **********************************/
          
          /**
          **********************************
          This is the code from the POST calls. This builds a SQL query.
          It needs to be refactored to use for filtering. But, there are many
          ways we can build a SQL statement to filter for us. We will need to
          do some research on the easiest way to just get this up and running
          
          
          $query = "SELECT INTO user (".''.") VALUES (".''.")";
		  $values = '';
	      $keys = '';
	      foreach($body as $k=>$v)
	      {
		    $keys .= $k.",";
            $values .= '"'.$v.'"'.",";
	
          }
          $keys = substr($keys, 0, -1);
          $values = substr($values, 0, -1);
         
          $query = "Insert INTO user (".$keys.") VALUES (".$values.")";
          */
          
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















$app->run();
?>