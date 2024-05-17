<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 179";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 179 AND placement_is_primary = 1";
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
	<meta name="keywords" content="yahoo news, high definition, biz yahoo, articles viewarticle, business wire, news, digital, Digital, yahoo, new, hdtv, HDTV, News, New, television, articles, Yahoo, film, article, high, biz, index, home, technology, definition" />
	<meta name="description" content="Kagan Forecasts 50% of TV Households Will Have Digital TV by 2007 (Business Wire / Yahoo News) http://home.businesswire.com/portal/site/google/index.jsp?ndmViewId=news_view&amp;newsId=20050809006064&amp;newsLang=en http://biz.yahoo.com/bw/050809/96064.html?.v=1 Date Extended for Filing Conflict Decision Form 383 (TV Technology) http://www.tvtechnology.com/dlrf/one.php?id=961 According to In-Stat Digital Terrestrial TV and Free-to-Air Satellite Services..." />
	<title>HDTV Magazine Bulletins - More News July 10 - 11, 2005 from Lee Wood</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/more_news_july_10_-_11_2005_from_lee_wood';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('More News July 10 - 11, 2005 from Lee Wood'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2005/08/more_news_july_10_-_11_2005_from_lee_wood.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">More News July 10 - 11, 2005 from Lee Wood</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>August 11, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2005/08/more_news_july_10_-_11_2005_from_lee_wood.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2005/08/more_news_july_10_-_11_2005_from_lee_wood.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2005/08/more_news_july_10_-_11_2005_from_lee_wood.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2005/08/more_news_july_10_-_11_2005_from_lee_wood.php&amp;phase=2&amp;title=More%20News%20July%2010%20-%2011%2C%202005%20from%20Lee%20Wood&amp;bodytext=Kagan%20Forecasts%2050%25%20of%20TV%20Households%20Will%20Have%20Digital%20TV%20by%202007%20%28Business%20Wire%20%2F%20Yahoo%20News%29%20http%3A%2F%2Fhome.businesswire.com%2Fportal%2Fsite%2Fgoogle%2Findex.jsp%3FndmViewId%3Dnews_view%26newsId%3D20050809006064%26newsLang%3Den%20http%3A%2F%2Fbiz.yahoo.com%2Fbw%2F050809%2F96064.html%3F.v%3D1%20Date%20Extended%20for%20Filing%20Conflict%20Decision%20Form%20383%20%28TV%20Technology%29%20http%3A%2F%2Fwww.tvtechnology.com%2Fdlrf%2Fone.php%3Fid%3D961%20According%20to%20In-Stat%20Digital%20Terrestrial%20TV%20and%20Free-to-Air%20Satellite%20Services...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><strong>Kagan Forecasts 50% of TV Households Will Have Digital TV by 2007</strong><br />
(Business Wire / Yahoo News)</p>

<p><a href="http://home.businesswire.com/portal/site/google/index.jsp?ndmViewId=news_view&newsId=20050809006064&newsLang=en">http://home.businesswire.com/portal/site/google/index.jsp?ndmViewId=news_view&newsId=20050809006064&newsLang=en</a><br />
<a href="http://biz.yahoo.com/bw/050809/96064.html?.v=1">http://biz.yahoo.com/bw/050809/96064.html?.v=1</a><br />
 </p>

<p><strong>Date Extended for Filing Conflict Decision Form 383</strong><br />
(TV Technology)</p>

<p><a href="http://www.tvtechnology.com/dlrf/one.php?id=961">http://www.tvtechnology.com/dlrf/one.php?id=961</a><br />
 </p>

<p><strong>According to In-Stat Digital Terrestrial TV and Free-to-Air Satellite Services to Drive PC-TV Tuners</strong><br />
(Business Wire via Yahoo News)</p>

<p><a href="http://biz.yahoo.com/bw/050810/105081.html?.v=1">http://biz.yahoo.com/bw/050810/105081.html?.v=1</a><br />
 </p>

<p><strong>Gamers Watch Less TV -- But More HDTV</strong><br />
Ziff Davis study of video game players finds that they are spending increasingly little time in front of the tube.</p>

