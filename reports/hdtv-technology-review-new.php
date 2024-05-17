<?
	require('../global.php');
	$title = 'HDTV Technology Review 2007';
	$short_title = '2007 HDTV Report';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title><?=$title?></title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		$price = 79;
	?>

	<h1><?=$title?></h1>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td style="text-align:center;vertical-align:top;width:175px">

			<a href="/hdstore/product.php?productid=16193"><img src="/images/btn_buynow.gif" alt="Buy Now"></a><br />
			<br />
			
			<table class="type1b" cellpadding=0 cellspacing=0>
				<tr><td class="type1b_header" style="text-align:center">Print Copy</td></tr>
				<tr><td class="type1b" style="height:100px;text-align:center">
					<a href="http://www.lulu.com/hdtv"><img src="/images/cover_thumbnail.jpg" alt="<?=$title?>"></a><br>
					<br>
					<b>Color Copy (hardcover):</b> $<?=(number_format(($price*1.25) + 70.10, 2))?><br>
					<b>Color Copy (softcover):</b> $<?=(number_format(($price*1.25) + 57.63, 2))?><br>
					<b>B&amp;W Copy (softcover):</b> $<?=(number_format(($price*1.25) + 11.61, 2))?><br>
					<b>Compact Disc:</b> $<?=(number_format(($price*1.25) + 5.50, 2))?><br>
					<a href="http://www.lulu.com/hdtv"><img src="/images/btn_buynow.gif" alt="Buy Now"></a>
				</td></tr>
			</table>
			
		</td><td style="padding-left:10px">
			<p>
				<img src="/images/panasonic-spread-2007.jpg" alt="Panasonic CES" align="right" style="margin:0 0 10px 10px;"-->
				The much anticipated <i><b><?=$title?></b></i> is now available!<br>
				<br>
				As with every year, this report reviews the state of HDTV technology for consumers, its implementation, industry matters, government affairs, and the industry behind it. This
				year there are two editions of this report available: Consumer and Industry.
			</p><p>
				 The Consumer version, which you can buy now via the link at the left, contains 350+ pages of information on HD hardware (especially TVs) along with brief reviews
				 of subjects related to DTV such as digital connectivity, satellite/cable/broadcast technology, IPTV and DTV standards and implementation.
				 Also included are future products and technologies announced as of May 2007, with information supplied directly by manufacturers, or gathered at HDTV conferences,
				 CEDIA, NAB, CEATEC, and the International CES (Consumer Electronics Show), where the industry also introduce innovations, prototypes, and technology statements.
			</p><p>
				The Industry version, available exclusively through DisplaySearch, contains all that is in the Consumer version, but with an additional 200 pages of detail. Also exclusive
				to the Industry Edition are sections on multi-channel audio for HD, 1080p implementation and content protection. For more information on purchasing the Industry Edition,
				read the DisplaySearch Press Release: <a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.displaysearch.com/press/?id=1487">Annual HDTV Technology Review Now Available from DisplaySearch</a>.
			</p><p>
				Please <a href="<?=URL_HELP_FEEDBACK?>?category=report">provide your feedback</a> to Rodolfo so that he can continue to improve the product.
			</p><p>
				<img src="/images/products/apple-tv.jpg" alt="Apple TV" align="right" style="margin:0 0 10px 10px;">
				Put simply, the <i><b><?=$title?></i></b> is a comprehensive (and searchable) desk reference detailing to the N<sup>th</sup> degree all existing and near-future HDTV technologies and H/DTV products.
				The report provides a clear explanation of the present and emerging HDTV technologies incorporated in these products as well as a seasoned assessment of their respective
				strengths and weaknesses and their likely market successes or failures. From politics to markets, the report unerringly delivers an up-to-the-minute overview of the entire state of the industry.
			</p><br clear="all">
			<h2>Who Should Buy It?</h2>
			<p>
				<img src="/images/sharp-spread-2007.jpg" alt="Sharp CES" align="right" style="margin:0 0 10px 10px;">
				While the report will influence millions of information seeking consumers, we know that centers-of-influence can and will take best advantage of its accumulated knowledge first.
				If you are a professional in television, movies, displays, distribution or any part of the retail chain, you owe it to yourself to own a copy. If you are a passionate evangelist for
				HDTV from any of the diversified business or cultural sectors, this report is perfectly suited to you.  While challenging (due to its scope) to a general audience, those consumers
				who demand the best in information need look no further, for they have found the mother lode in this <?=$short_title?>.
			</p><p>
				A product of this level could easily sell for twenty times the asking price, but Rodolfo wants no barriers erected to knowledge about his passion, and ours -- HDTV.
				It’s a bargain, plain and simple.
			</p><p>
				Order the <?=$short_title?> now while it is still fresh in your mind. Be the one that your friends come to with questions about HD technology, present and future.
			</p><p>
				We have included the introductory section of the report below at absolutely no cost whatsoever. Feel free to check back often as we may post other sections
				of the report for free here as they are made available.<br>
				<br>
				<b>Note</b>: If you click on the links that follow, please realize that they will likely open in your browser.
				You will then "page down" to read the file. If you wish to download, please right-click
				on the links below and choose "Save Target As..." (or similar) to save to your computer for opening directly.
				<ul class="brownsquare">
					<li class="browndot"><a href="/downloads/hdtv-technology-review-2007-intro.pdf">Introduction &amp; Table of Contents</a></li>
					<!--li class="browndot"><a href="/downloads/hdtv-technology-review-2006-1080p.pdf">1080p into HDTV Displays</a></li>
					<li class="browndot"><a href="/downloads/hdtv-technology-review-2006-connectivity.pdf">Digital Connectivity - Tutorial</a></li>
					<li class="browndot"><a href="/downloads/hdtv-technology-review-2006-glossary.pdf">Glossary of H/DTV Terms</a></li-->
				</ul>
		
				Also available are the <b>complete</b> 2003, 2004, 2005, and/or 2006 Reports ... each at no cost:
				<ul class="brownsquare">
					<li><a href="/downloads/hdtv-technology-review-2006.pdf">State of HDTV Technology and CES 2006</a></li>
					<li><a href="/downloads/hdtv-technology-review-2005.pdf">State of HDTV Technology and CES 2005</a></li>
					<li><a href="/downloads/hdtv-technology-review-2004.pdf">State of HDTV Technology and CES 2004</a></li>
					<li><a href="/downloads/hdtv-technology-review-2003.pdf">State of HDTV Technology and CES 2003</a></li>
				</ul>
				
				And when you're done looking through those, come back and buy the <?=$short_title?> ... it will do you some good.
			</p>
			<div align="center"><a href="http://www.adobe.com/products/acrobat/readermain.html" target="_blank"><img alt="Get Adobe Reader" src="/images/getacro.gif"></a></div>
		</td>
	</tr></table>
	</form>

	<?include(BASE_DIR .'/includes/body_footer.php');?>

</body>
</html>
