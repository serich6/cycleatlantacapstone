<?php
//grabbed from http://blog.garethj.com/2009/02/17/building-a-restful-web-application-with-php/

//Resources
//http://www.vinaysahni.com/best-practices-for-a-pragmatic-restful-api
//
//For simple login/session implementaiton
//http://forums.devshed.com/php-faqs-stickies-167/program-basic-secure-login-system-using-php-mysql-891201.html

	class RestService {
	
	  private $supportedMethods;
	
	  public function __construct($supportedMethods) {
	    $this->supportedMethods = $supportedMethods;
	  }

	  public function handleRawRequest() {
	    $url = $this->getFullUrl();
		echo "raw request: {$url} ";
		echo "<br>";
		
	    $method = $_SERVER['REQUEST_METHOD'];
	   
	    echo "_GET: {$_GET}";
	    echo "<br>";
	    
	    echo $method;
	    echo "<br>";
	    switch ($method) {
	      case 'GET':
	      	$arguments = $_GET;
	      	break;
	      case 'HEAD':
	        $arguments = $_GET;
	        break;
	      case 'POST':
	        $arguments = $_POST;
	        break;
	      case 'PUT':
	      	break;
	      case 'DELETE':
	        parse_str(file_get_contents('php://input'), $arguments);
	        break;
	    }
	    $accept = $_SERVER['HTTP_ACCEPT'];
	    echo "Accept: {$accept}";
	    echo "<br>";
	    echo "Arguments: {$arguments}";
	    echo "<br>";
	    $this->handleRequest($url, $method, $arguments, $accept);
	  }
	
	  protected function getFullUrl() {
	    $protocol = $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
	    $location = $_SERVER['REQUEST_URI'];
	    
	    if ($_SERVER['QUERY_STRING']) {
	      $location = substr($location, 0, strrpos($location, $_SERVER['QUERY_STRING']) - 1);
	    }
	    echo "Location: {$location}";
	    echo "<br>";
	    
	    return $protocol.'://'.$_SERVER['HTTP_HOST'].$location;
	    
	  }
	
	  public function handleRequest($url, $method, $arguments, $accept) {
	  //hardcoded $method for testing...needs to be removed
	  //$method='POST';
	   echo "Method: {$method}";
	        echo "<br>";
	    switch($method) {
	     case 'GET':
	        $this->performGet($url, $arguments, $accept);
	        break;
	        
	      case 'HEAD':
	        $this->performHead($url, $arguments, $accept);
	        break;
	      case 'POST':
	        $this->performPost($url, $arguments, $accept);
	        break;
	      case 'PUT':
	        $this->performPut($url, $arguments, $accept);
	        break;
	      case 'DELETE':
	        $this->performDelete($url, $arguments, $accept);
	        break;
	      default:
	        /* 501 (Not Implemented) for any unknown methods */
	        echo "Supported methods: {$this->supportedMethods}";
	        echo "<br>";
	        header('Allow: ' . $this->supportedMethods, true, 501);
	    }
	  }
	
	  protected function methodNotAllowedResponse() {
	    /* 405 (Method Not Allowed) */
	    header('Allow: ' . $this->supportedMethods, true, 405);
	    //FOR TESTING ONLY
	    echo $this->supportedMethods;
	  }
	
	  public function performGet($url, $arguments, $accept) {
	    //$this->methodNotAllowedResponse();
	    
	    header('Allow: ' . "GET", true, 200);
	    $args = (string)$arguments;
	    echo "Args: {$args}";
	    echo "<br>";
	    echo "Our url:". $url . "<br>";
	   
	    //hardcoding to remove http://api-dev.cycleatlanta.org/
	    $endpoint = substr ( $url ,32);
	    echo "Unchecked endpoint: " . $endpoint . "<br>";
	    //next need to filter out anything after ? (parameters)
	   $endpointArray = explode("?", $endpoint);
	   $endpoint = $endpointArray[0];
	   echo "<br>New endpoint: " . $endpoint . "<br>";
	   echo "<br>New Args: " . $arguments[0] . "<br>";
	    
	    if($endpoint=="users"){
	    	$con=mysqli_connect("mysql.govathon.cycleatlanta.org","govathon12db","7Jk3WYNt","catl_govathon");
	    	$result = mysqli_query($con,"SELECT * FROM user");
	    		while($row = mysqli_fetch_array($result)) {
  					echo $row['id'] . " " . $row['gender'];
  					echo "<br>";
				}	
	    	mysqli_close($con);
	  	}
	  	else if($endpoint=="trips"){
	  		 $con=mysqli_connect("mysql.govathon.cycleatlanta.org","govathon12db","7Jk3WYNt","catl_govathon");
	    	$result = mysqli_query($con,"SELECT * FROM trip");
	    		while($row = mysqli_fetch_array($result)) {
  					echo $row['user_id'] . " " . $row['purpose'];
  					echo "<br>";
				}	
	    	mysqli_close($con);
	  	}
	  	else if ($endpoint=="notes"){
	  		$con=mysqli_connect("mysql.govathon.cycleatlanta.org","govathon12db","7Jk3WYNt","catl_govathon");
	    	$result = mysqli_query($con,"SELECT * FROM note");
	    		while($row = mysqli_fetch_array($result)) {
  					echo $row['id'] . " " . $row['note_type'];
  					echo "<br>";
				}	
	    	mysqli_close($con);
	  	}
	  	else{
	  		echo "Invalid Endpoint!";
	  	}
	  }
	
	  public function performHead($url, $arguments, $accept) {
	    $this->methodNotAllowedResponse();
	  }
	
	  public function performPost($url, $arguments, $accept) {
	    //$this->methodNotAllowedResponse();
	    header('Allow: ' . $this->supportedMethods, true, 200);
	    $args = (string)$arguments;
	    echo "Args: {$args}";
	    echo "<br>";
	    $endpoint = substr ( $url ,32);
	    echo "Unchecked endpoint: " . $endpoint . "<br>";
	    
		if($endpoint=="users"){
			echo "Need to implement /users POST";
		}
		else if($endpoint=="trips"){
			echo "Need to implement /trips POST";
		}
		else if($endpoint=="notes"){
			echo "Need to implement /notes POST";
		}
		else{
			echo "Invalid Endpoint";
		}
	  }
	
	  public function performPut($url, $arguments, $accept) {
	    //$this->methodNotAllowedResponse();
	    
	    header('Allow: ' . $this->supportedMethods, true, 200);
	    $args = (string)$arguments;
	    echo "Args: {$args}";
	    echo "<br>";
	    $endpoint = substr ( $url ,32);
	    echo "Unchecked endpoint: " . $endpoint . "<br>";
	    
		if($endpoint=="users"){
			echo "Need to implement /users PUT";
		}
		else if($endpoint=="trips"){
			echo "Need to implement /trips PUT";
		}
		else if($endpoint=="notes"){
			echo "Need to implement /notes PUT";
		}
		else{
			echo "Invalid Endpoint";
		}
	  }
	
	  public function performDelete($url, $arguments, $accept) {
	    //$this->methodNotAllowedResponse();
	    
	    header('Allow: ' . $this->supportedMethods, true, 200);
	    $args = (string)$arguments;
	    echo "Args: {$args}";
	    echo "<br>";
	    $endpoint = substr ( $url ,32);
	    echo "Unchecked endpoint: " . $endpoint . "<br>";
	    
		if($endpoint=="users"){
			echo "Need to implement /users DELETE";
		}
		else if($endpoint=="trips"){
			echo "Need to implement /trips DELETE";
		}
		else if($endpoint=="notes"){
			echo "Need to implement /notes DELETE";
		}
		else{
			echo "Invalid Endpoint";
		}
	  }
	
	}
?>