<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	$debug = isset($_GET['debug']);

	function getByPGMasterID($masterid) {
		global $admindata, $debug;

		# First check to see if it's in the database
		$sql_pg = "SELECT * FROM pg_main WHERE masterid = '$masterid'";
		$res_pg = mQuery($sql_pg);
		if (mysql_num_rows($res_pg) != 0) return mysql_fetch_assoc($res_pg);

		# Not found ... try to get dynamically
		$pg_request_url = "http://ah.pricegrabber.com/search_xml.php?pid=718&key=7e084a24802&version=2.14&upc=1&spec=2&offers=1&masterid=$masterid";
		$contents = file_get_contents($pg_request_url);
		$xml = new SimpleXMLElement( $contents );

		$pg_main = array();
		$product = $xml->product;
		$pg_main['url'] = $product->url;
		$pg_main['masterid'] = $product->masterid;
		$pg_main['title'] = $product->title;
		$pg_main['image_small'] = $product->image_small;
		$pg_main['image_medium'] = $product->image_medium;
		$pg_main['image_large'] = $product->image_large;
		$pg_main['image_160'] = $product->image_160;
#		$pg_main['reative_rank'] = $product->reative_rank;
		$pg_main['manufacturer'] = $product->manufacturer;
		$pg_main['partnum'] = $product->partnum;
		$pg_main['upc'] = $product->upc;
#		$pg_main['price'] = $product->masterid;
		$pg_main['price_formatted'] = $product->price;
		$pg_main['sellers'] = $product->num_sellers[0];
		$pg_main['rating'] = $product->rating;
		$pg_main['num_reviews'] = $product->num_reviews;

		# Update pg_main
		$sql = "REPLACE INTO pg_main (". join(", ", array_keys($pg_main)) .", date_updated) VALUES ('". join("', '", array_values($pg_main)) ."', NOW())";
		if ($debug) {echo "$sql\n";} else {mQuery($sql);}

		# Now that we KNOW it's in the database ...
		$res_pg = mQuery($sql_pg);
		return mysql_fetch_assoc($res_pg);
	}

	function getByASIN($asin) {
		global $admindata, $debug;

		# First check to see if it's in the database
		$sql_amazon = "SELECT * FROM az_main m, az_attributes a WHERE m.ASIN = '$asin' AND m.ASIN = a.ASIN";
		$res_amazon = mQuery($sql_amazon);
		if (mysql_num_rows($res_amazon) != 0) return mysql_fetch_assoc($res_amazon);

		# Not found ... try to get dynamically
		$parameters = "AWSAccessKeyId={$admindata['amazon_access_key']}".
		"&AssociateTag={$admindata['amazon_associates_id']}".
		"&ItemId=$asin".
		"&Operation=ItemLookup".
		"&ResponseGroup=ItemAttributes,OfferSummary,Images".
		"&Service=AWSECommerceService".
		"&Timestamp=". gmdate("Y-m-d\TH:i:s\Z") .
		"&Version=2009-11-01";
		$parameters = str_replace(array(':',','), array('%3A','%2C'), $parameters);

		$signature = base64_encode(hash_hmac("sha256", "GET\nwebservices.amazon.com\n/onca/xml\n$parameters", $admindata['amazon_secret_access_key'], true));
		$signature = str_replace(array('+','='), array('%2B','%3D'), $signature);
		$signed_request = "http://webservices.amazon.com/onca/xml?{$parameters}&Signature=$signature";

		$contents = file_get_contents($signed_request);
		$xml = new SimpleXMLElement( $contents );

		# Verify a successful request
		if (is_object($xml->OperationRequest->Errors->Error)) {
			foreach($xml->OperationRequest->Errors->Error as $error) {
				$err = "Error {$error->Code}: (line ". __LINE__ .") $error->Message\n";
				mail("shane@hdtvmagazine.com", "ERROR: "& $_SERVER['SCRIPT_URL'], $err);
				return '';
			}
		}

		$az_main = array();
		$az_attributes = array();
		$item = $xml->Items->Item;
		$az_main['ASIN'] = $item->ASIN;
		$az_main['DetailPageURL'] = $item->DetailPageURL;

		if (is_object($item->ItemAttributes)) { # Update az_item_attributes
			$az_attributes['ASIN'] = $item->ASIN;
			$az_attributes['Brand'] = $item->ItemAttributes->Brand;
			$az_attributes['EAN'] = $item->ItemAttributes->EAN;
			$az_main['Weight'] = $item->ItemAttributes->ItemDimensions->Weight;
#			$az_main['Label'] = $item->ItemAttributes->Label;
			$az_main['ListPrice'] = $item->ItemAttributes->ListPrice->Amount;
			$az_main['ListPriceFormatted'] = $item->ItemAttributes->ListPrice->FormattedPrice;
			$az_attributes['Manufacturer'] = $item->ItemAttributes->Manufacturer;
			$az_attributes['Model'] = $item->ItemAttributes->Model;
			$az_attributes['MPN'] = $item->ItemAttributes->MPN;
			$az_attributes['ProductGroup'] = $item->ItemAttributes->ProductGroup;
#			$az_main['ProductTypeName'] = $item->ItemAttributes->ProductTypeName;
			$az_attributes['Publisher'] = $item->ItemAttributes->Publisher;
			$az_attributes['Studio'] = $item->ItemAttributes->Studio;
			$az_attributes['Title'] = $item->ItemAttributes->Title;
			$az_attributes['UPC'] = $item->ItemAttributes->UPC;
		}

		if (is_object($item->OfferSummary)) { # Update az_main (might want to put into a separate table)
			$az_main['TotalNew'] = $item->OfferSummary->TotalNew;
			if ($az_main['TotalNew'] > 0) {
				$az_main['LowestNewPrice'] = $item->OfferSummary->LowestNewPrice->Amount;
				$az_main['LowestNewPriceFormatted'] = $item->OfferSummary->LowestNewPrice->FormattedPrice;
			}
			$az_main['TotalUsed'] = $item->OfferSummary->TotalUsed;
			if ($az_main['TotalUsed'] > 0) {
				$az_main['LowestUsedPrice'] = $item->OfferSummary->LowestUsedPrice->Amount;
				$az_main['LowestUsedPriceFormatted'] = $item->OfferSummary->LowestUsedPrice->FormattedPrice;
			}
			$az_main['TotalRefurbished'] = $item->OfferSummary->TotalRefurbished;
			if ($az_main['TotalRefurbished'] > 0) {
				$az_main['LowestRefurbishedPrice'] = $item->OfferSummary->LowestRefurbishedPrice->Amount;
				$az_main['LowestRefurbishedPriceFormatted'] = $item->OfferSummary->LowestRefurbishedPrice->FormattedPrice;
			}
		}

		if (is_object($item->SmallImage)) { # Update az_main (might want to put images in separate table)
			$az_main['SmallImageURL'] = $item->SmallImage->URL;
			$az_main['SmallImageHeight'] = $item->SmallImage->Height;
			$az_main['SmallImageWidth'] = $item->SmallImage->Width;
		}
		if (is_object($item->MediumImage)) { # Update az_main (might want to put images in separate table)
			$az_main['MediumImageURL'] = $item->MediumImage->URL;
			$az_main['MediumImageHeight'] = $item->MediumImage->Height;
			$az_main['MediumImageWidth'] = $item->MediumImage->Width;
		}

		# Update az_main
		$sql = "REPLACE INTO az_main (". join(", ", array_keys($az_main)) .", date_updated) VALUES ('". join("', '", array_values($az_main)) ."', NOW())";
		if ($debug) {echo "$sql\n";} else {mQuery($sql);}

		# Update az_attributes
		$sql = "REPLACE INTO az_attributes (". join(", ", array_keys($az_attributes)) .") VALUES ('". join("', '", array_values($az_attributes)) ."')";
		if ($debug) {echo "$sql\n";} else {mQuery($sql);}

		# Now that we KNOW it's in the database ...
		$res_amazon = mQuery($sql_amazon);
		return mysql_fetch_assoc($res_amazon);
	}

	# Get author information
	$res_author = mQuery("SELECT title, channel, amazon_tracking_id, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Tom Starner'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Tom Starner" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 525 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 525
		AND a.topic_id = t.topic_id";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	# Get Comments
	if ($row_aux['topic_replies'] > 0) {
		$comments = '<li class="li_horizontal"><img src="'. BASE_IMG_URL .'/images/icon_comments.gif" alt="" height="14" width="16" /> '.
		'<a href="/forum/viewtopic.php?t='. $row_aux['topic_id'] .'">'. $row_aux['topic_replies'] .' Comments</a></li>';
	} else {
		$comments = '<li class="li_horizontal"><img src="'. BASE_IMG_URL .'/images/icon_comments.gif" alt="" height="14" width="16" /> '.
		'<a class="red" href="/forum/viewtopic.php?t='. $row_aux['topic_id'] .'">Post First Comment</a></li>';
	}

	# Get Pricegrabber info
	if ($row_aux['pg_masterid'] != '') $row_pg = getByPGMasterID($row_aux['pg_masterid']);
	if ($row_pg != '') {
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=DIRECTV\'s HR20 - DVR Debate Rages On&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
		$pg_url = $row_pg['url'];
		$pg_price = $row_pg['price_formatted'];
	}

	# Get Amazon info
	if ($row_aux['ASIN'] != '') $row_amazon = getByASIN($row_aux['ASIN']);
	if ($row_amazon != '') {
		$az_url = str_replace($admindata['amazon_associates_id'], $amazon_tracking_id, $row_amazon['DetailPageURL']);
		$az_image = '<img src="'. $row_amazon['MediumImageURL'] .'" alt="'. $row_amazon['Title'] .'" style="keyimg" height="'. $row_amazon['MediumImageHeight'] .'" width="'. $row_amazon['MediumImageWidth'] .'"/>';
		$review_header = <<<EOT
<table align="center" class="greygrid"><!--tr>
	<td style="font-weight:bold; text-align:center;" colspan="4">{$row_amazon['Title']}</td></tr>
<tr-->
	<td class="greygrid">&nbsp;</td>
	<td class="greygrid"><b>List</b></td>
	<td class="greygrid"><b>Street</b></td>
	<td class="greygrid"><b>Amazon.com</b></td>
</tr><tr>
	<td class="greygrid"><b>Current Pricing</b></td>
	<td class="greygrid">{$row_amazon['ListPriceFormatted']}</td>
	<td class="greygrid"><a href="$pg_url" target="_blank">$pg_price</a></td>
	<td class="greygrid"><a href="$az_url" target="_blank">{$row_amazon['LowestNewPriceFormatted']}</a></td>
</tr></table>
EOT;
	}

	# Set defaults which may be overridden by blog type below
	$container = 'article_container';
	$meta_medium_type = 'blog';
	$link_rel_image_src = $row_amazon['SmallImageURL'];
	$v_buttons = <<<EOT
<div id="dd_right"><ul>
	<li class="li_vertical">
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/articles/2007/01/directvs-hr20-dvr-debate-rages-on.php&amp;title=DIRECTV's HR20 - DVR Debate Rages On">
		<span style="display:none">As I write this, the negative/problem posts continue to stack up on &lt;a href=&quot;http://www.hdtvmagazine.com/cgi-bin/ntlinktrack.cgi?http://www.dbstalk.com/&quot;&gt;DBSTalk.com&lt;/a&gt;, a Web forum devoted to satellite TV and its users. Since late summer, that particular forum has been ground zero for a major &quot;debate&quot; of sorts, one that, in essence, separated DIRECTV defenders from detractors (though to be fair, not everyone who posts is firmly on one side or the other).</span></a>
	</li>
</ul></div>
EOT;
	$h_buttons = <<<EOT
<div id="dd_right"><ul>
	<li class="li_horizontal">$comments</li>
	<li class="li_horizontal" id="tm_li"></li>
	<li class="li_horizontal"><a name="fb_share" type="button_count" href="http://www.facebook.com/sharer.php"></a></li>
</ul></div>
EOT;

	switch (1) {
		case 1: # Articles
			$feed_name = 'hdtv-articles';
			$sub_type = SUB_ARTICLES;
			$sub_label = 'Receive instant notification of new articles';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="/profile-subscriptions.php">Modify your subscription profile</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="/profile-create.php">Register Now</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
			}
			break;
		case 4: # Interviews
			$feed_name = 'hdtv-interviews';
			break;
		case 5: # History
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Articles Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
			break;
		case 6: # Test
			$sub_type = 0;
			$sub_label = 'Receive instant notification of "Stuff"';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="/profile-subscriptions.php">Modify your subscription profile</a> to receive notification of "Stuff" via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="/profile-create.php">Register Now</a> to receive notification of "Stuff" via email as soon as they are published.';
			}
			break;
		case 7: # Bulletins
			$google_links_channel = ''; # Don't count bulletins
			$feed_name = 'hdtv-news';
			$container = 'bulletin_container';
			$author_headshot = '';
			$sub_type = SUB_BULLETINS;
			$sub_label = 'Receive instant notification of HDTV Bulletins';
			if ($userdata['session_logged_in']) {
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
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="/profile-subscriptions.php">Modify your subscription profile</a> to receive notification of new HDTV Magazine Reviews via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="/profile-create.php">Register Now</a> to receive notification of new HDTV Magazine Reviews via email as soon as they are published.';
			}
			break;
		case 9: # Podcasts
			# Get enclosure info
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 525";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download DIRECTV\'s HR20 - DVR Debate Rages On" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="DIRECTV\'s HR20 - DVR Debate Rages On" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
EOT;

			$sub_type = SUB_PODCAST;
			$sub_label = 'Receive instant notification of new episodes';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="/profile-subscriptions.php">Modify your subscription profile</a> to receive notification of new episodes of The HDTV Podcast via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="/profile-create.php">Register Now</a> to receive notification of new episodes of The HDTV Podcast via email as soon as they are published.';
			}
			$itunes_chicklet = BASE_IMG_HOST .'/images/chicklet-itunes.gif';
			# Need a better check here if we add other podcasts
			$contents = @file_get_contents('https://feedburner.google.com/api/awareness/1.0/GetFeedData?uri=hdtvpodcast');
			$xml = new SimpleXMLElement( $contents );

			$h_buttons = <<<EOT
