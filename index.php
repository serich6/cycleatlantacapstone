<?php
//session_cache_limiter(false);
session_start();
//echo "Page loaded: good";
//echo "<br>";
 $con=mysqli_connect("mysql.govathon.cycleatlanta.org","govathon12db","7Jk3WYNt","catl_govathon");
require 'Slim/Slim.php';
require_once('include/UserFactory.php');

/*********************
since we never actually used the dir structure live in prod for our dev,
we need to include this line like this:
require_once('../include/UserFactory.php');

to access anything we need from the include directory when we go live to
production
***********************/

Slim\Slim::registerAutoloader();
//echo "Registered AutoLoader: good";
//echo "<br>";
$app = new \Slim\Slim();
$app->add(new \Slim\Middleware\ContentTypes());
//echo "New Slim Object: good";
//echo "<br>";


// These constants may be changed without breaking existing hashes.
define("PBKDF2_HASH_ALGORITHM", "sha256");
define("PBKDF2_ITERATIONS", 1000);
define("PBKDF2_SALT_BYTE_SIZE", 24);
define("PBKDF2_HASH_BYTE_SIZE", 24);

define("HASH_SECTIONS", 4);
define("HASH_ALGORITHM_INDEX", 0);
define("HASH_ITERATION_INDEX", 1);
define("HASH_SALT_INDEX", 2);
define("HASH_PBKDF2_INDEX", 3);

function create_hash($password, $salt)
{
    // format: algorithm:iterations:salt:hash
    $mySalt = $salt;
    return PBKDF2_HASH_ALGORITHM . ":" . PBKDF2_ITERATIONS . ":" .  $mySalt . ":" .
        base64_encode(pbkdf2(
            PBKDF2_HASH_ALGORITHM,
            $password,
            $mySalt,
            PBKDF2_ITERATIONS,
            PBKDF2_HASH_BYTE_SIZE,
            true
        ));
}

function create_salt(){
	$salt = base64_encode(mcrypt_create_iv(PBKDF2_SALT_BYTE_SIZE, MCRYPT_DEV_URANDOM));
	return $salt;
}

function validate_password($password, $correct_hash)
{
    $params = explode(":", $correct_hash);
    if(count($params) < HASH_SECTIONS)
       return false;
    $pbkdf2 = base64_decode($params[HASH_PBKDF2_INDEX]);
    return slow_equals(
        $pbkdf2,
        pbkdf2(
            $params[HASH_ALGORITHM_INDEX],
            $password,
            $params[HASH_SALT_INDEX],
            (int)$params[HASH_ITERATION_INDEX],
            strlen($pbkdf2),
            true
        )
    );
}

// Compares two strings $a and $b in length-constant time.
function slow_equals($a, $b)
{
    $diff = strlen($a) ^ strlen($b);
    for($i = 0; $i < strlen($a) && $i < strlen($b); $i++)
    {
        $diff |= ord($a[$i]) ^ ord($b[$i]);
    }
    return $diff === 0;
}

/*
 * PBKDF2 key derivation function as defined by RSA's PKCS #5: https://www.ietf.org/rfc/rfc2898.txt
 * $algorithm - The hash algorithm to use. Recommended: SHA256
 * $password - The password.
 * $salt - A salt that is unique to the password.
 * $count - Iteration count. Higher is better, but slower. Recommended: At least 1000.
 * $key_length - The length of the derived key in bytes.
 * $raw_output - If true, the key is returned in raw binary format. Hex encoded otherwise.
 * Returns: A $key_length-byte key derived from the password and salt.
 *
 * Test vectors can be found here: https://www.ietf.org/rfc/rfc6070.txt
 *
 * This implementation of PBKDF2 was originally created by https://defuse.ca
 * With improvements by http://www.variations-of-shadow.com
 */
function pbkdf2($algorithm, $password, $salt, $count, $key_length, $raw_output = false)
{
    $algorithm = strtolower($algorithm);
    if(!in_array($algorithm, hash_algos(), true))
        trigger_error('PBKDF2 ERROR: Invalid hash algorithm.', E_USER_ERROR);
    if($count <= 0 || $key_length <= 0)
        trigger_error('PBKDF2 ERROR: Invalid parameters.', E_USER_ERROR);

    if (function_exists("hash_pbkdf2")) {
        // The output length is in NIBBLES (4-bits) if $raw_output is false!
        if (!$raw_output) {
            $key_length = $key_length * 2;
        }
        return hash_pbkdf2($algorithm, $password, $salt, $count, $key_length, $raw_output);
    }

    $hash_length = strlen(hash($algorithm, "", true));
    $block_count = ceil($key_length / $hash_length);

    $output = "";
    for($i = 1; $i <= $block_count; $i++) {
        // $i encoded as 4 bytes, big endian.
        $last = $salt . pack("N", $i);
        // first iteration
        $last = $xorsum = hash_hmac($algorithm, $last, $password, true);
        // perform the other $count - 1 iterations
        for ($j = 1; $j < $count; $j++) {
            $xorsum ^= ($last = hash_hmac($algorithm, $last, $password, true));
        }
        $output .= $xorsum;
    }

    if($raw_output)
        return substr($output, 0, $key_length);
    else
        return bin2hex(substr($output, 0, $key_length));
}

//Yan: Login
$app->post('/login', function () use($app, $con) 
{
	//get the parameters sent over as JSON 
    $body = $app->request()->params();
    //initialize key value variables   
	$values = '';
	$keys = '';
	
	$emailInput = '';
	$passwordInput = '';
	
	//loop through the JSON data
	foreach($body as $k=>$v)
	{	
		if($k == 'password'){
			$passwordInput = $v;
		}
		if($k == 'email'){
			$emailInput = $v;
		}
		
    }
	
	
	//echo $emailInput;
	//echo '<br>';
	//echo $passwordInput;
	
	$emailExists = false;
	$retrievedSalt = '';
	$retrievedPassword = '';
	//$userID = '';
	$result = mysqli_query($con, "SELECT email,salt,password, user_id FROM user_password");
	while($row = mysqli_fetch_array($result)) {
		if($row['email'] == $emailInput){
			$retrievedSalt = $row['salt'];
			$retrievedPassword = $row['password'];
			$userID = $row['user_id'];
			$_SESSION["uID"] = $row['user_id'];
			$_SESSION["username"] = $row['email'];
			session_write_close();
			//echo $retrievedPassword;
			//echo $retrievedSalt;
			$emailExists = true; 
		}
	}
	

	if($emailExists == true){
		$hash = create_hash($passwordInput, $retrievedSalt);
		//echo $hash;
		if($hash == $retrievedPassword){
			//echo 'Login success!';
			//echo $userID;
			
			//echo $_SESSION["uID"];
			
			header('Location:../portal.php');
			exit();
			
			
		}
		else{
			
			header('Location:../badLogin.html');
			exit();
		}
	}
	else{
		header('Location:../badLogin.html');
		exit();
	}
	exit();
	
}); 
 


