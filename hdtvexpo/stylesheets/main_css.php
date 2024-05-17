<?
	header('Content-Type: text/css');

	define('LINK_COLOR', '#b51818');
	define('BORDER_COLOR', '#9c5a08');
	define('BORDER_COLOR_2', '#D5CA8B');
	define('BG_COLOR', '#e2Ca9c');
	define('BG_COLOR_2', '#F6EBAD');
	define('TEXT_COLOR', '#0');
	define('TEXT_COLOR_2', '#73681F');
?>

/*** ELEMENT SELECTORS ***/
a:link {color:<?=LINK_COLOR?>;}
a:visited {color:<?=LINK_COLOR?>;}
a:hover {color:<?=LINK_COLOR?>;}
a:active {color:<?=LINK_COLOR?>;}

body {background:#fff; color:<?=TEXT_COLOR?>; margin:0; padding:0;}

/* redundant rules for bad browsers  */
body, td, th, h3, textarea, input, select {font-family:Arial,sans-serif; font-size:9pt; voice-family:"\"}\""; voice-family:inherit;}

form {margin:0;}

h1 {border-top:1px dashed <?=BORDER_COLOR_2?>; border-bottom:1px dashed <?=BORDER_COLOR_2?>;color:<?=LINK_COLOR?>; margin:0 0 10px 0;}
h2 {color:<?=BORDER_COLOR?>; font-size:12pt; margin:15px 0 2px 0}
h3 {font-weight:bold; margin:2px}

img {border:0;padding:0;margin:0}

li {margin:0; list-style-type:disc;}
li {list-style-image:url(/images/square-brown_5.gif); margin:0 0 4px -20px;}
li.plain {list-style-image:none; list-style-type:none; margin:0 0 4px -20px;}

p.big-first:first-letter {font-size:200%; float:left;}
p.header {font:bold 12pt Arial,sans-serif; color:<?=PRIMARY_COLOR?>}

span.caption {float:left; margin:15px; text-align:center;}
span.caption img {border:1px solid black;}

table.main {border:1px solid <?=BORDER_COLOR_2?>; border-collapse:collapse;}
td.main_header {background-color:<?=BG_COLOR_2?>; border:1px solid <?=BORDER_COLOR_2?>; color:<?=TEXT_COLOR_2?>; font-weight:bold; padding:3px}
td.main {padding:2px}

textarea {border:1px solid <?=BORDER_COLOR?>; padding-left:3px; width:100%;}

.alertbox {background-color:<?=BG_COLOR_2?>; border:1px solid <?=BORDER_COLOR_2?>; color:<?=TEXT_COLOR_2?>; margin:10px; padding:5px; text-align:center;}

/* no relative positioning due to IE6 */
#container {margin:20px auto; width:728px;}
#content {padding:10px 0;}
#countbox {background-color:<?=LINK_COLOR?>; color:#FFFFFF; font-size:12pt; padding:3px; margin-bottom:10px; text-align:center;}
#expobox {background-color:<?=BG_COLOR_2?>; border:1px solid <?=BORDER_COLOR_2?>; color:<?=TEXT_COLOR_2?>; margin:10px; padding:5px; float:right; line-height:150%}
#expobox div {background-color:<?=BORDER_COLOR_2?>; border:1px solid <?=TEXT_COLOR_2?>; color:<?=TEXT_COLOR_2?>; margin:0 -3px; padding:2px}
#footer {background-color:<?=BG_COLOR?>; padding:10px;}
#header {background:url(images/bg_expo.jpg) repeat-x; height:61px; padding:20px 10px;}

.menu {background-color:<?=LINK_COLOR?>; height:20px;padding:3px 0 1px 0}
.menu a:link, .menu a:visited {background-color:<?=LINK_COLOR?>; border-right:#FFF solid 1px; color:#fff; font-size:10pt; font-weight:bold; letter-spacing:0; padding:2px 5px; width:125px;}
