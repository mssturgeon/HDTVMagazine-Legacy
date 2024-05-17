<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 264";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 264 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# Set variables based on entry type. NOTE: Podcasts has its own entry template, so it is not included amongst the choices below.
	switch (7) {
		case 1: # Articles
			$feed_name = 'hdtv-articles';
			$container = 'article_container';
			$sub_type = SUB_ARTICLES;
			$sub_label = 'Receive instant notification of new articles';
			$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
			$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
			break;
		case 4: # Interviews
			$feed_name = 'hdtv-interviews';
			break;
		case 5: # History
			$feed_name = 'hdtv-archive';
			break;
		case 6: # Test
			$container = 'article_container';
			$sub_type = 0;
			break;
		case 7: # Bulletins
			$google_links_channel = ''; # Don't count bulletins
			$feed_name = 'hdtv-news';
			$container = 'bulletin_container';
			$sub_type = SUB_BULLETINS;
			$sub_label = 'Receive instant notification of HDTV Bulletins';
			$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of HDTV Bulletins via email as soon as they are published.';
			$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of HDTV Bulletins via email as soon as they are published.';
			break;
		case 8: # Reviews
			$feed_name = 'hdtv-reviews';
			$container = 'article_container';
			$sub_type = SUB_REVIEWS;
			$sub_label = 'Receive instant notification of new reviews';
			$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Reviews via email as soon as they are published.';
			$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Reviews via email as soon as they are published.';
			break;
#		case 9: # Podcasts
		case 10: # Columns
			$feed_name = 'hdtv-columns';
			$container = 'article_container';
			$sub_type = SUB_COLUMNS;
			$sub_label = 'Receive instant notification of new columns';
			$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Columns via email as soon as they are published.';
			$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Columns via email as soon as they are published.';
			$about = 'HDTV Magazine Columns are written by various personalities within the HDTV industry. They are typically shorter than our standard <a href="/articles">Article</a> and quite often express the opinion of the author(s). And of course, opinions expressed by these authors are not necessarily those of HDTV Magazine.';
			break;
		default:
			$container = 'body_container';
			break;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<?require(BASE_DIR .'/includes/common_header.php');?>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<meta name="generator" content="http://www.movabletype.org/" />
	<meta name="keywords" content="public display, public displays, lcd public, text decoration, npd group, public, Public, display, displays, displaysearch, pdp, Display, PDP, LCD, lcd, Displays, DisplaySearch, size, font, worldwide, market, total, NEC, nec, color" />
	<meta name="description" content="AUSTIN, TEXAS, December 1, 2005-DisplaySearch, an NPD Group Company and the worldwide leader in display market research, issued the first edition of its newest Quarterly Public Display Shipment and Forecast Report indicating that worldwide shipments of large flat-panel displays for non-TV applications reached a worldwide total of 87K units in Q2'05. While this number was slightly down from the Q1'05 worldwide total of 96.4K units, recent price reductions in both plasma (PDP) and large-format LCD (26&quot;+) panels are forecast to accelerate this market to over 400K units for 2005, and double-digit unit Y/Y growth is forecast through 2009 to reach 1.8M units. Conservative estimates of the worldwide market show the total Compound Annual Growth (CARG) rate for PDPs used in such applications to be as much as 10% from 2004-2009 with LCDs forecast to have an even larger CARG of 82% from 2004 to 2009! , resulting in an overall explosive 36% CARG for the flat panel public display market.

" />
	<title>HDTV Magazine Bulletins - DisplaySearch Forecasts $3B Market for FPD Public Displays by 2009; PDPs Out-Ship LCDs 3 to 1 in Q2'05</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/displaysearch_forecasts_3b_market_for_fpd_public_displays_by_2009_pdps_out-ship_lcds_3_to_1_in_q205';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('DisplaySearch Forecasts $3B Market for FPD Public Displays by 2009; PDPs Out-Ship LCDs 3 to 1 in Q2\'05'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2005/11/displaysearch_forecasts_3b_market_for_fpd_public_displays_by_2009_pdps_out-ship_lcds_3_to_1_in_q205.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">DisplaySearch Forecasts $3B Market for FPD Public Displays by 2009; PDPs Out-Ship LCDs 3 to 1 in Q2'05</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>November 30, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2005/11/displaysearch_forecasts_3b_market_for_fpd_public_displays_by_2009_pdps_out-ship_lcds_3_to_1_in_q205.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2005/11/displaysearch_forecasts_3b_market_for_fpd_public_displays_by_2009_pdps_out-ship_lcds_3_to_1_in_q205.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2005/11/displaysearch_forecasts_3b_market_for_fpd_public_displays_by_2009_pdps_out-ship_lcds_3_to_1_in_q205.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
				<span><img src="/images/save.gif" alt="Save Article" align="absmiddle" /><a target="_blank" href="<?=$save_url?>">Save</a></span>
				<span><img src="/images/email.gif" alt="Email Article" align="absmiddle" /><a href="<?=$email_url?>">Email</a></span>
				<span><img src="/images/print.png" alt="Print Article" align="absmiddle" /><a target="_blank" href="<?=$print_url?>">Print</a></span><br />
				<br /><br />
			</td>
		</tr>
	</table>
	<?if ($sub_type > 0 && ($userdata[subscriptions] & $sub_type) || $_SERVER[HTTP_USER_AGENT] == 'Googlebot') {} else {
		if ($userdata[session_logged_in]) {?>
			<div class="important"><span class="corners-top"><span></span></span>
				<img src="/images/i_inbox.gif" align="left" style="padding-right:10px" />
				<span class="label"><?=$sub_label?>:</span>
				<?=$sub_desc_logged_in?>
			<span class="corners-bottom"><span></span></span></div>
		<?} else {?>
			<div class="important"><span class="corners-top"><span></span></span>
				<img src="/images/i_inbox.gif" align="left" style="padding-right:10px" />
				<span class="label"><?=$sub_label?>:</span>
				<?=$sub_desc_anon?>
			<span class="corners-bottom"><span></span></span></div>
		<?}
	}?>
	<div>
		<div style="float:left; margin:0 5px 5px 0;"><?
			if ($digg_url == '') {
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2005/11/displaysearch_forecasts_3b_market_for_fpd_public_displays_by_2009_pdps_out-ship_lcds_3_to_1_in_q205.php&amp;phase=2&amp;title=DisplaySearch%20Forecasts%20%243B%20Market%20for%20FPD%20Public%20Displays%20by%202009%3B%20PDPs%20Out-Ship%20LCDs%203%20to%201%20in%20Q2%2705&amp;bodytext=AUSTIN%2C%20TEXAS%2C%20December%201%2C%202005-DisplaySearch%2C%20an%20NPD%20Group%20Company%20and%20the%20worldwide%20leader%20in%20display%20market%20research%2C%20issued%20the%20first%20edition%20of%20its%20newest%20Quarterly%20Public%20Display%20Shipment%20and%20Forecast%20Report%20indicating%20that%20worldwide%20shipments%20of%20large%20flat-panel%20displays%20for%20non-TV%20applications%20reached%20a%20worldwide%20total%20of%2087K%20units%20in%20Q2%2705.%20While%20this%20number%20was%20slightly%20down%20from%20the%20Q1%2705%20worldwide%20total%20of%2096.4K%20units%2C%20recent%20price%20reductions%20in%20both%20plasma%20%28PDP%29%20and%20large-format%20LCD%20%2826%22%2B%29%20panels%20are%20forecast%20to%20accelerate%20this%20market%20to%20over%20400K%20units%20for%202005%2C%20and%20double-digit%20unit%20Y%2FY%20growth%20is%20forecast%20through%202009%20to%20reach%201.8M%20units.%20Conservative%20estimates%20of%20the%20worldwide%20market%20show%20the%20total%20Compound%20Annual%20Growth%20%28CARG%29%20rate%20for%20PDPs%20used%20in%20such%20applications%20to%20be%20as%20much%20as%2010%25%20from%202004-2009%20with%20LCDs%20forecast%20to%20have%20an%20even%20larger%20CARG%20of%2082%25%20from%202004%20to%202009%21%20%2C%20resulting%20in%20an%20overall%20explosive%2036%25%20CARG%20for%20the%20flat%20panel%20public%20display%20market.%0A%0A&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
			} else {
				echo '<script src="http://digg.com/api/diggthis.js"></script>';
			}
		?></div>
		<div id="right" style="float:right; margin:0 0 5px 5px; text-align:center;" align="center">
			<?include(BASE_DIR .'/ads/mrectangle.php');?>
			<br />
			<div align="center">
				<?include(BASE_DIR .'/ads/skyscraper.php');?>
			</div>
		</div>
		<div id="<?=$container?>">
			<p><HTML><HEAD><TITLE>DisplaySearch: Press Release</TITLE><br />
<!link rel="stylesheet" href="/displaysearch/site/scripts/fonts.css" type="text/css"><br />
<style type="text/css"><br />
	BODY			{ margin: 0px 0px; font: 12px Arial, Helvetica, Verdana, Geneva, sans-serif; }<br />
	TABLE, TD		{ font: 10px Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060;}	/*line-height: 25px; letter-spacing: 5px;*/<br />
	.title_head		{ font-weight: bold; font-size: 18px; text-indent: 38px; }<br />
	.title 			{ font-weight: bold; font-size: 12px;  }	/* color: #720517; */<br />
	a:link			{ color:006FA2; text-decoration:none; }<br />
	a:visited		{ color:006FA2; text-decoration:none; }<br />
	a:hover			{ color:006FA2; text-decoration:underline; /* font-weight : bold; #720517*/ }<br />
	a.news			{ font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; font-size:10px; color:606060; text-decoration:none; }/*, 006699*/<br />
	a.news:hover	{ font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; font-size:10px; color:006FA2; text-decoration:underline;}<br />
.style1 {color: #FFFFFF}<br />
</style></p>

<p></HEAD><br />
<BODY BGCOLOR="#FFFFFF" TEXT="#000000" LEFTMARGIN="0" RIGHTMARGIN="0" TOPMARGIN="0" MARGINWIDTH="0" MARGINHEIGHT="0"></p>

<center>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr><td colspan="3" BGCOLOR="#333333">&nbsp;<br><br></td></tr>
<tr><td BGCOLOR="#333333" width="50%">&nbsp;</td>
	<td><table border="0" cellpadding="0" cellspacing="0" width="718">
		<tr><td 
background="http://www.displaysearch.com/press/pr_email/v4/press-head-1.gif"
width="582" class=title_head>	
				Press Release
				&nbsp;
			</td>
			<td width="136"><a href="http://www.displaysearch.com/"><img 
	src="http://www.displaysearch.com/press/pr_email/v4/press-head-logoNPD.gif" 
			alt="DisplaySearch" width="136" height="101" border="0"></a></td>
		</tr>
		</table>
	</td>
	<td BGCOLOR="#333333" width="50%">&nbsp;</td>
	</tr>
<tr><td BGCOLOR="#333333">&nbsp;</td>
	<td bgcolor="#FFFFFF">
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr><td width="100" bgcolor="#f9c252">&nbsp;</td>
			<td width="10">&nbsp;</td>
			<td><br>
<!-- ---- Begin Content ---- -->			

<p>Thursday, December 1, 2005<br />
<p><span class=title>DisplaySearch Forecasts $3B Market for FPD Public Displays by 2009; PDPs Out-Ship LCDs 3 to 1 in Q2'05 </span></p>

<p>AUSTIN, TEXAS, December 1, 2005-DisplaySearch, an NPD Group Company and the worldwide leader in display market research, issued the first edition of its newest <B><I><a href="http://www.displaysearch.com/products/?pn=pub_disp_ship">Quarterly Public Display Shipment and Forecast Report</B></I></a> indicating that worldwide shipments of large flat-panel displays for non-TV applications reached a worldwide total of 87K units in Q2'05.  While this number was slightly down from the Q1'05 worldwide total of 96.4K units, recent price reductions in both plasma (PDP) and large-format LCD (26"+) panels are forecast to accelerate this market to over 400K units for 2005, and double-digit unit Y/Y growth is forecast through 2009 to reach 1.8M units. Conservative estimates of the worldwide market show the total Compound Annual Growth (CARG) rate for PDPs used in such applications to be as much as 10% from 2004-2009 with LCDs forecast to have an even larger CARG of 82% from 2004 to 2009!
 , resulting in an overall explosive 36% CARG for the flat panel public display market.</p>

<p>With production gearing up for larger flat panel displays for the consumer TV market, the exploding Public Display market is benefiting from these economies of scale with similar displays used for non-TV applications such as Point of Sale Signage, Flight Information Displays, Electronic Menu Boards and even corporate conference room displays.  The two most popular technologies for the out-of-home Public Display environment are LCDs and PDPs, both of which continue to push the technology envelope in size, resolution and price points.  LCDs as large as 55" are now commercially available worldwide, while PDPs as large as 71" are now available for commercial applications with signs that these two technologies will both gain ground in this emerging space.</p>

<p>Q2'05 results show that the most popular size and technology for Public Display applications was the 40-43" PDPs with a 63.6% share; lower resolution ED versions of 42" PDPs had a 38.8% share alone.  The 40-43" PDP category was followed by the 50" PDP size, which had a 12.3% share of the worldwide shipments of Public Displays in Q2'05.  LCD based 40" Public Displays were the third best selling size and technology with an 8.0% share for the quarter.  While PDP products currently rule the Public Display market place, large-format LCDs (26"+) have shown the greatest Q/Q shipment growth since Q2'04 with positive Q/Q growth in all quarters surveyed with the exception of Q2'05, with explosive growth projected to be reported for Q3'05.</p>

<p><span class=title>Table 1: PDP and LCD Public Display Quarterly Growth Rates</span></p>
<table width="80%" cellpadding="0" cellspacing="0" align="center"  bordercolor="#999999" rules="all" frame="below">
  <tr  bgcolor="#333333">
    <td width="50%" align="center" class=table_title><strong><font color="#FFFFFF"> </font></strong></td>
    <td width="10%" align="center" class=table_title><strong><font color="#FFFFFF">Q3'04</font></strong></td>
    <td width="10%" align="center" class=table_title><strong><font color="#FFFFFF">Q4'04</font></strong></td>
    <td width="10%" align="center" class=table_title><strong><font color="#FFFFFF">Q1'05</font></strong></td>
    <td width="10%" align="center" class=table_title><strong><font color="#FFFFFF">Q2'05</font></strong></td>
    <td width="10%" align="center" class=table_title><strong><font color="#FFFFFF">Q3'05*</font></strong></td>
  </tr>
  <tr >
    <td width="50%"><p align="center">LCD Public Displays (26"+)</p></td>
    <td width="10%"><p align="center">36% </p></td>
    <td width="10%"><p align="center">17% </p></td>
    <td width="10%"><p align="center">13% </p></td>
    <td width="10%"><p align="center">-5% </p></td>
    <td width="10%"><p align="center">45% </p></td>
  </tr>
  <tr  bgcolor="#CCCCCC">
    <td><p align="center">PDP Public Displays (32"+) </p></td>
    <td width="10%"><p align="center">11% </p></td>
    <td width="10%"><p align="center">-2% </p></td>
    <td width="10%"><p align="center">-9% </p></td>
    <td width="10%"><p align="center">11% </p></td>
    <td width="10%"><p align="center">14% </p></td>
  </tr>
  <tr >
    <td width="50%"><p align="center">Total WW FPD Public Displays </p></td>
    <td width="10%"><p align="center">14% </p></td>
    <td width="10%"><p align="center">1% </p></td>
    <td width="10%"><p align="center">-5% </p></td>
    <td width="10%"><p align="center">-10% </p></td>
    <td width="10%"><p align="center">20% </p></td>
  </tr>
</table>
<ul><i>*Projected</ul></i>

<p>Street level pricing for Public Displays in the period dropped from a total Weighted Average of $3,616 in Q2'04 to $3,425 in Q2'05 with projections that on a weighted average basis pricing will fall as low as $3,032 once the results from Q3'05 are fully posted.  These price reductions were driven by the significant decline in PDP pricing, especially in the 40-43" ED PDP category, which dropped 12.1% in Q4'04 into the sub-$2,299 range thus increasing unit volume shipments.  Additionally, price reductions in the 40-42" LCD class of Public Displays helped accelerate sales, even though this technology is 1.5x more expensive than its nearest HD PDP competitor.</p>

<p><span class=title>Table 2: PDP and LCD Public Display Historical Pricing Sequential Changes</span></p>
<table width="80%" cellpadding="0" cellspacing="0" align="center"  bordercolor="#999999" rules="all" frame="below">
  <tr  bgcolor="#333333">
    <td width="50%" align="center" class=table_title><strong><font color="#FFFFFF"> </font></strong></td>
    <td width="10%" align="center" class=table_title><strong><font color="#FFFFFF">Q3'04</font></strong></td>
    <td width="10%" align="center" class=table_title><strong><font color="#FFFFFF">Q4'04</font></strong></td>
    <td width="10%" align="center" class=table_title><strong><font color="#FFFFFF">Q1'05</font></strong></td>
    <td width="10%" align="center" class=table_title><strong><font color="#FFFFFF">Q2'05</font></strong></td>
    <td width="10%" align="center" class=table_title><strong><font color="#FFFFFF">Q3'05*</font></strong></td>
  </tr>
  <tr >
    <td width="50%"><p align="center">40-42" LCD Sequential Change</p></td>
    <td width="10%"><p align="center">-16% </p></td>
    <td width="10%"><p align="center">0% </p></td>
    <td width="10%"><p align="center">-7% </p></td>
    <td width="10%"><p align="center">-6% </p></td>
    <td width="10%"><p align="center">-5% </p></td>
  </tr>
  <tr  bgcolor="#CCCCCC">
    <td><p align="center">40-43" ED PDP Sequential Change </p></td>
    <td width="10%"><p align="center">0% </p></td>
    <td width="10%"><p align="center">-12% </p></td>
    <td width="10%"><p align="center">1% </p></td>
    <td width="10%"><p align="center">8% </p></td>
    <td width="10%"><p align="center">-5% </p></td>
  </tr>
  <tr >
    <td width="50%"><p align="center">40-43" HD PDP Sequential Change </p></td>
    <td width="10%"><p align="center">0% </p></td>
    <td width="10%"><p align="center">-16% </p></td>
    <td width="10%"><p align="center">-2% </p></td>
    <td width="10%"><p align="center">7% </p></td>
    <td width="10%"><p align="center">-26% </p></td>
  </tr>
  <tr  bgcolor="#CCCCCC">
    <td><p align="center">Total Public Display Weighted Average Sequential Change </p></td>
    <td width="10%"><p align="center">-2% </p></td>
    <td width="10%"><p align="center">-5% </p></td>
    <td width="10%"><p align="center">1% </p></td>
    <td width="10%"><p align="center">0% </p></td>
    <td width="10%"><p align="center">-12% </p></td>
  </tr>
</table>
<ul><i>*Projected</ul></i>

<p>In the emerging area of LCD Public Displays, NEC had been the worldwide leader (operating under NEC-Mitsubishi prior to Q2'05 then under NEC Display Solutions thereafter) from Q3'04 through Q2'05 when it lost the lead to Samsung with 33.0% to NEC's 32.4% for the quarter. NEC is projected to again top the worldwide charts in Q3'05.  NEC has consistently been the North American leader, currently enjoying the top NA position in the LCD category in Q2'05 with a 41.1% share.</p>

<p><span class=title>Table 3: Q2'05 Worldwide Major Brand LCD Public Display Shares </span></p>
<table width="80%" cellpadding="0" cellspacing="0" align="center"  bordercolor="#999999" rules="all" frame="below">
  <tr  bgcolor="#333333">
    <td width="25%" align="center" class=table_title><strong><font color="#FFFFFF">Rank</font></strong></td>
    <td width="50%" align="center" class=table_title><strong><font color="#FFFFFF">Manufacturer</font></strong></td>
    <td width="25%" align="center" class=table_title><strong><font color="#FFFFFF">WW Share</font></strong></td>
</tr>
  <tr >
    <td width="25%"><p align="center">1 </p></td>
    <td width="50%"><p align="center">Samsung</p></td>
    <td width="25%"><p align="center">33% </p></td> 
</tr>
  <tr  bgcolor="#CCCCCC">
    <td><p align="center">2 </p></td>
    <td width="50%"><p align="center">NEC Display Solutions </p></td>
    <td width="25%"><p align="center">32.4% </p></td>    
</tr>
  <tr >
    <td width="25%"><p align="center">3 </p></td>
    <td width="50%"><p align="center">LGE </p></td>
    <td width="25%"><p align="center">29.1% </p></td>    
</tr>
  <tr  bgcolor="#CCCCCC">
    <td width="25%"><p align="center">4 </p></td>
    <td width="50%"><p align="center">iiyama </p></td>
    <td width="25%"><p align="center">3.8% </p></td>   
</tr>
  <tr >
    <td width="25%"><p align="center">5 </p></td>
    <td width="50%"><p align="center">Mitsubishi </p></td>
    <td width="25%"><p align="center">1.9% </p></td>    
</tr>
  <tr bgcolor="#CCCCCC" >
    <td width="25%"><p align="center"> </p></td>
    <td width="50%"><p align="center">Total </p></td>
    <td width="25%"><p align="center">100% </p></td>    
</tr>
</table>

<p><span class=title>Table 4: Q2'05 North American Major Brand LCD Public Display Shares </span></p>
<table width="80%" cellpadding="0" cellspacing="0" align="center"  bordercolor="#999999" rules="all" frame="below">
  <tr  bgcolor="#333333">
    <td width="25%" align="center" class=table_title><strong><font color="#FFFFFF">Rank</font></strong></td>
    <td width="50%" align="center" class=table_title><strong><font color="#FFFFFF">Manufacturer</font></strong></td>
    <td width="25%" align="center" class=table_title><strong><font color="#FFFFFF">NA Share</font></strong></td>
</tr>
  <tr >
    <td width="25%"><p align="center">1 </p></td>
    <td width="50%"><p align="center">NEC Display Solutions</p></td>
    <td width="25%"><p align="center">41.1% </p></td> 
</tr>
  <tr  bgcolor="#CCCCCC">
    <td><p align="center">2 </p></td>
    <td width="50%"><p align="center">Samsung </p></td>
    <td width="25%"><p align="center">31.0% </p></td>    
</tr>
  <tr >
    <td width="25%"><p align="center">3 </p></td>
    <td width="50%"><p align="center">LGE </p></td>
    <td width="25%"><p align="center">21.9% </p></td>    
</tr>
  <tr  bgcolor="#CCCCCC">
    <td width="25%"><p align="center">4 </p></td>
    <td width="50%"><p align="center">iiyama </p></td>
    <td width="25%"><p align="center">6.0% </p></td>   
</tr>
  <tr >
    <td width="25%"><p align="center">5 </p></td>
    <td width="50%"><p align="center">Mitsubishi </p></td>
    <td width="25%"><p align="center">0.0% </p></td>    
</tr>
  <tr bgcolor="#CCCCCC" >
    <td width="25%"><p align="center"> </p></td>
    <td width="50%"><p align="center">Total </p></td>
    <td width="25%"><p align="center">100% </p></td>    
</tr>
</table>

<p>DisplaySearch's newest <B><I><a href="http://www.displaysearch.com/products/?pn=pub_disp_ship">Quarterly Public Display Shipment and Forecast Report</B></I></a> includes shipment and forecast data for both LCD and PDP-based FPD Public Displays quarterly through 2009 with historical shipments by brand, by size, by region for LCD Public Displays and historical PDP total WW shipments by size from Q2'04 through the present. This new report also provides cost forecasts of LCD and PDP modules used in Public Display environments as well as projected average street prices by size and by technology through 2009. This report is delivered in PowerPoint and includes enhanced Excel data tables.  Please contact Arie Braun at DisplaySearch at 512-459-3126 or  <a href="mailto:arie@displaysearch.com">arie@displaysearch.com</a> for subscription information.

<p><B>Register for US FPD by December 31 and save!</B>  DisplaySearch will also hold its <B><I><a href="http://www.displaysearch.com/usfpd2006">8th Annual DisplaySearch US FPD Conference</a></I></B> at the beautiful Loews Coronado in San Diego on March 21-23, 2006.  Save $200 by signing up as an attendee by December 31,2005.  Sponsorships and exhibits are also available now!  Please visit the official conference website at <a href="http://www.displaysearch.com/usfpd2006">www.displaysearch.com/usfpd2006</a> or contact Kendra Smith at 512-459-3126 x107 or <a href="mailto:kendra@displaysearch.com">kendra@displaysearch.com.</a></p>

<p><B>About DisplaySearch</B><br />
<p>DisplaySearch, an NPD Group company, has a core team of 33 employees located in North America and Asia who produce a valued suite of market forecasts, technology assessments, surveys, studies and analyses. The company also organizes influential events worldwide. Headquartered in Austin, Texas, DisplaySearch has regional operations in Chicago, Hong Kong, Houston, Kyoto, San Diego, San Jose, Seoul, Shenzhen, Taipei and Tokyo, and the company is on the web at <a href="http://www.displaysearch.com">www.displaysearch.com.</a></p></p>

<p><B>About The NPD Group Inc.</B><br />
<p>Since 1967, The NPD Group has provided reliable and comprehensive consumer and retail information for a wide range of industries. Today more than 1,400 manufacturers and retailers rely on NPD to help them better understand their customers, product categories, distribution channels and competition in order to help guide their business. Information from The NPD Group is available for the following major vertical sectors: apparel, appliances, automotive, beauty, consumer electronics, food and beverage, foodservice, footwear, home improvement, housewares, imaging, information technology, music, software, travel, toys, video games, and wireless. For more information, visit <a href="http://www.npd.com">www.npd.com.</a></p></p>

<p></p>

<p>			<br />
<!-- ---- End Content ---- -->			<br />
				<br><br><br />
			</td><br />
			<td width="70">&nbsp;</td><br />
		</tr><br />
		</table><br />
	</td><td BGCOLOR="#333333">&nbsp;</td><br />
</tr><br />
<tr><td BGCOLOR="#333333">&nbsp;</td><br />
	<td><a href="http://www.displasearch.com/"><img <br />
	src="http://www.displaysearch.com/press/pr_email/press-foot.gif" <br />
	alt="" width="718" height="48" border="0"></a></td><br />
	<td BGCOLOR="#333333">&nbsp;</td><br />
</tr><br />
<tr><td BGCOLOR="#333333">&nbsp;</td><br />
	<td BGCOLOR="#333333" align="right"><br />
		<font size="-2" color="#666666"><br />
		<a href="mailto:listserve@displaysearch.com?subject=unsubscribe">unsubscribe</a><br><br />
		&copy; 2005 <a href="http://www.displaysearch.com/"><font color="#777777">DisplaySearch</font></a><br />
		&bull; Site by <a href="http://www.gogocreative.com/"<br />
		onMouseOver="self.status='Gogo Creative'; return true"><font color="#777777">GoGo Creative</font></a><br />
		</font><br />
		<BR><BR><BR><BR><BR><BR><br />
	</td><br />
	<td BGCOLOR="#333333">&nbsp;</td><br />
</tr><br />
</table><br />
</center><br />
<br><br><br></p>

<p></BODY><br />
</HTML></p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>November 30, 2005 07:04 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 264
 				AND a.topic_id = t.topic_id
 				AND t.topic_id = p.topic_id
 				AND p.post_id = pt.post_id
 			ORDER BY post_time";
 			$result = mQuery($sql);
 			$num_comments = mysql_num_rows($result);
 			
 			if ($num_comments > 0) {
 				# Skip the first one, as it's just the excerpt post.
 				$row = mysql_fetch_assoc($result);
 				$thread_url = URL_FORUM_VIEWTOPIC .'?t='. $row[topic_id];
 				echo '<h2 style="margin-bottom:10px"><a href="'. $thread_url .'">Reader Commentary</a></h2>'.
 				'<div class="item"><span class="corners-top"><span></span></span>'.
 					'<img src="/images/icon_topic.gif" alt="" /><b> See Forum Topic</b>: '.
 					'<a href="'. $thread_url .'">'. $row[topic_title] .'</a> <span class="grey">('. $row[topic_replies] .' replies)</span>'.
 				'<span class="corners-bottom"><span></span></span></div>';
 				
 				$x = 0;
 				while ($row = mysql_fetch_assoc($result)) {
 					if ($x == 10) break;
 					$x++;
 					$comment_url = URL_FORUM_VIEWTOPIC .'?p='. $row[post_id] .'#'. $row[post_id];
 					$text = strip_tags(str_replace('[', '<', str_replace(']', '>', $row[post_text])));
 					if ($row[post_subject] != '') {
 						$subject = $row[post_subject];
 					} else {
 						$subject = "Re: $row[topic_title]";
 					}
 	
 					$class = ($x % 2 == 0) ? 'item' : 'item_odd';
 					echo '<div class="'. $class .'"><span class="corners-top"><span></span></span>'.
 						'<div style="font-size:1.2em; font-weight:bold"><a href="'. $comment_url .'">'. $subject .'</a></div>'.
 						'<b>'. $row[poster_id] .'</b> '. date('M j, g:ia', $row[dt]) .'<br />'.
 						$text .
 					'<span class="corners-bottom"><span></span></span></div>';
 				}
 			}
 			if ($num_comments > $x) {
 				echo '<div align="center" class="important"><span class="corners-top"><span></span></span>'.
 				"Showing only excerpts from $x out of $num_comments, <a href='$thread_url'>Read More</a>".
 				'<span class="corners-bottom"><span></span></span></div>';
 			}
 		?><div class="dottedline"></div></div>

 		<div class="item"><span class="corners-top"><span></span></span>
 			<h2>More on </h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = ''
 				AND e.entry_status = 2
 				AND e.entry_blog_id IN (". INCLUDE_BLOGS_ALL .")
 				AND entry_author_id = a.author_id
 			ORDER BY entry_created_on DESC LIMIT 25";
 			$result = mQuery($sql);
 			while ($row = mysql_fetch_assoc($result)) {
 				$ts = strtotime($row[entry_created_on]);
 				$y = date('Y', $ts);
 				$m = date('m', $ts);
 				$entry = getEntryInfo($row[entry_blog_id]);
 	
 				$entry[date] = getDateString($ts);
 				$entry[link] = "/$entry[blog_dir]/$y/$m/". dirify($row[entry_title]) .".php";
 				$entry[title] = $row[entry_title];
 				$entry[author] = $row[author_name];

 				echo '<li><a href="'. $entry[link] .'">'. $entry[title] .'</a> - <span class="grey">'. $entry[author] .'</span> - '. $entry[date] .'</li>';
 			}
 		?></ul><span class="corners-bottom"><span></span></span></div>
			
 		<?if (7 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 7
 				AND entry_id <> 264
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'Dale Cripps'
 			ORDER BY entry_created_on DESC LIMIT 10";
 			$result = mQuery($qry);
 			
 			if (mysql_num_rows($result) > 0) {
 				$row = mysql_fetch_assoc($result);
 				echo '<div class="item"><span class="corners-top"><span></span></span>'.
 				'<h2><a href="/author.php?author='. urlencode($row[author_name]) .'&id='. $row[author_id] .'">More from '. $row[author_name] .'</a></h2><ul>';
 				mysql_data_seek($result, 0);
 				while ($row = mysql_fetch_assoc($result)) {
 					# Get categories
 					$sql = "
 					SELECT category_label FROM mt_category c, mt_placement p
 					WHERE $row[entry_id] = p.placement_entry_id
 						AND c.category_id = p.placement_category_id";
 					$res_categories = mQuery($sql);
 					$row_categories = mysql_fetch_assoc($res_categories);
 					$category = $row_categories[category_label];

 					$ts = strtotime($row[entry_created_on]);
 					$y = date('Y', $ts);
 					$m = date('m', $ts);
 					$blog_dir = getBlogDir($row[entry_blog_id]);
 					$date = getDateString($ts);
 					$link = "/$blog_dir/$y/$m/". dirify($row[entry_title]) .".php";
 					echo '<li><a href="'. $link .'">'. $row[entry_title] .'</a> - <span class="grey">'. $category .'</span> - '. $date .'</li>';
 				}
 				echo '</ul><span class="corners-bottom"><span></span></span></div>';
				}
			}

 		if ($author[bio_short] != '') {?>
 			<div class="item"><span class="corners-top"><span></span></span>
 				<h2>About Dale Cripps</h2>
 				<?=stripslashes($author[bio_short])?>
 			<span class="corners-bottom"><span></span></span></div>
 		<?}?>
		</td><td id="right">

 		<?if ($about != '') {?>
 			<div class="item"><span class="corners-top"><span></span></span>
 				<h2>About Bulletins</h2>
 				<?=$about?>
 			<span class="corners-bottom"><span></span></span></div>
		<?}?>
		
 		<div class="item"><span class="corners-top"><span></span></span>
 			<h2><a href="/forum/index.php">Other Recent Discussion</h2><ul class="brownsquare"><?
 				$qry = "
 				SELECT topic_title, t.topic_id, username as post_author, post_time, post_id
 				FROM phpbb_topics t, phpbb_users u, phpbb_posts p
 				WHERE
 					t.forum_id NOT IN (". EXCLUDE_FORUMS .")
 					AND p.poster_id = u.user_id
 					AND t.topic_id = p.topic_id
 					AND t.topic_last_post_id = p.post_id
 				ORDER BY post_time DESC LIMIT 10";
 				$result = mQuery($qry);
 				
 				while ($row = mysql_fetch_assoc($result)) {
   					$last_post = date('n/j g:ia T', $row[post_time]);
   					$title = html_entity_decode($row[topic_title]);
 		  					
 					echo '<li><a href="'. FULL_URL_FORUM_VIEWTOPIC .'?t='. $row[topic_id] .'">'. $title .'</a> - <span class="grey">'. $row[post_author] .'</span> - '. $last_post .'</li>';
 				}
 			?></ul>
 		<span class="corners-bottom"><span></span></span></div>

 		<div class="item"><span class="corners-top"><span></span></span>
 			<h2>Authors</h2>
 			<ul class="brownsquare"><?
 				$qry = "
 				SELECT author_id, author_name, COUNT(*) num
 				FROM mt_author a, mt_entry e
 				WHERE e.entry_blog_id IN (". INCLUDE_BLOGS_NO_BULLETINS .")
 					AND entry_status = 2
 					AND entry_author_id = author_id
 				GROUP BY author_id, author_name
 				ORDER BY num DESC";
 				$res_authors = mQuery($qry);
 				while ($row_authors = mysql_fetch_assoc($res_authors)) {
 					echo '<li><a href="/author.php?author='. urlencode($row_authors[author_name]) .'&id='. $row_authors[author_id] .'">'. $row_authors[author_name] .'</a><span class="grey"> ('. $row_authors[num] .')</span></li>';
 				}
 			?></ul>
 		<span class="corners-bottom"><span></span></span></div>

 		<div class="item"><span class="corners-top"><span></span></span>
 			<h2>Categories</h2>
 			<ul class="brownsquare"><?
 				$qry = "
 				SELECT category_label label, COUNT(*) num
 				FROM mt_entry e, mt_placement p, mt_category c
 				WHERE e.entry_blog_id IN (". INCLUDE_BLOGS_ALL .")
 					AND entry_status = 2
 					AND entry_id = p.placement_entry_id
 					AND p.placement_category_id = c.category_id
 				GROUP BY label
 				ORDER BY label";
 				$result = mQuery($qry);
 				while ($category = mysql_fetch_assoc($result)) {
 					echo '<li><a href="/category.php?category='. urlencode($category[label]) .'">'. $category[label] .'</a><span class="grey"> ('. $category[num] .')</span></li>';
 				}
 			?></ul>
 		<span class="corners-bottom"><span></span></span></div>

		</td>
	</tr></table>

	<?
		include(BASE_DIR .'/includes/body_footer.php');
	?>
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2005/11/displaysearch_forecasts_3b_market_for_fpd_public_displays_by_2009_pdps_out-ship_lcds_3_to_1_in_q205.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
