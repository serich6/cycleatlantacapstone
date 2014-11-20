
// The root URL for the RESTful services
//code courtesy (but refactored) of : https://github.com/ccoenraets/wine-cellar-php
var rootURL = "index.php/users/user";




$('#putButton').click(function() {
	
	updateUser();
	return false;
});

$('#submitUpdates').click(function() {
	
	updateUser();
	return false;
});

$('#postButton').click(function() {
	
	addUser();
	return false;
});

function addUser() {
	
	$.ajax({
		type: 'POST',
		contentType: 'application/json',
		url: rootURL,
		dataType: "json",
		data: postFormToJSON(),
		success: function(data, textStatus, jqXHR){
			alert('User added successfully');
		},
		error: function(jqXHR, textStatus, errorThrown){
			alert('addUser error: ' + textStatus);
		}
		
	});
}


function getHomeZip(id)
{
	
	$.ajax({
		type: 'GET',
		contentType: 'application/json',
		url: "index.php/users/"+ id +"/homeZIP",
		dataType: "json",
		
		success: function(response){
       // $(".name").html(response[0]["age"]);
       
       $('#hZIP').replaceWith(response[0]["homeZIP"]);
    }
		
	});
	
	
}

function getWorkZip(id)
{
	
	$.ajax({
		type: 'GET',
		contentType: 'application/json',
		url: "index.php/users/"+id+"/workZIP",
		dataType: "json",
		
		success: function(response){
   
       $('#wZIP').replaceWith(response[0]["workZIP"]);
    }
		
	});
	
	
}

function getSchoolZip(id)
{
	
	$.ajax({
		type: 'GET',
		contentType: 'application/json',
		url: "index.php/users/"+id+"/schoolZIP",
		dataType: "json",
		
		success: function(response){
   		console.log(response);
   		if(response[0]["schoolZIP"] == null)
   		{
   			$('#sZIP').replaceWith("Not applicable");
   		}
   		else
   		{
      	 $('#sZIP').replaceWith(response[0]["schoolZIP"]);
      	 }
    }
		
	});
	
	
}

function getEmail(id)
{
	
	$.ajax({
		type: 'GET',
		contentType: 'application/json',
		url: "index.php/users/" + id + "/email",
		dataType: "json",
		
		success: function(response){
   		console.log(response);
   		if(response[0]["email"] == null)
   		{
   			$('#email').replaceWith("Not applicable");
   		}
   		else
   		{
      	 $('#email').replaceWith(response[0]["email"]);
      	 }
    }
		
	});
	
	
}

function getCycleFreq(id)
{
	
	$.ajax({
		type: 'GET',
		contentType: 'application/json',
		url: "index.php/users/" + id + "/cycling_freq",
		dataType: "json",
		
		success: function(response){
   		console.log(response);
   		if(response[0]["cycling_freq"] == null)
   		{
   			$('#cFREQ').replaceWith("Not applicable");
   		}
   		if(response[0]["cycling_freq"] != null)
   		{
   			if(response[0]["cycling_freq"] == 0)
   			{
   				$('#cFREQ').replaceWith("No data");	
   			}
   			if(response[0]["cycling_freq"] == 1)
   			{
   				$('#cFREQ').replaceWith("> Once/month");	
   			}
   			if(response[0]["cycling_freq"] == 2)
   			{
   				$('#cFREQ').replaceWith("Several times/month");	
   			}
   			if(response[0]["cycling_freq"] == 3)
   			{
   				$('#cFREQ').replaceWith("Several times/week");	
   			}
   			if(response[0]["cycling_freq"] == 4)
   			{
   				$('#cFREQ').replaceWith("Daily");	
   			}
   		
      	 
      	 }
    }
		
	});
	
	
}

function getRiderType(id)
{
	
	$.ajax({
		type: 'GET',
		contentType: 'application/json',
		url: "index.php/users/" + id + "/rider_type",
		dataType: "json",
		
		success: function(response){
   		console.log(response);
   		if(response[0]["rider_type"] == null)
   		{
   			$('#cCONF').replaceWith("Not applicable");
   		}
   		if(response[0]["rider_type"] != null)
   		{
   			if(response[0]["rider_type"] == 0)
   			{
   				$('#cCONF').replaceWith("No data");	
   			}
   			if(response[0]["rider_type"] == 1)
   			{
   				$('#cCONF').replaceWith("Strong and fearless");	
   			}
   			if(response[0]["rider_type"] == 2)
   			{
   				$('#cCONF').replaceWith("Enthused and confident");	
   			}
   			if(response[0]["rider_type"] == 3)
   			{
   				$('#cCONF').replaceWith("Comfortable, but cautious");	
   			}
   			if(response[0]["rider_type"] == 4)
   			{
   				$('#cCONF').replaceWith("Interested, but concerned");	
   			}
   		
      	 
      	 }
    }
		
	});
	
	
}