//Yan: register new user
$app->post('/register', function () use($app, $con) 
{

	//get the parameters sent over as JSON 
    $body = $app->request()->params();
    //initialize key value variables   
	$values = '';
	$keys = '';
	
	//the new email that the user just had input
	$userEmail = '';
	$userPassword = '';
	$userId = '';
	
	//loop through the JSON data
	foreach($body as $k=>$v)
	{	
		if($k != 'password'){//only add fields for the 'user' table
			//create a comma separated string of keys and values to pass to SQL
			$keys .= $k.",";
			$values .= '"'.$v.'"'.",";
		}
		if($k == 'password'){
			$userPassword = $v;
		}
		if($k == 'email'){
			$userEmail = $v;
		}
		
    }
	
	$invalidEmail = false;
	//store all emails in an array for comparison purposes 
	$result = mysqli_query($con, "SELECT email FROM user");
	$emailArray = Array();
	while ($row = mysqli_fetch_array($result)) {
		$emailArray[] =  $row['email'];  
	}
	
	//check if new email matches with any of the emails in db 
	foreach($emailArray as $email){
		if($email == $userEmail){
			//echo "That email already exists";
			$invalidEmail = true;
		}
	}
	
	//go ahead and POST into 'user' table
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
			//echo '{"error":{"text":'. $e->getMessage() .'}}';
		  }
		  
		  
		$result = mysqli_query($con, "SELECT id,email FROM user");
		while($row = mysqli_fetch_array($result)) {
			if($row['email'] == $userEmail){
				$userId = $row['id'];
			}
		}
		
		//encrypt their password
		$salt = create_salt();
		$hash = create_hash($userPassword, $salt);
		//echo $hash;
		//POST into UserPassword table 	
		$query = "Insert INTO user_password (user_id, password, email, salt)
				VALUES ('$userId','$hash','$userEmail','$salt');";
	
		  try
		  {    		
			mysqli_query($con, $query);
			
			
		  } catch(PDOException $e) 
		  {
			//echo '{"error":{"text":'. $e->getMessage() .'}}';
		  }
		header('Location:../userCreated.php');
		exit();
		
	}//end post

	
	
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
//dhruv POST notes
$app->post('/notes/note', function () use($app, $con) 
{
    $body = $app->request()->params();
	$values = '';
	$keys = '';
	foreach($body as $k=>$v)
	{	
		//create a comma separated string of keys and values to pass to SQL
		
		$keys .= $k.",";
        $values .= '"'.$v.'"'.",";
	
    }
    $keys = substr($keys, 0, -1);
    $values = substr($values, 0, -1);
    $query = "Insert INTO note (".$keys.") VALUES (".$values.")";
      try
      {    		
        mysqli_query($con, $query);
      } catch(PDOException $e) 
      {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
      }
	    	
    echo $query;

    });

//Sam POST trip (need to work on getting back the global ID

$app->post('/trips/trip', function () use($app, $con) 
{
	//get the parameters sent over as JSON 
    $body = $app->request()->params();
    //for testing purposes only
    var_dump($body);
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
    $query = "Insert INTO trip (".$keys.") VALUES (".$values.")";
    //try-catch block, make sure we can try to insert and not break things      		
      try
      {    		
        mysqli_query($con, $query);
      } catch(PDOException $e) 
      {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
      }
	    	
    //for debugging purposes, make sure query looks like it should      	
    header('Location:../../tripMainView.php');
    exit();

	//NEED TO GET THE GLOBAL TRIP ID BACK
	
	});



//kelley: PUT

/**User PUT for multiple params
*/

$app->put('/users/user', function () use($app, $con)
{

	$body = $app->request()->getBody();
	$id='';
	$age='';
	$email='';
	$gender='';
	$income='';
	$ethnicity='';
	$homeZIP='';
	$schoolZIP='';
	$workZIP='';
	$cycling_freq='';
	$rider_type='';
	foreach($body as $k=>$v)
	{
		if($k=="id")
		{
			$id=$v;
		}
		if($k=="homeZIP")
		{
			$homeZIP=$v;
		}
		if($k=="workZIP")
		{
			$workZIP=$v;
		}
		if($k=="schoolZIP")
		{
			$schoolZIP=$v;
		}
		if($k=="cycling_freq")
		{
			$cycling_freq=$v;
		}
		if($k=="rider_type")
		{
			$rider_type=$v;
		}
		if($k=="email")
		{
			$email=$v;
		}
		
	}	
	
	$query = "UPDATE user SET";	
	
	
	//if(isset($age)){
	if($age!=''){
		$query = $query . " 'age' = " . $age . " ,";
	}
	
	//if(isset($gender)){
	if($gender!=''){
		$query = $query . " 'gender' = " . $gender . " ,";
	}

	//if(isset($income)){
	if($income!=''){
		$query = $query . " income = " . $income . " ,";
	}
	//if(isset($ethnicity)){
	if($ethnicity!=''){
		$query = $query . " ethnicity = " . $ethnicity . " ,";
	}
	//if(isset($homeZIP)){
	if($homeZIP!=''){
		$query = $query . " homeZIP = " . $homeZIP . " ,";
	}
	//if(isset($schoolZIP)){
	if($schoolZIP!=''){
		$query = $query . " schoolZIP = " . $schoolZIP . " ,";
	}
	//if(isset($workZIP)){
	if($workZIP!=''){
		$query = $query . " workZIP = " . $workZIP . " ,";
	}
	//if(isset($cycling_freq)){
	if($cycling_freq!=''){
		$query = $query . " cycling_freq = " . $cycling_freq . " ,";
	}
	//if(isset($rider_type)){
	if($rider_type!=''){
		$query = $query . " rider_type = " . $rider_type . " ,";
	}	
	if($email!=''){
		$query = $query . " email = " . "'".$email."'"." ,";
		//$query2 = " email = " . "'".$email."'";
		$query2 = "UPDATE user_password SET email = '$email' WHERE user_id = '$id'";//need to also update the user_password table
	}	

	//take of the last AND
	$query = substr($query, 0, -1);
	if(isset($id)){
		$query = $query . "WHERE" . " id = " . $id ;
		//$query2 = $query2 . "WHERE" . " user_id = " . $id ;
	}
		
	//echo $query2;
	//echo '<br>';
	
	//need to check to see if there are NO parameters, the "w" character needs to be taken from the string
	if(substr($query, -1)== 'W'){
		$query = substr($query, 0, -1);
		//$query2 = substr($query2, 0, -1);
	}
	
	mysqli_query($con, $query2);
	mysqli_query($con, $query);
	
	
	$result = array("status" => "success");
	json_encode($result);
	$response = $app->response();
   	$response['Content-Type'] = 'application/json';
    $data = $response->body(json_encode($result));
    return $data;
});


