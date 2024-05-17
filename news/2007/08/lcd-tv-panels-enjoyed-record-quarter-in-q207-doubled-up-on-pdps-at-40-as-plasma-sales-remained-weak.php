<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	require(BASE_DIR .'/includes/lib_amazon.php');
	require(BASE_DIR .'/includes/lib_pg.php');

	$debug = isset($_GET['debug']);
	if ($debug) header('Content-type: text/plain');

	function getReviewHeader($asin, $amazon_tracking_id, $pg_url, $pg_price) {
		global $admindata, $debug;

		if ($asin != '') $row_amazon = getByASIN($asin);
		if ($row_amazon != '') {
			if ($debug) echo "amazon_tracking_id: $amazon_tracking_id<br />";
			if ($debug) echo "admin_data: {$admindata['amazon_associates_id']}<br />";
			$az_url = str_replace($admindata['amazon_associates_id'], $amazon_tracking_id, $row_amazon['DetailPageURL']);
			$az_image = ($row_amazon['MediumImageURL'] == '') ? '' : '<img src="'. $row_amazon['MediumImageURL'] .'" alt="'. $row_amazon['Title'] .'" height="'. $row_amazon['MediumImageHeight'] .'" width="'. $row_amazon['MediumImageWidth'] .'"/>';
			$az_product = $row_amazon['Manufacturer'] .' '. $row_amazon['Model'];
			$az_list = ($row_amazon['ListPriceFormatted'] == '') ? 'N/A' : $row_amazon['ListPriceFormatted'];
			$az_price = $row_amazon['LowestNewPriceFormatted'];
			$az_price = ($az_price == 'Too low to display') ? 'Unknown' : $az_price;

			if ($row_amazon['ProductGroup'] == 'DVD') {
				$releaseDate = date('M j, Y', strtotime($row_amazon['ReleaseDate']));
				$review_header .= <<<EOT
					<div class="item_review"><span class="corners-top"><span></span></span>
						<h2>{$row_amazon['Title']}</h2>
						<div class="image"><a href="$az_url">{$az_image}</a></div>
						<div class="text">
							<b>Studio:</b> {$row_amazon['Studio']}<br />
							<b>List Price:</b> {$az_list}<br />
							<b>Street Price:</b> <a href="{$pg_url}" target="_blank">{$pg_price}</a><br />
							<b>Amazon.com:</b> <a href="{$az_url}" target="_blank">{$az_price}</a><br />
							<b>Release Date:</b> $releaseDate<br />
							<b>Aspect Ratio:</b> {$row_amazon['AspectRatio']}<br />
							<b>Running Time:</b> {$row_amazon['RunningTime']} minutes<br />
						</div>
					<span class="corners-bottom"><span></span></span></div>
EOT;
			} else {
				$review_header .= <<<EOT
					<div class="item_review"><span class="corners-top"><span></span></span>
						<div class="image"><a href="$az_url">{$az_image}</a></div>
						<div class="text">
							<h2>{$row_amazon['Title']}</h2>
							<b>Manufacturer:</b> {$row_amazon['Manufacturer']}<br />
							<b>List Price:</b> {$az_list}<br />
							<b>Street Price:</b> <a href="{$pg_url}" target="_blank">{$pg_price}</a><br />
							<b>Amazon.com:</b> <a href="{$az_url}" target="_blank">{$az_price}</a><br />
						</div>
					<span class="corners-bottom"><span></span></span></div>
EOT;
			}

			return $review_header;
		}
	}

	# Get auxiliary information
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short, aux_e.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM mt_entry e, mt_author a, aux_author aux_a, aux_mt_entry aux_e
	LEFT JOIN phpbb3_topics t ON (aux_e.topic_id = t.topic_id)
	WHERE e.entry_id = aux_e.entry_id
		AND a.author_id = aux_a.author_id
		AND e.entry_author_id = a.author_id
		AND e.entry_id = 658";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Dale Cripps" height="100" width="100"/>';
	$author_bio = $author['bio_short'];
	$amazon_tracking_id = ($row_aux['amazon_tracking_id'] != '') ? $row_aux['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $row_aux['channel'];
	$viglink_source = $row_aux['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get Pricegrabber info
	if (intval($row_aux['pg_masterid']) > 0) $row_pg = getByPGMasterID($row_aux['pg_masterid']);
	if ($row_pg != '') {
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword='. urlencode($row_pg['title']) .'&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
		$pg_url = $row_pg['url'];
		$pg_price = $row_pg['price_formatted'];
	}

	# Get primary category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 658 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Dale Cripps" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 658 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 658";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	# Get Pricegrabber info
	if (intval($row_aux['pg_masterid']) > 0) $row_pg = getByPGMasterID($row_aux['pg_masterid']);
	if ($row_pg != '') {
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword='. urlencode($row_pg['title']) .'&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
		$pg_url = $row_pg['url'];
		$pg_price = $row_pg['price_formatted'];
	}
*/

	# Get Comments
	if ($row_aux['topic_replies'] > 0) {
#		$comments = '<li class="li_horizontal"><img src="'. BASE_IMG_URL .'/images/icon_comments.gif" alt="" height="14" width="16" /> '.
#		'<a href="/forum/viewtopic.php?t='. $row_aux['topic_id'] .'">'. $row_aux['topic_replies'] .' Comments</a></li>';
		$comments = '<img src="'. BASE_IMG_URL .'/images/icon_comments.gif" alt="" height="14" width="16" /> '.
		'<a href="/forum/viewtopic.php?t='. $row_aux['topic_id'] .'">'. $row_aux['topic_replies'] .' Comments</a>';
	} else {
#		$comments = '<li class="li_horizontal"><img src="'. BASE_IMG_URL .'/images/icon_comments.gif" alt="" height="14" width="16" /> '.
#		'<a class="red" href="/forum/viewtopic.php?t='. $row_aux['topic_id'] .'">Post First Comment</a></li>';
		$comments = '<img src="'. BASE_IMG_URL .'/images/icon_comments.gif" alt="" height="14" width="16" /> '.
		'<a class="red" href="/forum/viewtopic.php?t='. $row_aux['topic_id'] .'">Post First Comment</a>';
	}

	# Set defaults which may be overridden by blog type below
	$container = 'article_container';
	$meta_medium_type = 'blog';

	# Determine og type
	$og_type = 'article'; // Default
	if ($category_id == 295) $og_type = 'movie'; // Blu-ray
	if ($category_id == 296) $og_type = 'game'; // PlayStation 3 (PS3) & Xbox 360

	# Set image
	$link_rel_image_src = $row_aux['image_src'];
	if ($link_rel_image_src == '') $link_rel_image_src = $row_amazon['SmallImageURL'];

	$h_buttons = <<<EOT
<div align="right">
	<span style="float:left; padding-top:10px;">$comments</span>
	<span class='st_fblike_hcount' ></span>
	<span class='st_plusone_hcount' ></span>
	<span class='st_twitter_hcount' displayText='Tweet'></span>
	<span class='st_sharethis_hcount' displayText='ShareThis'></span>
</div>
EOT;
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2007/08/lcd-tv-panels-enjoyed-record-quarter-in-q207-doubled-up-on-pdps-at-40-as-plasma-sales-remained-weak.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (7) {
		case 1: # Articles
			$feed_name = 'hdtv-articles';
			$sub_type = SUB_ARTICLES;
			$sub_label = 'Receive instant notification of new articles';
			if ($user->data['is_registered']) {
				$sub_desc = '<a href="/profile-subscriptions.php">Modify your subscription profile</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="/profile-create.php">Register Now</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
			}
			break;
		case 4: # Interviews
			$feed_name = 'hdtv-interviews';
			break;
		case 5: # History
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
			break;
		case 6: # Test
			$sub_type = 0;
			$sub_label = 'Receive instant notification of "Stuff"';
			if ($user->data['is_registered']) {
				$sub_desc = '<a href="/profile-subscriptions.php">Modify your subscription profile</a> to receive notification of "Stuff" via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="/profile-create.php">Register Now</a> to receive notification of "Stuff" via email as soon as they are published.';
			}
			break;
		case 7: # Bulletins
			$google_links_channel = ''; # Don't count bulletins
			$viglink_source = ''; # Don't count bulletins
			$feed_name = 'hdtv-news';
			$container = 'bulletin_container';
			$author_headshot = '';
			$sub_type = SUB_BULLETINS;
			$sub_label = 'Receive instant notification of HDTV Bulletins';
			if ($user->data['is_registered']) {
				$sub_desc = '<a href="/profile-subscriptions.php">Modify your subscription profile</a> to receive notification of HDTV Bulletins via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="/profile-create.php">Register Now</a> to receive notification of HDTV Bulletins via email as soon as they are published.';
			}
			$meta_medium_type = 'news';
			break;
		case 8: # Reviews
			$feed_name = 'hdtv-reviews';
			$sub_type = SUB_REVIEWS;
			$sub_label = 'Receive instant notification of new reviews';
			if ($user->data['is_registered']) {
				$sub_desc = '<a href="/profile-subscriptions.php">Modify your subscription profile</a> to receive notification of new HDTV Magazine Reviews via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="/profile-create.php">Register Now</a> to receive notification of new HDTV Magazine Reviews via email as soon as they are published.';
			}
			break;
		case 9: # Podcasts
			# Get enclosure info
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 658";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download LCD TV Panels Enjoyed Record Quarter in Q2\'07, Doubled Up on PDPs at 40"+ as Plasma Sales Remained Weak" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="LCD TV Panels Enjoyed Record Quarter in Q2\'07, Doubled Up on PDPs at 40"+ as Plasma Sales Remained Weak" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="LCD TV Panels Enjoyed Record Quarter in Q2\'07, Doubled Up on PDPs at 40"+ as Plasma Sales Remained Weak" />
	<meta property="og:audio:artist" content="Ara Derderian &amp; Braden Russell" />
	<meta property="og:audio:album" content="The HDTV and Home Theater Podcast" />
	<meta property="og:audio:type" content="audio/mpeg" />
EOT;

			$sub_type = SUB_PODCAST;
			$sub_label = 'Receive instant notification of new episodes';
			if ($user->data['is_registered']) {
				$sub_desc = '<a href="/profile-subscriptions.php">Modify your subscription profile</a> to receive notification of new episodes of The HDTV Podcast via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="/profile-create.php">Register Now</a> to receive notification of new episodes of The HDTV Podcast via email as soon as they are published.';
			}
			$itunes_chicklet = BASE_IMG_HOST .'/images/chicklet-itunes.gif';

			# Need a better check here if we add other podcasts
//			$contents = @file_get_contents('https://feedburner.google.com/api/awareness/1.0/GetFeedData?uri=hdtvpodcast');
//			$xml = new SimpleXMLElement( $contents );

			$h_buttons = <<<EOT
<div id="dd_right">
	<span style="float:left; padding-top:10px;">$comments</span>
	<span class='st_fblike_hcount' ></span>
	<span class='st_plusone_hcount' ></span>
	<span class='st_twitter_hcount' displayText='Tweet'></span>
	<span class='st_sharethis_hcount' displayText='ShareThis'></span>
	<span>
		<span style="line-height:16px;vertical-align:middle;">{$xml->feed->entry['circulation']}
			<a href="http://click.linksynergy.com/fs-bin/click?id=FK62p2waXuc&subid=&offerid=146261.1&type=10&tmpid=1826&RD_PARM1=http%3A%2F%2Fphobos.apple.com%2FWebObjects%2FMZStore.woa%2Fwa%2FviewPodcast%3Fid%3D73799860" target="_blank"
				><img src="$itunes_chicklet" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="top" height="15" width="80"></a>
		</span>
	</span>
</div>
EOT;

			break;
		case 10: # Columns
			$feed_name = 'hdtv-columns';
			$sub_type = SUB_COLUMNS;
			$sub_label = 'Receive instant notification of new columns';
			if ($user->data['is_registered']) {
				$sub_desc = '<a href="/profile-subscriptions.php">Modify your subscription profile</a> to receive notification of new HDTV Magazine Columns via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="/profile-create.php">Register Now</a> to receive notification of new HDTV Magazine Columns via email as soon as they are published.';
			}
			$about = 'HDTV Magazine Columns are written by various personalities within the HDTV industry. They are typically shorter than our standard <a href="/articles">Article</a> and quite often express the opinion of the author(s). And of course, opinions expressed by these authors are not necessarily those of HDTV Magazine.';
			break;
		default:
			break;
	}

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - LCD TV Panels Enjoyed Record Quarter in Q2'07, Doubled Up on PDPs at 40"+ as Plasma Sales Remained Weak</title>
	<meta name="keywords" content="panel shipments, plasma panel, samsung sdi, record quarter, plasma sales, panel, panels, plasma, lcd, share, shipments, remained, increase, samsung, pdp, displaysearch, brands, growth, matsushita, enjoyed, sdi, record, suppliers, rising, rose" />
	<meta name="description" content="AUSTIN, TEXAS, July 31, 2007-DisplaySearch, the worldwide leader in flat panel display market research and consulting, reported in its latest Quarterly PDP Module and TV Shipment and Forecast ReportandWeekly TV Flash Report that plasma and LCD TV panel sales were headed in opposite directions in Q2'07.

LCD TV panel shipments enjoyed a record quarter in Q2'07, rising 32% Q/Q and 65% Y/Y to 19.6M panels.  The Q2'07 results exceeded suppliers' expectations and DisplaySearch's forecast by over 700K panels or 4%. Revenues grew 28% Q/Q and 39% Y/Y to a record $7.2B.  ASPs were only down 2% Q/Q and 16% Y/Y to $369. For the first time, twice as many 40&quot;+ LCD TV panels were shipped as 40&quot;+ plasma panels, as shown in Figure 1. The LCD TV share of the 40&quot;+ flat panel market rose from 42% in Q2'06 to 68% in Q2'07. This can be attributed to..." />
	<meta name="title" content="LCD TV Panels Enjoyed Record Quarter in Q2'07, Doubled Up on PDPs at 40&quot;+ as Plasma Sales Remained Weak" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="LCD TV Panels Enjoyed Record Quarter in Q2'07, Doubled Up on PDPs at 40&quot;+ as Plasma Sales Remained Weak" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2007/08/lcd-tv-panels-enjoyed-record-quarter-in-q207-doubled-up-on-pdps-at-40-as-plasma-sales-remained-weak.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="AUSTIN, TEXAS, July 31, 2007-DisplaySearch, the worldwide leader in flat panel display market research and consulting, reported in its latest Quarterly PDP Module and TV Shipment and Forecast ReportandWeekly TV Flash Report that plasma and LCD TV panel sales were headed in opposite directions in Q2'07.

LCD TV panel shipments enjoyed a record quarter in Q2'07, rising 32% Q/Q and 65% Y/Y to 19.6M panels.  The Q2'07 results exceeded suppliers' expectations and DisplaySearch's forecast by over 700K panels or 4%. Revenues grew 28% Q/Q and 39% Y/Y to a record $7.2B.  ASPs were only down 2% Q/Q and 16% Y/Y to $369. For the first time, twice as many 40&quot;+ LCD TV panels were shipped as 40&quot;+ plasma panels, as shown in Figure 1. The LCD TV share of the 40&quot;+ flat panel market rose from 42% in Q2'06 to 68% in Q2'07. This can be attributed to..." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=658', 400, 200);">Link Products</a>
				<span class="corners-bottom"><span></span></span></div>
			<? }?>

			<!-- Subscription box -->
			<? if ($sub_type > 0 && ($user->data['subscriptions'] & $sub_type)) {} else {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<img src="<?=BASE_IMG_HOST?>/images/i_inbox.gif" alt="" align="left" height="31" width="38" style="float:left; padding-right:10px" />
					<span class="label"><?=$sub_label?>:</span>
					<?=$sub_desc?>
				<span class="corners-bottom"><span></span></span></div>
			<? }?>

			<!-- Article Header -->
			<table class="bare" cellpadding="0" cellspacing="0" style="width:100%">
				<tr>
					<td id="article_headshot" rowspan="3"><?=$author_headshot?></td>
					<td>
						<table class="bare" cellspacing="0" style="width:100%"><tr>
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2007/08/lcd-tv-panels-enjoyed-record-quarter-in-q207-doubled-up-on-pdps-at-40-as-plasma-sales-remained-weak.php">LCD TV Panels Enjoyed Record Quarter in Q2'07, Doubled Up on PDPs at 40"+ as Plasma Sales Remained Weak</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>August  1, 2007</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=262&category=Business & Investment">Business & Investment</a></b>
							</td>
						</tr><tr colspan="2">
							<td id="article_buttons" colspan="2">
								<?=$h_buttons?>
							</td>
						</tr></table>
					</td>
				</tr>
			</table>

			<!-- Main Article Body -->
			<div id="<?=$container?>">
				<?=getReviewHeader($row_aux['ASIN'], $amazon_tracking_id, $pg_url, $pg_price)?>
				<!--?=$v_buttons?-->
				<p><html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns="http://www.w3.org/TR/REC-html40"></p>

<p><head><br />
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252"><br />
<link rel="File-List" href="new_page_1_files/filelist.xml"><br />
<title>New Page 1</title><br />
<style><br />
<!--<br />
p<br />
	{margin-right:0in;<br />
	margin-left:0in;<br />
	font-size:12.0pt;<br />
	font-family:"Times New Roman";<br />
	}<br />
span.title<br />
	{}<br />
 table.MsoNormalTable<br />
	{mso-style-parent:"";<br />
	font-size:10.0pt;<br />
	font-family:"Times New Roman";<br />
	}<br />
 p.MsoNormal<br />
	{mso-style-parent:"";<br />
	margin-bottom:.0001pt;<br />
	font-size:12.0pt;<br />
	font-family:"Times New Roman";<br />
	margin-left:0in; margin-right:0in; margin-top:0in}<br />
--><br />
</style><br />
<!--[if !mso]><br />
<style><br />
v\:*         { behavior: url(#default#VML) }<br />
o\:*         { behavior: url(#default#VML) }<br />
.shape       { behavior: url(#default#VML) }<br />
</style><br />
<![endif]--><!--[if gte mso 9]><br />
<xml><o:shapedefaults v:ext="edit" spidmax="1027"/><br />
</xml><![endif]--><br />
</head></p>

<p><body></p>

<p><span class="title">DisplaySearch Reports LCD TV Panels Enjoyed Record 
Quarter in Q2'07, Doubled Up on PDPs at 40&quot;+ as Plasma Sales Remained Weak
</span></p>
<p>AUSTIN, TEXAS, July 31, 2007-DisplaySearch, the worldwide leader in flat 
panel display market research and consulting, reported in its latest <em><b>
<a target="_blank" title="http://now.eloqua.com/e/er.aspx?s=488&amp;lid=54&amp;elq=E3DF1255F42445E5B55F15E2022183B9" style="color: blue; text-decoration: underline; text-underline: single" href="http://now.eloqua.com/e/er.aspx?s=488&lid=54&elq=E3DF1255F42445E5B55F15E2022183B9">
Quarterly PDP Module and TV Shipment and Forecast Report</a></b></em>and<em><b><a target="_blank" title="http://now.eloqua.com/e/er.aspx?s=488&amp;lid=117&amp;elq=E3DF1255F42445E5B55F15E2022183B9" style="color: blue; text-decoration: underline; text-underline: single" href="http://now.eloqua.com/e/er.aspx?s=488&lid=117&elq=E3DF1255F42445E5B55F15E2022183B9">Weekly 
TV Flash Report</a>&nbsp;</b></em>that plasma and LCD TV panel sales were headed in 
opposite directions in Q2'07. </p>
<p>LCD TV panel shipments enjoyed a record quarter in Q2'07, rising 32% Q/Q and 
65% Y/Y to 19.6M panels. &nbsp;The Q2'07 results exceeded suppliers' expectations and 
DisplaySearch's forecast by over 700K panels or 4%. Revenues grew 28% Q/Q and 
39% Y/Y to a record $7.2B.&nbsp; ASPs were only down 2% Q/Q and 16% Y/Y to $369. For 
the first time, twice as many 40&quot;+ LCD TV panels were shipped as 40&quot;+ plasma 
panels, as shown in Figure 1. The LCD TV share of the 40&quot;+ flat panel market 
rose from 42% in Q2'06 to 68% in Q2'07. This can be attributed to the amount of 
optimized Gen 7 and larger LCD fab capacity brought online, to the success of 
LCD TV brands and retailers in getting consumers excited over 1080p TVs where 
LCDs have dominated PDPs and to the greater number of LCD TV brands overwhelming 
plasma TV brands in retail. </p>
<p><strong>Figure 1: 40&quot;+ LCD TV and Plasma TV Panel Shipment Results</strong>
</p>
<p><!--[if gte vml 1]><v:shapetype id="_x0000_t75"
 coordsize="21600,21600" o:spt="75" o:preferrelative="t" path="m@4@5l@4@11@9@11@9@5xe"
 filled="f" stroked="f">
 <v:stroke joinstyle="miter"/>
 <v:formulas>
  <v:f eqn="if lineDrawn pixelLineWidth 0"/>
  <v:f eqn="sum @0 1 0"/>
  <v:f eqn="sum 0 0 @1"/>
  <v:f eqn="prod @2 1 2"/>
  <v:f eqn="prod @3 21600 pixelWidth"/>
  <v:f eqn="prod @3 21600 pixelHeight"/>
  <v:f eqn="sum @0 0 1"/>
  <v:f eqn="prod @6 1 2"/>
  <v:f eqn="prod @7 21600 pixelWidth"/>
  <v:f eqn="sum @8 21600 0"/>
  <v:f eqn="prod @7 21600 pixelHeight"/>
  <v:f eqn="sum @10 21600 0"/>
 </v:formulas>
 <v:path o:extrusionok="f" gradientshapeok="t" o:connecttype="rect"/>
 <o:lock v:ext="edit" aspectratio="t"/>
</v:shapetype><v:shape id="_x0000_s1025" type="#_x0000_t75" alt="" style='width:469.5pt;
 height:269.25pt'>
 <v:imagedata src="new_page_12_files/image001.jpg" o:href="http://img.en25.com/eloquaimages/clients/DisplaySearch/%7bd857f157-a33a-4f49-b3bb-327cdd13b83d%7d_lcdpdppanelshipment.jpg"/>
</v:shape><![endif]--><![if !vml]><img border=0 width=626 height=359
src="new_page_12_files/image001.jpg" v:shapes="_x0000_s1025"><![endif]></p>
<p>Of the total LCD TV panel shipments, the 40&quot;+ share rose from 22% in Q1'07 to 
23% in Q2'07 and the 1080p share of all LCD TV panel shipments rose from 10% to 
11%. Not all larger sized categories increased share in Q2'07; the 45-47&quot; 
category fell from 5.0% to 4.7% as prices remained too high for most consumers. 
32&quot; remained the dominant size category, earning a 38% share with OEMs and 
brands likely buying more 32&quot; panels than they needed as 32&quot; panel prices have 
been rising. LPL remained #1 on a unit basis followed by AUO and Samsung. On a 
revenue basis, Samsung remained #1 followed by LPL and AUO. </p>
<p>Plasma panel shipments were flat Q/Q and down 4% Y/Y to 2.3M panels. Year to 
date, plasma panel shipments are down 3% despite a 51% increase in capacity. 
Plasma panel manufacturers were expecting an increase of 18% to 2.7M panels, so 
clearly Q2'07 was disappointing. With plasma panel prices falling rapidly, 
plasma panel revenues were down 13% Q/Q and 37% Y/Y to $1.2B.&nbsp; ASPs were down 
13% Q/Q and 34% Y/Y to $507. </p>
<p>Despite the overall PDP decline, there was growth at 50&quot;+ and rapid growth in 
1080p panels. The 50&quot;+ share of PDP panel shipments continued to increase, 
rising from 29% in Q1'07 to 31% in Q2'07 on an 8% Q/Q increase and is expected 
to reach a 34% share in Q3'07 as PDP suppliers continue to shift their focus to 
larger sizes due to 42&quot; share losses to LCDs.&nbsp; There was also a dramatic 
increase in 1080p panel shipments, up 545% to 169K panels with 1080p penetration 
rising from 1.1% to 7.3%.&nbsp; The increase in 1080p shipments and improved supply 
should improve their competitive position against LCDs. </p>
<p>Matsushita remained #1 with Samsung SDI overtaking LGE for #2 as shown in 
Table 1. Of the five major suppliers, only Matsushita and Samsung SDI enjoyed 
Y/Y growth. By size, Matsushita led at 37&quot;, 42&quot;, 55-59&quot; and 60&quot;+ while Samsung 
SDI led at 50&quot;. 1080p panels accounted for an impressive 17% of Matsushita's 
mix, up from just 2% in Q1'07. </p>
<p><strong>Table 1:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Plasma Panel Share and Growth by Supplier </strong>
</p>
<div align="center">
	<table class="MsoNormalTable" border="1" cellspacing="0" cellpadding="0" width="100%" style="width: 100.0%" id="table1">
		<tr>
			<td style="padding: 0in; background: #333333">
			<p align="center" style="text-align:center"><strong><i>
			<span style="color:white">Supplier</span></i></strong><i><span style="color:white">
			</span></i></td>
			<td nowrap style="padding: 0in; background: #333333">
			<p align="center" style="text-align:center"><strong><i>
			<span style="color:white">Q1'07 Share </span></i></strong></td>
			<td nowrap style="padding: 0in; background: #333333">
			<p align="center" style="text-align:center"><strong><i>
			<span style="color:white">Q2'07 Share </span></i></strong></td>
			<td nowrap style="padding: 0in; background: #333333">
			<p align="center" style="text-align:center"><strong><i>
			<span style="color:white">Y/Y Growth </span></i></strong></td>
		</tr>
		<tr>
			<td nowrap valign="bottom" style="padding: 0in; background: white">
			<p align="center" style="text-align:center">Matsushita</td>
			<td nowrap valign="bottom" style="padding: 0in; background: white">
			<p align="center" style="text-align:center">31.3%</td>
			<td nowrap valign="bottom" style="padding: 0in; background: white">
			<p align="center" style="text-align:center">36.5%</td>
			<td nowrap valign="bottom" style="padding: 0in; background: white">
			<p align="center" style="text-align:center">14%</td>
		</tr>
		<tr>
			<td nowrap valign="bottom" style="padding: 0in; background: #999999">
			<p align="center" style="text-align:center">Samsung SDI</td>
			<td nowrap valign="bottom" style="padding: 0in; background: #999999">
			<p align="center" style="text-align:center">23.9%</td>
			<td nowrap valign="bottom" style="padding: 0in; background: #999999">
			<p align="center" style="text-align:center">27.4%</td>
			<td nowrap valign="bottom" style="padding: 0in; background: #999999">
			<p align="center" style="text-align:center">13%</td>
		</tr>
		<tr>
			<td nowrap valign="bottom" style="padding: 0in; background: white">
			<p align="center" style="text-align:center">LGE</td>
			<td nowrap valign="bottom" style="padding: 0in; background: white">
			<p align="center" style="text-align:center">27.1%</td>
			<td nowrap valign="bottom" style="padding: 0in; background: white">
			<p align="center" style="text-align:center">23.5%</td>
			<td nowrap valign="bottom" style="padding: 0in; background: white">
			<p align="center" style="text-align:center">-25%</td>
		</tr>
		<tr>
			<td nowrap valign="bottom" style="padding: 0in; background: #999999">
			<p align="center" style="text-align:center">Hitachi </td>
			<td nowrap valign="bottom" style="padding: 0in; background: #999999">
			<p align="center" style="text-align:center">10.4%</td>
			<td nowrap valign="bottom" style="padding: 0in; background: #999999">
			<p align="center" style="text-align:center">8.5%</td>
			<td nowrap valign="bottom" style="padding: 0in; background: #999999">
			<p align="center" style="text-align:center">-22%</td>
		</tr>
		<tr>
			<td nowrap valign="bottom" style="padding: 0in; background: white">
			<p align="center" style="text-align:center">Pioneer</td>
			<td nowrap valign="bottom" style="padding: 0in; background: white">
			<p align="center" style="text-align:center">7.2%</td>
			<td nowrap valign="bottom" style="padding: 0in; background: white">
			<p align="center" style="text-align:center">3.9%</td>
			<td nowrap valign="bottom" style="padding: 0in; background: white">
			<p align="center" style="text-align:center">-30%</td>
		</tr>
		<tr>
			<td nowrap valign="bottom" style="padding: 0in; background: #999999">
			<p align="center" style="text-align:center">Orion</td>
			<td nowrap valign="bottom" style="padding: 0in; background: #999999">
			<p align="center" style="text-align:center">0.1%</td>
			<td nowrap valign="bottom" style="padding: 0in; background: #999999">
			<p align="center" style="text-align:center">0.2%</td>
			<td nowrap valign="bottom" style="padding: 0in; background: #999999">
			<p align="center" style="text-align:center">20%</td>
		</tr>
		<tr>
			<td nowrap valign="bottom" style="padding: 0in; background: white">
			<p align="center" style="text-align:center">Total</td>
			<td nowrap valign="bottom" style="padding: 0in; background: white">
			<p align="center" style="text-align:center">100.0%</td>
			<td nowrap valign="bottom" style="padding: 0in; background: white">
			<p align="center" style="text-align:center">100.0%</td>
			<td nowrap valign="bottom" style="padding: 0in; background: white">
			<p align="center" style="text-align:center">-4%</td>
		</tr>
	</table>
</div>
<p>Looking forward, PDP suppliers are expecting over a 30% Q/Q increase to over 
3M panels in Q3'07. In order for this to happen, we would expect to see PDP TV 
brands make significant price moves within the next 60 days. </p>

<p><br />
DisplaySearch, an NPD Group company, has a core team of 46 employees located in <br />
Europe, North America and Asia who produce a valued suite of FPD-related market <br />
forecasts, technology assessments, surveys, studies and analyses. The company <br />
also organizes influential events worldwide. Headquartered in Austin, Texas, <br />
DisplaySearch has regional operations in Chicago, Houston, Kyoto, London, San <br />
Diego, San Jose, Seoul, Shenzhen, Taipei and Tokyo, and the company is on the <br />
web at<br />
<a title="http://now.eloqua.com/e/er.aspx?s=488&amp;lid=23&amp;elq=E3DF1255F42445E5B55F15E2022183B9" style="color: blue; text-decoration: underline; text-underline: single" href="http://now.eloqua.com/e/er.aspx?s=488&lid=23&elq=E3DF1255F42445E5B55F15E2022183B9"><br />
www.displaysearch.com</a>. </p><br />
 </p>

<p></body></p>

<p></html></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>August  1, 2007  7:11 AM</b>
					<span style="float:right">
						<span class='st_email' ></span>
						<span class='st_digg' ></span>
						<span class='st_facebook' ></span>
						<span class='st_twitter' ></span>
						<span class='st_sharethis' ></span>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(658)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 658)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Dale Cripps</h2>
					<?=stripslashes($author_bio)?>
				<span class="corners-bottom"><span></span></span></div>
			<? }?>
		</td><td id="right">
			<div align="center">
				<? include(BASE_DIR .'/ads/mrectangle.php');?>
			</div><br />

			<div align="center">
				<? include(BASE_DIR .'/ads/skyscraper.php');?>
			</div><br />

			<?=getBoxAuthors()?>

			<?=getBoxCategories()?>

			<?=getBoxDiscussions()?>

			<div align="right">
				<? include(BASE_DIR .'/ads/mrectangle.php');?>
			</div>
		</td>
	</tr></table><br />

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2007/08/lcd-tv-panels-enjoyed-record-quarter-in-q207-doubled-up-on-pdps-at-40-as-plasma-sales-remained-weak.php" type="text/javascript" charset="utf-8"></script>
	<script type="text/javascript">var switchTo5x=true;</script>
	<script type="text/javascript" src="http://w.sharethis.com/button/buttons.js"></script>
	<script type="text/javascript">
		stLight.options({
			publisher:'3da06545-0753-46cb-8739-3ffcef208c1f',
			embeds:'true',
			theme:'2'
		});
	</script>
</div></body>
</html>