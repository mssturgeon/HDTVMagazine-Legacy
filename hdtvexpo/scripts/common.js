//######################################################################################
// Author: ricocheting.com
// For: public release (freeware)
// Date: 4/24/2003 (update: 5/24/2006)
// Description: displays the amount of time until the "dateFuture" entered below.


// NOTE: the month entered must be one less than current month. ie; 0=January, 11=December
// NOTE: the hour is in 24 hour format. 0=12am, 15=3pm etc
// format: dateFuture = new Date(year,month-1,day,hour,min,sec)
// example: dateFuture = new Date(2003,03,26,14,15,00) = April 26, 2003 - 2:15:00 pm

dateFuture = new Date(2009,1,17,0,0,0);

// TESTING: comment out the line below to print out the "dateFuture" for testing purposes
//document.write(dateFuture +"<br />");


//###################################
//nothing beyond this point
function GetCount(){

	dateNow = new Date();									//grab current date
	amount = dateFuture.getTime() - dateNow.getTime();		//calc milliseconds between dates
	delete dateNow;

	// time is already past
	if(amount < 0){
		document.getElementById('countbox').innerHTML="Now!";
	}
	// date is still good
	else{
		days=0;hours=0;mins=0;secs=0;out="";

		amount = Math.floor(amount/1000);//kill the "milliseconds" so just secs

		days=Math.floor(amount/86400);//days
		amount=amount%86400;

		hours=Math.floor(amount/3600);//hours
		amount=amount%3600;

		mins=Math.floor(amount/60);//minutes
		amount=amount%60;

		secs=Math.floor(amount);//seconds

		out = '<b>A New Era of Digital Television Begins In:&nbsp;&nbsp;&nbsp;</b>';
		if(days != 0){out += "<b>"+ days +"</b> day"+((days!=1)?"s":"")+", ";}
		if(days != 0 || hours != 0){out += "<b>"+ hours +"</b> hour"+((hours!=1)?"s":"")+", ";}
		if(days != 0 || hours != 0 || mins != 0){out += "<b>"+ mins +"</b> minute"+((mins!=1)?"s":"")+", ";}
		out += "<b>"+ secs +"</b> seconds";
		document.getElementById('countbox').innerHTML=out;

		setTimeout("GetCount()", 1000);
	}
}

window.onload=function(){GetCount();}//call when everything has loaded
