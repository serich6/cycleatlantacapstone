<?php
echo "Page loaded: good";
echo "<br>";
 $con=mysqli_connect("mysql.govathon.cycleatlanta.org","govathon12db","7Jk3WYNt","catl_govathon");
require 'Slim/Slim.php';
require_once('include/UserFactory.php');

Slim\Slim::registerAutoloader();
echo "Registered AutoLoader: good";
echo "<br>";
$app = new \Slim\Slim();
$app->add(new \Slim\Middleware\ContentTypes());
echo "New Slim Object: good";
echo "<br>";


//Yan: register new user
$app->post('/register', function () use($app, $con) 
{

	//get the parameters sent over as JSON 
    $body = $app->request()->params();
    //initialize key value variables   
	$values = '';
	$keys = '';
	
	//the new email that the user just had input
	$userEmail;

	//loop through the JSON data
	foreach($body as $k=>$v)
	{	
		//create a comma separated string of keys and values to pass to SQL
		$keys .= $k.",";
        $values .= '"'.$v.'"'.",";
		
		if($k == 'email'){
			echo "<br>";
			echo "your email: ";
			echo $v;
			echo "<br>";
			$userEmail = $v;
		}
	
    }
	
	//store all emails in an array
	$result = mysqli_query($con, "SELECT email FROM user");
	$emailArray = Array();
	while ($row = mysqli_fetch_array($result)) {
		$emailArray[] =  $row['email'];  
	}
	
	

	$invalidEmail = false;
	//check if new email matches with any of the emails in db 
	foreach($emailArray as $email){
		if($email == $userEmail){
			echo "That email already exists";
			$invalidEmail = true;
		}
	}
	
	
	if($invalidEmail == false){
		//knock off the last comma at the end 
		$keys = substr($keys, 0, -1);
		$values = substr($values, 0, -1);
		//build the query, we're adding to the user table for this POST    
		$query = "Insert INTO user (".$keys.") VALUES (".$values.")";
		//try-catch block, make sure we can try to insert and not break things      		
		  try
		  {    		
			mysqli_query($con, $query);
		  } catch(PDOException $e) 
		  {
			echo '{"error":{"text":'. $e->getMessage() .'}}';
		  }
				
		//for debugging purposes, make sure query looks like it should      	
		echo $query;
	}
	
	
    }); 
 

//kelley: post user
//kelley's iphone uuid: a0d546c1224dfe5fb192e28837ab0447f01be3d6
//user fields: device = ^, email = none, age = 2, gender = 1, income = 1, ethnicity = 1, homeZIP = 30032,
//schoolZIP = 30032, workZIP = 30032, cycling_freq = 1, rider_history = 1, rider_type = 1, app_version = 1.0


$app->post('/users/user', function () use($app, $con) 
{
	//get the parameters sent over as JSON 
    $body = $app->request()->params();
    //initialize key value variables   
	$values = '';
	$keys = '';
	//loop through the JSON data
	foreach($body as $k=>$v)
	{	
		//create a comma separated string of keys and values to pass to SQL
		$keys .= $k.",";
        $values .= '"'.$v.'"'.",";
	
    }
    //knock off the last comma at the end 
    $keys = substr($keys, 0, -1);
    $values = substr($values, 0, -1);
    //build the query, we're adding to the user table for this POST    
    $query = "Insert INTO user (".$keys.") VALUES (".$values.")";
    //try-catch block, make sure we can try to insert and not break things      		
      try
      {    		
        mysqli_query($con, $query);
      } catch(PDOException $e) 
      {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
      }
	    	
    //for debugging purposes, make sure query looks like it should      	
    echo $query;

    });
    
//kelley: PUT

$app->put('/users/user/:id', function ($id) use($app, $con) 
{
			//only works for workZIP
			/***********************
			@todo:
			To make this most efficient, we will need to implement something
			similar to the POST call. For anything PUT related for a USER,
			we should parse all the inputs, then create a SQL query based on
			that
			**************************/
			
			
    		$body = $app->request()->getBody();
    		$workZIP = '';
    		foreach($body as $k=>$v)
			{	
				
				if($k == 'workZIP')
				{
					$workZIP = $v;
					echo $v;
				}
			
       			
	
    		}
			
			
		echo $workZIP;
	     mysqli_query($con,"UPDATE user SET workZIP = '$workZIP' WHERE id = '$id'");
	    		
				
	    	mysqli_close($con);

	
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
			if($type == "rider_type")
          	{
          		$result = mysqli_query($con,"SELECT * FROM user WHERE income = '$val'");
	    		while($row = mysqli_fetch_array($result)) {
  					echo $row['id'] . " " . $row['rider_type'];
  					echo "<br>";
				}	
          	}
			if($type == "rider_history")
          	{
          		$result = mysqli_query($con,"SELECT * FROM user WHERE income = '$val'");
	    		while($row = mysqli_fetch_array($result)) {
  					echo $row['id'] . " " . $row['rider_history'];
  					echo "<br>";
				}	
          	}
			if($type == "homeZIP")
          	{
          		$result = mysqli_query($con,"SELECT * FROM user WHERE income = '$val'");
	    		while($row = mysqli_fetch_array($result)) {
  					echo $row['id'] . " " . $row['homeZIP'];
  					echo "<br>";
				}	
          	}
          }
          var_dump( $paramValue );
         
	    	mysqli_close($con);
});















$app->run();
?>