<div id="dd_right"><ul>
	<li class="li_horizontal">$comments</li>
	<li class="li_horizontal" id="tm_li"></li>
	<li class="li_horizontal"><a name="fb_share" type="button_count" href="http://www.facebook.com/sharer.php"></a></li>
	<li class="li_horizontal">
		<span style="line-height:16px;vertical-align:middle;">{$xml->feed->entry['circulation']}
			<a href="http://click.linksynergy.com/fs-bin/click?id=FK62p2waXuc&subid=&offerid=146261.1&type=10&tmpid=1826&RD_PARM1=http%3A%2F%2Fphobos.apple.com%2FWebObjects%2FMZStore.woa%2Fwa%2FviewPodcast%3Fid%3D73799860" target="_blank"
				><img src="$itunes_chicklet" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="top" height="15" width="80"></a>
		</span>
	</li>
</ul></div>
EOT;

			break;
		case 10: # Columns
			$feed_name = 'hdtv-columns';
			$sub_type = SUB_COLUMNS;
			$sub_label = 'Receive instant notification of new columns';
			if ($userdata['session_logged_in']) {
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
	<title>HDTV Magazine - DIRECTV's HR20 - DVR Debate Rages On</title>
	<meta name="keywords" content="black screen, nationally version, released nationally, whatever reason, liberty media, directv, dvr, version, problems, issues, dbstalk, been, tivo, software, most, even, cnet, new, still, problem, good, downloads, users, debate, screen" />
	<meta name="description" content="As I write this, the negative/problem posts continue to stack up on &lt;a href=&quot;http://www.hdtvmagazine.com/cgi-bin/ntlinktrack.cgi?http://www.dbstalk.com/&quot;&gt;DBSTalk.com&lt;/a&gt;, a Web forum devoted to satellite TV and its users. Since late summer, that particular forum has been ground zero for a major &quot;debate&quot; of sorts, one that, in essence, separated DIRECTV defenders from detractors (though to be fair, not everyone who posts is firmly on one side or the other)." />
	<meta name="title" content="DIRECTV's HR20 - DVR Debate Rages On" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Articles Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />

	<script type="text/javascript">
		// Digg Script
		(function() {
			var s = document.createElement('SCRIPT'), s1 = document.getElementsByTagName('SCRIPT')[0];
			s.type = 'text/javascript';
			s.src = 'http://widgets.digg.com/buttons.js';
			s1.parentNode.insertBefore(s, s1);
		})();

		function init() {
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/articles/2007/01/directvs-hr20-dvr-debate-rages-on.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
		}

		function tweetMemeButton() {
			if (document.getElementById("tm_li")) {
				var iframeCode = '';
				iframeCode += '<iframe src="http://api.tweetmeme.com/button.js?url='+ escape(document.URL) +'&amp;style=normal&amp;source=SEOmofo&amp;service=bit.ly" scrolling="no" frameborder="0" width="50" height="61">';
				document.getElementById("tm_li").innerHTML = iframeCode;
			}
		}
		function getTMButton(url, style, source, service) {
			if (style == 'compact') {w = 70;h = 20;} else {w = 50;h = 61;}
			return '<iframe src="http://api.tweetmeme.com/button.js?url='+ escape(url) +'&amp;style='+ style +'&amp;source='+ source +'&amp;service='+ service +'" scrolling="no" frameborder="0" width="'+ w +'" height="'+ h +'">';
		}
	</script>
	<style>
		#dd_right {float:right;padding:2px;text-align:right;}
		#dd_right ul {padding:0;margin:0;}
		#dd_right ul li {list-style-image:none;list-style-position:outside;padding:4px;margin:0;outline:0 none;background-color:transparent;border:0 none;list-style-type:none;background-image:none;}
		#dd_right .li_horizontal {align:right;display:inline;float:left;font-weight:bold;margin-top:2px;padding:0 10px}
		#dd_right .li_vertical {display:block;list-style-type:none;}
/*		#dd_right img {border:none !important;}*/
		a.stbar.chicklet img {border:0;height:16px;width:16px;margin-right:3px;vertical-align:middle;}
		a.stbar.chicklet {height:16px;line-height:16px;}
	</style>
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=525', 340, 125);">Add ASIN</a>
				<span class="corners-bottom"><span></span></span></div>
			<? }?>

			<!-- Subscription box -->
			<? if ($sub_type > 0 && ($userdata['subscriptions'] & $sub_type)) {} else {?>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2007/01/directvs-hr20-dvr-debate-rages-on.php">DIRECTV's HR20 - DVR Debate Rages On</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Tom Starner</b> on <b>January 11, 2007</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=10&category=New Products & Equipment">New Products & Equipment</a></b>
							</td>
						</tr><tr colspan="2">
							<td id="article_buttons" colspan="2"><?=$h_buttons?></td>
						</tr></table>
					</td>
				</tr>
			</table>

			<!-- Main Article Body -->
			<div id="<?=$container?>">
				<?=$v_buttons?>
				<?=$review_header?>
				<?=$az_image?>
				<p><img src="/images/articles/hr20.gif" alt="DIRECTV HR20" align="left" /><b>The Debate</b><br />
As I write this, the negative/problem posts continue to stack up on <a href="http://www.hdtvmagazine.com/cgi-bin/ntlinktrack.cgi?http://www.dbstalk.com/">DBSTalk.com</a>, a Web forum devoted to satellite TV and its users. Since late summer, that particular forum has been ground zero for a major "debate" of sorts, one that, in essence, separated DIRECTV defenders from detractors (though to be fair, not everyone who posts is firmly on one side or the other).</p>

<p><b>The Issues</b><br />
Whether or not the HR20 DIRECTV Plus HD DVR, the satcaster's much-ballyhooed (and somewhat-delayed) flagship high-definition digital video recorder (DVR), is a dependable piece of equipment or a POS/POC (to use the euphemisms most bandied about by those having serious problems getting theirs to work reliably) - or something in between. Problem is, when a DVR is "something in between," it's probably not working as advertised. And that's certainly the case with the HR20 for some unknown but, judging by the extensive firmware downloads and plethora of posted problems, significant percentage of HR20 owners.</p>

<p>I admittedly fall into the DIRECTV detractor camp on this one (and I am an A+ DIRECTV sub, with a high monthly outlay and an 8-year history). Oddly enough, my HR20 is even currently working. For whatever reason (the HD Gods are smiling on me? Dumb luck?), my HR20 has worked for the past month or so after some early, and very irritating, problems. Oh, I still get the occasional video or audio dropout (a deal-breaker for my family, but more on that later). Other than that, it's doing what it is supposed to do. So why take a negative view of DIRECTV? Because for others posting on DBSTalk, the HR20 has been a "DVR from hell" experience, nothing less. And in response, DIRECTV has conducted itself extremely poorly on the customer service/tech support front.</p>

<p>There are those on DBSTalk.com who claim they have never had a single HR20 problem (and for reasons better left to a professional therapist, proceed to belittle others who have had problems for being "whiners " and/or "complainers"). Others give DIRECTV a pass because this is "bleeding edge" technology, so naturally some hiccups are to be expected. Another camp says complaining doesn't solve the problem, working to help DIRECTV fix things by reporting issues, etc., is the right-headed path. And my stance: No one buys (in this case, "leases") a $299 piece of home electronics gear that is clearly advertised as ready for prime time expecting to be a beta tester. A few minor early problems? Sure. But this mess? Not a chance.</p>

<p>Where to begin? How about there have been an amazing 14 software updates since Sept. 1 (see list below, which came directly from the DBSTalk.com forum). Of course, DIRECTV's defenders point to the software parade as proof that the satcaster cares. The detractors point to the list as strong evidence that DIRECTV doesn't know what it's doing when it comes to delivering a capable HD DVR (rather than letting someone else handle it, as they did with the first HD-DVR effort, the TiVo-powered HR10-250). Understand that unless you are plugged into the DBSTalk forum, the typical subscriber would have no idea that you received these software updates on their HR20 because they are delivered via satellite in the middle of the night. (How DBSTalk, a site not owned by DIRECTV, became the company's de facto clearinghouse for subscriber complaints, bug/data reporting, download information, etc., is another story, for another time.)</p>

