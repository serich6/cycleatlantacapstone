var length;
/******
Called on page load of the portal.html
Populates the trip data fields
********/


function getTripData(id)
{

	
		$.ajax({
		type: 'GET',
		contentType: 'application/json',
		url: "index.php/trips/"+id,
		dataType: "json",
		
		success: function(response){
   		console.log(response[3]);
   		if(response!='')
   		{
   			var tripNames = "";
   			for(var i = 0; i<response.length;i++)
   			{
   				var myDateArray = (response[i]["start"]).split("-");
				var theDate = new Date(myDateArray[0], myDateArray[1]-1, myDateArray[2].substring(0, 1) );
   				d = ((theDate.getMonth()+1)+'/'+theDate.getDate()+'/'+theDate.getFullYear());
   				tripNames+="<option value='" + response[i]["id"] + "'>" + d + "</option>";
   			}
   			
   			$("#tripList").html(tripNames);
   			if(response[0]["purpose"]!='')
   			{
   				$('#tripPurpose').html(response[0]["purpose"]);
   			}
   			
   			if(typeof response[0]["notes"]!= undefined)
   			{
   				$('#tripNotes').html(response[0]["notes"]);
   			}
   			
   			if(response[0]["start"]!='')
   			{
   				$('#tripStart').html(response[0]["start"]);
   			}
   			
   			if(response[0]["stop"]!='')
   			{
   				$('#tripEnd').html(response[0]["stop"]);
   			}
   			if(response[0]["n_coord"]!='')
   			{
   				length = (parseInt(response[0]["n_coord"]))/60;
   				console.log(length);
   				$('#tripLength').html((parseInt(response[0]["n_coord"]))/60 + '' +" minutes");
   			}
   				
   			
   		}
   		
    	}
		
	});
	
}

/*****
Called when the drop down menu on the portal page is changed
to display new trip data
******/

function changeTripDetails()
{
   
	var tripSelect = document.getElementById("tripList");
	var trip = document.getElementById("tripList").value;
	//var trip = tripSelect.options[tripSelect.selectedIndex].text;
	var curTrip;
	
		$.ajax({
		type: 'GET',
		contentType: 'application/json',
		url: "index.php/trips?id="+ trip,
		dataType: "json",
		
		success: function(response){
   		
   			for(var i = 0; i < response.length; i++)
   			{ 				
   				if(response[i]["id"] == trip)
   				{
   					curTrip = response[i];
   				
   				}
   			}
   			$('#tripPurpose').empty().html(curTrip.purpose);
   		    $('#tripNotes').empty().html(curTrip.notes);   			
   			$('#tripStart').html(curTrip.start);      		
   			$('#tripEnd').html(curTrip.stop);   				
   			$('#tripLength').html((parseInt(curTrip.n_coord))/60 + '' +" minutes");   		
    	}
		
	});
}
