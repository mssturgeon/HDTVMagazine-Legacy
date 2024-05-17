<?
	require('../global.php');

	include(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - HDTV Resources</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/body_header-4.php');
	?>

	<h1>HDTV Resources</h1>
	<p>
		The following pages consist of a hodge-podge of reference information for HDTV. Although there are very few right now, this number
		will grow considerable over the next few weeks as we add more content.
	</p>
	<ul class="brownsquare">
		<li><a href="/reports/hdtv-technology-review.php">HDTV Technology Reports</a></li>
		<li><a href="<?=URL_HISTORY?>">HDTV History</a></li>
		<li><a href="<?=URL_EVENTS?>">HDTV Events &amp; Conferences</a></li>
		<li><a href="/resources/books.php">HDTV Books</a></li>
		<li><a href="/resources/tvshow_how-it-works.php">HDTV: How it Works, What to Buy</a> (TV Show)</li>
		<li><a href="/resources/links.php">Links to External Resources</a> (Web sites, etc.)</li>
		<li><a href="<?=URL_GLOSSARY?>">HDTV Glossary</a></li>
	</ul>

	<table class="bare" cellpadding="10" cellspacing="0" summary="" style="width:100%;text-align:center"><tr>
		<td style="vertical-align:middle"><a rel="nofollow" target="_blank" href="/downloads/DTV_Tip_Sheet.pdf"><img src="/images/DTV_Tip_Sheet.jpg" alt="Buying a Digital Television"></a><br>Buying a Digital Television</td>
		<td style="vertical-align:middle"><a rel="nofollow" target="_blank" href="/downloads/HDTV-Brochure_2005final.pdf"><img src="/images/HDTV-Brochure_2005final.jpg" alt="The 3 Simple Steps to HDTV"></a><br>The 3 Simple Steps to HDTV</td>
		<td style="vertical-align:middle"><a rel="nofollow" target="_blank" href="/downloads/CableConnects_sign.pdf"><img src="/images/dcr_logo.jpg" alt="Digital Cable Ready HDTV's"></a><br>Digital Cable Ready HDTV's</td>
		<td style="vertical-align:middle"><a rel="nofollow" target="_blank" href="/downloads/HDTVBrch2NewPages.pdf"><img src="/images/HDTVpdfCover1861.jpg" alt="A Consumer’s Guide to the Wonderful World of HDTV"></a><br>A Consumer’s Guide to the Wonderful World of HDTV</td>
	</tr><tr>
		<td style="vertical-align:middle"><a rel="nofollow" target="_blank" href="/downloads/HDTV_buckslip.pdf"><img style="border:1px solid black" src="/images/HDTV_buckslip.jpg" alt="HDTV Retailer Tip Sheet"></a><br>HDTV Retailer Tip Sheet</td>
		<td style="vertical-align:middle"><a rel="nofollow" target="_blank" href="/downloads/HDTV_Guide-Spring05.pdf"><img src="/images/HDTVGuide.jpg" alt="CEA HDTV Guide"></a><br>CEA HDTV Guide</td>
		<td style="vertical-align:middle"><a rel="nofollow" target="_blank" href="/downloads/DTV_White_Paper2.pdf"><img src="/images/DTV_White_Paper2.jpg" alt="Washington Insider Series: The HDTV Transition"></a><br>Washington Insider Series: The HDTV Transition</td>

	</tr></table>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</body>
</html>