<p>(TVPredictions.com)</p>

<p><a href="http://www.tvpredictions.com/gamershdtv080905.htm">http://www.tvpredictions.com/gamershdtv080905.htm</a><br />
 </p>

<p><strong>Prices for flat-panel televisions drop sharply in 2Q05</strong><br />
PMA sell-through research finds flat-panel TV prices plummet as DLP&trade; and 3LCD RPTVs strike out for market share</p>

<p>(Infocomm.org)</p>

<p><a href="http://www.infocomm.org/index.cfm?objectID=AE80DD84-DA4F-4C77-AC2BB015DB80C9BB">http://www.infocomm.org/index.cfm?objectID=AE80DD84-DA4F-4C77-AC2BB015DB80C9BB</a><br />
  <br />
<strong>MSTV Receives 'Impressive Response' to Terrestrial D-A Converter Box RFQ</strong><br />
(TV Technology)</p>

<p><a href="http://www.tvtechnology.com/dlrf/one.php?id=962">http://www.tvtechnology.com/dlrf/one.php?id=962</a><br />
 </p>

<p><strong>New HD Site Launched By DMN</strong><br />
HDIssues.com addresses the rapid developments in HD technology and content production</p>

<p>(Broadcast Newsroom / HDTV Buyer)</p>

<p><a href="http://www.broadcastnewsroom.com/articles/viewarticle.jsp?id=34002">http://www.broadcastnewsroom.com/articles/viewarticle.jsp?id=34002</a><br />
<a href="http://www.hdtvbuyer.com/articles/viewarticle.jsp?id=34002">http://www.hdtvbuyer.com/articles/viewarticle.jsp?id=34002</a><br />
 </p>

<p> </p>

<p><strong>Experience colour like no other at home with Sony  [UK]</strong><br />
(Digital TV Group)</p>

<p><a href="http://griffin.dtg.org.uk/news/news.php?class=PR&subclass=&id=1068">http://griffin.dtg.org.uk/news/news.php?class=PR&subclass=&id=1068</a><br />
 </p>

<p><strong>UK Gov Looking To Subsidise Digital TV Transition via BBC?  [UK]</strong><br />
(Digital Lifestyles)</p>

<p><a href="http://www.digital-lifestyles.info/display_page.asp?section=platforms&id=2467">http://www.digital-lifestyles.info/display_page.asp?section=platforms&id=2467</a><br />
 </p>

<p><strong>Spain allocates new DTT licences  [Spain]</strong><br />
(Advanced Television)</p>

<p><a href="http://www.advanced-television.com/2005/news_archive_2005/Aug8_Aug12.htm#spainallo">http://www.advanced-television.com/2005/news_archive_2005/Aug8_Aug12.htm#spainallo</a></p>

<p><strong>August 11, 2005</strong></p>

<p><strong>NAB Wants Sooner DTV-Tuner Mandate</strong><br />
The NAB is urging the FCC to accelerate mandates for inclusion of over-the-air digital tuners in new TV sets. </p>

<p>(Multichannel News)</p>

<p><a href="http://www.multichannel.com/article/CA634076.html?display=Breaking+News&referral=SUPP">http://www.multichannel.com/article/CA634076.html?display=Breaking+News&referral=SUPP</a><br />
 </p>

<p><strong>Wolfsson's Wednesday Words (Mark's Monday Memo)</strong><br />
(Digital Television)</p>

<p><a href="http://www.digitaltelevision.com/mondaymemo/mlist/frm02195.html">http://www.digitaltelevision.com/mondaymemo/mlist/frm02195.html</a><br />
 </p>

<p><strong>Nearly 90% of All US Households to Have Digital TV by 2009</strong><br />
eMarketer Report Explores the Technologies and Dynamics That Will Change Broadcasting, Communications and Advertising Forever </p>

<p>(Market Wire / Yahoo News)</p>

<p><a href="http://www.marketwire.com/mw/release_html_b1?release_id=92865">http://www.marketwire.com/mw/release_html_b1?release_id=92865</a><br />
http://biz.yahoo.com/iw/050811/092865.html</p>

