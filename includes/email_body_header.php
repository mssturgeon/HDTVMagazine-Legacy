<?
	if ($online_url == '') {
		$online_url = $_SERVER['SCRIPT_URI'];
	}
?>
<div style="float:left">
	<a href="<?=BASE_URL?>/"><img src="<?=BASE_URL?>/images/hdtvmagazine.gif<?=$tracking?>" alt="HDTV Magazine" /></a>
	<!--a href="<?=$base_url?>/"><img src="<?=$base_url?>/images/hdtvmagazine-holiday_338x58.gif<?=$tracking?>" alt="HDTV Magazine" /></a-->
</div>
<div style="float:right;">
	<div id="viewonline">[ <a href="<?=$online_url?>">View This Email Online</a> ]</div>
	<div id="search">
		<form name="google_search" method="get" action="<?=$base_url?>/search.php" target="_top">
			<b>Search HDTV Magazine:</b>
			<input type="text" class="inputText" name="q" maxlength="255" value="" />
			<input type="hidden" name="domains" value="hdtvmagazine.com" />
			<input type="hidden" name="sitesearch" value="hdtvmagazine.com" />
			<input type="hidden" name="client" value="pub-2099480674594177" />
			<input type="hidden" name="forid" value="1" />
			<input type="hidden" name="ie" value="ISO-8859-1" />
			<input type="hidden" name="oe" value="ISO-8859-1" />
			<input type="hidden" name="safe" value="active"></input>
			<input type="hidden" name="flav" value="0001"></input>
			<input type="hidden" name="sig" value="kyBota6MZwmrrLCK"></input>
			<input type="hidden" name="cof" value="GALT:#008000;GL:1;DIV:#<?=BORDER_COLOR?>;VLC:<?=LINK_COLOR?>;AH:center;BGC:FFFFFF;LBGC:EEEEEE;ALC:<?=LINK_COLOR?>;LC:<?=LINK_COLOR?>;T:000000;GFNT:<?=LINK_COLOR?>;GIMP:<?=LINK_COLOR?>;LH:57;LW:324;L:http://www.hdtvmagazine.com/images/hdtvmagazine.gif;S:http://www.hdtvmagazine.com/;FORID:11;" />
			<input type="hidden" name="hl" value="en" />
			<input type="submit" value="Go" />
		</form>
	</div>
</div>
<div style="clear:both; padding-top:5px" />
<div class="menu"><span class="corners-top"><span></span></span>
	<ul class="menu">
		<li><a href="<?=BASE_URL?>" style="border:0; padding-left:0">Home</a></li>
		<li><a href="<?=BASE_URL?>/news">News</a></li>
		<li><a href="<?=BASE_URL?>/reviews">Reviews</a></li>
		<li><a href="<?=BASE_URL?>/forum">Forum</a></li>
		<li><a href="<?=BASE_URL?>/articles">Articles</a></li>
		<li><a href="<?=BASE_URL?>/columns">Columns</a></li>
		<li><a href="<?=BASE_URL?>/podcast">Podcast</a></li>
		<li><a href="<?=BASE_URL?>/programming">Programming</a></li>
		<li><a href="<?=BASE_URL?>/reports/hdtv-technology-review.php">Reports</a></li>
		<li><a href="<?=BASE_URL?>/studies">Studies</a></li>
		<!--li><a href="<?=BASE_URL?>/store">HD Store</a></li-->
		<!--li><a href="<?=BASE_URL?>/resources">Resources</a></li-->
		<li><a href="<?=BASE_URL?>/equipment/hdtvs-best-rated.php">HDTVs</a></li>
   </ul>
  	<div style="clear:left"></div>
<span class="corners-bottom"><span></span></span></div>