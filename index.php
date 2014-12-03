<?php
//session_cache_limiter(false);
session_start();
//echo "Page loaded: good";
//echo "<br>";
 $con=mysqli_connect("mysql.govathon.cycleatlanta.org","govathon12db","7Jk3WYNt","catl_govathon");
require 'Slim/Slim.php';
require_once('include/UserFactory.php');

/*********************
Since we never actually used the dir structure live in prod for our dev,
we need to include this line like this:
require_once('../include/UserFactory.php');

To access anything we need from the include directory when we go live to
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


/*************************************************
WEB SERVICE FUNCTIONS: (UTILITY CODE)
LOGIN
REGISTER
ENCRYPT PASSWORD
**************************************************/

define("PBKDF2_HASH_ALGORITHM", "sha256");
define("PBKDF2_ITERATIONS", 1000);
define("PBKDF2_SALT_BYTE_SIZE", 24);
define("PBKDF2_HASH_BYTE_SIZE", 24);

define("HASH_SECTIONS", 4);
define("HASH_ALGORITHM_INDEX", 0);
define("HASH_ITERATION_INDEX", 1);
define("HASH_SALT_INDEX", 2);
define("HASH_PBKDF2_INDEX", 3);

/*****
*create_hash() was pulled from preexisting code found here:https://crackstation.net/hashing-security.htm#phpsourcecode
*See website for additional documentation (most is repeated here). 
*This function creates a secure has of the user password + salt
*****/
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
/***
*create_salt generates a random salt value to be added to the user password
*Returns: the salt value
**/
function create_salt(){
	$salt = base64_encode(mcrypt_create_iv(PBKDF2_SALT_BYTE_SIZE, MCRYPT_DEV_URANDOM));
	return $salt;
}
/*
*validate_password is used during the log in and delted user processes
*$password = the user entered password
*$correct_hash = the has pulled from the table that $password will be compared to
*once hashed
*/
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

/*
* Login Endpoint
* Method gets data from a JSON object and then validates the login credentials against db values
* JSON object during testing was passed in from login.php. Similar formats should be used
*/
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

	
	$emailExists = false;
	$retrievedSalt = '';
	$retrievedPassword = '';
	
	//complete the query
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
	
	//handle existing emails
	if($emailExists == true){
		$hash = create_hash($passwordInput, $retrievedSalt);
 
 		if($hash == $retrievedPassword){	
			header('Location:../demo_site/portal.php');
			exit();			
		}
		else{
			header('Location:../demo_site/badLogin.html');
			exit();
		}
	}
	else{
		header('Location:../demo_site/badLogin.html');
		exit();
	}
	exit();
	
}); 
 


/*
*Register a new user endpoint
*Params: email (not null, valid email) and password
*If successful, the email, password hash, and salt are added to the 'user_password' table
*/
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
			if(filter_var($v, FILTER_VALIDATE_EMAIL))
			{
				$userEmail = $v;
			}
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
		header('Location:../demo_site/userCreated.php');
		exit();
		
	}//end post

	
	
  }); 
 

/*****************************************************
END UTILITY CODE
*******************************************************/ 




/***************************************************
RESOURCE: USER
*****************************************************/


/***************************************************
USER: GET
*****************************************************/


//USERS filtering URI
/******* THIS IS USED TO PASS IN MULTIPLE PARAMETERS; FORMAT: <root_url>/users?<param>=<val>&<param>=<val> for as many inputs as needed**********/

$app->get('/users', function() use($app, $con)
 {
	$req = $app->request(); //get the request
	
	$bad_request = array(); //prep for bad parameters
	
		
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
	$email = $req->get('email');
	$device=$req->get('device');
	$created = $req->get('created');

	$query = 'SELECT * FROM user WHERE ';

	//if each parameter is set, add it to the query
	//also checks if params are valid for the expected input using FILTER_VALIDATE_<TYPE> (a PHP function)
	if(isset($id)){
		if(filter_var($id, FILTER_VALIDATE_INT)){
			$query = $query . " id = " . $id . " AND ";
		}
		else{
			array_push($bad_request, "id = ". $id);
			
		}
	}

	
	if(isset($age)){
		if(filter_var($age, FILTER_VALIDATE_INT)){
			$query = $query . " age = " . $age . " AND ";
		}
		else{
			array_push($bad_request, "age = ". $age);
		}
	}
	
	if(isset($gender)){
		if(filter_var($gender, FILTER_VALIDATE_INT)){
			$query = $query . " gender = " . $gender . " AND ";
		}
		else{
			array_push($bad_request, "gender = ". $gender);
		}
		
	}

	if(isset($income)){
		if(filter_var($income, FILTER_VALIDATE_INT)){
			$query = $query . " income = " . $income . " AND ";
		}
		else{
			array_push($bad_request, "income = ". $income);
		}
	}
	if(isset($ethnicity)){
		if(filter_var($ethnicity, FILTER_VALIDATE_INT)){
			$query = $query . " ethnicity = " . $ethnicity . " AND ";
		}
		else{
			array_push($bad_request, "ethnicity = ". $ethnicity);
		}
	}
	if(isset($homeZIP)){
		if(filter_var($homeZIP, FILTER_VALIDATE_INT)){
			$query = $query . " homeZIP = " . $homeZIP . " AND ";
		}
		else{
			array_push($bad_request, "homeZIP = ". $homeZIP);
		}
	}
	if(isset($schoolZIP)){
		if(filter_var($schoolZIP, FILTER_VALIDATE_INT)){
			$query = $query . " schoolZIP = " . $schoolZIP . " AND ";
		}
		else{
			array_push($bad_request, "schoolZIP = ". $schoolZIP);
		}
	}
	if(isset($workZIP)){
		if(filter_var($workZIP, FILTER_VALIDATE_INT)){
			$query = $query . " workZIP = " . $workZIP . " AND ";
		}
		else{
			array_push($bad_request, "workZIP = ". $workZIP);
		}
	}
	if(isset($cycling_freq)){
		if(filter_var($cycling_freq, FILTER_VALIDATE_INT)){
			$query = $query . " cycling_freq = " . $cycling_freq . " AND ";
		}
		else{
			array_push($bad_request, "cycling_freq = ". $cycling_freq);
		}
	}
	if(isset($rider_type)){
		if(filter_var($rider_type, FILTER_VALIDATE_INT)){
			$query = $query . " rider_type = " . $rider_type . " AND ";
		}
		else{
			array_push($bad_request, "rider_type = ". $rider_type);
		}	
	}
	if(isset($email)){
		if(filter_var($email, FILTER_VALIDATE_EMAIL)){
				$query = $query . " email = '" . $email . "' AND ";
		}
		else{
			array_push($bad_request, "email = ". $email);
		}
	}	
	if(isset($device)){
		if(strpos($device, ';') == FALSE && strpos($device, ' ') == FALSE){
			$query = $query . " device = '" . $device . "' AND ";
		}
		else{
			array_push($bad_request, "device = ". $device);
		}
	}
	if(isset($created)){
		if(strpos($created, ';') == FALSE){
			$query = $query . " created = '" . $created . "' AND ";
		}
		else{
			array_push($bad_request, "created = ". $created);
		}
	}
	if(count($bad_request)!=0){
		//for testing
		echo implode(", ", $bad_request);
		
		//SHOULD LOG TO FILE INSTEAD
	}
	
	else{
		//take of the last AND
		$query = substr($query, 0, -5);
	
		//need to check to see if there are NO parameters, the "w" character needs to be taken from the string
		if(substr($query, -1)== 'W'){
			$query = substr($query, 0, -1);
		}

		try
		{
			$result = mysqli_query($con, $query); //perform the query
		}
		catch(PDOException $e)
		{
			echo'{"error":{"text":'.$e->getMessage().'}}';
		}

		mysqli_close($con); //close the connection
		
		while($r = mysqli_fetch_assoc($result)) //get all the data requested
		{
			$rows[] = $r;
		}

		$response = $app->response(); //prepare the response
   		$response['Content-Type'] = 'application/json'; //sending JSON back
   		 
    	$response->body(json_encode($rows)); //encode the data
    	$data = $response->body(json_encode($rows));
    	return $data; //return it
    }
    exit();	//stop executing program

});

