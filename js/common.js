/*******************************************************************************************************************
	adjustAreaSize
		- Makes a textarea dynamically size
	createAJAXRequest
		- Standard AJAX caller
	formatCurrency(num)
		- Given a number, formats it as currency
	highlight(sid)
		- Used for onmouseover highlights of table rows
	hover_over(oRow)
	hover_out(oRow)
	isValidEmail(obj)
	link_over(oLink)
	link_out(oLink)
	quickMod(id, action)
	expand
	collapse
*******************************************************************************************************************/

	// For hover_over and hover_out functions
	var prevColor = new Array();
	var adjustment = 16;  // Adjust colors

	/***** adjustAreaSize *****/
	function adjustAreaSize(area, minimum, maximum) {
		offset = 5;
		minimum = (minimum == null) ? 60 : minimum;
		maximum = (maximum == null) ? 300 : maximum;

		area.style.height = minimum +'px';
		content_height = area.scrollHeight + offset;
		if (content_height > minimum) {
			if (content_height > maximum) {content_height = maximum;}
			area.style.height = content_height +'px';
		}
	}

	/***** createAJAXRequest *****/
	function ajaxSendRequest(retrievalURL, responseFunction) {
		if (window.ActiveXObject) {
			ajaxReq = new ActiveXObject("Microsoft.XMLHTTP");
		} else if (window.XMLHttpRequest) {
			ajaxReq = new XMLHttpRequest();
		}
		ajaxReq.open('GET', retrievalURL);
		ajaxReq.onreadystatechange = eval(responseFunction);
		ajaxReq.send(null);
	}

	/***** createAJAXRequest *****/
	function createAJAXRequest(url, parameters, responseFunction) {
		if (window.XMLHttpRequest) { // Mozilla, Safari, ...
			httpRequest = new XMLHttpRequest();
		} else if (window.ActiveXObject) { // IE
			try {
				httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
			} catch (e) {
				try {
					httpRequest = new ActiveXObject("Microsoft.XMLHTTP");
				} catch (e) {}
			}
		}

		if (!httpRequest) {
			alert('Invalid httpRequest object');
			return false;
		}

		if (parameters == '') {
			method = 'GET';
		} else {
			method = 'POST';
		}
		httpRequest.onreadystatechange = function() {eval(responseFunction);}
		httpRequest.open(method, url, true);
		httpRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		httpRequest.setRequestHeader("Content-length", parameters.length);
		httpRequest.send(parameters);
	}

	/***** formatCurrency *****/
	function formatCurrency(num) {
		num = num.toString().replace(/\$|\,/g,'');
		if (isNaN(num)) num = "0";
		sign = (num == (num = Math.abs(num)));
		num = Math.floor(num*100+0.50000000001);
		cents = num % 100;
		num = Math.floor(num/100).toString();
		if (cents<10) cents = "0" + cents;
		for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++) num = num.substring(0,num.length-(4*i+3))+','+
		num.substring(num.length-(4*i+3));
		return (((sign)?'':'-') + '$' + num + '.' + cents);
	}

	/***** highlight *****/
	function highlight(sid) {
		oCheckbox = document.getElementById('c'+ sid);
		oRow = document.getElementById('r'+ sid);
		if (oCheckbox.checked) {
			oRow.style.backgroundColor = '#c0c0c0';
		} else {
			oRow.style.backgroundColor = '';
		}
	}

	/***** hover_over *****/
	function hover_over(oRow) {
		for (x = 0; x < oRow.cells.length; x++) {
			bgColor = oRow.cells[x].style.backgroundColor;
			if (bgColor == 'transparent' || bgColor == '') { // Get background color
				bgColor = document.bgColor;
				if (bgColor == 'transparent' || bgColor == '') { // Set color = white
					bgColor = '#FFFFFF';
				}
			}
			prevColor[x] = bgColor;

			if (bgColor[0] == '#') {
				bgc1 = parseInt(bgColor.substr(1,2), 16) - adjustment;
				bgc2 = parseInt(bgColor.substr(3,2), 16) - adjustment;
				bgc3 = parseInt(bgColor.substr(5,2), 16) - adjustment;
			} else if (bgColor[0] == 'r') {
				lp = bgColor.indexOf('(');
				rp = bgColor.indexOf(')');
				bgColor = bgColor.substring(lp+1, rp);
				bgColor = bgColor.split(', ');
				bgc1 = parseInt(bgColor[0]) - adjustment;
				bgc2 = parseInt(bgColor[1]) - adjustment;
				bgc3 = parseInt(bgColor[2]) - adjustment;
			} else {
				bgc1 = 255 - adjustment;
				bgc2 = 255 - adjustment;
				bgc3 = 255 - adjustment;
			}

			// Ensure that we're not out of bounds
			if (bgc1 < 0) {bgc1 = 0}
			if (bgc2 < 0) {bgc2 = 0}
			if (bgc3 < 0) {bgc3 = 0}

			// Pad the number
			bgc1 = bgc1.toString();
			bgc2 = bgc2.toString();
			bgc3 = bgc3.toString();

			oRow.cells[x].style.backgroundColor = 'rgb('+ bgc1 +', '+ bgc2 +', '+ bgc3 +')';
		}
//		alert(oRow.cells[0].style.backgroundColor);
	}

	/***** hover_out *****/
	function hover_out(oRow) {
//		alert(prevColor[0]);
		for (x = 0; x < oRow.cells.length; x++) {
			oRow.cells[x].style.backgroundColor = prevColor[x];
		}
	}

	/***** isValidEmail *****/
	function isValidEmail(obj) {
		/*
			Inputs:	obj - Any valid form element (input type="text")
			Returns:	True, if obj.value is a valid email address, False otherwise.
			Actions:	Throws focus to field on False.
		*/

		var re = /\w+@\w+\.\w+/;
		if ( (obj.value == '') || (!obj.value.match(re)) ) {
			alert('You must enter a valid Email Address!.');
			obj.focus();
			return false;
		}
		return true;
	}

	/***** link_over *****/
	function link_over(oLink) {
		var lnk = oLink.href.substr(oLink.href.indexOf('?') + 1);
		window.status = lnk;
		return true;
	}

	/***** link_out *****/
	function link_out(oLink) {
		window.status = '';
		return true;
	}

	/***** popOpen *****/
	function popOpen(url, w, h, name) {
		w = (w === undefined) ? 800 : w;
		h = (h === undefined) ? 600 : h;
		name = (name === undefined) ? '' : name;

		t = (screen.height - h)/2;
		l = (screen.width - w)/2;

		window.open(url, name, 'resizable=1, scrollbars=1, toolbar=0, location=1, directories=0, menubar=0, width='+w+', height='+h+', top='+t+', left='+l) ;
	}

	/***** quickMod *****/
	function quickMod(id, action) {
		frmQM.id.value = id.toString();
		frmQM.action.value = action;
		frmQM.submit();
	}

	/***** setCookie *****/
	function setCookie(name, value, days) {
		if (days) {
			var date = new Date();
			date.setTime(date.getTime()+(days*24*60*60*1000));
			var expires = "; expires="+date.toGMTString();
		}
		else var expires = "";
		document.cookie = name+"="+value+expires+"; domain=.hdtvmagazine.com; path=/";
	}

	/***** getCookie *****/
	function getCookie(name) {
		var nameEQ = name + "=";
		var ca = document.cookie.split(';');
		for(var i=0;i < ca.length;i++) {
			var c = ca[i];
			while (c.charAt(0)==' ') c = c.substring(1,c.length);
			if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
		}
		return null;
	}

	/***** eraseCookie *****/
	function eraseCookie(name) {
		setCookie(name,"",-1);
	}

	/***** expand_collapse *****/
	function expand_collapse(id, flg_setcookie) {
		divBody = document.getElementById(id+'_body');
		if (divBody.style.display == 'none') {
			Effect.BlindDown(id+'_body');
			if (flg_setcookie) setCookie(id+'_showhide', 'show', 365);
		} else {
			Effect.BlindUp(id+'_body');
			if (flg_setcookie) setCookie(id+'_showhide', 'hide', 365);
		}
	}