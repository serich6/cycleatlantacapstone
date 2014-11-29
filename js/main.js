
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

$('#postTripButton').click(function() {
	
	addTrip();
	return false;
});

var div = document.getElementById("dom-target");
var user_id = div.textContent;





function showJSONuser(id)
{
	
	$.ajax({
		type: 'GET',
		contentType: 'application/json',
		url: "index.php/users/"+id,
		dataType: "json",
		
		success: function(response){
   		console.log(response);
   		
   		
      	 $('#uJSON').text(JSON.stringify(response[0], null, 4));
      	 
    }
		
	});
	
	
}

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


function addTrip() {
	
	$.ajax({
		type: 'POST',
		contentType: 'application/json',
		url: "index.php/trips/trip",
		dataType: "json",
		data: postTripFormToJSON(),
		success: function(data, textStatus, jqXHR){
			alert('Trip added successfully');
		},
// 		error: function(jqXHR, textStatus, errorThrown){
// 			alert('addTrip error: ' + textStatus);
// 		}
		
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

function created(data){
	if(data["created"] != null)
   	{
   		$('#created').replaceWith(data["created"]);
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
      	  $('#uJSON').text(JSON.stringify(response[0], null, 4));  
      	 
      	
    }
		
	});
		


}

function getNoteData(id)
{

	//can honestly just use one ajax call to users/:id and parse the json response as needed
		$.ajax({
		type: 'GET',
		contentType: 'application/json',
		url: "index.php/notes/" + id ,
		dataType: "json",
		
		success: function(response){
   		console.log(response);
   		
      	$('#noteDate').replaceWith(response[0]["recorded"]);
      	$('#noteDetails').replaceWith(response[0]["details"]);
      	$(document).ready(function() {
 
 		 //I'm not doing anything else, so just leave
		if(!navigator.geolocation) return;
	
		//navigator.geolocation.getCurrentPosition(function(pos) {
			geocoder = new google.maps.Geocoder();
			var latlng = new google.maps.LatLng(response[0]["latitude"],response[0]["longitude"]);
		//	console.log(latlng);
			geocoder.geocode({'latLng': latlng}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				//Check result 0
				var result = results[0];
				//look for locality tag and administrative_area_level_1
				var city = "";
				var state = "";
				//console.log(result.address_components);
				for(var i=0, len=result.address_components.length; i<len; i++) {
					var ac = result.address_components[i];
					if(ac.types.indexOf("neighborhood") >= 0) city = ac.long_name;
					//if(ac.types.indexOf("administrative_area_level_1") >= 0) state = ac.long_name;
				}
				//only report if we got Good Stuff
				if(city != '') {
					$("#noteLocation").replaceWith(city);
				}
				} 
			//});
		
	
		});
		});	
      
  		
     // 	  $('#uJSON').text(JSON.stringify(response[0], null, 4));  
      	 
      	
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
		"password": $('#updatePassword').val(),
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

function postTripFormToJSON() {
	return JSON.stringify({
		"user_id": $('#user_id').val(), 
		"purpose": $('#purpose').val(),
		//"notes": $('#notes').val(), 
		"start": $('#start').val(),
		"stop": $('#stop').val(), 
		"n_coord": $('#n_coord').val()
		});
}