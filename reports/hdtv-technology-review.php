<?
	require('../global.php');

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine Reports - HDTV Technology Review</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header-4.php');
		$price = 0;
	?>
	<h1>HDTV Technology Review</h1>

	<div style="float:left; width:150px; text-align:center; margin:0 10px 10px 0">
			<!--b>Download PDF:</b> $<?=(number_format($price, 2))?><br>
			<a href="/hdstore/product.php?productid=16711"><img src="/images/btn-buy-download.png" alt="Buy Download"></a><br />
			<br /-->
			<div style="font:16pt bold"><a href="/downloads/hdtv-technology-review-2007.pdf">Download</a></div>
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
					<a href="http://www.lulu.com/hdtv"><img src="/images/btn-buy-hard-copy.png" alt="Buy Hard Copy"></a>
				</td></tr>
			</table>
	</div>

	<div style="float:right; margin:0 0 10px 10px; text-align:center"">
		<img src="/images/aquos-spread.jpg" alt="Sharp Aquos CES"><br /><br />
		<img src="/images/dlp-spread.jpg" alt="DLP CES">
	</div>

	<p style="font-size:12pt;font-weight:bold">
		It's a Must-Have
	</p><p>
		You might think you have the whole story about HDTV until you thumb through its amazing fact-filled pages.
		With this annual masterpiece, author <a href="<?=URL_ABOUT_CONTACT?>?name=lamaestra">Rodolfo La Maestra</a> has secured his place in the Pantheon of HDTV educators.
		Please <a href="<?=URL_HELP_FEEDBACK?>?category=report">provide your feedback</a> to Rodolfo so that he can continue to improve the product.
	</p>

	<h2>What Is It?</h2>
	<p>
		The <i><b>HDTV Technology Review</i></b> is a comprehensive (and searchable) desk reference detailing to the N<sup>th</sup> degree all existing and near-future HDTV technologies and H/DTV products.
		The report provides a clear explanation of the present and emerging HDTV technologies incorporated in these products as well as a seasoned assessment of their respective
		strengths and weaknesses and their likely market successes or failures. From politics to markets, the report unerringly delivers an up-to-the-minute overview of the entire state of the industry.
	</p>

	<h2>Who Should Buy It?</h2>
	<p>
		While the report will influence millions of information seeking consumers, we know that centers-of-influence can and will take best advantage of its accumulated knowledge first.
		If you are a professional in television, movies, displays, distribution or any part of the retail chain, you owe it to yourself to own a copy. If you are a passionate evangelist for
		HDTV from any of the diversified business or cultural sectors, this report is perfectly suited to you.  While challenging (due to its scope) to a general audience, those consumers
		who demand the best in information need look no further, for they have found the mother lode in this report.
	</p><p>
		A product of this level could easily sell for twenty times the asking price, but Rodolfo wants no barriers erected to knowledge about his passion, and ours -- HDTV.
		It's a bargain, plain and simple.
	</p><p>
		Order the HDTV Technology Review now while it is still fresh in your mind.
	</p><p>
		We have several sections of this year's report available at absolutely no cost whatsoever.<br>
		<br>
		<b>Note</b>: If you click on the links that follow, please realize that they will likely open in your browser.
		You will then "page down" to read the file. If you wish to download, please right-click
		on the links below and choose "Save Target As..." (or similar) to save to your computer for opening directly.
		<ul class="brownsquare">
			<li><a href="/downloads/hdtv-technology-review-2007-intro.pdf">Introduction &amp; Table of Contents</a></li>
			<li><a href="/downloads/hdtv-technology-review-2007-satellite-cable-broadcast.pdf">Satellite, Cable &amp; Broadcast</a></li>
			<li><a href="/downloads/hdtv-technology-review-2007-glossary.pdf">Glossary of H/DTV Terms</a></li>
		</ul>

		Also available are the <b>complete</b> reports from previous years ... each at no cost:
		<ul class="brownsquare">
			<li><a href="/downloads/hdtv-technology-review-2006.pdf">State of HDTV Technology and CES 2006</a></li>
			<li><a href="/downloads/hdtv-technology-review-2005.pdf">State of HDTV Technology and CES 2005</a></li>
			<li><a href="/downloads/hdtv-technology-review-2004.pdf">State of HDTV Technology and CES 2004</a></li>
			<li><a href="/downloads/hdtv-technology-review-2003.pdf">State of HDTV Technology and CES 2003</a></li>
		</ul>
	</p>
	<div align="center"><a href="http://www.adobe.com/products/acrobat/readermain.html" target="_blank"><img alt="Get Adobe Reader" src="/images/getacro.gif"></a></div>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</div></body>
</html>
