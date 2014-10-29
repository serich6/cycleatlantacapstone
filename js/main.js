// The root URL for the RESTful services
//code courtesy (but refactored) of : https://github.com/ccoenraets/wine-cellar-php
var rootURL = "index.php/users/user";




$('#putButton').click(function() {
	
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


function getHomeZip()
{
	
	$.ajax({
		type: 'GET',
		contentType: 'application/json',
		url: "index.php/users/10/homeZIP",
		dataType: "json",
		
		success: function(response){
       // $(".name").html(response[0]["age"]);
       
       $('#hZIP').replaceWith(response[0]["homeZIP"]);
    }
		
	});
	
	
}

function getWorkZip()
{
	
	$.ajax({
		type: 'GET',
		contentType: 'application/json',
		url: "index.php/users/10/workZIP",
		dataType: "json",
		
		success: function(response){
   
       $('#wZIP').replaceWith(response[0]["workZIP"]);
    }
		
	});
	
	
}

function getSchoolZip()
{
	
	$.ajax({
		type: 'GET',
		contentType: 'application/json',
		url: "index.php/users/10/schoolZIP",
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

function updateUser() {
	
	$.ajax({
		type: 'PUT',
		contentType: 'application/json',
		url: rootURL + '/' + $('#id').val() + '/workZip',
		dataType: "json",
		data: userFormToJSON(),
		success: function(textStatus, jqXHR){
			alert('User updated successfully');
		}
		
	});
}


// Helper function to serialize all the form fields into a JSON string
function userFormToJSON() {
	return JSON.stringify({
		"id": $('#id').val(), 
		"workZIP": $('#putWorkZip').val()
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
