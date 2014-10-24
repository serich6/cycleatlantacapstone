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