$app->put('/users/user/:id/workZip', function ($id) use($app, $con) 
{
						
    		$body = $app->request()->getBody();    		
    		$workZIP = '';
    		foreach($body as $k=>$v)
			{					
				
				if($k == 'workZIP')
				{
					
					$workZIP = $v;
					
				}		
       			
	
    		}		
		
	    	 mysqli_query($con,"UPDATE user SET workZIP = '$workZIP' WHERE id = '$id'");
	    	 mysqli_close($con);
	    	 $result = array("status" => "success");
			 json_encode($result);
			 $response = $app->response();
   	         $response['Content-Type'] = 'application/json';
             $data = $response->body(json_encode($result));
             return $data;

	
});    

$app->put('/users/user/:id/schoolZip', function ($id) use($app, $con) 
{
						
    		$body = $app->request()->getBody();    		
    		$schoolZip = '';
    		foreach($body as $k=>$v)
			{					
				
				if($k == 'schoolZip')
				{
					
					$schoolZip = $v;
					
				}		
       			
	
    		}			
		
	    	 mysqli_query($con,"UPDATE user SET schoolZIP = '$schoolZIP' WHERE id = '$id'");
	    	 mysqli_close($con);
	    	 $result = array("status" => "success");
			 json_encode($result);
			 $response = $app->response();
   		 	 $response['Content-Type'] = 'application/json';
    	 	 $data = $response->body(json_encode($result));
    		 return $data;
			
	
});    


$app->put('/users/user/:id/email', function ($id) use($app, $con) 
{
						
    		$body = $app->request()->getBody();    		
    		$email = '';
    		foreach($body as $k=>$v)
			{					
				
				if($k == 'email')
				{
					
					$email = $v;
					
				}		
       			
	
    		}			
		
	    	 mysqli_query($con,"UPDATE user SET email = '$email' WHERE id = '$id'");
	    	 mysqli_close($con);
	    	 $result = array("status" => "success");
			 json_encode($result);
			 $response = $app->response();
   			 $response['Content-Type'] = 'application/json';
    		 $data = $response->body(json_encode($result));
    		 return $data;

	
});    
//dhruv put / patch income, rider_type, rider_history, cycling_freq

$app->put('/users/user/:id/income', function ($id) use($app, $con) 
{		
    		$body = $app->request()->getBody();    		
    		$income = '';
    		foreach($body as $k=>$v)
			{					
				if($k == 'income')
				{
					$income = $v;	
				}		
    		}			
	    	 mysqli_query($con,"UPDATE user SET income = '$income' WHERE id = '$id'");
	    	 mysqli_close($con);
}); 

$app->put('/users/user/:id/rider_type', function ($id) use($app, $con) 
{		
    		$body = $app->request()->getBody();    		
    		$rider_type = '';
    		foreach($body as $k=>$v)
			{					
				if($k == 'rider_type')
				{
					$rider_type = $v;	
				}		
    		}			
	    	 mysqli_query($con,"UPDATE user SET rider_type = '$rider_type' WHERE id = '$id'");
	    	 mysqli_close($con);
}); 


$app->put('/users/user/:id/rider_history', function ($id) use($app, $con) 
{		
    		$body = $app->request()->getBody();    		
    		$rider_history = '';
    		foreach($body as $k=>$v)
			{					
				if($k == 'rider_history')
				{
					$rider_history = $v;	
				}		
    		}			
	    	 mysqli_query($con,"UPDATE user SET rider_history = '$rider_history' WHERE id = '$id'");
	    	 mysqli_close($con);
}); 

$app->put('/users/user/:id/cycling_freq', function ($id) use($app, $con) 
{		
    		$body = $app->request()->getBody();    		
    		$cycling_freq = '';
    		foreach($body as $k=>$v)
			{					
				if($k == 'cycling_freq')
				{
					$cycling_freq = $v;	
				}		
    		}			
	    	 mysqli_query($con,"UPDATE user SET cycling_freq = '$cycling_freq' WHERE id = '$id'");
	    	 mysqli_close($con);
}); 
$app->put('/users/user/:id/age', function ($id) use($app, $con) 
{		
    		$body = $app->request()->getBody();    		
    		$age = '';
    		foreach($body as $k=>$v)
			{					
				if($k == 'age')
				{
					$age = $v;	
				}		
    		}			
	    	 mysqli_query($con,"UPDATE user SET age = '$age' WHERE id = '$id'");
	    	 mysqli_close($con);
}); 
$app->put('/users/user/:id/gender', function ($id) use($app, $con) 
{		
    		$body = $app->request()->getBody();    		
    		$gender = '';
    		foreach($body as $k=>$v)
			{					
				if($k == 'gender')
				{
					$gender = $v;	
				}		
    		}			
	    	 mysqli_query($con,"UPDATE user SET gender = '$gender' WHERE id = '$id'");
	    	 mysqli_close($con);
}); 
$app->put('/users/user/:id/ethnicity', function ($id) use($app, $con) 
{		
    		$body = $app->request()->getBody();    		
    		$ethnicity = '';
    		foreach($body as $k=>$v)
			{					
				if($k == 'ethnicity')
				{
					$ethnicity = $v;	
				}		
    		}			
	    	 mysqli_query($con,"UPDATE user SET ethnicity = '$ethnicity' WHERE id = '$id'");
	    	 mysqli_close($con);
}); 

//kelley: users/<id>/homeZIP, users/<id>/workZIP, users/<id>/schoolZIP, users/<id>/email