<p><b>HR20 Revision History:</b><br />
Version 0x115 (1/8/07) <i>Limited release</i><br />
Version 0x10B (12/15/2006)<br />
Version 0x108 (12/12/2006) <i>Not released nationally</i><br />
Version 0x104 (12/06/2006) <i>Not released nationally</i><br />
Version 0xFA (11/22/2006)<br />
Version 0xF6 (11/21/2006) <i>Not released nationally</i><br />
Version 0xEF (11/15/2006)<br />
Version 0xEB (11/07/2006) <i>Not released nationally</i><br />
Version 0xE3 (10/19/2006)<br />
Version 0xDC (10/11/2006)<br />
Version 0xD8 (10/04/2006)<br />
Version 0xD1 (09/26/2006)<br />
Version 0xCC (09/16/2006)<br />
Version 0xBE (09/01/2006)</p>

<p>Granted, some of the revisions on the list added features (such as over-the-air functionality, which just became available on 12/15). But most have tried to fix ongoing issues that have nefarious names like "Black Screen of Death," "Unwatchable Bug," or the "Instant Keep or Delete Bug." There is even a newly posted "Catalog of HR20 Bugs" sticky thread on DBSTalk, and the list is impressive (if that's the right word). The acronym RBR (red button reboot) has become part of the HR20 lexicon, since it has been one of the only ways to get the box to behave (even though it doesn't always work).</p>

