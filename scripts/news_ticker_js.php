<?
	require('../includes/lib_mysql.php');
//	header('Content-type: text/javascript');
?>
var theight=22;	var transtime=2000;	borderw=1;	var pad_top=4;	var pad_top2=4;	var pad_left=8;	var pad_left2=0;	borderd='border-style:solid; border-width:1px; border-color:#000000;';	var text_alignt='left';	var text_alignh='center';	var text_decort='none';	var text_decorh='none';	var fstylet='normal';	var fstyleh='normal';	var fweightt='normal';	var fweighth='bold';	var fontn='Arial';	bgcol = '#EEEEEE';	bgcolh = '#990000';	txtcol = '#000000';	txthicol = '#990000';	txtcolh = '#FFFFFF';	border_color_str='#000000';	var nstheight=0,nst2width=0;

<?
   $news_result = mQuery('SELECT title, pubDate FROM hdtv_rss ORDER BY pubDate DESC LIMIT 10');
	$x = 0;
	while ($row = mysql_fetch_assoc($news_result)) {
		$text_array .= 'text['. $x .'] = "'. addslashes($row['title']) .'";';
		$header_array .= 'header['. $x .'] = "'. gmdate('n/j, g:ia', $row['pubDate'] + $user->data['time_zone_offset']) .'";';
		$linka_array .= 'linka['. $x .'] = "/news.php";';
		$targa_array .= 'targa['. $x .'] = "_self";';
		$x++;
	}
?>
var text = new Array();<?=$text_array?>
var header = new Array();<?=$header_array?>
var linka = new Array();<?=$linka_array?>
var targa = new Array();<?=$targa_array?>

//var text = new Array();text[0] = "test1";text[1] = "test2";text[2] = "test3";
//var header = new Array();header[0] = "";header[1] = "";header[2] = "";
//var linka = new Array();linka[0] = "/news.php";linka[1] = "/news.php";linka[2] = "/news.php";
//var targa = new Array();targa[0] = "_parent";targa[1] = "_parent";targa[2] = "_parent";

	var divtext = new Array();	var divh = new Array();	var objst = new Array();	var objs = new Array();	var objsh = new Array();
	var IE4B=false;var NS4B=false;var NS6B=false;var OP5B=false;	NS4B=((document.layers)?true:false);	IE4B=((document.all)?true:false);	NS6B=((document.getElementById)&&(!IE4B))?true:false;	uagent = window.navigator.userAgent.toLowerCase();IE5=false;IE4=false;IEold=false;IE5=(uagent.indexOf('msie 5.0') != -1)?true:false;IE4=(uagent.indexOf('msie 4') != -1)?true:false;if(IE5||IE4){IEold=true;}	OP5B=(uagent.indexOf('opera') != -1)?true:false;	if(OP5B==true){IE4B=false;NS6B=true;}	if(NS6B==true){IE4B=false;}
	var ns6obj=null;	var str2='';	var msgw,msgh;	var mc=0;	var say=0,onceki=0;	msgw=500-75;	mc=text.length;	onceki=mc-1;	strpos='';strvis='';strpadding='',strpadding2='';nsdiv='';	nstheight=theight-borderw-borderw;	nst2width=500-borderw-borderw;		pad_top=pad_top-borderw;	if(pad_top<0){pad_top=0;}	pad_top2=pad_top2-borderw;	if(pad_top2<0){pad_top2=0;}