<p> </p>

<p><strong>Is your TV set for digital?</strong><br />
(Canton, OH Repository)</p>

<p><a href="http://www.cantonrep.com/index.php?Category=5&ID=236869&r=0">http://www.cantonrep.com/index.php?Category=5&ID=236869&r=0</a><br />
 <br />
<strong>Television Stations</strong><br />
High definition television, with its promise of sharper, movie- theater quality pictures, has slowly been entering the Richmond area. </p>

<p>(RedNova)</p>

<p><a href="http://www.rednova.com/news/display/?id=204268&source=r_technology">http://www.rednova.com/news/display/?id=204268&source=r_technology</a><br />
 </p>

<p><strong>KARE 11 Launches First Local Twenty-Four Hour Digital Weather Service</strong></p>

<p>(PR Newswire / Yahoo News)</p>

<p><a href="http://www.prnewswire.com/cgi-bin/stories.pl?ACCT=104&STORY=/www/story/08-10-2005/0004086239&EDATE=">http://www.prnewswire.com/cgi-bin/stories.pl?ACCT=104&STORY=/www/story/08-10-2005/0004086239&EDATE=</a></p>

<p><a href="http://biz.yahoo.com/prnews/050810/cgw040.html?.v=20">http://biz.yahoo.com/prnews/050810/cgw040.html?.v=20</a><br />
 </p>

<p><strong>HDFEST 2005 World Tour to Launch</strong><br />
South Florida Film Festival event is first stop</p>

<p>(Broadcast Newsroom / HDTV Buyer / HD Issues)</p>

<p><a href="http://www.broadcastnewsroom.com/articles/viewarticle.jsp?id=34030">http://www.broadcastnewsroom.com/articles/viewarticle.jsp?id=34030</a><br />
<a href="http://www.hdissues.com/articles/viewarticle.jsp?id=34030">http://www.hdissues.com/articles/viewarticle.jsp?id=34030</a><br />
http://www.hdtvbuyer.com/articles/viewarticle.jsp?id=34030</p>

<p> </p>

<p><strong>High Definition Tours World</strong><br />
HDFEST's "high-definition only" screenings will allow attendees the full experience of what high-definition and digital cinema currently have to offer audiences and will include a special selection of animation , documentaries, features and shorts.</p>

<p>(Digital Broadcasting)</p>

<p><a href="http://www.digitalbroadcasting.com/content/news/article.asp?DocID={3FA85319-778F-47DB-B998-ABED1897531C}&Bucket=Current+Headlines">http://www.digitalbroadcasting.com/content/news/article.asp?DocID={3FA85319-778F-47DB-B998-ABED1897531C}&Bucket=Current+Headlines</a><br />
 </p>

<p><strong>HDFEST 2005 World Tour Launches with South Florida Film Festival Event</strong><br />
<a href="http://">HDFEST will be launching its 2005 World Tour next month with a film festival event in South Florida September 9-10.</a></p>

<p>(Videography)</p>

<p><a href="http://www.videography.com/articles/article_13551.shtml">http://www.videography.com/articles/article_13551.shtml</a><br />
 </p>

<p> </p>

<p><strong>A Look Ahead at HDTV, Shot by You</strong><br />
(New York, NY Times)</p>

<p><a href="http://tech2.nytimes.com/2005/08/11/technology/circuits/11pogue.html">http://tech2.nytimes.com/2005/08/11/technology/circuits/11pogue.html</a><br />
 </p>

<p><strong>Australian Film Shot on JVC's GY-HD101E HD Camcorders</strong><br />
<a href="http://">The first film in the world to be shot with two JVC GY-HD101E ProHD cameras is now in production. The film entitled "Reality Check" stars Paul Mercurio (lead actor of Australian film, Strictly Ballroom) and Dieter Brenmar (actor in Australian television drama, Home and Away) is a kind of 'Survivor gone wrong' movie.</a><br />
(Videography)</p>

<p><a href="http://www.videography.com/articles/article_13553.shtml">http://www.videography.com/articles/article_13553.shtml</a><br />
 </p>