<p class="editorial"><b>Update</b>: On 1/8, DIRECTV released the latest&nbsp;(14th) software download. Based on early reports on DBSTalk, there are still issues with this release. However, it's "a release candidate" (DIRECTV's new strategy on the downloads: Make it voluntary and see how it goes. Then release it nationally when they think it's ready.) That recent strategy is an improvement, but past releases still had bugs despite the "test and report" process by willing subscribers (I'm not one of them). In other words, it has not made the HR20 stable for everyone, so the drama and debate continue.</p>

<p>The DIRECTV defenders say similar issues are reported on TiVo forums, and the DVRs provided by cable companies are no more reliable. But that's hardly consolation to subscribers who have had little or no problems with their SD DVRs (the ones not made by DIRECTV). I mean, try telling my wife and daughter that there will always be "growing pains" connected with new hardware, as they lose critical bits of dialogue from the latest Grey's Anatomy episode. Both refuse to even consider the HR20 after what they've seen in terms of audio/video dropouts, freezes and other glitches (they've stuck with the TiVo-based R10 SD recorders and refuse to even watch HD as a result - not a good thing for the HD industry in general, and DIRECTV in particular.). I want to be clear here: I like the HR20 when it works. No problem with the GUI. It delivers a very good picture. It even has a couple of advantages over the TiVo GUI. When working, the HR20 can be an excellent part of any HD lover's hardware arsenal. But, judging by the hostile reviews/feedback and multiple downloads (and my experience as well), that's been a big "if" for a significant number of users.</p>