$app->get('/trips/:id/', function ($id) use($app, $con) 
{
		//	$user = UserFactory::getUser($id); //how to access methods in factory files
		//	var_dump($user);
	
	    	$result = mysqli_query($con,"SELECT * FROM trip WHERE user_id = '$id'  ORDER BY stop DESC");
	    
	    //		while($row = mysqli_fetch_array($result)) {
  		//			echo $row['id'] . " " . $row['workZIP'];
  		//			echo "<br>";
		//		}	
	    	mysqli_close($con);
	    	$rows = array();
	    	while($r = mysqli_fetch_assoc($result))
	    	{
	    	
	    		$rows[] = $r;
	    	}
	      	$response = $app->response();
   		  	$response['Content-Type'] = 'application/json';
   		 
    	  $response->body(json_encode($rows));
    	  $data = $response->body(json_encode($rows));
    	  return $data;
    	  exit();

	
});


$app->get('/users/:id/workZIP', function ($id) use($app, $con) 
{
		//	$user = UserFactory::getUser($id); //how to access methods in factory files
		//	var_dump($user);
	
	    	$result = mysqli_query($con,"SELECT * FROM user WHERE id = '$id'");
	    //		while($row = mysqli_fetch_array($result)) {
  		//			echo $row['id'] . " " . $row['workZIP'];
  		//			echo "<br>";
		//		}	
	    	mysqli_close($con);
	    	$rows = array();
	    	while($r = mysqli_fetch_assoc($result))
	    	{
	    		$rows[] = $r;
	    	}
	      	$response = $app->response();
   		  	$response['Content-Type'] = 'application/json';
   		 
    	  $response->body(json_encode($rows));
    	  $data = $response->body(json_encode($rows));
    	  return $data;
    	  exit();

	
});





$app->get('/users/:id/homeZIP', function ($id) use($app, $con) 
{

	    	$result = mysqli_query($con,"SELECT * FROM user WHERE id = '$id'");
	    	//	while($row = mysqli_fetch_array($result)) {
  			//		echo $row['id'] . " " . $row['homeZIP'];
  			//		echo "<br>";
			//	}	
	    	mysqli_close($con);
	    	$rows = array();
	    	while($r = mysqli_fetch_assoc($result))
	    	{
	    		$rows[] = $r;
	    	}
	      	$response = $app->response();
   		  	$response['Content-Type'] = 'application/json';
   		 
    	  $response->body(json_encode($rows));
    	  $data = $response->body(json_encode($rows));
    	  return $data;
    	  exit();


	
});

$app->get('/users/:id/schoolZIP', function ($id) use($app, $con) 
{

	    	$result = mysqli_query($con,"SELECT * FROM user WHERE id = '$id'");
	    	//	while($row = mysqli_fetch_array($result)) {
  			//		echo $row['id'] . " " . $row['schoolZIP'];
  			//		echo "<br>";
			//	}	
	    	mysqli_close($con);
	    	    	$rows = array();
	    	while($r = mysqli_fetch_assoc($result))
	    	{
	    		$rows[] = $r;
	    	}
	      	$response = $app->response();
   		  	$response['Content-Type'] = 'application/json';
   		 
    	  $response->body(json_encode($rows));
    	  $data = $response->body(json_encode($rows));
    	  return $data;
    	  exit();

	
});

$app->get('/users/:id/email', function ($id) use($app, $con) 
{

	    	$result = mysqli_query($con,"SELECT * FROM user WHERE id = '$id'");
	    //		while($row = mysqli_fetch_array($result)) {
  		//			echo $row['id'] . " " . $row['email'];
  		//			echo "<br>";
		//		}	
	    	mysqli_close($con);
	    		    	$rows = array();
	    	while($r = mysqli_fetch_assoc($result))
	    	{
	    		$rows[] = $r;
	    	}
	      	$response = $app->response();
   		  	$response['Content-Type'] = 'application/json';
   		 
    	  $response->body(json_encode($rows));
    	  $data = $response->body(json_encode($rows));
    	  return $data;
    	  exit();

	
});


//Yan
//users/<id>/ethnicity, users/<id>/created, users/<id>/device

$app->get('/users/:id/ethnicity', function ($id) use($app, $con) 
{

	    	//need to use this for authentication purposes, hopefully will later pull back a password as well?
	    	$result = mysqli_query($con,"SELECT * FROM user WHERE id = '$id'");
	    		
	    	mysqli_close($con);
	    	while($r = mysqli_fetch_assoc($result))
	    	{
	    		$rows[] = $r;
	    	}
	      	$response = $app->response();
   		  	$response['Content-Type'] = 'application/json';
   		 
    	  $response->body(json_encode($rows));
    	  $data = $response->body(json_encode($rows));
    	  return $data;
    	  exit();
});				


$app->get('/users/:id/device', function ($id) use($app, $con) 
{

	    	//need to use this for authentication purposes, hopefully will later pull back a password as well?
	    	$result = mysqli_query($con,"SELECT * FROM user WHERE id = '$id'");
	    		
	    	mysqli_close($con);
	    	while($r = mysqli_fetch_assoc($result))
	    	{
	    		$rows[] = $r;
	    	}
	      	$response = $app->response();
   		  	$response['Content-Type'] = 'application/json';
   		 
    	  $response->body(json_encode($rows));
    	  $data = $response->body(json_encode($rows));
    	  return $data;
    	  exit();

	
});

$app->get('/users/:id/created', function ($id) use($app, $con) 
{

	    	//need to use this for authentication purposes, hopefully will later pull back a password as well?
	    	$result = mysqli_query($con,"SELECT * FROM user WHERE id = '$id'");
	    		
	    	mysqli_close($con);
	    	while($r = mysqli_fetch_assoc($result))
	    	{
	    		$rows[] = $r;
	    	}
	      	$response = $app->response();
   		  	$response['Content-Type'] = 'application/json';
   		 
    	  $response->body(json_encode($rows));
    	  $data = $response->body(json_encode($rows));
    	  return $data;
    	  exit();

	
});
					

//Dhruv: income, rider type, rider history,rider frequency
$app->get('/users/:id/income', function ($id) use($app, $con) 
{

	    	//need to use this for authentication purposes, hopefully will later pull back a password as well?
	    	$result = mysqli_query($con,"SELECT * FROM user WHERE id = '$id'");
	    		
	    	mysqli_close($con);
	    	while($r = mysqli_fetch_assoc($result))
	    	{
	    		$rows[] = $r;
	    	}
	      	$response = $app->response();
   		  	$response['Content-Type'] = 'application/json';
   		 
    	  $response->body(json_encode($rows));
    	  $data = $response->body(json_encode($rows));
    	  return $data;
    	  exit();
});