<p><strong>HDTV: Do your worst</strong><br />
(TV Squad)</p>

<p><a href="http://www.tvsquad.com/2005/08/10/hdtv-do-your-worst/">http://www.tvsquad.com/2005/08/10/hdtv-do-your-worst/</a><br />
 </p>

<p><strong>'Software guy' builds HDTV antennas out of frustration  [How Antennas Direct got started]</strong><br />
(St. Louis, MO Post-Dispatch)</p>

<p><a href="http://www.stltoday.com/stltoday/business/stories.nsf/story/09BE6D90BF616D338625705A000CC681?OpenDocument">http://www.stltoday.com/stltoday/business/stories.nsf/story/09BE6D90BF616D338625705A000CC681?OpenDocument</a></p>

<p> </p>

<p> </p>

<p><strong>Format fight </strong><br />
The peace talks are over - a war seems inevitable between the two formats competing to replace DVD to become the high definition (HDTV) pre-recorded/recordable disc standard.</p>

<p>(Guardian Weekly)</p>

<p><a href="http://www.guardian.co.uk/online/story/0,3605,1546157,00.html">http://www.guardian.co.uk/online/story/0,3605,1546157,00.html</a></p>

<p> </p>

<p><strong>DVD format war will rage for two years</strong><br />
Experts predict long battle for supremacy between Blu-ray and HD DVD</p>

<p>(vnunet.com)</p>

<p><a href="http://www.vnunet.com/vnunet/news/2140916/dvd-format-battle-gartner-two">http://www.vnunet.com/vnunet/news/2140916/dvd-format-battle-gartner-two</a><br />
 </p>

<p><strong>Consumers Reluctant To Buy High-Definition Media </strong></p>

<p>(TechWeb News via InternetWeek)</p>

<p><a href="http://www.internetweek.com/news/168600602">http://www.internetweek.com/news/168600602</a></p>

<p> </p>

<p> </p>

<p> </p>

<p><strong>JVC Commences Volume Production of New 0.7-Inch D-ILA Full HD Liquid Crystal Device</strong><br />
(PhysOrg)</p>

<p><a href="http://www.physorg.com/news5693.html">http://www.physorg.com/news5693.html</a><br />
 </p>

<p><strong>New Pioneer PureVision Plasma Televisions Make HDTV Look Picture Perfect</strong><br />
Enhanced Contrast Ratios Offer Better Black Level Than Ever Before </p>

<p>(Business Wire / Yahoo News / eCoustics.com)</p>

<p><a href="http://home.businesswire.com/portal/site/google/index.jsp?ndmViewId=news_view&newsId=20050810005669&newsLang=en">http://home.businesswire.com/portal/site/google/index.jsp?ndmViewId=news_view&newsId=20050810005669&newsLang=en</a><br />
http://biz.yahoo.com/bw/050810/105669.html?.v=1</p>

<p><a href="http://news.ecoustics.com/bbs/messages/10381/154055.html">http://news.ecoustics.com/bbs/messages/10381/154055.html</a><br />
 </p>

<p><strong>New Plasmas Set the Mark for Excellence in HDTV with Increased Contrast Ratio and Excellent Black Levels</strong><br />
(New Age Media Concepts)</p>

<p><a href="http://press.namct.com/content/view/2599/2/">http://press.namct.com/content/view/2599/2/</a><br />
 </p>

<p><strong>The Difference is Black and White with New Pioneer Elite PureVision Plasma Televisions</strong><br />
New Plasmas Set the Mark for Excellence in HDTV with Increased Contrast Ratio and Excellent Black Levels </p>

<p>(Business Wire / Yahoo News / eCoustics.com)</p>

<p><a href="http://home.businesswire.com/portal/site/google/index.jsp?ndmViewId=news_view&newsId=20050810005658&newsLang=en">http://home.businesswire.com/portal/site/google/index.jsp?ndmViewId=news_view&newsId=20050810005658&newsLang=en</a><br />
http://biz.yahoo.com/bw/050810/105658.html?.v=1</p>