<p>In particular, the HR20 is equipped to handle DIRECTV's new MPEG4 HD transmission of local channels in HD (as well as SD and MPEG2 HD). Prior to the HR20, subscribers who wanted to record HD signals from DIRECTV relied on the HR10-250, which captured MPEG2 HD signals as well as SD programming. Initially, that box was nearly $1,000, so cost kept it off my "must have" list. I stuck to SD recording via DIRECTV receivers equipped with TiVo, and for several years it was great, almost flawless.</p>

<p>But then, for whatever reason (and there is much speculation on why), DIRECTV decided to go it alone on the DVR front. After all, Rupert Murdoch's News Corp., which owned DIRECTV (it is now in control of John Malone's Liberty Media after a swap for stock and cash deal, which closes in mid-2007), also owns a company called NDS, which - surprise! - makes DVRs. But what might have sounded like a good idea on paper just hasn't panned out. There also is precedent for poor results on the NDS DVR front. DIRECTV has another non-TiVo DVR, the R15, that records only SD programming. According to many posters on DBSTalk (and some folks I know), the R15 is suffering from some of the same issues as the HR20, including missed recordings - and it's been on the market for more than a year! So it's not like this is the first DVR-building try at DIRECTV. I understand that the R15 and the HR20 are not made by the same subcontractors, nor do they share the same software code, but for whatever reason, subscribers are sharing some of the same headaches.</p>