$app->get('/users/:id/rider_type', function ($id) use($app, $con) 
{

	    	$result = mysqli_query($con,"SELECT * FROM user WHERE id = '$id'");
	    //		while($row = mysqli_fetch_array($result)) {
  		//			echo $row['id'] . " " . $row['rider_type'];
  		//			echo "<br>";
		//		}	
	    	mysqli_close($con);
	    	 	while($r = mysqli_fetch_assoc($result))
	    	{
	    		$rows[] = $r;
	    	}
	      	$response = $app->response();
   		  	$response['Content-Type'] = 'application/json';
   		 
    	  $response->body(json_encode($rows));
    	  $data = $response->body(json_encode($rows));
    	  return $data;
    	 // var_dump($test);
    	  exit();


	
});







$app->get('/users/:id/rider_history', function ($id) use($app, $con) 
{

	    	//need to use this for authentication purposes, hopefully will later pull back a password as well?
	    	$result = mysqli_query($con,"SELECT * FROM user WHERE id = '$id'");
	    		
	    	mysqli_close($con);
	    	while($r = mysqli_fetch_assoc($result))
	    	{
	    		$rows[] = $r;
	    	}
	      	$response = $app->response();
   		  	$response['Content-Type'] = 'application/json';
   		 
    	  $response->body(json_encode($rows));
    	  $data = $response->body(json_encode($rows));
    	  return $data;
    	  exit();

});

$app->get('/users/:id/cycling_freq', function ($id) use($app, $con) 
{

	    	$result = mysqli_query($con,"SELECT * FROM user WHERE id = '$id'");
	    	//	while($row = mysqli_fetch_array($result)) {
  			//		echo $row['id'] . " " . $row['cycling_freq'];
  			//		echo "<br>";
			//	}	
	    	mysqli_close($con);
	    	 		    	$rows = array();
	    	while($r = mysqli_fetch_assoc($result))
	    	{
	    		$rows[] = $r;
	    	}
	      	$response = $app->response();
   		  	$response['Content-Type'] = 'application/json';
   		 
    	  $response->body(json_encode($rows));
    	  $data = $response->body(json_encode($rows));
    	  return $data;
    	  exit();

});


//Get a specific user's information for authentication
$app->get('/users/:id', function ($id) use($app, $con) 
{
			//need to use this for authentication purposes, hopefully will later pull back a password as well?
	    	$result = mysqli_query($con,"SELECT * FROM user WHERE id = '$id'");
	    		
	    	mysqli_close($con);
	    	while($r = mysqli_fetch_assoc($result))
	    	{
	    		$rows[] = $r;
	    	}
	      	$response = $app->response();
   		  	$response['Content-Type'] = 'application/json';
   		 
    	  $response->body(json_encode($rows));
    	  $data = $response->body(json_encode($rows));
    	  return $data;
    	  exit();


});
//Get a specific user's age
$app->get('/users/:id/age', function ($id) use($app, $con) 
{
	    	$result = mysqli_query($con,"SELECT age FROM user WHERE id = '$id'");
	    	mysqli_close($con);
	    	
	    	while($r = mysqli_fetch_assoc($result))
	    	{
	    		$rows[] = $r;
	    	}
	      	$response = $app->response();
   		  	$response['Content-Type'] = 'application/json';
   		 
    	  $response->body(json_encode($rows));
    	  $data = $response->body(json_encode($rows));
    	  return $data;
    	  exit();

});
//Get a specific user's gender
$app->get('/users/:id/gender', function ($id) use($app, $con) 
{
			$result = mysqli_query($con,"SELECT gender FROM user WHERE id = '$id'");
	    	
	    	mysqli_close($con);
	    	
	    	
	    	while($r = mysqli_fetch_assoc($result))
	    	{
	    		$rows[] = $r;
	    	}
	      	$response = $app->response();
   		  	$response['Content-Type'] = 'application/json';
   		 
    	  $response->body(json_encode($rows));
    	  $data = $response->body(json_encode($rows));
    	  return $data;
    	  exit();

});