/*******GETS THE WORKZIP DATA FROM A GIVEN USER; PARAM: ID (AN INT) *******/

$app->get('/users/:id/workZIP', function ($id) use($app, $con) 
{
		
	
	    	$result = mysqli_query($con,"SELECT workZIP FROM user WHERE id = '$id'");
	   
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



/*******GETS THE HOMEZIP DATA FROM A GIVEN USER; PARAM: ID (AN INT) *******/

$app->get('/users/:id/homeZIP', function ($id) use($app, $con) 
{

	    	$result = mysqli_query($con,"SELECT homeZIP FROM user WHERE id = '$id'");
	    	
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

/*******GETS THE SCHOOLZIP DATA FROM A GIVEN USER; PARAM: ID (AN INT) *******/

$app->get('/users/:id/schoolZIP', function ($id) use($app, $con) 
{

	    	$result = mysqli_query($con,"SELECT schoolZIP FROM user WHERE id = '$id'");
	    
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

/*******GETS THE EMAIL DATA FROM A GIVEN USER; PARAM: ID (AN INT) *******/

$app->get('/users/:id/email', function ($id) use($app, $con) 
{

	    	$result = mysqli_query($con,"SELECT email FROM user WHERE id = '$id'");
	  	
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


/*******GETS THE ETHNICITY DATA FROM A GIVEN USER; PARAM: ID (AN INT) *******/

$app->get('/users/:id/ethnicity', function ($id) use($app, $con) 
{

	    	
	    	$result = mysqli_query($con,"SELECT ethnicity FROM user WHERE id = '$id'");
	    		
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

/*******GETS THE DEVICE DATA FROM A GIVEN USER; PARAM: ID (AN INT) *******/

$app->get('/users/:id/device', function ($id) use($app, $con) 
{

	    	
	    	$result = mysqli_query($con,"SELECT device FROM user WHERE id = '$id'");
	    		
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

/*******GETS THE CREATED DATA FROM A GIVEN USER; PARAM: ID (AN INT) *******/

$app->get('/users/:id/created', function ($id) use($app, $con) 
{

	    	//need to use this for authentication purposes, hopefully will later pull back a password as well?
	    	$result = mysqli_query($con,"SELECT created FROM user WHERE id = '$id'");
	    		
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
					

/*******GETS THE INCOME DATA FROM A GIVEN USER; PARAM: ID (AN INT) *******/

$app->get('/users/:id/income', function ($id) use($app, $con) 
{

	    	
	    	$result = mysqli_query($con,"SELECT income FROM user WHERE id = '$id'");
	    		
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

/*******GETS THE RIDER_TYPE DATA FROM A GIVEN USER; PARAM: ID (AN INT) *******/

$app->get('/users/:id/rider_type', function ($id) use($app, $con) 
{

	    	$result = mysqli_query($con,"SELECT rider_type FROM user WHERE id = '$id'");
	  
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





/*******GETS THE RIDER_HISTORY DATA FROM A GIVEN USER; PARAM: ID (AN INT) *******/

$app->get('/users/:id/rider_history', function ($id) use($app, $con) 
{

	    	//need to use this for authentication purposes, hopefully will later pull back a password as well?
	    	$result = mysqli_query($con,"SELECT rider_history FROM user WHERE id = '$id'");
	    		
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

/*******GETS THE CYCLING_FREQ DATA FROM A GIVEN USER; PARAM: ID (AN INT) *******/

$app->get('/users/:id/cycling_freq', function ($id) use($app, $con) 
{

	    	$result = mysqli_query($con,"SELECT cycling_freq FROM user WHERE id = '$id'");
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


/*******GETS THE ID DATA FROM A GIVEN USER; PARAM: ID (AN INT) *******/
/****was going to be used for authentication, before /login was created******/
/*****could still be useful, but currently is not used in the demo website for anything of substance ****/

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

/*******GETS THE AGE DATA FROM A GIVEN USER; PARAM: ID (AN INT) *******/

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

/*******GETS THE GENDER DATA FROM A GIVEN USER; PARAM: ID (AN INT) *******/

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




/***************************************************
USER: POST
*****************************************************/


/***** USING A WEBFORM IN THE DEMO, JSON DATA IS PASSED TO THIS ENDPOINT
THE ENDPOINT PROCESSES IT INTO KEY=VALUE PAIRS, AND CREATES A SQL QUERY
IT IS AN INSERT STATEMENT THAT INSERTS ALL OF THE GIVEN DATA INTO THE PROPER
COLUMNS OF A NEW USER IN THE USER TABLE
***********/

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
   // echo $query;

    });


/***************************************************
USER: PUT
*****************************************************/



/**User PUT for multiple params
*/

/************
THIS WILL UPDATE MULTIPLE FIELDS OF USER DATA AT ONCE, AS OPPOSED TO THE ORIGINAL
STURCTURE OF INDIVIDUAL ENDPOINTS FOR EACH TYPE OF USER DATA
THIS IS SIMILAR TO THE FILTERING CALL FOR MULTIPLE PARAMTERS, AS WELL AS POSTING 
MULTIPLE PARAMETERS

MOST BROWSERS DO NOT SUPPORT THE PUT CALL; IN THE JAVASCRIPT/AJAX SIDE OF THINGS,
THE URI MUST BE INTERCEPTER MID-REQUEST AND THE METHOD MUST BE OVERRIDEN TO PUT

THE DATA WILL COME IN AS KEY=>VALUE PAIRS, FOR TESTING PURPOSES WE STORE
A JSON ENCODED COPY OF THE DATA IN A SESSION VARIABLE. THIS IS NOT BEST PRACTICE
AND IS NOT MEANT TO BE A PERMANENT SOLUTION, ONLY FOR TESTING AND DEBUGGING THE
PUT ENDPOINT FOR USER DATA

*****************/

$app->put('/users/user', function () use($app, $con)
{

	$body = $app->request()->getBody(); //get the request body
	$jsonBody = json_encode($body);
	$_SESSION['userPutJSON'] = $jsonBody; //set session token of the request for debugging and testing
	$id='';
	$age='';
	$email='';
	$password='';
	$gender='';
	$income='';
	$ethnicity='';
	$homeZIP='';
	$schoolZIP='';
	$workZIP='';
	$cycling_freq='';
	$rider_type='';
	//go through each key and get the data, if any
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
		if($k=="password"){
			$password=$v;
		}
		
	}	
	
	//beging the query
	
	$query = "UPDATE user SET";	//UPDATing data in the table, so SET 
	//will fix/clean up later, I was in a hurry to get this to work :p
	$query2 = "UPDATE user_password SET"; //for password updates
	
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
		$query2 = $query2 . " email = " . "'".$email."'"." ,";
	}	
	if($password!=''){
		$newSalt = create_salt();
		$newHash = create_hash($password, $newSalt);
		$query2 = $query2 . " salt = " . "'".$newSalt."'"." ,";
		$query2 = $query2 . " password = " . "'".$newHash."'"." ,";
		

	}
	//take of the last AND
	$query = substr($query, 0, -1);
	$query2 = substr($query2,0,-1);
	
	if(isset($id)){
		$query = $query . "WHERE" . " id = " . $id ;
		$query2 = $query2 . "WHERE" . " user_id = '$id'"; 
		
	}
	
	//need to check to see if there are NO parameters, the "w" character needs to be taken from the string
	if(substr($query, -1)== 'W'){
		$query = substr($query, 0, -1);
		$query2 = substr($query2, 0, -1);

	}
	//execute queries
	mysqli_query($con, $query);
	mysqli_query($con, $query2);
	
	
	$result = array("status" => "success");
	json_encode($result);
	$response = $app->response();
   	$response['Content-Type'] = 'application/json';
    $data = $response->body(json_encode($result));
    return $data; //return manual success code
}); 


/*******INDIVIDUAL PUT METHODS FOR USER, NOT EFFICIENT, LEFT HERE FROM FIRST ROUND OF DEV, THESE DO WHAT THE ABOVE END POINT DOES*******/

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



/***************************************************
USER: DELETE
*****************************************************/
/*******DELETES THE GIVEN USER  FROM USER_PASSWORD TABLE *******/

$app->delete('/users', function() use($app, $con)
{
	
		//THIS COULD BE REFACTORED TO REQUIRE MANUALLY PASSING IN A USER ID
		//FOR NOW, WE JUST GET IT FROM THE WEB SESSION AND PASS IT OVER IN THE BODY 
		
	    $body = $app->request()->getBody();
	    
   	  
		$values = '';
		$keys = '';
	
		$userId = '';
	
	
	foreach($body as $k=>$v)
	{	
		
		if($k == 'deleteId'){
			$userId = $v;
		}		
    }
    $query = "Delete from user_password WHERE user_id=".  $userId  ;
    mysqli_query($con, $query);
    mysqli_close($con);
     $result = array("status" => "success");
			 json_encode($result);
			 $response = $app->response();
   			 $response['Content-Type'] = 'application/json';
    		 $data = $response->body(json_encode($result));
    		 
    		 return $data;


});

/*******************************
END USER RESOURCE CODE
********************************/





/***************************************************
RESOURCE: TRIP
*****************************************************/


/***************************************************
TRIP: GET
*****************************************************/

//TRIPS filtering URI
$app->get('/trips', function() use($app, $con)
 {

	$req = $app->request();
	$bad_params = array();
	//set all possible variables...
	$id = $req->get('id');
	$user_id = $req->get('user_id');
	$purpose = $req->get('purpose');
	$notes = $req->get('notes');
	$start = $req->get('start');
	$stop = $req->get('stop');
	$n_coord = $req->get('n_coord');
	
	$query = 'SELECT * FROM trip WHERE ';

	//if each parameter is set, add it to the query
	if(isset($id)){
		if(filter_var($id, FILTER_VALIDATE_INT)){
			$query = $query . " id = " . $id . " AND ";
		}
		else{
			array_push($bad_params, "id = ". $id);
		}
	}
	
	if(isset($user_id)){
		if(filter_var($user_id, FILTER_VALIDATE_INT)){
			$query = $query . " user_id = " . $user_id . " AND ";
		}
		else{
			array_push($bad_params, "user_id = ". $user_id);
		}
	}
	
	if(isset($notes)){
		$query = $query . " notes = '" . $notes . "' AND ";
	}

	if(isset($start)){
		$query = $query . " start = '" . $start . "' AND ";
	}
	if(isset($stop)){
		$query = $query . " stop = '" . $stop . "' AND ";
	}
	if(isset($n_coord)){
		if(filter_var($n_coord, FILTER_VALIDATE_INT)){
			$query = $query . " n_coord = " . $n_coord . " AND ";
		}
		else{
			array_push($bad_params, "n_coord = ". $n_coord);
		}
	}
	
	if(count($bad_params)!=0){
		//for testing
		echo implode(", ", $bad_params);
		
		//SHOULD LOG TO FILE INSTEAD
	}

	else{
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
    }
    exit();	

});


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


$app->get('/trips/:id/purpose', function ($id) use($app, $con) 
{
		//	$user = UserFactory::getUser($id); //how to access methods in factory files
		//	var_dump($user);
	
	    	$result = mysqli_query($con,"SELECT purpose FROM trip WHERE id = '$id' ");
	    
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


$app->get('/trips/:id/notes', function ($id) use($app, $con) 
{
		//	$user = UserFactory::getUser($id); //how to access methods in factory files
		//	var_dump($user);
	
	    	$result = mysqli_query($con,"SELECT notes FROM trip WHERE id = '$id' ");
	    
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

$app->get('/trips/:id/start', function ($id) use($app, $con) 
{
		//	$user = UserFactory::getUser($id); //how to access methods in factory files
		//	var_dump($user);
	
	    	$result = mysqli_query($con,"SELECT start FROM trip WHERE id = '$id' ");
	    
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

$app->get('/trips/:id/stop', function ($id) use($app, $con) 
{
		//	$user = UserFactory::getUser($id); //how to access methods in factory files
		//	var_dump($user);
	
	    	$result = mysqli_query($con,"SELECT stop FROM trip WHERE id = '$id' ");
	    
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

$app->get('/trips/:id/n_coord', function ($id) use($app, $con) 
{
		//	$user = UserFactory::getUser($id); //how to access methods in factory files
		//	var_dump($user);
	
	    	$result = mysqli_query($con,"SELECT n_coord FROM trip WHERE id = '$id' ");
	    
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







$app->get('/rides',function() use($app, $con)
{
	$req = $app->request();
	$start_date = $req->get('start_date');
	$end_date = $req->get('end_date');
	$week_day = $req->get('week_day');
	$hour = $req->get('hour');
	$week = $req->get('week');
	$month = $req->get('month');
	$aggregate = $req->get('aggregate');	
	
	$purposeCounts='';
	$purposeCounts = "SELECT SUM(IF(purpose = 'Commute', 1, 0)) 
								as commute, SUM(IF(purpose = 'Social',1, 0)) as social, 
								SUM(IF(purpose = 'Errand', 1, 0 )) as errand, 
								SUM(IF(purpose = 'Work-Related', 1, 0)) as workRelated, 
								SUM(IF(purpose = 'School', 1, 0)) as school, 
								SUM(IF(purpose = 'Exercise', 1, 0)) as exercise, 
								SUM(IF(purpose = 'Shopping', 1, 0)) as shopping, 
								SUM(IF(purpose = 'Other', 1, 0)) as other,
								COUNT(purpose) as total FROM trip";
	$weekDayCounts = "SELECT DAYOFWEEK(start) as day, SUM(IF(purpose = 'Commute', 1, 0)) 
								as commute, SUM(IF(purpose = 'Social',1, 0)) as social, 
								SUM(IF(purpose = 'Errand', 1, 0 )) as errand, 
								SUM(IF(purpose = 'Work-Related', 1, 0)) as workRelated, 
								SUM(IF(purpose = 'School', 1, 0)) as school, 
								SUM(IF(purpose = 'Exercise', 1, 0)) as exercise, 
								SUM(IF(purpose = 'Shopping', 1, 0)) as shopping, 
								SUM(IF(purpose = 'Other', 1, 0)) as other,
								COUNT(purpose) as total FROM trip  GROUP BY WEEK(start), DAYOFWEEK(start)";
	$hourCounts = "SELECT DAYOFWEEK(start) as day, HOUR(start) as hour,  SUM(IF(purpose = 'Commute', 1, 0)) 
								as commute, SUM(IF(purpose = 'Social',1, 0)) as social, 
								SUM(IF(purpose = 'Errand', 1, 0 )) as errand, 
								SUM(IF(purpose = 'Work-Related', 1, 0)) as workRelated, 
								SUM(IF(purpose = 'School', 1, 0)) as school, 
								SUM(IF(purpose = 'Exercise', 1, 0)) as exercise, 
								SUM(IF(purpose = 'Shopping', 1, 0)) as shopping, 
								SUM(IF(purpose = 'Other', 1, 0)) as other,
								COUNT(purpose) as total 
						FROM trip GROUP BY WEEK(start), DAYOFWEEK(start), HOUR(start)";
	$weekCounts = "SELECT WEEK(start) as week, SUM(IF(purpose = 'Commute', 1, 0)) 
								as commute, SUM(IF(purpose = 'Social',1, 0)) as social, 
								SUM(IF(purpose = 'Errand', 1, 0 )) as errand, 
								SUM(IF(purpose = 'Work-Related', 1, 0)) as workRelated, 
								SUM(IF(purpose = 'School', 1, 0)) as school, 
								SUM(IF(purpose = 'Exercise', 1, 0)) as exercise, 
								SUM(IF(purpose = 'Shopping', 1, 0)) as shopping, 
								SUM(IF(purpose = 'Other', 1, 0)) as other,
								COUNT(purpose) as total FROM trip GROUP BY WEEK(start)";
	$monthCounts = "SELECT MONTH(start) as month, SUM(IF(purpose = 'Commute', 1, 0)) 
								as commute, SUM(IF(purpose = 'Social',1, 0)) as social, 
								SUM(IF(purpose = 'Errand', 1, 0 )) as errand, 
								SUM(IF(purpose = 'Work-Related', 1, 0)) as workRelated, 
								SUM(IF(purpose = 'School', 1, 0)) as school, 
								SUM(IF(purpose = 'Exercise', 1, 0)) as exercise, 
								SUM(IF(purpose = 'Shopping', 1, 0)) as shopping, 
								SUM(IF(purpose = 'Other', 1, 0)) as other,
								COUNT(purpose) as total  FROM trip  GROUP BY MONTH(start)";
	


	
	if(isset($start_date))
	{
		$purposeCounts = "SELECT SUM(IF(purpose = 'Commute', 1, 0)) 
								as commute, SUM(IF(purpose = 'Social',1, 0)) as social, 
								SUM(IF(purpose = 'Errand', 1, 0 )) as errand, 
								SUM(IF(purpose = 'Work-Related', 1, 0)) as workRelated, 
								SUM(IF(purpose = 'School', 1, 0)) as school, 
								SUM(IF(purpose = 'Exercise', 1, 0)) as exercise, 
								SUM(IF(purpose = 'Shopping', 1, 0)) as shopping, 
								SUM(IF(purpose = 'Other', 1, 0)) as other,
								COUNT(purpose) as total
		
								FROM trip 
		
								WHERE DATE(start) >= DATE(". ' " ' . $start_date . '"' .")";
		
		
		if(isset($week_day))
		{
			$weekDayCounts = "SELECT DAYOFWEEK(start) as day, 
						SUM(IF(purpose = 'Commute', 1, 0)) 
								as commute, SUM(IF(purpose = 'Social',1, 0)) as social, 
								SUM(IF(purpose = 'Errand', 1, 0 )) as errand, 
								SUM(IF(purpose = 'Work-Related', 1, 0)) as workRelated, 
								SUM(IF(purpose = 'School', 1, 0)) as school, 
								SUM(IF(purpose = 'Exercise', 1, 0)) as exercise, 
								SUM(IF(purpose = 'Shopping', 1, 0)) as shopping, 
								SUM(IF(purpose = 'Other', 1, 0)) as other,
								COUNT(purpose) as total  
			
						FROM trip WHERE DATE(start) >= DATE(". ' " ' . $start_date . '"' .") 
						GROUP BY WEEK(start), DAYOFWEEK(start)";
		}
		if(isset($hour))
		{
			$hourCounts = "SELECT DAYOFWEEK(start) as day, HOUR(start) as hour,  
			
			SUM(IF(purpose = 'Commute', 1, 0)) 
								as commute, SUM(IF(purpose = 'Social',1, 0)) as social, 
								SUM(IF(purpose = 'Errand', 1, 0 )) as errand, 
								SUM(IF(purpose = 'Work-Related', 1, 0)) as workRelated, 
								SUM(IF(purpose = 'School', 1, 0)) as school, 
								SUM(IF(purpose = 'Exercise', 1, 0)) as exercise, 
								SUM(IF(purpose = 'Shopping', 1, 0)) as shopping, 
								SUM(IF(purpose = 'Other', 1, 0)) as other,
								COUNT(purpose) as total 
			
			
			FROM trip WHERE DATE(start) >= DATE(". ' " ' . $start_date . '"' .")  
			GROUP BY WEEK(start), DAYOFWEEK(start), HOUR(start)";
		}
		if(isset($month))
		{
			$monthCounts = "SELECT MONTH(start) as month, 
			
			SUM(IF(purpose = 'Commute', 1, 0)) 
								as commute, SUM(IF(purpose = 'Social',1, 0)) as social, 
								SUM(IF(purpose = 'Errand', 1, 0 )) as errand, 
								SUM(IF(purpose = 'Work-Related', 1, 0)) as workRelated, 
								SUM(IF(purpose = 'School', 1, 0)) as school, 
								SUM(IF(purpose = 'Exercise', 1, 0)) as exercise, 
								SUM(IF(purpose = 'Shopping', 1, 0)) as shopping, 
								SUM(IF(purpose = 'Other', 1, 0)) as other,
								COUNT(purpose) as total  
			
			FROM trip WHERE DATE(start) >= DATE(".'"'.$start_date.'"'.") 
			GROUP BY  MONTH(start)";
		}
		if(isset($week))
		{
			$weekCounts = "SELECT WEEK(start) as week, 
			
			SUM(IF(purpose = 'Commute', 1, 0)) 
								as commute, SUM(IF(purpose = 'Social',1, 0)) as social, 
								SUM(IF(purpose = 'Errand', 1, 0 )) as errand, 
								SUM(IF(purpose = 'Work-Related', 1, 0)) as workRelated, 
								SUM(IF(purpose = 'School', 1, 0)) as school, 
								SUM(IF(purpose = 'Exercise', 1, 0)) as exercise, 
								SUM(IF(purpose = 'Shopping', 1, 0)) as shopping, 
								SUM(IF(purpose = 'Other', 1, 0)) as other,
								COUNT(purpose) as total 
			
			FROM trip WHERE WEEK(start) >= WEEK(".'"'.$start_date.'"'.") 
			GROUP BY WEEK(start)";
		}
		if(isset($end_date))
		{
			$purposeCounts = "SELECT 
			
			SUM(IF(purpose = 'Commute', 1, 0)) 
								as commute, SUM(IF(purpose = 'Social',1, 0)) as social, 
								SUM(IF(purpose = 'Errand', 1, 0 )) as errand, 
								SUM(IF(purpose = 'Work-Related', 1, 0)) as workRelated, 
								SUM(IF(purpose = 'School', 1, 0)) as school, 
								SUM(IF(purpose = 'Exercise', 1, 0)) as exercise, 
								SUM(IF(purpose = 'Shopping', 1, 0)) as shopping, 
								SUM(IF(purpose = 'Other', 1, 0)) as other,
								COUNT(purpose) as total
			
			FROM trip WHERE DATE(start) >= DATE(". ' " ' . $start_date . '"' .") 
			AND DATE(start) <= DATE(". ' " ' . $end_date . '"' .")";
			if(isset($week_day))
			{
				$weekDayCounts = "SELECT DAYOFWEEK(start) as day,   
				
				SUM(IF(purpose = 'Commute', 1, 0)) 
								as commute, SUM(IF(purpose = 'Social',1, 0)) as social, 
								SUM(IF(purpose = 'Errand', 1, 0 )) as errand, 
								SUM(IF(purpose = 'Work-Related', 1, 0)) as workRelated, 
								SUM(IF(purpose = 'School', 1, 0)) as school, 
								SUM(IF(purpose = 'Exercise', 1, 0)) as exercise, 
								SUM(IF(purpose = 'Shopping', 1, 0)) as shopping, 
								SUM(IF(purpose = 'Other', 1, 0)) as other,
								COUNT(purpose) as total
				
				FROM trip WHERE DATE(start) >= DATE(". ' " ' . $start_date . '"' .")  
				
				
				AND DATE(start) <= DATE(". ' " ' . $end_date . '"' .") GROUP BY WEEK(start), DAYOFWEEK(start)";
			}	
			if(isset($hour))
			{
				$hourCounts = "SELECT start, DAYOFWEEK(start) as day, HOUR(start) as hour,  
				
				
				SUM(IF(purpose = 'Commute', 1, 0)) 
								as commute, SUM(IF(purpose = 'Social',1, 0)) as social, 
								SUM(IF(purpose = 'Errand', 1, 0 )) as errand, 
								SUM(IF(purpose = 'Work-Related', 1, 0)) as workRelated, 
								SUM(IF(purpose = 'School', 1, 0)) as school, 
								SUM(IF(purpose = 'Exercise', 1, 0)) as exercise, 
								SUM(IF(purpose = 'Shopping', 1, 0)) as shopping, 
								SUM(IF(purpose = 'Other', 1, 0)) as other,
								COUNT(purpose) as total  
				
				
				FROM trip WHERE DATE(start) >= DATE(". ' " ' . $start_date . '"' .")  AND DATE(start) <= DATE(". ' " ' . $end_date . '"' .")
				GROUP BY WEEK(start), DAYOFWEEK(start), HOUR(start)";	
			}
			if(isset($month))
			{
				$monthCounts = "SELECT MONTH(start) as month, 
				
				SUM(IF(purpose = 'Commute', 1, 0)) 
								as commute, SUM(IF(purpose = 'Social',1, 0)) as social, 
								SUM(IF(purpose = 'Errand', 1, 0 )) as errand, 
								SUM(IF(purpose = 'Work-Related', 1, 0)) as workRelated, 
								SUM(IF(purpose = 'School', 1, 0)) as school, 
								SUM(IF(purpose = 'Exercise', 1, 0)) as exercise, 
								SUM(IF(purpose = 'Shopping', 1, 0)) as shopping, 
								SUM(IF(purpose = 'Other', 1, 0)) as other,
								COUNT(purpose) as total  
				
				
				FROM trip WHERE DATE(start) >= DATE(". ' " ' . $start_date . '"' .") 
				AND DATE(start) <= DATE (".'"'.$end_date.'"'.") GROUP BY MONTH(start)";
			}
			if(isset($week))
			{
				$weekCounts = "SELECT WEEK(start) as week, 
				
				SUM(IF(purpose = 'Commute', 1, 0)) 
								as commute, SUM(IF(purpose = 'Social',1, 0)) as social, 
								SUM(IF(purpose = 'Errand', 1, 0 )) as errand, 
								SUM(IF(purpose = 'Work-Related', 1, 0)) as workRelated, 
								SUM(IF(purpose = 'School', 1, 0)) as school, 
								SUM(IF(purpose = 'Exercise', 1, 0)) as exercise, 
								SUM(IF(purpose = 'Shopping', 1, 0)) as shopping, 
								SUM(IF(purpose = 'Other', 1, 0)) as other,
								COUNT(purpose) as total 
				
				FROM trip WHERE WEEK(start) >= WEEK(".'"'.$start_date.'"'.")
				AND WEEK(start) <= WEEK(". ' " ' . $end_date . '"' .") GROUP BY WEEK(start)";
			}
		}
				
	}
	if(isset($end_date) && IS_NULL($start_date))
	{
		$purposeCounts = "SELECT
		
		 SUM(IF(purpose = 'Commute', 1, 0)) 
								as commute, SUM(IF(purpose = 'Social',1, 0)) as social, 
								SUM(IF(purpose = 'Errand', 1, 0 )) as errand, 
								SUM(IF(purpose = 'Work-Related', 1, 0)) as workRelated, 
								SUM(IF(purpose = 'School', 1, 0)) as school, 
								SUM(IF(purpose = 'Exercise', 1, 0)) as exercise, 
								SUM(IF(purpose = 'Shopping', 1, 0)) as shopping, 
								SUM(IF(purpose = 'Other', 1, 0)) as other,
								COUNT(purpose) as total 
		
		
		FROM trip WHERE DATE(start) <= DATE(". ' " ' . $end_date . '"' .")";
		if(isset($week_day))
		{
			$weekDayCounts = "SELECT DAYOFWEEK(start) as day,   
				
				SUM(IF(purpose = 'Commute', 1, 0)) 
								as commute, SUM(IF(purpose = 'Social',1, 0)) as social, 
								SUM(IF(purpose = 'Errand', 1, 0 )) as errand, 
								SUM(IF(purpose = 'Work-Related', 1, 0)) as workRelated, 
								SUM(IF(purpose = 'School', 1, 0)) as school, 
								SUM(IF(purpose = 'Exercise', 1, 0)) as exercise, 
								SUM(IF(purpose = 'Shopping', 1, 0)) as shopping, 
								SUM(IF(purpose = 'Other', 1, 0)) as other,
								COUNT(purpose) as total
				
				FROM trip WHERE DATE(start) <= DATE(". ' " ' . $end_date . '"' .") GROUP BY WEEK(start), DAYOFWEEK(start)";
		}
		if(isset($hour))
		{
			$hourCounts = "SELECT start, DAYOFWEEK(start) as day, HOUR(start) as hour, 
			
			 SUM(IF(purpose = 'Commute', 1, 0)) 
								as commute, SUM(IF(purpose = 'Social',1, 0)) as social, 
								SUM(IF(purpose = 'Errand', 1, 0 )) as errand, 
								SUM(IF(purpose = 'Work-Related', 1, 0)) as workRelated, 
								SUM(IF(purpose = 'School', 1, 0)) as school, 
								SUM(IF(purpose = 'Exercise', 1, 0)) as exercise, 
								SUM(IF(purpose = 'Shopping', 1, 0)) as shopping, 
								SUM(IF(purpose = 'Other', 1, 0)) as other,
								COUNT(purpose) as total  
			
			
			FROM trip WHERE DATE(start) DATE(start) <= DATE(". ' " ' . $end_date . '"' .")
			GROUP BY WEEK(start), DAYOFWEEK(start), HOUR(start)";
		}
		if(isset($month))
		{
			$monthCounts = "SELECT MONTH(start) as month, 
			
			SUM(IF(purpose = 'Commute', 1, 0)) 
								as commute, SUM(IF(purpose = 'Social',1, 0)) as social, 
								SUM(IF(purpose = 'Errand', 1, 0 )) as errand, 
								SUM(IF(purpose = 'Work-Related', 1, 0)) as workRelated, 
								SUM(IF(purpose = 'School', 1, 0)) as school, 
								SUM(IF(purpose = 'Exercise', 1, 0)) as exercise, 
								SUM(IF(purpose = 'Shopping', 1, 0)) as shopping, 
								SUM(IF(purpose = 'Other', 1, 0)) as other,
								COUNT(purpose) as total  
			
			
			FROM trip WHERE DATE(start) <= DATE(". ' " ' . $end_date . '"' .") 
			GROUP BY DATE(start)";
		}
		if(isset($week))
		{
			$weekCounts = "SELECT WEEK(start) as week, 
			
			SUM(IF(purpose = 'Commute', 1, 0)) 
								as commute, SUM(IF(purpose = 'Social',1, 0)) as social, 
								SUM(IF(purpose = 'Errand', 1, 0 )) as errand, 
								SUM(IF(purpose = 'Work-Related', 1, 0)) as workRelated, 
								SUM(IF(purpose = 'School', 1, 0)) as school, 
								SUM(IF(purpose = 'Exercise', 1, 0)) as exercise, 
								SUM(IF(purpose = 'Shopping', 1, 0)) as shopping, 
								SUM(IF(purpose = 'Other', 1, 0)) as other,
								COUNT(purpose) as total
			
			
			 FROM trip WHERE 
			 WEEK(start) <= WEEK(". ' " ' . $end_date . '"' .") GROUP BY WEEK(start)";
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
		print_r(error_get_last());
		$weekDayCount = mysqli_query($con, $weekDayCounts);
		$aggregateWeek = array(array('Monday'), array('Tuesday'), array('Wednesday'),array("Thursday"), array("Friday"),
							array("Saturday"), array("Sunday"));
							
	
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
		if(isset($aggregate))
		{
			
			for($m = 0; $m<count($wRows)-1; $m++)
			{
				for($n = 0; $n<count($aggregateWeek); $n++)
				{
			
					if(strcmp($wRows[$m]['day'],$aggregateWeek[$n][0]) == 0)
			 	 	{		 		
				
						 array_push($aggregateWeek[$n], array('purpose'=>array('commute'=>$wRows[$m]['commute'],
						 							'social'=>$wRows[$m]['social'],
						 							'errand'=>$wRows[$m]['errand'],'workRelated'=>$wRows[$m]['workRelated'],
						 							'school'=>$wRows[$m]['school'],'exercise'=>$wRows[$m]['exercise'],
						 							'shopping'=>$wRows[$m]['shopping'],'other'=>$wRows[$m]['other'],
						 							'total_trips'=>$wRows[$m]['total'])	));
				
			  		}
				
						
				}
				
			}
			
			
			 
		}
		if(isset($aggregate))
		{
			$wRows = $aggregateWeek;
		}
		
			$returnData = array(
				"weekstart" => $start_date,
				"week_days" => $wRows
				);
		
	
		
		
	}
	if(isset($hour))
	{
		$dayRow = array("weekstart"=>$start_date);
		$hourCount = mysqli_query($con, $hourCounts);
		$prettyHour = array(array('Monday'), array('Tuesday'), array('Wednesday'),array("Thursday"), array("Friday"),
							array("Saturday"), array("Sunday"));
		while($r = mysqli_fetch_assoc($hourCount))
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
	
			
			
			
			$hourRows[] = $r;
			
	
			
		}
	
		for($i = 0; $i<count($hourRows)-1; $i++)
		{
			for($k = 0; $k<count($prettyHour); $k++)
			{
			
				if(strcmp($hourRows[$i]['day'],$prettyHour[$k][0]) == 0)
			 	 {		 		
				
						 array_push($prettyHour[$k], array('hour'=>$hourRows[$i]['hour'], 'trips'=>array('commute'=>$hourRows[$i]['commute'],
						 							'social'=>$hourRows[$i]['social'],
						 							'errand'=>$hourRows[$i]['errand'],'workRelated'=>$hourRows[$i]['workRelated'],
						 							'school'=>$hourRows[$i]['school'],'exercise'=>$hourRows[$i]['exercise'],
						 							'shopping'=>$hourRows[$i]['shopping'],'other'=>$hourRows[$i]['other'],
						 							'total_trips'=>$hourRows[$i]['total']))	);
				
			  		}
				
						
				}
				
		} 
			
		
		
		//$test = json_encode($prettyHour);
		//echo $test;
		$returnData = array(
				"weekstart" => $start_date,
				"purpose"=>$prettyHour
				);
			
	}
	if(isset($month))
	{
		$monthCount=mysqli_query($con, $monthCounts);
		$mRows;
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
		array_push($returnData, array("month_totals"=>$mRows));
	}
	if(isset($week))
	{
		$weekCount = mysqli_query($con, $weekCounts);
		while($r = mysqli_fetch_assoc($weekCount))
		{
			$weekRows[] = $r;
		}
		array_push($returnData, array("week_totals"=>$weekRows));
	}
		
	mysqli_close($con);		
	
	$response = $app->response();
	$response['Content-Type'] = 'application/json';
  
   
	$newData= json_encode($returnData, JSON_PRETTY_PRINT); 
	echo $newData;
  
    exit();

});



/***************************************************
TRIP: POST
*****************************************************/

//Sam POST trip (need to work on getting back the global ID

$app->post('/trips/trip', function () use($app, $con) 
{
	//get the parameters sent over as JSON 
    $body = $app->request()->params();
    //for testing purposes only
    //var_dump($body);
    //initialize key value variables   
	$values = '';
	$keys = '';
	//loop through the JSON data
	$user_id='';

	
	foreach($body as $k=>$v)
	{	
		//create a comma separated string of keys and values to pass to SQL
		if($k=='user_id'){
			$user_id = $v;
		}
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
	    	
       	
    //header('Location:../../tripMainView.php');
    //exit();
	
	
	//QUERY FOR THE GLOBAL ID
	$query = 'SELECT id FROM trip WHERE user_id = '. $user_id.' ORDER BY id DESC LIMIT 1';
	
	try
      {    		
        $result = mysqli_query($con, $query);
      } catch(PDOException $e) 
      {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
      }

      
      $rows = array();
	  while($r = mysqli_fetch_assoc($result))
	    	{ 	
	    		$rows[] = $r;
	    	}
	    	
	//Return the global ID in the body    	
	$response = $app->response();
   	$response['Content-Type'] = 'application/json';
   		 
    $response->body(json_encode($rows));
    $data = $response->body(json_encode($rows));

    return $data;
    exit();
    
	});



/***************************************************
TRIP: PUT
*****************************************************/


$app->put('/trips/trip', function () use($app, $con)
{

	$body = $app->request()->getBody();
//	$jsonBody = json_encode($body);
//	$_SESSION['userPutJSON'] = $jsonBody;
	$id='';
	$user_id='';
	$start='';
	$stop='';
	$purpose='';
	$notes='';
	$n_coord='';
	
	foreach($body as $k=>$v)
	{
		if($k=="id")
		{
			$id=$v;
		}
		if($k=="user_id")
		{
			$user_id=$v;
		}
		if($k=="start")
		{
			$start=$v;
		}
		if($k=="stop")
		{
			$stop=$v;
		}
		if($k=="purpose")
		{
			$purpose=$v;
		}
		if($k=="notes")
		{
			$notes=$v;
		}
		if($k=="n_coord")
		{
			$n_coord=$v;
		}
		
		
	}	
	$query = "UPDATE trip SET";	
	
	

	if($id!=''){
		$query = $query . " 'id' = " . $id . " ,";
	}
	

	if($user_id!=''){
		$query = $query . " 'user_id' = " . $user_id . " ,";
	}

	if($start!=''){
		$query = $query . " start = " . $start . " ,";
	}

	if($stop!=''){
		$query = $query . " stop = " . $stop . " ,";
	}

	if($purpose!=''){
		$query = $query . " purpose = " . $purpose . " ,";
	}
	
	if($notes!=''){
		$query = $query . " notes = " . $notes . " ,";
	}
	
	if($n_coord!=''){
		$query = $query . " n_coord = " . $n_coord . " ,";
	}
	
	
	//take of the last AND
	$query = substr($query, 0, -1);
	
	
	if(isset($id)){
		$query = $query . "WHERE" . " id = " . $id ;
	
	}
	
	//need to check to see if there are NO parameters, the "w" character needs to be taken from the string
	if(substr($query, -1)== 'W'){
		$query = substr($query, 0, -1);
		
	}
	
	mysqli_query($con, $query);

	
	
	$result = array("status" => "success");
	json_encode($result);
	$response = $app->response();
   	$response['Content-Type'] = 'application/json';
    $data = $response->body(json_encode($result));
    return $data;
});



/****************************************************
END TRIP RESOURCE
******************************************************/



/***************************************************
RESOURCE: NOTE
*****************************************************/


/***************************************************
NOTE: GET
*****************************************************/

$app->get('/notes/:id', function ($id) use($app, $con) 
{

	    	//need to use this for authentication purposes, hopefully will later pull back a password as well?
	    	$result = mysqli_query($con,"SELECT * FROM note WHERE user_id = '$id'");
	    		
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
	$bad_params = array();
	//set all possible variables...
	$id = $req->get('id');
	$user_id = $req->get('user_id');
	$trip_id = $req->get('purpose');
	$recorded = $req->get('notes');
	$latitude = $req->get('latitude');
	$longitude = $req->get('longitude');
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
		if(filter_var($id, FILTER_VALIDATE_INT)){
			$query = $query . " id = " . $id . " AND ";
		}
		else{
			array_push($bad_params, "id = ". $id);
		}
	}
	
	if(isset($user_id)){
		if(filter_var($user_id, FILTER_VALIDATE_INT)){
			$query = $query . " user_id = " . $user_id . " AND ";
		}
		else{
			array_push($bad_params, "user_id = ". $user_id);
		}
	}

	if(isset($trip_id) && filter_var($trip_id, FILTER_VALIDATE_INT)){
		$query = $query . " trip_id = " . $trip_id . " AND ";
	}
	if(isset($recorded)){
		$query = $query . " recorded = '" . $recorded . "' AND ";
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
	if(isset($note_type)){
		if(filter_var($note_type,FILTER_VALIDATE_INT)){
			$query = $query . " note_type = " . $note_type . " AND ";
		}
		else{
			array_push($bad_params, "note_type = ". $note_type);
		}
	}
	if(isset($details)){
		$query = $query . " details = " . $details . " AND ";
	}
	if(isset($img_url)){
		if(filter_var($img_url, FILTER_VALIDATE_URL)){
			$query = $query . " img_url = " . $img_url . " AND ";
		}
		else{
			array_push($bad_params, "img_url = ". $img_url);
		}
	}
	
	if(count($bad_params)!=0){
		//for testing
		//echo implode(", ", $bad_params);
		
		//SHOULD LOG TO FILE INSTEAD
	}
	else{
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
    }
    exit();	

});


/***************************************************
NOTE: POST
*****************************************************/


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



/***************************************************
NOTE: PUT
*****************************************************/


$app->put('/trips/trip', function () use($app, $con)
{

	$body = $app->request()->getBody();
//	$jsonBody = json_encode($body);
//	$_SESSION['userPutJSON'] = $jsonBody;
	$id='';
	$user_id='';
	$recorded='';
	$latitude='';
	$longitude='';
	$altitude='';
	$speed='';
	$hAccuracy = '';
	$vAccuracy = '';
	$note_type = '';
	$details = '';
	
	foreach($body as $k=>$v)
	{
		if($k=="id")
		{
			$id=$v;
		}
		if($k=="user_id")
		{
			$user_id=$v;
		}
		if($k=="recorded")
		{
			$recorded=$v;
		}
		if($k=="latitude")
		{
			$latitude=$v;
		}
		if($k=="longitude")
		{
			$longitude=$v;
		}
		if($k=="altitude")
		{
			$altitude=$v;
		}
		if($k=="speed")
		{
			$speed=$v;
		}
		if($k=="hAccuracy")
		{
			$hAccuracy=$v;
		}
		if($k=="vAccuracy")
		{
			$vAccuracy=$v;
		}
		if($k=="note_type")
		{
			$note_type=$v;
		}
		if($k=="details")
		{
			$details=$v;
		}		
		
	}	
	$query = "UPDATE note SET";	
	
	

	if($id!=''){
		$query = $query . " 'id' = " . $id . " ,";
	}
	

	if($user_id!=''){
		$query = $query . " 'user_id' = " . $user_id . " ,";
	}

	if($recorded!=''){
		$query = $query . " recorded = " . $recorded . " ,";
	}

	if($latitude!=''){
		$query = $query . " latitude = " . $latitude . " ,";
	}

	if($longitude!=''){
		$query = $query . " longitude = " . $longitude . " ,";
	}
	
	if($altitude!=''){
		$query = $query . " altitude = " . $altitude . " ,";
	}
	
	if($speed!=''){
		$query = $query . " speed = " . $speed . " ,";
	}
	
	if($hAccuracy!=''){
		$query = $query . " hAccuracy = " . $hAccuracy . " ,";
	}
	
	if($vAccuracy!=''){
		$query = $query . " vAccuracy = " . $vAccuracy . " ,";
	}
	
	if($note_type!=''){
		$query = $query . " note_type = " . $note_type . " ,";
	}
	
		
	if($details!=''){
		$query = $query . " details = " . $details . " ,";
	}
	
	
	
	//take of the last AND
	$query = substr($query, 0, -1);
	
	
	if(isset($id)){
		$query = $query . "WHERE" . " id = " . $id ;
	
	}
	
	//need to check to see if there are NO parameters, the "w" character needs to be taken from the string
	if(substr($query, -1)== 'W'){
		$query = substr($query, 0, -1);
		
	}
	
	mysqli_query($con, $query);

	
	
	$result = array("status" => "success");
	json_encode($result);
	$response = $app->response();
   	$response['Content-Type'] = 'application/json';
    $data = $response->body(json_encode($result));
    return $data;
});



/***************************************************
END NOTE RESOURCE
****************************************************/











$app->run();



?>