<p>When local HD channels became available in my market this past summer (I had been a DIRECTV HD subscriber for 3-plus years, using a trusty non-DVR Zenith HD receiver pulling in the MPEG2 channels), I took the plunge and ordered a new 5LNB dish and the HR20. Mine arrived in early September, and the install eventually went smoothly. From day one, the HR20 acted screwy, freezing up unexpectedly and delivering the "black screen of death" regularly (it's akin to the old MS Windows blue screen of death, but it this case, instead of a recorded program, you got a black screen requiring an RBR). I was not a happy camper, but eventually the software downloads fixed things, on balance.</p>

<p><b>The Reviews</b><br />
This would all be very humorous, sort of a home entertainment Keystone Kops, if it weren't so infuriating for the owners who have had serious HR20 problems. To me, when you replace a very reliable piece of gear ("DirecTiVo", HR10-250, etc.) with a box promising to make things even better, then you'd better deliver at least the previous level of reliability. Of course, some new electronics products have their lemons (or issues), but the HR20 has become the undisputed unreliability champ, based on what is being reported at DBSTalk, at HDTV Magazine (see Ed Milbourn's critical October two-part review), user feedback (at CNET and big box retailer Circuit City), and on sites like tvpredictions.com.</p>

<p>A very interesting aspect of the entire debate raging over the HR20 can be found on <a href="/cgi-bin/ntlinktrack/cgi?http://reviews.cnet.com/DirecTV_HR20_DirecTV_Plus_HD_DVR/4505-6474_7-32065196.html">CNET.com</a>, which reviewed the box back in October - before most of the software downloads and the mounting, yet still unsolved, problems became widespread. The good folks at CNET, whose reviews don't always mesh with what their users have to say, gave the HR20 an "excellent" rating of 8.1 out of 10, based on one month of use. Of course, they luckily got one of the "good" HR20s (though they did report some issues, specifically "...We did experience a few snafus with the EPG [electronic program guide], but we expect DirecTV to work out those kinks soon."</p>

<p>Almost immediately, the negative user reviews on CNET started piling up like tires at a junkyard (the average user rating as of this writing is 4.2, with 107 users delivering feedback, myself included. I rated it a 5.5 based on reliability problems, but did say I liked it when it worked). That 4.2 average indicates a less than 50-50 satisfaction split, certainly no ringing endorsement of the HR20. In fact, the most recent wave of reviews are almost entirely negative (between December 14 and January 7, most of the scores are 3 or lower).</p>

<p>Back in early November, CNET took notice of the negative reviews and asked DIRECTV to explain. DIRECTV's carefully worded response offered some interesting language. In part, DIRECTV's CNET posting on 11/3/06 said...</p>

<blockquote>The vast majority of our HR20 customers have had the same good experience with their receivers as reported in the CNET review. They are happy with the performance of the HR20, its features, and functions.

<p>Some of the issues raised by CNET users were not all receiver-related, but involved a combination of out-of-spec HD signal feeds provided by local broadcast networks, our own broadcast configuration, and receiver software. These have been largely resolved and overall receiver performance is good and will continue to improve as we optimize its operation.</blockquote></p>

<p>Later, in the same statement, DIRECTV added:</p>

<blockquote>In summary, the HR20 is an outstanding product and demand is so high we are bringing a second manufacturer on-line.&nbsp; There have been issues since the launch in September--to be expected in the national rollout of any sophisticated consumer electronics product--but most of those issues have already been resolved and any remaining are being worked on aggressively. The fact is that the HR20 has had the smoothest launch of any of our previous DVRs including the TiVo HD DVR.

<p>The HR20 DirecTV Plus HD DVR is our flagship receiver and will be the launching pad for several new, exciting features and services in the coming year. We will not be satisfied until it is performing flawlessly.</blockquote></p>

<p>Uh-Huh. <i>Vast majority?</i> I'd love to get the actual numbers of problem HR20s (and the number of refurbished machines sent out as replacements).  Note: Some people reported that their replacement HR20s still had programming on them from former owners. In other words:Garbage in, garbage out.</p>

<p><i>The smoothest launch?</i> Somehow, based on the mounting evidence to the contrary, I seriously doubt it.</p>

<p><i>Most of the issues have been resolved?</i> Hardly. Remember, that statement was posted on Nov. 3! Of course, there are satisfied subs out there who have fared well with their HR20s, but even if only 15-20 percent are having the recurring reliability problems (downloads notwithstanding), it's still way too many (though my hunch is the number is higher).</p>

<p><b>DIRECTVs Response</b><br />
DIRECTV certainly has worked <i>aggressively</i>, but <i>effectively</i> would have been much better. A number of major bugs remain, and some users on DBSTalk report that while they initially had no problems, the latest download (Dec. 15) has caused new problems (geez, I hope my luck holds out).</p>

<p>Company spokesman Robert Mercer would only say DIRECTV is continuing to listen to customer feedback and improve the product via software downloads. "This is our workhorse set-top going forward, so we are committed to maximizing its performance," Mercer added via email. He did not respond a request for specific statistical data on problem HR20s, nor to questions about reliability, specific issues, etc.</p>

<p><b>Conclusion</b><br />
There's little doubt a significant number of subscribers will hit the boiling point (of course, some already have) if the HR20 isn't fixed real soon. I can only imagine the poor DIRECTV customer who never heard of an online forum or can't tell MPEG4 from MTV. He or she "leases" the HR20 for $299 (and don't forget the two-year commitment) and expects it to faithfully record the latest episode of Ugly Betty, just like the old "DirecTiVo". Instead, they get the ugly "keep or delete" message when they hit play (meaning they don't get to see the recording). Or, during playback, their HR20 doesn't respond to its remote control without an RBR. The list goes on and on.</p>

<p>No, DIRECTV clearly blew this one. Its aggressive "Let's get it fixed" stance notwithstanding (what else could it <i>really</i> do?), by not getting out in front of this situation, the company is seriously alienating a portion of the "big spender" segment of its subscriber base. Will those discouraged, frustrated HD subs stick around? Most probably will for now (not much real choice at this early point in HD recording history), but long-term, they will stay only if the HR20's performance really is "maximized" for everyone. In fact, forget maximized. How about getting it to work reliably as a DVR? Most people having problems could care less about video on demand, networking or other bells and whistles on the HR20. They just want the darn thing to record and play back their favorite shows!</p>

<p>Of course, it's nice to hear that Liberty Media (the new DIRECTV owner) had previously invested in TiVo, according to some recent reports. If nothing else, waiting to see what changes are brought about by Liberty Media running the show will be interesting. Despite its nine national firmware updates (with one pending), to date DIRECTV hasn't yet delivered a DVR that all of its subscribers can enjoy. And that's true no matter what side of the DBSTalk "debate" you find yourself on.</p>

<p class="editorial"><b>Postscript</b>: No sooner did I write the initial draft of this piece before the final weekend of regular season NFL games and my HR20 started acting flaky. When I turned off the power (in effect, putting it in standby) and turned it back on, I got only my local HD feeds and no other channels (only a black screen, one of a few "black screen" bugs). The "guide" and "info" functions still worked, but only an RBR got the picture back. Twice since then, I've also had a non-responsive remote (another RBR required), and finally, my trick play features (FF, Rew, etc.) started to act choppy, missing a lot of frames (very frustrating when trying to use them during an NFL game). Believe me,  I'd rather be wrong about DIRECTV on this one. But I'm afraid I'm not.</p>

<p>* DIRECTV is a registered trademarks of DIRECTV, Inc. TiVo is a registered trademarks of TiVo Inc. or its subsidiaries. All other trademarks and service marks are the property of their respective owners.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Tom Starner</b>, <b>January 11, 2007  6:41 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(525)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Tom Starner', 525)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author['bio_short'] != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Tom Starner</h2>
					<?=stripslashes($author['bio_short'])?>
				<span class="corners-bottom"><span></span></span></div>
			<? }?>
		</td><td id="right">
			<div align="center" style="margin:5px 0;">
				<? include(BASE_DIR .'/ads/mrectangle.php');?>
			</div>
			<br />

			<div align="right">
				<? include(BASE_DIR .'/ads/skyscraper.php');?>
			</div>

			<?=getBoxAuthors()?>

			<?=getBoxCategories()?>

			<?=getBoxDiscussions()?>
		</td>
	</tr></table><br />

	<? include(BASE_DIR .'/includes/body_footer.php');?>
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2007/01/directvs-hr20-dvr-debate-rages-on.php" type="text/javascript" charset="utf-8"></script>
	<script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"></script>
	<script type="text/javascript" src="http://w.sharethis.com/button/sharethis.js#publisher=3da06545-0753-46cb-8739-3ffcef208c1f&amp;type=website&amp;post_services=email%2Ctwitter%2Cdigg%2Cfacebook%2Cmyspace%2Csms%2Cdelicious%2Cstumbleupon%2Cgoogle_bmarks%2Clinkedin%2Cwindows_live%2Creddit%2Cbebo%2Cybuzz%2Cblogger%2Cyahoo_bmarks%2Cmixx%2Ctechnorati%2Cfriendfeed%2Cpropeller%2Cwordpress%2Cnewsvine%2Cxanga&amp;linkfg=%23003F87&amp;button=false"></script>
	<script type="text/javascript">
		var shared_object = SHARETHIS.addEntry({title: document.title,url: document.location.href});

		shared_object.attachButton(document.getElementById("ck_sharethis"));
		shared_object.attachChicklet("email", document.getElementById("ck_email"));
		shared_object.attachChicklet("facebook", document.getElementById("ck_facebook"));
		shared_object.attachChicklet("twitter", document.getElementById("ck_twitter"));
	</script>
</div></body>
</html>