if(IE4B){	strpos='position:absolute;';	strvis='';	strpadding='padding-top:'+pad_top+'px;padding-left:'+pad_left+'px;';	strpadding2='padding-top:'+pad_top2+'px;padding-left:'+pad_left2+'px;';}else if(NS6B){	strpos='position:absolute;';	strvis='visibility:hidden;';	strpadding='padding-top:'+pad_top+'px;padding-left:'+pad_left+'px;';	strpadding2='padding-top:'+pad_top2+'px;padding-left:'+pad_left2+'px;';}
	divtev1=' onmouseover="mdivmo(';	divtev2=')" onmouseout ="restime(';	divtev3=')" onclick="butclick(';	divtev4=')"';
	for(i=0;i<mc;i++)	{		if(IE4B)		{			divtext[i]='<div id=d'+i+' onmouseover="mdivmo('+i+')" onmouseout ="restime('+i+')" onclick="butclick('+i+')"'+' style="'+strpos+''+strvis+'background:'+bgcol+'; COLOR: '+txtcol+'; '+strpadding+' left:'+100+'; top:0; width:1600 ;height:'+theight+'; FONT-FAMILY: '+fontn+'; FONT-SIZE: '+8+'pt; font-style: '+fstylet+'; font-weight: '+fweightt+'; margin:0px; TEXT-DECORATION: '+text_decort+'; overflow-y:hidden;text-align:'+text_alignt+';cursor: default;">';			divh[i]='<div id=dh'+i+' style="'+strpos+''+strvis+'background:'+bgcolh+'; COLOR: '+txtcolh+'; '+strpadding2+' left:0; top:0; width:'+100+'; height:'+theight+'; FONT-FAMILY: '+fontn+'; FONT-SIZE: '+8+'pt; font-style: '+fstyleh+'; font-weight: '+fweighth+'; TEXT-DECORATION: '+text_decorh+';margin:0px; overflow:hidden;;text-align:'+text_alignh+';cursor: default;">';		} else if(NS6B)		{			divtext[i]='<div id=d'+i+' onmouseover="mdivmo('+i+')" onmouseout ="restime('+i+')" onclick="butclick('+i+')"'+' style="'+strpos+''+strvis+'background:'+bgcol+'; COLOR: '+txtcol+'; '+strpadding+' left:'+100+'; top:0; width:1600 ;height:'+nstheight+'; FONT-FAMILY: '+fontn+'; FONT-SIZE: '+8+'pt; font-style: '+fstylet+'; font-weight: '+fweightt+'; TEXT-DECORATION: '+text_decort+';margin:0px; overflow-y:hidden;text-align:'+text_alignt+';cursor: default;">';			divh[i]='<div id=dh'+i+' style="'+strpos+''+strvis+'background:'+bgcolh+'; COLOR: '+txtcolh+'; '+strpadding2+' left:0; top:0; width:'+100+'; height:'+nstheight+'; FONT-FAMILY: '+fontn+'; FONT-SIZE: '+8+'pt; font-style: '+fstyleh+'; font-weight: '+fweighth+'; TEXT-DECORATION: '+text_decorh+';margin:0px;overflow:hidden;text-align:'+text_alignh+';cursor: default;">';		}	}
	function mdivmo(gnum)	{	if(IE4B)	{		if(linka[gnum]!='')		{			objd=eval('d'+gnum);			objd.style.color=txthicol;			objd.style.cursor='hand';			window.status=''+linka[gnum];		}	}	else if(NS6B)	{		if(linka[gnum]!='')		{			objs[onceki].style.color=txthicol;			objs[onceki].style.cursor='pointer';			window.status=''+linka[gnum];		}	}}function restime(gnum2){	if(IE4B)	{		objd=eval('d'+gnum2);		objd.style.color=txtcol;		window.status='';	}	else if(NS6B)	{		objs[onceki].style.color=txtcol;		window.status='';	}}function butclick(gnum3){	if(targa[gnum3]==''){targa[gnum3]='_parent';}	if(IE4B)	{		window.open(''+linka[gnum3],''+targa[gnum3]);	}	else if(NS6B)	{if((targa[gnum3].indexOf("_parent")>-1))
{
				eval("parent.window.location='"+linka[gnum3]+"'");
			}
			else if((targa[gnum3].indexOf("_top")>-1))
			{
				eval("top.window.location='"+linka[gnum3]+"'");
			}
			else
			{
				window.open(''+linka[gnum3],''+targa[gnum3]);
			}
	}}
if(IE4B){} else if(NS6B){	str2='';	for(i=0;i<mc;i++)	{
if(OP5B==true){		str2=str2+'<div id=op'+i+' style="position:absolute;overflow:hidden;'+strvis+' left:0;top:0;width:'+(nst2width+borderw+borderw)+'; height:'+theight+';'+strvis+borderd+'">';}else{		str2=str2+'<div id=op'+i+' style="position:absolute;overflow:hidden;'+strvis+' left:0;top:0;width:'+nst2width+'; height:'+nstheight+';'+strvis+borderd+'">';}		str2=str2+''+divh[i]+header[i]+'</div>'+divtext[i]+''+text[i]+'</div>';		str2=str2+'</div>';	}} else if(NS4B){}
function dotrans(){	if(IE4B)	{			spage.innerHTML =''+divh[say]+'</div>'+divtext[say]+'</div>';			if(IEold!=true){spage.filters[0].apply();}			spage.innerHTML =''+divh[say]+header[say]+'</div>'+divtext[say]+text[say]+'</div>';			if(IEold!=true){spage.filters[0].play();}		setTimeout('dotrans()',5000+transtime);	} else if(NS6B)	{if(say!=onceki){		objsh[say].style.visibility='visible';		objsh[onceki].style.visibility='hidden';		objs[say].style.color=txtcol;		objs[say].style.visibility='visible';		objs[onceki].style.visibility='hidden';		objst[say].style.visibility='visible';		objst[onceki].style.visibility='hidden';}		onceki=say;		setTimeout('dotrans()',5000);	}	say++;	if(say>=mc){say=0;}}
	function dofirst()	{		var i=0;		var str="";		for(i=0;i<mc;i++)		{			str="d"+i;				objs[i]=document.getElementById(str);			objs[i].style.left=""+100+"px";			objs[i].style.top="0px";			objs[i].style.visibility="hidden";			str="dh"+i;				objsh[i]=document.getElementById(str);			objsh[i].style.left="0px";			objsh[i].style.top="0px";			objsh[i].style.visibility="hidden";			str="op"+i;				objst[i]=document.getElementById(str);			objst[i].style.left="0px";			objst[i].style.top="0px";			objst[i].style.visibility="hidden";		}		objs[0].style.visibility="visible";		objsh[0].style.visibility="visible";		objst[0].style.visibility="visible";		dotrans();	}
	function initte()	{							if(IE4B)			{					spage.style.borderStyle="solid";			spage.style.borderWidth=""+borderw+"px";			spage.style.borderColor=border_color_str;			spage.innerHTML=""+divh[say]+header[say]+"</div>"+divtext[0]+text[0]+"</div>";			say=1;if(say>=mc){say=0;}			setTimeout('dotrans()',5000);		} else if(NS6B)		{			say=0;			ns6obj=document.getElementById('spage');			ns6obj.innerHTML=str2;			setTimeout('dofirst()',500);		}	}window.onload=initte;
