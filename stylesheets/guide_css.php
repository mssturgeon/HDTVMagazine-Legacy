<?
	include_once('../includes/constants.php');
	header('Content-Type: text/css');

	# DO NOT EDIT >>> USE css/main.css FROM NOW ON
?>
/**********
BASE_IMG_HOST: <?=BASE_IMG_HOST?>;
BG_COLOR: <?=BG_COLOR?>;
BORDER_COLOR: <?=BORDER_COLOR?>;
LINK_COLOR: <?=LINK_COLOR?>;
PRIMARY_COLOR: <?=PRIMARY_COLOR?>;
PROGRAMHD_COLOR: <?=PROGRAMHD_COLOR?>;
PROGRAMSD_COLOR: <?=PROGRAMSD_COLOR?>;
SECONDARY_COLOR: <?=SECONDARY_COLOR?>;
TERTIARY_COLOR: <?=TERTIARY_COLOR?>;
TEXT_COLOR: <?=TEXT_COLOR?>;
**********/

/* Main Guide Page Stylesheet */
.pHD a:link {color:white}
.pHD a:visited {color:white}
.pHD a:hover {color:white}
.pHD a:active {color:white}

.pSD a:link {color:black}
.pSD a:visited {color:black}
.pSD a:hover {color:black}
.pSD a:active {color:black}

.programHighlight a:link {color:#73681F}
.programHighlight a:visited {color:#73681F}
.programHighlight a:hover {color:#73681F}
.programHighlight a:active {color:#73681F}

/*** These classes control display of the stations and times on the grid axes ***/
.timeLabel {background-color:#<?=BG_COLOR?>; border:1px solid #<?=BORDER_COLOR?>; font:normal 8pt arial,sans-serif; padding-left:2px; vertical-align:middle;}
.dateLabel {background-color:#<?=BG_COLOR?>; border:1px solid #<?=BORDER_COLOR?>; padding-left:2px; width:75px;}
.sLabel {background-color:#<?=BG_COLOR?>; border:1px solid #<?=BORDER_COLOR?>; width:190px;}
.sImage {padding:0; text-align:center; width:20px;}
.sImage img {height:15px;width:20px;}
.sChannel {font:normal 8pt arial,sans-serif; padding:0 1px; text-align:right; width:40px;}
div.grid {border:0; font:normal 8pt arial,sans-serif; margin:1px; overflow:hidden; white-space:nowrap; width:100%;}

/*** These classes control display of the actual grid itself ***/
table.grid {border:1px solid #<?=BORDER_COLOR?>; border-collapse:collapse; font:normal 8pt arial,sans-serif; overflow:hidden; table-layout:fixed;}
.pHD {background-color:#<?=PROGRAMHD_COLOR?>; border:1px solid #<?=BORDER_COLOR?>; color:white; cursor:default; font:normal 8pt arial,sans-serif; overflow:hidden; vertical-align:middle; white-space:nowrap;}
.pSD {background-color:#<?=PROGRAMSD_COLOR?>; border:1px solid #<?=BORDER_COLOR?>; color:black; cursor:default; font:normal 8pt arial,sans-serif; overflow:hidden; vertical-align:middle; white-space:nowrap;}
.programHighlight {background-color:#F6EBAD; border:1px solid #D5CA8B; color:#73681F; cursor:default; font:normal 8pt arial,sans-serif; overflow:hidden; vertical-align:middle; white-space:nowrap;}
.pDD {background:url(<?=BASE_IMG_HOST?>/images/logos/dolby-digital_20.png) no-repeat bottom right; bottom:5px; right:5px; float:right; height:20px; position:absolute; width:53px;}
.pEpisode {font-weight:bold; margin-bottom:.5em}
.pInfo img {margin:0; padding:0; position:relative}
.pLive {color:red; font-weight:bold}
.pNew {color:red; font-weight:bold}
.pPrototip {display:none;}

.rating-mpaa-G {background:url(<?=BASE_IMG_HOST?>/images/ratings/mpaa-g_20.png) no-repeat top right; top:5px; right:5px; float:right; height:20px; position:absolute; width:16px;}
.rating-mpaa-NC17 {background:url(<?=BASE_IMG_HOST?>/images/ratings/mpaa-nc17_20.png) no-repeat top right; top:5px; right:5px; float:right; height:20px; position:absolute; width:69px;}
.rating-mpaa-PG {background:url(<?=BASE_IMG_HOST?>/images/ratings/mpaa-pg_20.png) no-repeat top right; top:5px; right:5px; float:right; height:20px; position:absolute; width:29px;}
.rating-mpaa-PG13 {background:url(<?=BASE_IMG_HOST?>/images/ratings/mpaa-pg13_20.png) no-repeat top right; top:5px; right:5px; float:right; height:20px; position:absolute; width:51px;}
.rating-mpaa-R {background:url(<?=BASE_IMG_HOST?>/images/ratings/mpaa-r_20.png) no-repeat top right; top:5px; right:5px; float:right; height:20px; position:absolute; width:24px;}

.rating-ustv-TV14 {background:url(<?=BASE_IMG_HOST?>/images/ratings/ustv-tv14_20.png) no-repeat top right; top:5px; right:5px; float:right; height:20px; position:absolute; width:21px;}
.rating-ustv-TVG {background:url(<?=BASE_IMG_HOST?>/images/ratings/ustv-tvg_20.png) no-repeat top right; top:5px; right:5px; float:right; height:20px; position:absolute; width:21px;}
.rating-ustv-TVMA {background:url(<?=BASE_IMG_HOST?>/images/ratings/ustv-tvma_20.png) no-repeat top right; top:5px; right:5px; float:right; height:20px; position:absolute; width:21px;}
.rating-ustv-TVPG {background:url(<?=BASE_IMG_HOST?>/images/ratings/ustv-tvpg_20.png) no-repeat top right; top:5px; right:5px; float:right; height:20px; position:absolute; width:21px;}
.rating-ustv-TVY {background:url(<?=BASE_IMG_HOST?>/images/ratings/ustv-tvy_20.png) no-repeat top right; top:5px; right:5px; float:right; height:20px; position:absolute; width:21px;}
.rating-ustv-TVY7 {background:url(<?=BASE_IMG_HOST?>/images/ratings/ustv-tvy7_20.png) no-repeat top right; top:5px; right:5px; float:right; height:20px; position:absolute; width:21px;}

/* img classes */
img.dd {height:7px;vertical-align:middle;width:13px}
img.cc {height:10px;vertical-align:middle;width:14px}
img.new {height:7px;vertical-align:middle;width:7px}
img.lb {height:9px;vertical-align:middle;width:12px}

.blank {border:1px solid #<?=BORDER_COLOR?>; font:normal 8pt arial,sans-serif; padding:0;}
span.legend {background-color:#<?=BG_COLOR?>; border-left:1px solid #<?=BORDER_COLOR?>; border-right:1px solid #<?=BORDER_COLOR?>; font:normal 8pt arial,sans-serif; padding:2px;}