function riderType(data)
{
	if(data["rider_type"] != null)
   		{
   			if(data["rider_type"] == 0)
   			{
   				$('#cCONF').replaceWith("No data");	
   			}
   			if(data["rider_type"] == 1)
   			{
   				$('#cCONF').replaceWith("Strong and fearless");	
   			}
   			if(data["rider_type"] == 2)
   			{
   				$('#cCONF').replaceWith("Enthused and confident");	
   			}
   			if(data["rider_type"] == 3)
   			{
   				$('#cCONF').replaceWith("Comfortable, but cautious");	
   			}
   			if(data["rider_type"] == 4)
   			{
   				$('#cCONF').replaceWith("Interested, but concerned");	
   			} 		
      	 
      	 }
	
}

function cycleFrequency(data)
{
	
   		if(data["cycling_freq"] != null)
   		{
   			if(data["cycling_freq"] == 0)
   			{
   				$('#cFREQ').replaceWith("No data");	
   			}
   			if(data["cycling_freq"] == 1)
   			{
   				$('#cFREQ').replaceWith("> Once/month");	
   			}
   			if(data["cycling_freq"] == 2)
   			{
   				$('#cFREQ').replaceWith("Several times/month");	
   			}
   			if(data["cycling_freq"] == 3)
   			{
   				$('#cFREQ').replaceWith("Several times/week");	
   			}
   			if(data["cycling_freq"] == 4)
   			{
   				$('#cFREQ').replaceWith("Daily");	
   			}
   		
      	 
      	 }

}

function schoolZip(data)
{
	if(data["schoolZIP"] != null)
   	{
   		$('#sZIP').replaceWith(data["schoolZIP"]);
   	}   		
}

function workZip(data)
{
	if(data["workZIP"] != null)
   	{
   		$('#wZIP').replaceWith(data["workZIP"]);
   	}
}

function homeZip(data)
{
	if(data["homeZIP"] != null)
   	{
   		$('#hZIP').replaceWith(data["homeZIP"]);
   	}	
}
function email(data)
{
	 if(data["email"] != null)
   	 {
   	 	$('#email').replaceWith(data["email"]);
     }
}
      	 
      	 
/*************************************
function getAllRiderType(type)
{

	
		$.ajax({
		type: 'GET',
		contentType: 'application/json',
		url: "index.php/users/?rider_type=" + type ,
		dataType: "json",
		
		success: function(response){
   		console.log(response);
   		
      	
      
  		 email(response[0]);
      	 schoolZip(response[0]);
      	 workZip(response[0]);
      	 homeZip(response[0]);
      	 riderType(response[0]);
      	 cycleFrequency(response[0]);
      	 
      	
    }
		
	});
		


}
*******************************************/



function getUserData(id)
{

	//can honestly just use one ajax call to users/:id and parse the json response as needed
		$.ajax({
		type: 'GET',
		contentType: 'application/json',
		url: "index.php/users/" + id ,
		dataType: "json",
		
		success: function(response){
   		console.log(response);
   		
      	
      
  		 email(response[0]);
      	 schoolZip(response[0]);
      	 workZip(response[0]);
      	 homeZip(response[0]);
      	 riderType(response[0]);
      	 cycleFrequency(response[0]);
      	 
      	
    }
		
	});
		


}




function updateUser() {
	
	$.ajax({
		type: 'PUT',
		contentType: 'application/json',
		url: 'index.php/users/user',
		dataType: "json",
		data: userFormToJSON(),
		success: function(data){
			if(data["status"]=="success")
			{
				location.href = "success.php"
			//	console.log(data["status"]);
			}
		}
		
	});
}


// Helper function to serialize all the form fields into a JSON string
function userFormToJSON() {
	return JSON.stringify({
		"id": $('#updateId').val(), 
		"email": $('#updateEmail').val(),
		"homeZIP": $('#updateHomeZip').val(),
		"workZIP": $('#updateWorkZip').val(),
		"schoolZIP": $('#updateSchoolZip').val(),
		"cycling_freq": $('#updateCycleFreq').val(),
		"rider_type": $('#updateCycleConf').val()
		});
}

//getting null for all values
//why?


function postFormToJSON() {
	return JSON.stringify({
		"email": $('#email').val(), 
		"gender": $('#gender').val(),
		"income": $('#income').val(), 
		"ethnicity": $('#ethnicity').val(),
		"homeZIP": $('#homeZIP').val(), 
		"schoolZIP": $('#schoolZIP').val(),
		"workZIP": $('#workZIP').val(), 
		"cycling_freq": $('#cycling_freq').val(),
		"rider_history": $('#rider_history').val(), 
		"rider_type": $('#rider_type').val(),
		"app_version": $('#app_version').val()
		});
}
