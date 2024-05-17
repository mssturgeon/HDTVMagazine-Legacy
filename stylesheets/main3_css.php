<?
	include_once('../includes/constants.php');
	header('Content-Type: text/css');
?>

html .email_share_link {padding:2px 0 0 25px; height:16px; background:url(<?=BASE_IMG_HOST?>/images/email.gif) no-repeat top left;}
html .fb_share_link {padding:2px 0 0 20px; height:16px; background:url(http://b.static.ak.fbcdn.net/images/share/facebook_share_icon.gif?8:26981) no-repeat top left;}
html .rss_share_link {padding:2px 0 0 20px; height:16px; background:url(<?=BASE_IMG_HOST?>/images/livemark.png) no-repeat top left;}
html .twitter_share_link {padding:2px 0 0 20px; height:16px; background:url(<?=BASE_IMG_HOST?>/images/twitter.png) no-repeat top left;}

/*** NEW STUFF ***/
h2 {color:#<?=SECONDARY_COLOR?>; font:1.5em Arial, sans serif; font-weight:bold; margin:0; padding:0}
h2 a:link {color:#<?=SECONDARY_COLOR?>;}
h2 a:visited {color:#<?=SECONDARY_COLOR?>;}
h2 a:hover {color:#<?=SECONDARY_COLOR?>;}
h2 a:active {color:#<?=SECONDARY_COLOR?>;}

/* no relative positioning due to IE6 */
#container {margin:0 auto; padding:0 50px; min-width:740px;}

#body_container h1 {background:url(<?=BASE_IMG_HOST?>/images/bg_h1.gif) repeat; border-top:3px solid #<?=PRIMARY_COLOR?>; border-bottom:1px solid #<?=PRIMARY_COLOR?>; font-size:2em; font-weight:bold; padding:3px;}

.anchors {font-size:1.2em; font-weight:bold; margin-bottom:10px;}
.anchors a:link {color:#<?=SECONDARY_COLOR?>}
.anchors a:visited {color:#<?=SECONDARY_COLOR?>}
.anchors a:hover {color:#<?=SECONDARY_COLOR?>}
.anchors a:active {color:#<?=SECONDARY_COLOR?>}

#article_container {font:1.1em/1.2em verdana, sans-serif; margin:5px 0}
#article_container h1 {background:url(<?=BASE_IMG_HOST?>/images/bg_h1.gif) repeat; border-top:3px solid #<?=PRIMARY_COLOR?>; border-bottom:1px solid #<?=PRIMARY_COLOR?>; font-size:2em; font-weight:bold; padding:3px;}
#article_container h2 {margin-top:1.5em;}
#article_container ul li {list-style-image:url(<?=BASE_IMG_HOST?>/images/square-brown_5.gif); margin-left:-20px; padding:.25em 0}
#article_container blockquote {border:1px dotted #ccc; border-width:1px 0; color:#666; margin:.75em 0; padding:5px 15px;}
#article_container img {float:left; padding: 0 5px 5px 0}
/*
#article_container blockquote {background-color:#eee; border-left:1px solid #666; color:#666; display:block; padding:0 60px; width:400px}
#article_container blockquote:before, #article_container blockquote:after {color:#<?=PRIMARY_COLOR?>; display:block; font-family:"Times New Roman", Times, serif; font-size:700%; width:50px;}
#article_container blockquote:before {content:open-quote; height:0; padding-top:30px; margin-left:-0.55em;}
#article_container blockquote:after {content:close-quote; height:20px; margin-top:-15px; margin-left:410px;}
#article_container blockquote > p {margin:-20px 20px 0 0;}
*/

/* Used in: profile-stations */
.autocomplete {background-color:white; border:1px solid #<?=BORDER_COLOR?>; margin:10px; overflow:auto; padding:0; position:absolute; width:25em; height:200px; z-index:1;}
.autocomplete ul {list-style-type:none; margin:0; padding:0;}
.autocomplete ul li {list-style-type:none; display:block; margin:0; padding:2px; cursor:pointer;}
.autocomplete ul li.selected {border-width:0; background-color:#CEDFEF; margin:0; padding:2px}

#bulletin_container {font:1.1em/1.2em verdana, sans-serif; margin:5px 0}
#bulletin_container li {list-style-image:url(<?=BASE_IMG_HOST?>/images/square-brown_5.gif); margin-left:-20px; padding:.25em 0}

.button_green {background-color:#339900; color:white; font-size:.9em; margin-left:5px; padding:0}
.button_red {background-color:#cc0000; color:white; font-size:.9em; margin-left:5px; padding:0}
.button_blue {background-color:#336699; color:white; font-size:.9em; margin-left:5px; padding:0}

/* FEATURE - Something we want to feature, current displayed in the "brown" scheme */
.feature {background-color:#CEDFEF; padding:0 10px; width:280px;}
.feature span.corners-top, .feature span.corners-bottom {margin:0 -10px;}

/* AD - Only used for ad backgrounds, currently displayed in a "light grey" scheme */
.ad {background-color:#F5F7FA; font-size:1em; margin:5px 0; padding:0 10px;}
.ad span.corners-top, .ad span.corners-bottom {margin:0 -10px;}

/* ITEM - Used for displaying other boxed items, currently displayed in the "light blue" scheme */
.item {background-color:#CEDFEF; margin-bottom:10px; padding:0 10px;}
.item > .label {color:#<?=PRIMARY_COLOR?>; float:left; font-weight:bold; padding-right:5px}
.item > .label a {color:#<?=PRIMARY_COLOR?>}
.item li {border-bottom:1px solid white; list-style-image:url(<?=BASE_IMG_HOST?>/images/square-brown_5.gif); margin-left:-20px; padding:.25em 0}
.item li a{text-decoration:none;}
.item li a:hover{text-decoration:underline}
.item span.corners-top, .item span.corners-bottom {margin:0 -10px;}

div.local_info {height:140px; overflow-y:auto;}
div.local_info li {float:left; width:10.2em;}

/* ITEM_ODD - Used for displaying other boxed items, currently displayed in the "light light blue" scheme */
.item_odd {background-color:#ecf3f7; margin-bottom:10px; padding:0 10px;}
.item_odd li {border-bottom:1px solid white;}
.item_odd li a{text-decoration:none;}
.item_odd li a:hover{text-decoration:underline}
.item_odd span.corners-top, .item_odd span.corners-bottom {margin:0 -10px;}

/* IMPORTANT - Something we want to stand out, currently displayed in the "red" scheme */
.important {background-color:#ECD5D8; margin:5px 0; padding:0 10px;}
.important a:link {text-decoration:underline}
.important a:visited {text-decoration:underline}
.important .label {color:#800000; float:left; font-weight:bold; padding-right:5px}
.important .label a {color:#800000}
.important span.corners-top, .important span.corners-bottom {margin:0 -10px;}

#admin_information {margin-top:20px;}
#admin-information label {display:block; float:left; margin-right:2px; text-align:right; width:15em}
#admin-information br {clear:left}
#network-information label {display:block; float:left; margin-right:2px; text-align:right; width:10em}
#network-information br {clear:left}
#program-information label {display:block; float:left; margin-right:2px; text-align:right; width:10em}
#program-information br {clear:left}

.story {border-bottom:1px dashed #AAAAAA; margin:.5em 0; padding:.5em 0;}
.story h2 {color:#<?=PRIMARY_COLOR?>; font:2em Times New Roman, serif; font-weight:bold; margin:0;}
.story h2 > a {text-decoration:none;}
.story h2 > a:hover {text-decoration:underline;}
.story h3 {color:#<?=PRIMARY_COLOR?>; font:1em Times New Roman, serif; font-weight:bold; margin:0;}
.story h3 > a {text-decoration:none;}
.story h3 > a:hover {text-decoration:underline;}
.story h4 {color:#AAAAAA; font:.8em Arial, sans serif; margin:0; text-transform:uppercase;}
.story .footer {margin-top:5px; text-align:right}

.entry {margin:0; padding:0 10px;}
.entry h2 {color:#<?=PRIMARY_COLOR?>; font:2em Times New Roman, serif; font-weight:bold; margin:0;}
.entry h2 > a:link {color:#<?=PRIMARY_COLOR?>; text-decoration:none;}
.entry h2 > a:visited {color:#<?=PRIMARY_COLOR?>; text-decoration:none;}
.entry h2 > a:hover {color:#<?=PRIMARY_COLOR?>; text-decoration:underline;}
.entry h2 > a:active {color:#<?=PRIMARY_COLOR?>; text-decoration:underline;}
.entry h3 {color:#<?=PRIMARY_COLOR?>; font:1em Arial, sans serif; font-weight:bold; margin:0;}
.entry h3 > a {text-decoration:none;}
.entry h3 > a:hover {text-decoration:underline;}
.entry h4 {color:#AAAAAA; font:.8em Arial, sans serif; margin:0; text-transform:uppercase;}
.entry .footer {margin-top:5px; text-align:right}
.entry div.entry_text {margin-top:10px}
.entry span.corners-top, .entry span.corners-bottom {margin:0 -10px;}

.dottedline {border-bottom:1px dashed #AAAAAA; margin:5px 0;}

.product {margin:0; padding:0 10px;}
.product h2 {color:#<?=PRIMARY_COLOR?>; font:1.5em Times New Roman, serif; margin:0;}
.product h2 > a:link {color:#<?=PRIMARY_COLOR?>;}
.product h2 > a:visited {color:#<?=PRIMARY_COLOR?>;}
.product h2 > a:hover {color:#<?=PRIMARY_COLOR?>;}
.product h2 > a:active {color:#<?=PRIMARY_COLOR?>;}
.product h4 {color:#606060; font-weight:normal; margin:0; padding-bottom:5px}
.product .image {width:100px;}
.product .price {font-size:1.1em; font-weight:bold; text-align:right; vertical-align:top; width:100px}
.product .features {}
.product .rating {color:#606060; margin-top:5px}
.product span.corners-top, .product span.corners-bottom {margin:0 -10px;}

.newsticker {color:black; font-weight:normal; filter:progid:DXImageTransform.Microsoft.alpha(opacity=90); -moz-opacity: 0.99; overflow:hidden; white-space:nowrap}

#sortable_container {position:relative}
#sortable_updating {background:rgba(255,255,255,0.5); position:absolute; top:0; left:0; width:100%; height:100%; z-index:1}
#sortable_updating > div {font-size:14pt; font-weight:bold; margin-top:45%}
#sortable_loading {background:rgba(255,255,255,0.5); position:absolute; top:0; left:0; width:100%; height:100%; z-index:1}
#sortable_loading > div {font-size:14pt; font-weight:bold; margin-top:45%}
.updating {background:rgba(255,255,255,0.5); position:absolute; top:0; left:0; width:100%; height:100%; z-index:1}
.updating > div {font-size:14pt; font-weight:bold; margin-top:45%}

.story_container {border-bottom:1px dashed #AAAAAA; margin:.5em 0; padding-bottom:1em;}
.story_container > h1 {font:2em Times New Roman, serif; font-weight:bold; margin:0;}
.story_container > h1 > a {color:#<?=PRIMARY_COLOR?>; text-decoration:none;}
.story_container > h1 > a:hover {text-decoration:underline;}
.story_container > h3 {color:#AAAAAA; font:.8em Arial, sans serif; margin:0; text-transform:uppercase;}
.story_container > h3 a {text-decoration:underline}

#search_box {background:#FFF url(<?=BASE_IMG_HOST?>/images/i_search.gif) no-repeat right; border:0; color:#777777; width:8em;}

#tab-container {display:table; width:600px;}
#tabs {float:left; width:100%; font-size:93%; line-height:normal;}
#tabs ul {margin:0; padding:10px 10px 0 0px; list-style:none;}
#tabs li {display:inline; margin:0; padding:0;}
#tabs a {float:left; background:url(<?=BASE_IMG_HOST?>/images/tableft.gif) no-repeat left top; margin:0; padding:0 0 0 5px; text-decoration:none;}
#tabs a span {float:left; display:block; background:url(<?=BASE_IMG_HOST?>/images/tabright.gif) no-repeat right top; padding:5px 15px 4px 6px; color:#24618E;}
/* Commented Backslash Hack hides rule from IE5-Mac \*/
#tabs a span {float:none;}
/* End IE5-Mac hack */
#tabs a:hover {background-position:0% -42px;}
#tabs a:hover span {background-position:100% -42px; color:#FFF;}
#tabs .tab-selected {background-position:0% -42px;}
#tabs .tab-selected span {background-position:100% -42px; color:#FFF; font-weight:bold}
.tab-content {border:1px solid #24618E; clear:both; display:table; padding:5px; width:100%}

#eq_br {clear:both; display:table; padding-top:10px; width:100%}
#eq-image {background-color:#FFF; float:left; height:200px; padding-right:10px; vertical-align:middle}
#eq-layout {padding-right:10px; vertical-align:top; width:100%}
#eq-mrec {background-color:#FFF; float:right; padding-left:10px;}
#eq-sky {float:right; padding:10px 0 0 10px}
#eq-title {font-size:1.5em}
#eq-pricing {color:#808080; font-size:.9em}
#eq-pricing b {color:#800000}

/*** ELEMENT SELECTORS ***/
a:link {color:#<?=LINK_COLOR?>;}
a:visited {color:#<?=LINK_COLOR?>;}
a:hover {color:#<?=LINK_COLOR?>;}
a:active {color:#<?=SECONDARY_COLOR?>;}

abbr, acronym, .help {border-bottom:1px dotted #333; cursor:help;}

body {background:#fff; color:#<?=TEXT_COLOR?>; margin:10px 0; padding:0;}

/* redundant rules for bad browsers  */
body, td, th, h3, textarea {font-family:Arial,sans-serif; font-size:9pt; voice-family:"\"}\""; voice-family:inherit;}

fieldset {border:1px solid #<?=BORDER_COLOR?>; margin:5px 5px 15px 5px; padding:5px; text-align:left;}
fieldset > br {line-height:75%}
form {margin:0;}
/*
h1 {background:url(<?=BASE_IMG_HOST?>/images/bg_h1.gif) repeat; border-top:3px solid #<?=PRIMARY_COLOR?>; border-bottom:1px solid #<?=PRIMARY_COLOR?>; font-size:14pt; font-weight:bold; padding:3px;}
h2 {color:#<?=TERTIARY_COLOR?>; margin:0; padding:0}
*/
h1 {font:2em Times New Roman, serif; font-weight:bold; margin-bottom:5px;}
h3 > a{text-decoration:none}
h3 > a:hover{text-decoration:underline}
h3 > img{padding-right:.3em;}

img {border:0;padding:0;margin:0}
input[type="text"] {border:1px solid #<?=BORDER_COLOR?>; margin:1px 0 1px 3px; padding-left:2px}
input[type="password"] {border:1px solid #<?=BORDER_COLOR?>; margin:1px 0 1px 3px; padding-left:2px}
label {display:block; font-weight:bold; margin-left:3px}
label > input[disabled="disabled"] {color:#CAC8BB;}
legend {color:#<?=PRIMARY_COLOR?>; font-weight:bold;}
ul.brownsquare > li {list-style-image:url(<?=BASE_IMG_HOST?>/images/square-brown_5.gif); margin-left:-20px; padding:.25em 0}
ul.nosquare > li {list-style-type:none; margin:0 0 4px -20px; padding:0}
select {margin-left:3px}
textarea {border:1px solid #<?=BORDER_COLOR?>; padding-left:3px; width:100%;}


/*** CLASS & ID SELECTORS ***/
/*
#body_container {}
#body_container > h1 {background:url(<?=BASE_IMG_HOST?>/images/bg_h1.gif) repeat; border-top:3px solid #<?=PRIMARY_COLOR?>; border-bottom:1px solid #<?=PRIMARY_COLOR?>; font-size:14pt; font-weight:bold; padding:3px;}
#body_container h2 {color:#<?=SECONDARY_COLOR?>; font:1.5em Arial, sans serif; font-weight:bold;}
#body_container h2 a {color:#<?=SECONDARY_COLOR?>;}
#body_container h3 {border:none; font-size:1.25em; line-height:125%; margin:1em 0 0 0; padding:2px 0 0 0; text-transform:none;}
#body_container h4 {font-size:1em}
*/

#ad_leaderboard {margin:5px 0;}
#ad_leaderboard div {background-color:#F5F7FA; padding:0 10px; width:728px}
#ad_leaderboard span.corners-top, #ad_leaderboard span.corners-bottom {margin:0 -10px;}

#ad_links {margin:.5em;}

.ad_mrectangle {color:#<?=BORDER_COLOR?>; font-size:.8em; letter-spacing:.3em; margin:0; padding:0; text-align:center; width:300px;}

#ad_skyscraper {margin-bottom:10px;}
#ad_skyscraper div {background-color:#F5F7FA; padding:0 10px; width:160px}
#ad_skyscraper span.corners-top, #ad_skyscraper span.corners-bottom {margin:0 -10px;}

.alertbox {background-color:#F6EBAD; border:1px solid #D5CA8B; color:#<?=TERTIARY_COLOR?>; margin:10px; padding:5px; text-align:center; width:500px;}
.alertbox a:link {color:#<?=TERTIARY_COLOR?>;}
.alertbox a:visited {color:#<?=TERTIARY_COLOR?>;}
.alertbox a:hover {color:#<?=TERTIARY_COLOR?>;}
.alertbox a:active {color:#<?=TERTIARY_COLOR?>;}
.alertbox b {color:#<?=TERTIARY_COLOR?>}
/*.alert_green div {color:#008000; border:1px solid #408040; background-color:#EEFFEE; font-weight:bold; margin:5px 0; padding:7px; width:6.5in;}*/
.alert_green {border:1px solid #408040; background-color:#EEFFEE}
.alert_green td {color:#008000; font-weight:bold; margin:5px 0; padding:7px;}
.alert_red div {color:#800000; border:1px solid #804040; background-color:#FFEEEE; font-weight:bold; margin:5px 0; padding:7px; width:6.5in;}

.article_title {background:url(<?=BASE_IMG_HOST?>/images/bg_h1.gif) repeat; border:1px solid #<?=BORDER_COLOR?>; color:#<?=PRIMARY_COLOR?>; font:bold 14pt arial,sans-serif; padding:7px; vertical-align:top; width:100%;}

table.bare {border:0; border-collapse:collapse; padding:0;}

p.big-first:first-letter {font-size: 200%; float: left;}

.block_small {background-color:#<?=BG_COLOR?>; border:1px solid #<?=BORDER_COLOR?>; float:left; height:80px; margin:3px; padding:2px; width:30%;}
.block_small img {padding-right:2px}

.booksnavbar {background-color:#<?=BG_COLOR?>; border:1px solid #<?=BORDER_COLOR?>; padding:3px; text-align:center;}

.brown {color:#<?=SECONDARY_COLOR?>}

table.btn {float:left; padding-right:3px;}
td.btn-left {background:url(<?=BASE_IMG_HOST?>/images/btn-left.gif) 0 0 no-repeat; padding:0}
td.btn-back {background:url(<?=BASE_IMG_HOST?>/images/btn-back.gif) repeat-x; color:white; font-weight:bold; padding:5px; height:36px;}
td.btn-back a:link {color:white;}
td.btn-back a:visited {color:white;}
td.btn-back a:hover {color:white;}
td.btn-back a:active {color:white;}
td.btn-right {background:url(<?=BASE_IMG_HOST?>/images/btn-right.gif) no-repeat;}

.buttonBar {border:1px dashed #<?=BORDER_COLOR?>; padding:5px 0; text-align:center;}

table.calendar {border:1px solid #<?=BORDER_COLOR?>; border-collapse:collapse;}
td.calendar {border:1px solid #<?=BORDER_COLOR?>; background-color:#<?=BG_COLOR?>; width:50px; text-align:center;}
td.calendar_header {border:1px solid #<?=BORDER_COLOR?>; text-align:center;}

.categories {background-color:#CEDFEF; font-weight:bold; padding:2px; width:600px;}
.categories a {font-weight:normal;}

span.caption {float:left; margin:15px; text-align:center;}
span.caption img {border:1px solid black;}

#container-footer {margin:5px 0; clear:both}
#container-footer div {background-color:#F5F7FA; padding:0 10px;}
#container-footer span.corners-top, #container-footer span.corners-bottom {margin:0 -10px;}

.content .caption {text-align:center;}
.content img {padding:7px;}
.content li {line-height:150%;}
.content pre {font-size:larger;}

span.corners-top, span.corners-bottom, span.corners-top span, span.corners-bottom span {font-size: 1px; line-height: 1px; display: block; height: 5px; background-repeat: no-repeat;}
span.corners-top {background-image: none; background-position: 0 0; margin: 0 -5px;}
span.corners-top span {background-image: none;	background-position: 100% 0;}
span.corners-bottom {background-image: none; background-position: 0 100%; margin: 0 -5px; clear: both;}
span.corners-bottom span {background-image: none; background-position: 100% 100%;}
span.corners-top {background-image: url(<?=BASE_IMG_HOST?>/forum/templates/HDTVMagazine4/theme/images/corners_left.png);}
span.corners-top span {background-image: url(<?=BASE_IMG_HOST?>/forum/templates/HDTVMagazine4/theme/images/corners_right.png);}
span.corners-bottom {background-image: url(<?=BASE_IMG_HOST?>/forum/templates/HDTVMagazine4/theme/images/corners_left.png);}
span.corners-bottom span {background-image: url(<?=BASE_IMG_HOST?>/forum/templates/HDTVMagazine4/theme/images/corners_right.png);}

.date {clear:left; padding:3px; text-align:right; width:100px;}
td.dayamount_normal {padding:8px 1px; text-align:center;}
td.dayamount_today {background-color:#<?=BORDER_COLOR?>; padding:8px 1px; text-align:center;}
td.daytitle {font-size:6pt; color:white; background-color:black; padding:1px; text-align:right;}

.diggbox {padding:0 5px 5px 0;}

/*
.entry {padding:3px;}
*/
.editorial {background-color:#<?=BG_COLOR?>; border:1px dotted black; border-left:3px solid #<?=PRIMARY_COLOR?>; clear:left; font-size:12px; padding:3px 5px; display:table}
.entry_date {float:left; padding:3px; text-align:right; width:100px;}
.featured {background-color:#F6EBAD; color:#<?=TERTIARY_COLOR?>; padding:3px;}
.featured a {margin-left:10px}
.grey {color:#808080}
.grey_italic {color:#808080; font-style:italic}
table.greygrid {border-collapse:collapse;}
td.greygrid {background-color:#<?=BG_COLOR?>; border:2px solid white; padding:3px; text-align:center}
td.grid {border:1px solid #<?=BORDER_COLOR?>; padding:2px 4px;}
td.grid_bold_shade {background-color:#<?=BG_COLOR?>; border:1px solid #<?=BORDER_COLOR?>; font-weight:bold; padding:2px 4px;}

p.header {color:#<?=PRIMARY_COLOR?>; font:bold 12pt Arial,sans-serif;}

.infobox {background-color:#F6EBAD; border:1px solid #D5CA8B; float:right; padding:2px; width:300px}
.inputButton {background:url(<?=BASE_IMG_HOST?>/images/bg-button.gif) repeat-x; border:1px solid #<?=BORDER_COLOR?>; cursor:pointer; font:8pt Arial,sans-serif; height:21px;}
.inputLabel {font-weight:bold; padding:3px; text-align:right;}
.inputTextDisabled {background-color:#<?=BG_COLOR?>; border:1px solid #<?=BORDER_COLOR?>; font-weight:normal; height:18px; padding-left:3px;}

.livefeed {font-weight:bold; margin:3px; text-align:right; vertical-align:middle; width:100%}

/* .newsblock {background-color:#<?=BG_COLOR?>; border:1px solid #<?=BORDER_COLOR?>; float:left; height:200px; margin:3px; overflow:auto; padding:5px; width:235px;}*/
.newsitem {color:#333333; line-height:1.48em; margin:1em 0 0 0; padding:0;}
.newsitem > h4 {color:#606060; font-weight:bold; margin:0}
/*.newsnet {background-color:#EFF3F7; padding:0 10px; width:280px;}*/

.posted {background-color:#<?=BG_COLOR?>; border-top:1px solid #<?=BORDER_COLOR?>; clear:both; color:#<?=TEXT_COLOR?>; margin-bottom:10px; padding:3px;}
.primary_bold {font-weight:bold; color:#<?=LINK_COLOR?>}
.primary_bold_10 {font-size:10pt; font-weight:bold; color:#<?=PRIMARY_COLOR?>}
.primary_bold_12 {font-size:12pt; font-weight:bold; color:#<?=PRIMARY_COLOR?>}
.primary_bold_14 {font-size:14pt; font-weight:bold; color:#<?=PRIMARY_COLOR?>}
.premium_only {color:#CAC8BB;}
.prtitle {text-align:center; font-weight:bold; font-size:12pt}
.keyimg {float:left; padding: 0 5px 5px 0;}

a.red {color:#AA0000; font-weight:bold;}

.evenRow {background-color:#<?=BG_COLOR?>; padding:3px;}
.oddRow {padding:3px;}

.scroll {overflow:auto; padding:2px; border:1px solid #<?=BORDER_COLOR?>}
.section_heading {font:14pt bold verdana,arial,sans-serif; color:#<?=PRIMARY_COLOR?>;}
.selected {background-color:#<?=BG_COLOR?>; border:1px solid #<?=BORDER_COLOR?>; padding:2px 5px 2px 2px;}

.shadebox {background-color:#<?=BG_COLOR?>; border:1px solid #<?=BORDER_COLOR?>; margin-bottom:7px; padding:3px; vertical-align:middle;}
.shade_border {background-color:#<?=BG_COLOR?>; border:1px solid #<?=BORDER_COLOR?>; padding:3px; margin:3px;}
.shade_border_bold {background-color:#<?=BG_COLOR?>; border:1px solid #<?=BORDER_COLOR?>; color:#<?=PRIMARY_COLOR?>; font-weight:bold; padding:3px; margin:3px;}

.sidebar {background-color:#F6EBAD; border:1px solid #D5CA8B; padding:3px;}
.sidebar li {color:#<?=TEXT_COLOR?>; margin-top:5px; line-height:150%; list-style-type:none; text-align:left;}
/*.sidebar ul {margin:0; margin-bottom:15px; padding-left:0;}*/
.sidebar ul ul {margin-bottom: 0px;}
/*.sidebar #categories ul {padding-left:15px;}*/
.sidebar #categories li {list-style-type:disc;}
.sidebar h2 {background-color:transparent; border:0; border-bottom:1px dotted #<?=BORDER_COLOR?>; color:#<?=PRIMARY_COLOR?>; font:bold 8pt Arial,sans-serif; letter-spacing:.2em; margin:0; padding-bottom:3px; text-transform:uppercase;}

td.space_header {background-color:#AAAAAA; color:white; font-weight:bold; padding:2px; text-align:center}

/*** Tabbed table styles  ***/
table.tabTable {border:0; width:6.5in;}
table.tabHeader {border-collapse:collapse; width:100%;}
table.tabContent {border:1px solid #<?=BORDER_COLOR?>; border-collapse:collapse; border-top:0; width:100%;}
td.tabContent {border:1px solid #<?=BORDER_COLOR?>; border-top:0; padding:15px 5px 5px 5px;}
td.activeTab {background:url(<?=BASE_IMG_HOST?>/images/bg-activetab.jpg) repeat-x; border:1px solid #<?=BORDER_COLOR?>; border-bottom:0px; font-weight:bold; padding: 5px 10px; text-align:center;}
td.inactiveTab {background:url(<?=BASE_IMG_HOST?>/images/bg-inactivetab.gif); border:1px solid #<?=BORDER_COLOR?>; padding:5px 10px; text-align:center;}
td.tabActive {background:url(<?=BASE_IMG_HOST?>/images/bg-activetab.jpg) repeat-x; border:1px solid #<?=BORDER_COLOR?>; border-bottom:0px; font-weight:bold; padding: 5px 10px; text-align:center;}
td.tabInactive {background:url(<?=BASE_IMG_HOST?>/images/bg-inactivetab.gif); border:1px solid #<?=BORDER_COLOR?>; padding:5px 10px; text-align:center;}

table.type1b {border:1px solid #<?=BORDER_COLOR?>; border-collapse:collapse;}
.type1b_header {background-color:#<?=BG_COLOR?>; border:1px solid #<?=BORDER_COLOR?>; color:#<?=LINK_COLOR?>; font-weight:bold; padding:2px 3px; vertical-align:top;}
td.type1b {border-bottom:1px solid #<?=BORDER_COLOR?>; padding:2px 4px;}

/* Used in: hdtvs-by-manufacturer, manufacturer
.simple {border:1px solid #<?=BORDER_COLOR?>; border-collapse:collapse}
.simple td {border:1px solid #<?=BORDER_COLOR?>; padding:2px; vertical-align:middle}
.simple .header td {background-color:#<?=BG_COLOR?>; color:#<?=LINK_COLOR?>; font-weight:bold; padding:2px 3px; vertical-align:top;}

.type2 {background-color:#<?=BG_COLOR?>; border-bottom:1px solid #<?=BORDER_COLOR?>; margin-bottom:1em; width:100%;}

/*** ID SELECTORS ***/
#ad-right {padding-left:10px; text-align:right; vertical-align:top; width:180px;}
#ad-left {padding-right:10px; text-align:left; vertical-align:top; width:180px;}
#article_headshot {}
#article_title {background:url(<?=BASE_IMG_HOST?>/images/bg_h1.gif) repeat; border:1px solid #<?=BORDER_COLOR?>; color:#<?=PRIMARY_COLOR?>; font:bold 14pt arial,sans-serif; padding:7px; vertical-align:top; width:100%;}
#article_byline {color:#666666; float:left; padding:3px; width:300px;}
#article_links {text-align:right; width:100%;}
#article_links img {padding:0 2px 0 5px;}

#card {border:1px solid black;}

#center {overflow:hidden;}
#left {padding-right:10px; vertical-align:top}
#right {border-left:1px solid #AAAAAA; float:right; padding-left:10px; vertical-align:top; width:310px;}

#content {min-height:400px; width:100%;}
#email_preference {border-top:1px solid #<?=BORDER_COLOR?>;vertical-align:middle;padding:4px}
#headertext {float:right; text-align:right; vertical-align:top; width:480px}

#listing {border:1px solid #<?=BORDER_COLOR?>; margin-bottom:5px; width:100%;}
#map {height:550px;border:1px solid #<?=BORDER_COLOR?>}

#menu {background:#<?=PRIMARY_COLOR?> url(<?=BASE_IMG_HOST?>/images/menu-back.png) no-repeat 0 0; border:0; clear:both; height:40px; margin:0.1em 0; padding-left:10px;}
#menu div {background:url(<?=BASE_IMG_HOST?>/images/menu-back.png) no-repeat 100% 0; height:36px; overflow:hidden; padding-top:4px;}
table.menuItem {border:0; border-collapse:collapse; padding:0; margin:0; width:100%;}
.menuItem {border-right:1px solid #<?=BORDER_COLOR?>; font:bold 9pt Arial,sans-serif; height:32px; text-align:center; vertical-align:middle;}
.menuItem a:link {color:#FFF; text-decoration:underline;}
.menuItem a:visited {color:#FFF; text-decoration:underline;}
.menuItem a:hover {color:#FFF; text-decoration:underline;}
.menuItem a:active {color:#FFF; text-decoration:underline;}

.networkLogo {border:1px solid #<?=BORDER_COLOR?>; padding:2px; margin:0 3px 3px 0; float:left}