<p><a href="http://news.ecoustics.com/bbs/messages/10381/154057.html">http://news.ecoustics.com/bbs/messages/10381/154057.html</a><br />
 </p>

<p><strong>Samsung's New Plasma TVs Target Specialty A/V Retailers With High-End Design</strong><br />
CES Innovations Award-Winning HP-R5072 Anchors New 72-Series, Which Offers 42", 50" and 63" Models Through Professional A/V Retail and Home Install Channels</p>

<p>(Widescreen Review)</p>

<p><a href="http://www.widescreenreview.com/news_detail.php?recid=10230">http://www.widescreenreview.com/news_detail.php?recid=10230</a><br />
 </p>

<p><br />
<strong>In-Stat: Digital TV services to drive PC-TV tuner sales</strong></p>

<p>(DigiTimes)</p>

<p><a href="http://www.digitimes.com/mobos/a20050811PR200.html">http://www.digitimes.com/mobos/a20050811PR200.html</a><br />
 </p>

<p><strong>Digital Terrestrial TV And Free-to-Air Satellite Services To Drive PC-TV Tuners</strong><br />
(Media Center PC World)</p>

<p><a href="http://www.mediacenterpcworld.com/news/479">http://www.mediacenterpcworld.com/news/479</a><br />
 </p>

<p><strong>TV on the PC Gets Real</strong></p>

<p>Time Warner launches trial in San Diego</p>

<p>(Broadcasting & Cable)</p>

<p><a href="http://www.broadcastingcable.com/article/CA632697.html?display=Feature&referral=SUPP">http://www.broadcastingcable.com/article/CA632697.html?display=Feature&referral=SUPP</a><br />
 </p>

<p><strong>DTV: Video for nothing and your flicks for free</strong><br />
(CNET Crave)</p>

<p><a href="http://crave.cnet.co.uk/software/0,39029471,39191476,00.htm">http://crave.cnet.co.uk/software/0,39029471,39191476,00.htm</a><br />
 </p>

<p> </p>

<p><strong>Live Mobile TV Broadcasts Of International Sporting Event</strong><br />
DVB-H technology allows television channels to be distributed effectively to users of mobile devices. All television channels and special event channels can be accessed by viewers as live broadcasts.</p>

<p>(Digital Broadcasting)</p>

<p><a href="http://www.digitalbroadcasting.com/content/news/article.asp?DocID={A757DB8D-D656-4F90-B750-F2246C3E64A9}&Bucket=Current+Headlines">http://www.digitalbroadcasting.com/content/news/article.asp?DocID={A757DB8D-D656-4F90-B750-F2246C3E64A9}&Bucket=Current+Headlines</a><br />
 </p>

<p> </p>

<p><strong>National Geographic plans UK HDTV channel  [UK]</strong><br />
(Digital Spy)</p>

<p><a href="http://www.digitalspy.co.uk/article/ds23356.html">http://www.digitalspy.co.uk/article/ds23356.html</a><br />
 </p>

<p><strong>Tibetan farmers and herdsmen enjoy "digital TV"  [Tibet]</strong><br />
(People's Daily)</p>

<p><a href="http://english.peopledaily.com.cn/200508/11/eng20050811_201717.html">http://english.peopledaily.com.cn/200508/11/eng20050811_201717.html</a><br />
 </p>

<p><strong>Gov't to allow 2 new BS digital HDTV stations  [Japan]</strong><br />
(Kyodo News via Yahoo News)</p>

<p><a href="http://asia.news.yahoo.com/050810/kyodo/d8bt0akg1.html">http://asia.news.yahoo.com/050810/kyodo/d8bt0akg1.html</a></p>

<p> </p>

<p><strong>Digital TV Markets  [Australia]</strong></p>

<p>When is digital television available in your area (updated 3 August 2005)</p>

<p>(Digital Broadcasting Australia)</p>

<p><a href="http://www.dba.org.au/index.asp?sectionID=22">http://www.dba.org.au/index.asp?sectionID=22</a><br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>August 11, 2005 09:34 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 179
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
 				AND entry_id <> 179
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2005/08/more_news_july_10_-_11_2005_from_lee_wood.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
