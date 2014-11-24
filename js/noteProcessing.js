function populateNoteTable(id)
{

	$.ajax({
		type: 'GET',
		contentType: 'application/json',
		url: "index.php/notes/"+id,
		dataType: "json",
		
		success: function(response){
   		var geocoder = new google.maps.Geocoder();
   		if(response!='')
   		{
   					var table = document.getElementById("noteTable");
   			
   			
   			for(var i = response.length-1; i > 0; i=i-1)
   			{
   	
				
    			var row = table.insertRow();
    			var recorded = row.insertCell();
    			
    			var details = row.insertCell();
    			var neighborhood = row.insertCell();
   				recorded.innerHTML = response[i]["recorded"];
    			
    			details.innerHTML = response[i]["details"];
    			
    			//if(!navigator.geolocation) return;
	
				//navigator.geolocation.getCurrentPosition(function(pos) {
					
					var latlng = new google.maps.LatLng(response[i]["latitude"],response[i]["longitude"]);
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
							neighborhood.innerHTML = city;
						}
						} 
					//});
		
	
				});
    			
    			
    			
    			
    		}
    			var header = table.createTHead();

				// Create an empty <tr> element and add it to the first position of <thead>:
				var headRow = header.insertRow(0);     

				// Insert a new cell (<td>) at the first position of the "new" <tr> element:
				var nCell = headRow.insertCell(0);
				var pCell = headRow.insertCell(0);
				var sCell = headRow.insertCell(0);
				
				
				pCell.innerHTML = "<strong>Recorded</strong>";
				nCell.innerHTML = "<strong>Details</strong>";
				sCell.innerHTML = "<strong>Neighborhood</strong>";
				
				
				
				
				var rows = table.getElementsByTagName("tr");
				
				for(var i = 0; i < rows.length; i++)
				{
					if(i%2 == 0)
					{
						rows[i].className = "even";
					}
					else
					{
						rows[i].className = "odd";
					}
				}
				
    	}
    }
    });
	
}