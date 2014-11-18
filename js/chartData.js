

function testTrips()
{
var twenty13=0;
	var twenty12=0;
	var twenty11=0;
	var twenty10=0;
$.ajax({
     type: "GET",
     url: "index.php/trips/10",
     async: false,
    
 
 dataType: "json",
 success: function(trips){
    //do your stuff with the JSON data
    var myData = trips;
		console.log(myData);
		for(var i = 0; i<myData.length;i++)
		{
		
			if(myData[i]["start"])
			{
				if(myData[i]["start"].match(/2013/g))
   				{
   				console.log(myData[i]["start"]);
       			twenty13=twenty13+1;
   				}
   				if(myData[i]["start"].match(/2012/g))
   				{
       			twenty12++;
   				}
   				if(myData[i]["start"].match(/2011/g))
   				{
       			twenty11++;
   				}
   				if(myData[i]["start"].match(/2010/g))
   				{
       			twenty10++;
   				}
			}
		}
    datesTrips = [{year:"2013",total:twenty13},{year:"2012",total:twenty12},{year:"2011",total:twenty11},{year:"2010",total:twenty10}];
    //datesTrips=[twenty13, twenty12, twenty11, twenty10];
    console.log(datesTrips);
   
 }
});
 return datesTrips;
}
//datesTrips = [["2013",twenty13],["2012",twenty12],["2011",twenty11],["2010",twenty10]];

/**
var twenty13=0;
	var twenty12=0;
	var twenty11=0;
	var twenty10=0;
	$.getJSON("index.php/trips/10", function(trips)
	{
		var myData = trips;
		console.log(myData);
		$.each(trips, function(key,value)
		{
			if(key=="start")
			{
				if(value.match(/2013/g))
   				{
   				console.log(jsonData[i]["start"]);
       			twenty13=twenty13+1;
   				}
   				if(value.match(/2012/g))
   				{
       			twenty12++;
   				}
   				if(value.match(/2011/g))
   				{
       			twenty11++;
   				}
   				if(value.match(/2010/g))
   				{
       			twenty10++;
   				}
			}
		});**/
		
//	});
	//datesTrips = [["2013",twenty13],["2012",twenty12],["2011",twenty11],["2010",twenty10]];
	//console.log(datesTrips);
	//return datesTrips;