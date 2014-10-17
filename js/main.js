// The root URL for the RESTful services
//code courtesy (but refactored) of : https://github.com/ccoenraets/wine-cellar-php
var rootURL = "index.php/users/user";




$('#putButton').click(function() {
	
	updateUser();
	return false;
});




function updateUser() {
	
	$.ajax({
		type: 'PUT',
		contentType: 'application/json',
		url: rootURL + '/' + $('#id').val(),
		dataType: "json",
		data: formToJSON(),
		
	});
}


// Helper function to serialize all the form fields into a JSON string
function formToJSON() {
	return JSON.stringify({
		"id": $('#id').val(), 
		"workZIP": $('#workZIP').val()
		});
}