$app->get('/rides/:id', function($id) use($app, $con)
{
	$result = mysqli_query($con, "select YEAR(start) as year, SUM(IF(purpose = 'Commute', 1, 0)) 
								as commute, SUM(IF(purpose = 'Social',1, 0)) as social, 
								SUM(IF(purpose = 'Errand', 1, 0 )) as errand, 
								SUM(IF(purpose = 'Work-Related', 1, 0)) as workRelated, 
								SUM(IF(purpose = 'School', 1, 0)) as school, 
								SUM(IF(purpose = 'Exercise', 1, 0)) as exercise, 
								SUM(IF(purpose = 'Shopping', 1, 0)) as shopping, 
								SUM(IF(purpose = 'Other', 1, 0)) as other,
								COUNT(purpose) as total from trip 
								WHERE user_id = '$id'  GROUP BY YEAR(start)");
								
	mysqli_close($con);
	
 	while($r = mysqli_fetch_assoc($result))
	    	{
	    		$rows[] = $r;
	    	}
	      	$response = $app->response();
   		  	$response['Content-Type'] = 'application/json';
   		 
    	  $response->body(json_encode($rows));
    	  $data = $response->body(json_encode($rows));
    	  return $data;
    	  exit();

});



$app->get('/rides',function() use($app, $con)
{
	$req = $app->request();
	$start_date = $req->get('start_date');
	$end_date = $req->get('end_date');
	$week_day = $req->get('week_day');
	$hour = $req->get('hour');
	$week = $req->get('week');
	$month = $req->get('month');	
	
	$purposeCounts='';
	$purposeCounts = "SELECT purpose, COUNT(purpose) as total FROM trip GROUP BY purpose";
	$weekDayCounts = "SELECT DAYOFWEEK(start) as day, purpose, COUNT(*) as total  FROM trip  GROUP BY WEEK(start), DAYOFWEEK(start), purpose";
	$hourCounts = "SELECT DAYOFWEEK(start) as day, HOUR(start) as hour,  purpose, COUNT(HOUR(start)) as hourCount  
						FROM trip GROUP BY WEEK(start), DAYOFWEEK(start), HOUR(start), purpose";
	$weekCounts = "SELECT start, WEEK(start) as week, purpose FROM trip GROUP BY WEEK(start), purpose";
	$monthCounts = "SELECT MONTH(start) as month, purpose, COUNT(*) as total  FROM trip  GROUP BY MONTH(start), purpose";
	

 
/**
JSON for date range
{weekstart:<date>
	{purpose:errand, count:91}
JSON for week_day
{weekstart:<date>
	{sunday{purpose:errand,count:91},
	 monday{purpose:social,count:20}
	}
}
JSON for hour
{weekstart:<date>
	"sunday"
		"1"
			{purpose:errand, count:20}
		
		"2"
			{purpose,count}
		
	
	"monday"
		"1"
		`	{purpose}
		
	}
}
for month, just add a month by month total at bottom
for week, just add a week by week total at bottom
**/
	
	if(isset($start_date))
	{
		$purposeCounts = "SELECT purpose, COUNT(purpose) as total FROM trip WHERE DATE(start) >= DATE(". ' " ' . $start_date . '"' .") GROUP BY purpose";
		if(isset($week_day))
		{
			$weekDayCounts = "SELECT DAYOFWEEK(start) as day, purpose, COUNT(*) as total  FROM trip WHERE DATE(start) >= DATE(". ' " ' . $start_date . '"' .") 
						GROUP BY WEEK(start), DAYOFWEEK(start), purpose";
		}
		if(isset($hour))
		{
			$hourCounts = "SELECT DAYOFWEEK(start) as day, HOUR(start) as hour,  purpose, COUNT(HOUR(start)) as hourCount  
			FROM trip WHERE DATE(start) >= DATE(". ' " ' . $start_date . '"' .")  
			GROUP BY WEEK(start), DAYOFWEEK(start), HOUR(start), purpose";
		}
		if(isset($month))
		{
			$monthCounts = "SELECT MONTH(start) as month, purpose, COUNT(*) as total  FROM trip WHERE MONTH(start) >= MONTH(".'"'.$start_date.'"'.") 
			GROUP BY MONTH(start), purpose";
		}
		if(isset($week))
		{
			$weekCounts = "SELECT start, WEEK(start) as week, purpose FROM trip WHERE WEEK(start) >= WEEK(".'"'.$start_date.'"'.") 
			GROUP BY WEEK(start), purpose";
		}
		if(isset($end_date))
		{
			$purposeCounts = "SELECT purpose, COUNT(purpose) as total FROM trip WHERE DATE(start) >= DATE(". ' " ' . $start_date . '"' .") 
			AND DATE(start) <= DATE(". ' " ' . $end_date . '"' .") GROUP BY purpose";
			if(isset($week_day))
			{
				$weekDayCounts = "SELECT DAYOFWEEK(start) as day, purpose, COUNT(*) as total  FROM trip WHERE DATE(start) >= DATE(". ' " ' . $start_date . '"' .")  
				AND DATE(start) <= DATE(". ' " ' . $end_date . '"' .") GROUP BY WEEK(start), DAYOFWEEK(start), purpose";
			}	
			if(isset($hour))
			{
				$hourCounts = "SELECT start, DAYOFWEEK(start) as day, HOUR(start) as hour,  purpose, COUNT(HOUR(start)) as hourCount  
				FROM trip WHERE DATE(start) >= DATE(". ' " ' . $start_date . '"' .")  AND DATE(start) <= DATE(". ' " ' . $end_date . '"' .")
				GROUP BY WEEK(start), DAYOFWEEK(start), HOUR(start), purpose";	
			}
			if(isset($month))
			{
				$monthCounts = "SELECT MONTH(start) as month, purpose, COUNT(*) as total  FROM trip WHERE MONTH(start) >= MONTH(". ' " ' . $start_date . '"' .") 
				AND MONTH(start) <= MONTH (".'"'.$end_date.'"'.") GROUP BY MONTH(start), purpose";
			}
			if(isset($week))
			{
				$weekCounts = "SELECT start, WEEK(start) as week, purpose FROM trip WHERE WEEK(start) >= WEEK(".'"'.$start_date.'"'.")
				AND WEEK(start) <= WEEK(". ' " ' . $end_date . '"' .") GROUP BY WEEK(start), purpose";
			}
		}
				
	}
	if(isset($end_date) && IS_NULL($start_date))
	{
		$purposeCounts = "SELECT purpose, COUNT(purpose) as total FROM trip WHERE DATE(start) <= DATE(". ' " ' . $end_date . '"' .") GROUP BY purpose";
		if(isset($week_day))
		{
			"SELECT DAYOFWEEK(start) as day, purpose, COUNT(*) as total  FROM trip WHERE DATE(start) <= DATE(". ' " ' . $end_date . '"' .") 
						GROUP BY WEEK(start), DAYOFWEEK(start), purpose";
		}
		if(isset($hour))
		{
			$hourCounts = "SELECT start, DAYOFWEEK(start) as day, HOUR(start) as hour,  purpose, COUNT(HOUR(start)) as hourCount  
			FROM trip WHERE DATE(start) DATE(start) <= DATE(". ' " ' . $end_date . '"' .")
			GROUP BY WEEK(start), DAYOFWEEK(start), HOUR(start), purpose";
		}
		if(isset($month))
		{
			$monthCounts = "SELECT MONTH(start) as month, purpose, COUNT(*) as total  FROM trip WHERE MONTH(start) <= MONTH(". ' " ' . $end_date . '"' .") 
			GROUP BY MONTH(start), purpose";
		}
		if(isset($week))
		{
			$weekCounts = "SELECT start, WEEK(start) as week, purpose FROM trip WHERE 
			AND WEEK(start) <= WEEK(". ' " ' . $end_date . '"' .") GROUP BY WEEK(start), purpose";
		}	
	}
	
	$returnData = array();
	
	if(isset($start_date) || isset($end_date))
	{
		$purposeCount = mysqli_query($con, $purposeCounts);
		while($r = mysqli_fetch_assoc($purposeCount))
		{
			$pRows[] = $r;
		}
		
		$returnData = array(
				"weekstart" => $start_date,
				"purpose" => $pRows
				);
	}
	if(isset($week_day))
	{
		$weekDayCount = mysqli_query($con, $weekDayCounts);
	
		while($r = mysqli_fetch_assoc($weekDayCount))
		{
			if($r['day']=="1")
			{
				$r['day']="Sunday";
			}
			if($r['day']=="2")
			{
				$r['day']="Monday";
			}
			if($r['day']=="3")
			{
				$r['day']="Tuesday";
			}
			if($r['day']=="4")
			{
				$r['day']="Wednesday";
			}
			if($r['day']=="5")
			{
				$r['day']="Thursday";
			}
			if($r['day']=="6")
			{
				$r['day']="Friday";
			}
			if($r['day']=="7")
			{
				$r['day']="Saturday";
			}
			$wRows[] = $r;
		}
		
		$returnData = array(
				"weekstart" => $start_date,
				"purpose" => $wRows
				);
	}
	if(isset($hour))
	{
		$dayRow = array("weekstart"=>$start_date);
		$hourCount = mysqli_query($con, $hourCounts);
		while($r = mysqli_fetch_assoc($hourCount))
		{
			if($r['day']=="1")
			{
				$r['day']="Sunday";
				$dayRow[] =array("day"=>$r["day"], "hour"=>$r["hour"], "purpose"=>$r["purpose"] ,"total"=>$r["hourCount"]);
			}
			if($r['day']=="2")
			{
				$r['day']="Monday";
				$dayRow[] =array("day"=>$r["day"], "hour"=>$r["hour"], "purpose"=>$r["purpose"] ,"total"=>$r["hourCount"]);
			}
			if($r['day']=="3")
			{
				$r['day']="Tuesday";
				$dayRow[] =array("day"=>$r["day"], "hour"=>$r["hour"], "purpose"=>$r["purpose"] ,"total"=>$r["hourCount"]);
			}
			if($r['day']=="4")
			{
				$r['day']="Wednesday";
				$dayRow[] =array("day"=>$r["day"], "hour"=>$r["hour"], "purpose"=>$r["purpose"] ,"total"=>$r["hourCount"]);
			}
			if($r['day']=="5")
			{
				$r['day']="Thursday";
				$dayRow[] =array("day"=>$r["day"], "hour"=>$r["hour"], "purpose"=>$r["purpose"] ,"total"=>$r["hourCount"]);
			}
			if($r['day']=="6")
			{
				$r['day']="Friday";
				$dayRow[] =array("day"=>$r["day"], "hour"=>$r["hour"], "purpose"=>$r["purpose"] ,"total"=>$r["hourCount"]);
			}
			if($r['day']=="7")
			{
				$r['day']="Saturday";
				$dayRow[] =array("day"=>$r["day"], "hour"=>$r["hour"], "purpose"=>$r["purpose"] ,"total"=>$r["hourCount"]);
			}
	
			//$dayRow[] = array($r["day"]);
			//$dayRow[] =array(($r["day"]), array(array($r["hour"]=>$r["purpose"])));
			
			
			
		
			
		}
		
		$returnData = $dayRow;
			
	}
	if(isset($month))
	{
		$monthCount=mysqli_query($con, $monthCounts);
		while($r = mysqli_fetch_assoc($monthCount))
		{
			if($r['month']=="1")
			{
				$r['month']="January";
			}
			if($r['month']=="2")
			{
				$r['month']="February";
			}
			if($r['month']=="3")
			{
				$r['month']="March";
			}
			if($r['month']=="4")
			{
				$r['month']="April";
			}
			if($r['month']=="5")
			{
				$r['month']="May";
			}
			if($r['month']=="6")
			{
				$r['month']="June";
			}
			if($r['month']=="7")
			{
				$r['month']="July";
			}
			if($r['month']=="8")
			{
				$r['month']="August";
			}
			if($r['month']=="9")
			{
				$r['month']="September";
			}
			if($r['month']=="10")
			{
				$r['month']="October";
			}
			if($r['month']=="11")
			{
				$r['month']="November";
			}
			if($r['month']=="12")
			{
				$r['month']="December";
			}
			$mRows[] = $r;
		}
	}
	if(isset($week))
	{
		$weekCount = mysqli_query($con, $weekCounts);
		while($r = mysqli_fetch_assoc($weekCount))
		{
			$weekRows[] = $r;
		}
	}
		
	mysqli_close($con);		
	
	$response = $app->response();
	$response['Content-Type'] = 'application/json';
   //$response->body(json_encode($pRows));
   //$data = $response->body(json_encode($pRows));
   $combined = array();
   $combined = array(
    			"purpose"=>$pRows,
    			["weekday"=>$wRows,
    				//"hour"=>$hRows
    			]
    
    );
    //$combined['weekstart'] = "weekdata";//json_encode(["weekdata"]);
    //$combined['weekday'] = $wRows;
    //$combined['weekday']['hour']= $hRows;//json_encode($rows);
    //$combined['weekday']['purpose'] = $pRows;//json_encode($rows);   
    
    //$combined = array(
  	//["week_start" => $weekSt],  		
  	//["purpose" => $purData]
  	//"otherTest" => 'otherTesting',
  	//"weeklyData" => 'weeklyData'
	//);

	$newData= json_encode($returnData, JSON_PRETTY_PRINT); 
	echo $newData;
    // var_dump($newData);
   	// return $newData;
   	//return $data;
    exit();

});

//USERS filtering URI
$app->get('/users', function() use($app, $con)
 {
	$req = $app->request();

	//set all possible variables...
	$id = $req->get('id');
	$age = $req->get('age');
	$gender = $req->get('gender');
	$income = $req->get('income');
	$ethnicity = $req->get('ethnicity');
	$homeZIP = $req->get('homeZIP');
	$schoolZIP = $req->get('schoolZIP');
	$workZIP = $req->get('workZIP');
	$cycling_freq = $req->get('cycling_freq');
	$rider_type = $req->get('rider_type');

	$query = 'SELECT * FROM user WHERE ';

	//if each parameter is set, add it to the query
	if(isset($id) && filter_var($id, FILTER_VALIDATE_INT)){
		$query = $query . " id = " . $id . " AND ";
	}
// 	else if (isset($id) && !filter_var($id, FILTER_VALIDATE_INT)){
// 		trigger_error("user id must be an int value");
// 	}
	
	if(isset($age) && filter_var($age, FILTER_VALIDATE_INT)){
		$query = $query . " age = " . $age . " AND ";
	}
	
	if(isset($gender) && filter_var($gender, FILTER_VALIDATE_INT)){
		$query = $query . " gender = " . $gender . " AND ";
	}

	if(isset($income) && filter_var($income, FILTER_VALIDATE_INT)){
		$query = $query . " income = " . $income . " AND ";
	}
	if(isset($ethnicity) && filter_var($ethnicity, FILTER_VALIDATE_INT)){
		$query = $query . " ethnicity = " . $ethnicity . " AND ";
	}
	if(isset($homeZIP) && filter_var($homeZIP, FILTER_VALIDATE_INT)){
		$query = $query . " homeZIP = " . $homeZIP . " AND ";
	}
	if(isset($schoolZIP) && filter_var($schoolZIP, FILTER_VALIDATE_INT)){
		$query = $query . " schoolZIP = " . $schoolZIP . " AND ";
	}
	if(isset($workZIP) && filter_var($workZIP, FILTER_VALIDATE_INT)){
		$query = $query . " workZIP = " . $workZIP . " AND ";
	}
	if(isset($cycling_freq) && filter_var($cycling_freq, FILTER_VALIDATE_INT)){
		$query = $query . " cycling_freq = " . $cycling_freq . " AND ";
	}
	if(isset($rider_type) && filter_var($rider_type, FILTER_VALIDATE_INT)){
		$query = $query . " rider_type = " . $rider_type . " AND ";
	}	

	//take of the last AND
	$query = substr($query, 0, -5);
	
	//need to check to see if there are NO parameters, the "w" character needs to be taken from the string
	if(substr($query, -1)== 'W'){
		$query = substr($query, 0, -1);
	}

	try
	{
		$result = mysqli_query($con, $query);
	}
	catch(PDOException $e)
	{
		echo'{"error":{"text":'.$e->getMessage().'}}';
	}

	mysqli_close($con);
	while($r = mysqli_fetch_assoc($result))
	{
		$rows[] = $r;
	}
	$response = $app->response();
   	$response['Content-Type'] = 'application/json';
   		 
    $response->body(json_encode($rows));
    $data = $response->body(json_encode($rows));
    return $data;
    exit();	

});

//TRIPS filtering URI
$app->get('/trips', function() use($app, $con)
 {

	$req = $app->request();

	//set all possible variables...
	$id = $req->get('id');
	$user_id = $req->get('user_id');
	$purpose = $req->get('purpose');
	$notes = $req->get('notes');
	$start = $req->get('start');
	$stop = $req->get('stop');
	//$n_coord = $req->get('n_coord');
	
	$query = 'SELECT * FROM trip WHERE ';

	//if each parameter is set, add it to the query
	if(isset($id) && filter_var($id, FILTER_VALIDATE_INT)){
		$query = $query . " id = " . $id . " AND ";
	}
	
	if(isset($user_id) && filter_var($user_id, FILTER_VALIDATE_INT)){
		$query = $query . " user_id = " . $user_id . " AND ";
	}
	
	if(isset($notes)){
		$query = $query . " notes = " . $notes . " AND ";
	}

	if(isset($start)){
		$query = $query . " start = " . $start . " AND ";
	}
	if(isset($stop)){
		$query = $query . " stop = " . $stop . " AND ";
	}
	if(isset($n_coord) && filter_var($n_coord, FILTER_VALIDATE_INT)){
		$query = $query . " n_coord = " . $n_coord . " AND ";
	}
	
	

	//take of the last AND
	$query = substr($query, 0, -5);

	//need to check to see if there are NO parameters, the "w" character needs to be taken from the string	
	if(substr($query, -1)== 'W'){
		$query = substr($query, 0, -1);
	}
	
	try
	{
		$result = mysqli_query($con, $query);
	}
	catch(PDOException $e)
	{
		echo'{"error":{"text":'.$e->getMessage().'}}';
	}

	mysqli_close($con);
	
	while($r = mysqli_fetch_assoc($result))
	{
		$rows[] = $r;
	}
	$response = $app->response();
   	$response['Content-Type'] = 'application/json';
   		 
    $response->body(json_encode($rows));
    $data = $response->body(json_encode($rows));
    return $data;
    exit();	

});

//NOTES filtering URI
$app->get('/notes', function() use($app, $con)
 {

	$req = $app->request();

	//set all possible variables...
	$id = $req->get('id');
	$user_id = $req->get('user_id');
	$trip_id = $req->get('purpose');
	$recorded = $req->get('notes');
	$latitude = $req->get('start');
	$longitude = $req->get('stop');
	$altitude = $req->get('altitude');
	$speed = $req->get('speed');
	$hAccuracy = $req->get('hAccuracy');
	$vAccuracy = $req->get('vAccuracy');
	$note_type = $req->get('note_type');
	$details = $req->get('details');
	$img_url = $req->get('img_url');
	
	$query = 'SELECT * FROM note WHERE ';

	//if each parameter is set, add it to the query
	if(isset($id)){
		$query = $query . " id = " . $id . " AND ";
	}
	
	if(isset($user_id) && filter_var($user_id, FILTER_VALIDATE_INT)){
		$query = $query . " user_id = " . $user_id . " AND ";
	}

	if(isset($trip_id) && filter_var($trip_id, FILTER_VALIDATE_INT)){
		$query = $query . " trip_id = " . $trip_id . " AND ";
	}
	if(isset($recorded)){
		$query = $query . " recorded = " . $recorded . " AND ";
	}
	if(isset($latitude)){
		$query = $query . " latitude = " . $latitude . " AND ";
	}
	if(isset($longitude)){
		$query = $query . " longitude = " . $longitude . " AND ";
	}
	if(isset($altitude)){
		$query = $query . " altitude = " . $altitude . " AND ";
	}
	if(isset($speed)){
		$query = $query . " speed = " . $speed . " AND ";
	}
	if(isset($hAccuracy)){
		$query = $query . " hAccuracy = " . $hAccuracy . " AND ";
	}
	if(isset($vAccuracy)){
		$query = $query . " vAccuracy = " . $vAccuracy . " AND ";
	}
	if(isset($note_type) && filter_var($note_type,FILTER_VALIDATE_INT)){
		$query = $query . " note_type = " . $note_type . " AND ";
	}
	if(isset($details)){
		$query = $query . " details = " . $details . " AND ";
	}
	if(isset($img_url) && filter_var($img_url, FILTER_VALIDATE_URL)){
		$query = $query . " img_url = " . $img_url . " AND ";
	}
	//take of the last AND
	$query = substr($query, 0, -5);

	if(substr($query, -1)== 'W'){
		$query = substr($query, 0, -1);
	}
	
	try
	{
		$result = mysqli_query($con, $query);
	}
	catch(PDOException $e)
	{
		echo'{"error":{"text":'.$e->getMessage().'}}';
	}

	mysqli_close($con);
	while($r = mysqli_fetch_assoc($result))
	{
		$rows[] = $r;
	}
	$response = $app->response();
   	$response['Content-Type'] = 'application/json';
   		 
    $response->body(json_encode($rows));
    $data = $response->body(json_encode($rows));
    return $data;
    exit();	

});

$app->run();
?>