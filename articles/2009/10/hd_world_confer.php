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
	$res_author = mQuery("SELECT title, channel, amazon_tracking_id, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Rodolfo La Maestra" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3361 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 3361
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=HD World Conference in NY - 3D, IP Online Video, and Mobile DTV&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/articles/2009/10/hd-world-conference-in-ny-3d-ip-online-video-and-mobile-dtv.php&amp;title=HD World Conference in NY - 3D, IP Online Video, and Mobile DTV">
		<span style="display:none">The &lt;a href=&quot;http://www.hdworldshow.com/&quot;&gt;HD World Conference&lt;/a&gt; closed its doors this past Thursday October 15, 2009 at the Jacob Javits Convention Center in NY. It was a two day event dedicated to HD, from content creation to production, editing, and distribution.

One of my objectives for this conference was to update &lt;a href=&quot;/articles/2007/08/mobile_dtv_reception_advancedvestigial_sideband_avsb_the_system.php&quot;&gt;my coverage of Mobile DTV&lt;/a&gt; and &lt;a href=&quot;/articles/2007/09/iptv_part_1_read_the_fine_print.php&quot;&gt;IPTV&lt;/a&gt;, and to also discuss the technical details of 3D as it is being implemented for the home. I engaged in several discussions regarding the production and the distribution stages of 3D, with emphasis on the issues that affect the quality of the final viewing by consumers.

I also covered...</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3361";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HD World Conference in NY - 3D, IP Online Video, and Mobile DTV" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HD World Conference in NY - 3D, IP Online Video, and Mobile DTV" />
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
	<title>HDTV Magazine - HD World Conference in NY - 3D, IP Online Video, and Mobile DTV</title>
	<meta name="keywords" content="online video, mobile dtv, blu ray, main channel, high quality, image, quality, resolution, video, images, consumers, online, dtv, display, jvc, mobile, content, may, show, channel, home, etc, industry, broadcast, viewing" />
	<meta name="description" content="The &lt;a href=&quot;http://www.hdworldshow.com/&quot;&gt;HD World Conference&lt;/a&gt; closed its doors this past Thursday October 15, 2009 at the Jacob Javits Convention Center in NY. It was a two day event dedicated to HD, from content creation to production, editing, and distribution.

One of my objectives for this conference was to update &lt;a href=&quot;/articles/2007/08/mobile_dtv_reception_advancedvestigial_sideband_avsb_the_system.php&quot;&gt;my coverage of Mobile DTV&lt;/a&gt; and &lt;a href=&quot;/articles/2007/09/iptv_part_1_read_the_fine_print.php&quot;&gt;IPTV&lt;/a&gt;, and to also discuss the technical details of 3D as it is being implemented for the home. I engaged in several discussions regarding the production and the distribution stages of 3D, with emphasis on the issues that affect the quality of the final viewing by consumers.

I also covered..." />
	<meta name="title" content="HD World Conference in NY - 3D, IP Online Video, and Mobile DTV" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/articles/2009/10/hd-world-conference-in-ny-3d-ip-online-video-and-mobile-dtv.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3361', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2009/10/hd-world-conference-in-ny-3d-ip-online-video-and-mobile-dtv.php">HD World Conference in NY - 3D, IP Online Video, and Mobile DTV</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>October 30, 2009</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=349&category=3D HDTV">3D HDTV</a></b>, <b><a href="/category.php?id=337&category=Internet HD Video">Internet HD Video</a></b>, <b><a href="/category.php?id=364&category=Mobile HDTV">Mobile HDTV</a></b>
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
				<p>The <a href="http://www.hdworldshow.com/">HD World Conference</a> closed its doors this past Thursday October 15, 2009 at the Jacob Javits Convention Center in NY. It was a two day event dedicated to HD, from content creation to production, editing, and distribution.</p>

<p>Other than some 3D demos, very few displays were at the show. The show was predominantly attended by professionals involved in the areas mentioned above, and it was interesting to witness how the industry "handles" HD content before it gets to the home, not to mention how the industry is planning to handle 3D to make it fit the limitations of the current broadcast bandwidth.</p>

<p>One of my objectives for this conference was to update <a href="/articles/2007/08/mobile_dtv_reception_advancedvestigial_sideband_avsb_the_system.php">my coverage of Mobile DTV</a> and <a href="/articles/2007/09/iptv_part_1_read_the_fine_print.php">IPTV</a>, and to also discuss the technical details of 3D as it is being implemented for the home. I engaged in several discussions regarding the production and the distribution stages of 3D, with emphasis on the issues that affect the quality of the final viewing by consumers.</p>

<p>I also covered other areas, such as satellite transmission, MPEG-2/MPEG-4/JPEG-2000 compressions and conversions, film transfers to HD, film restoration for HD archival, HD content optimization, etc. but those will not be included in this article.</p>

<p>Some tracks were very well presented, and most were driven by panels composed of key professionals in their areas. As to be expected, I found more value on the presentations that focused on facts, such as the excellent 3D presentation by Michel Proulx, <a href="http://www.miranda.com/index.php?l=1">Miranda</a> CTO, rather than company/product presentations influenced by a promotional interest.</p>

<p><br />
<h2>IP Online Video</h2></p>

<p>A panel consisting of Comcast, Media Valuation Partners, and Canoe Ventures conducted a presentation to address how IP influenced the TV industry. Larry Gerbrant, Principal of Media Valuation Partners said that in 2008 40.5% of people watched online videos.</p>

<p>The total time people spend viewing online was reported as less impressive, he said. Online video represents only 1.1% per month of typical TV viewing. Mr. Gerbrant said that online video was typically viewed 500 minutes per month while TV was typically viewed 300 minutes per day, which is about 5.5% (the 1.1% he provided did not reconcile with my math, nor with the math of another panel member).</p>

<p>According to Mr. Gerbrant online video was projected to grow to 22% by 2020, and he added that within that growth some wildcards could affect his projection, such as 1) the proliferation of web enabled TVs which would allow people to access IP video from the same TV set with more flexibility, and 2) the online reading of magazines. He was not specific about how those two factors contributed to the 22% of his projection for 2020, nor did he give a clarification of any assumptions on those factors.</p>

<p>Contrary to the expectation that online video is being predominantly viewed by a young generation, his analysis was that the demographics of viewers rather pointed to people 25-35 years of age.</p>

<p>It was mentioned that there may be a relationship between the increase of online video viewing and the decrease of DVD sales, which was -25% this year, he said. Blu-ray sales may contribute to the decrease of DVD sales not bought by the growing number of Blu-ray player owners since 2006, when Blu-ray was introduced.</p>

<p>No detail was provided as to how the online video numbers are subdivided to identify HD content, which was disappointing, considering this was an HD Conference. Nor was any data provided about IP video delivered thru <a href="/articles/2007/09/iptv_part_1_read_the_fine_print.php">IPTV services</a> using dedicated closed networks (mentioned by Comcast briefly but not numerically supported) rather than online video on the open Internet, which other than some HD IP movie services is mostly not in HD.</p>

<p><br />
<h2>Mobile DTV</h2></p>

<p>Several presentations were made about this subject. Mr. John Taylor, Vice President of LG Electronics USA, opened a panel by saying that by the end of today (Oct 15) the ATSC was to approve the final standard for Mobile DTV and that would give a lot of energy to the mobile DTV industry.</p>

<p>Although the industry was already moving under some agreed upon conditions to plan for their manufacturing schedules of devices and chips, the final approval of today was said to give reassurance to many efforts that were underway. As is turned out, the DTV Mobile standard was actually <a href="/news/2009/10/with_standard_adopted_broadcasters_poised_to_bring_mobile_dtv_to_american_consumers.php">approved that night</a>.</p>

<p>Mr. Taylor showed a 7" screen DVD player capable of receiving mobile DTV and designed for rear seat car applications and other portable uses. He said the device will be introduced by LG at CES 2010 this coming January at Las Vegas at a price to be disclosed then. Mr. Taylor also showed a cell-size portable TV screen with a small antenna that will also be introduced at CES. He said it is difficult to quantify the price of such a device to consumers because phone companies such as AT&amp;T and Sprint typically offer phones at very low prices amortized over several years of service contracts.</p>

<p>At the conference it was mentioned that a typical DTV Mobile signal resolution would be QVGA. The Q refers to &frac14; or 'Quarter'-VGA.  So the picture resolution is 416 x 240 square pixels in 16:9 screen format. This is defined as an emission standard, not a receiver standard. As such, in any receiving device, the processing in that device could manipulate (increase or decrease) the pixel count believed to be appropriate to the display technology and presumed viewer/user needs. Mr. Taylor said that not one of the decision makers supported the 4:3 screen format based on the general direction of the industry with widescreen TVs and content.</p>

<p>It was also mentioned that the signal of a mobile DTV service is transmitted with 75% overhead so the system can make sure that a moving device would always find a signal to tune. The typical bandwidth requirement of content + overhead was estimated at 2Mbps, of which the actual image of content would use about 500 Kbps.</p>

<p>At the end of the presentation I opened a discussion about the gradual deterioration of the HD image since 1998. HD Broadcast stations initially used their 6MHz for a single HD channel but with time they gradually shifted to multicasting several SD sub-channels together with a severely compressed HD main channel. The HD channel was then forced to reduce its available bandwidth to much less than the typically required 19.4Mbps, and become subjected to unacceptable video artifacts.</p>

<p>Considering that each SD sub-channel could typically need about 2Mbps (perhaps less if the content is mostly made of static images/letters), a station that multicasts 3 SD channels could use a total of about 6Mbps out of bandwidth of the HD main channel (19.4Mbps - 6 = 13.4Mbps). If additionally the broadcast station takes another 2Mbps for DTV Mobile the HD main channel would be subjected to considerable degradation.</p>

<p>Subdividing the 6Mz channel slot for different purposes may be viewed as a good business opportunity for broadcasters, but quality degradation is of concern when consumers are enticed to invest in HDTVs for quality HD images not just DTV. Consumers are gradually purchasing larger and larger screens with more resolution which would make the compression artifacts of a degraded HD image more obvious.</p>

<p>To that criticism the panel members responded that new MPEG-2 encoders/transmitters claiming 20% efficiency were being installed at some of the broadcast stations, and current MPEG-2 receivers in consumer homes benefit from the improvement without any hardware or software changes. Such efficiency, they said, would represent about 2Mbps savings within the 6MHz channel slot and that could be used for Mobile DTV without further compressing the HD main channel.</p>

<p><br />
<h2>3D</h2></p>

<p>3D has been shown to the public for decades at the local theaters but it is relatively new for the home. This HD show covered the 3D subject from the content creation phase to the display at home, and many companies participated and demoed their products. </p>

<p>The way 3D is applied for the home today is limited by a number of technical factors that affect image quality and occur mainly in the last stages of the 3D image food chain, namely: content distribution to the home, and displays. </p>

<p>The subject of image quality is rarely mentioned by the press, and consumers deserve to be informed as to what to expect from a newly acquired 3D display before investing in the technology.</p>

<p>I will present the subject from a technical angle, not from the perceived impact of viewing a 3D image, which is often driven by the subjectivity of informal viewing during a couple of hours at local theaters. Although that viewing generally is reported as a positive experience by the public, it is a relatively reasonable return for the investment of a 3D theater ticket. Consider the return on investment of a <a href="http://www.engadget.com/2009/09/10/jvc-brings-46-inch-gd-463d10-3d-lcd-hdtv-to-america-shipping-no">$9,000 JVC LCD</a> to view a couple of movies a year on a 46" panel, and not even at 1080p full resolution per eye.</p>

<p>The momentum of rushing to 3D is also being reinforced by journalism that usually does not cover the technology limitations of transmitting and displaying 3D, and also because many consumers prefer not to listen to negative facts when decisions might have been made already based on perception and loose expectations.</p>

<p><br />
<h2>The 3D Expectation</h2></p>

<p>Companies like Mitsubishi, JVC, Hyundai, Panasonic, Sony, Samsung, etc. are gradually introducing displays claiming to have 3D capabilities. JVC and Hyundai showed 3D demos of their LCDs at the show. One common issue, the claim of "having 3D capabilities" should be more specific regarding the actual quality of the displayed image, not just how is perceived.</p>

<p>Unsuspected consumers accustomed to enjoy the quality of full HD images over the past decade of HDTV (and more recently in 1080p), may expect such level of quality with 3D as well, especially if they had a positive experience with 3D at the local theaters. </p>

<p>Judging by the higher light output and image punch of a good quality 1080p screen at home compared to the typical low lumens projection environment at a local theater, which is further reduced when wearing special 3D glasses, consumers may expect a high quality bright image at home. Can 3D deliver such expectation?</p>

<p><br />
<h2>Some 3D Implementations for the Home</h2></p>

<p>Recent conferences included demos of 3D HDTVs from JVC, Mitsubishi, Hyundai, etc that received a variety of positive and negative comments. At this HD conference I was "perceptually" pleased with the demo offered by the JVC 1080p LCD 60Hz display, even when I do not recommend the LCD technology for discriminating viewing.</p>

<p>However, after analyzing the image for a while I unavoidably lost my liking when I evaluated the issues of resolution limitations and relative darkness of the 3D image, not to mention the requirement for special glasses.</p>

<p>The Hyundai demo was not even on a 1080p display, and was perceptually inferior relative to the JVC experience. Rear projection DLP sets by Mitsubishi have been claiming 3D readiness for the past couple of years but the quality of the image never impressed me.</p>

<p>An issue that disappointed me for years was their implementation of a Texas Instruments chip having only 1080x960 arrays of mirrors to display 1080x1920 images, a concept some call "wobulation" applied to DLP rear-projection HDTVs for reasons of cost and TV manufacturer requirements, according to Texas Instruments; the concept was reused for 3D HDTVs. However, front-projectors implemented the full array of 1080x1920 mirrors on their chips.</p>

<p>A Mitsubishi senior executive said to me at one of the past conferences: "we know about the limitation, but most people would not notice the weakness points you mention when experiencing 3D".</p>

<p>In other words, people buy mostly by perception and manufacturers have no incentive in disclosing technical limitations of their products, consumers have to find those by themselves, and most people never do.</p>

<p><br />
<h2>Counting Pixels - Image Depth but at What Quality</h2></p>

<p>Most of the 3D implementations (by JVC, Mitsubishi, Hyundai, etc) do NOT show the full resolution of the original HD images recorded by the pair of cameras. The dual 1080p images (1080 lines x 1920 horizontal pixels) each made of 2+ million pixels of spatial resolution per video frame are being displayed on many "3D capable" HDTV displays with only about half of their original resolution. </p>

<p>After the cameras capture the full HD resolution of the image pair, the production and editing facilities can mostly reuse their investment of HD equipment, wiring, etc, but broadcast distribution is constrained by the limited bandwidth of their current 2D HD infrastructure.</p>

<p>Other methods of distribution such as cable and satellite could be capable to deliver the whole resolution of the 3D image pair if the price of the service justifies the allocation of enough bandwidth and/or more efficient compression is used (such as MPEG-4 rather than MPEG-2 of the broadcast standard). Consumers should be able to see the 3D quality if the TV can display it.</p>

<p>The JVC display demo at the show was said to be capable to accept and process two types of compressed split 3D images, as follows:</p>

<p>A)	Using the horizontal axis it would accept two side-by-side images anamorphically squeezed so both can fit into the 1920-pixel lines (1080), which means the horizontal pixel count of "each" image is reduced from 1920 to 960, a 50% horizontal resolution loss, or<br />
 <br />
B)	Using the vertical axis of the 1080 horizontal lines the JVC set would accept two images interleaved as 540 lines each (line 1 for one eye, line 2 for the other eye, etc.), which means the original vertical resolution of each image of 1080 is reduced to a half (540), each line with 1920 horizontal pixels.</p>

<p>Polarization glasses and the display technology help the eyes see only the parts of the images intended for each eye, and the brain interprets the blend of both images as depth.</p>

<p>The vertical axis method by JVC using interleaved lines from both images displays the image progressively as a whole frame of 1080 lines every 60th of a second. The screen is made of a material that provides circular polarization to work with each eye separately using passive glasses. JVC at the HD show said they prefer the side-by-side method.</p>

<p>Although the 3D image appeals to many viewers, there is a noticeable degradation of brightness, contrast ratio and color rendition. Striking whites of 2D HD originals are shown as dull whites on 3D, deep blacks are less profound, which in turn affects the rest of the colors. A quick look at the (now discontinued) Pioneer Elite plasma Kuros would help appreciate what near perfect blacks do to the rest of the colors of an image.</p>

<p>Appropriate test comparisons would be ideal to find the real differences, including the <a href="http://www.panasonic.com/3D/?No=0&jspStoreDir=panasonicdirect&catalogId=13401&Ne=&Ntt=3d&eedb=com.panasonic.commerce.enterprisesearch.beans.PNAEndecaEnterpriseDataBean%40763073c1&NttRaw=3d&N=779832187&URL=vEnterpriseSearch&Ns=&storeId=15001&Nr=12001&edb=com.panasonic.commerce.enterprisesearch.beans.PNAEndecaEnterpriseDataBean%40763073c1&Ntk=EnterpriseSearch">3D plasmas</a> Panasonic announced as near future products, which use active shutter glasses and display true 1080p images for each eye using faster frame rates. </p>

<p>Many in the 3D industry believe that 50% loss on the resolution (and its collateral damage over brightness, contrast, colors, etc) is not an issue considering the value of the 3D experience, but I found that most of those are "coincidentally" related to a profitable future of reusing the existing 2D distribution systems for 3D, which needs that high level of compression to work. </p>

<p>Others that are more technically oriented are reluctant to accept such quality loss, me included. </p>

<p>According to a Quantel <a href="http://www.quantel.com/repository/files/whitepapers_s3dmarch19th.pdf">white paper</a>:<blockquote>While this reduces the resolution of the image, one curious property of stereo is that the image still appears sharp. The way we perceive stereo is still not fully agreed upon but we do know that the brain creates stereo rather than passively capturing it and, if supplied with two lower resolution signals, many viewers 'see' a high quality result.</blockquote></p>

<p>Some key elements of that statement are "not fully agreed" and "many viewers 'see'". Who did not agree on what? Who are the many viewers? How much those viewers know about image quality to judge the result as 'high-quality'?</p>

<p>Depending on how much concern a viewer may have about losing image detail and quality in exchange for perceived image depth, the prospect of 3D could mean a: "can-not-wait-to-have-it" or a: "maybe-later-when-done-well".</p>

<p><br />
<h2>Thinking About Buying 3D?</h2></p>

<p>If I have to wear special glasses using any 3D method to perceive depth I rather buy a 3D set that adopted a method that does not sacrifice the detail and quality of the original pair of 2D images recorded by the dual cameras, regardless how the image is delivered to the TV, which seems to be the driving factor in rushing to 3D: the reuse of the existing 2D delivery channels.</p>

<p>Consumers may unknowingly be enticed to rush an invest on new 3D passive displays while not being told that the display would NOT be able to eventually handle the <a href="http://www.multichannel.com/article/365955-3_D_Blu_ray_Spec_Expected_by_Year_End.php?nid=2387&source=title&rid=5380669">higher quality</a> of an active shooter 3D style expected from Blu-ray or other pay services with less bandwidth restrictions. Hopefully, active display manufacturers may be able to design their TVs to also handle passive methods to be compatible with compressed 3D delivered by broadcast or other distribution services with limited bandwidths.</p>

<p>My point is simple, if consumers would be able to reuse their current HDTVs and video equipment for 3D as the delivery channels did for themselves (broadcast, etc.) I have no objection implementing (temporarily or not) compressed 3D methods at limited resolution. </p>

<p>But I find objectionable for the industry to entice consumers to unknowingly invest now in new 3D displays with limited capabilities and wear special glasses, only to be told shortly after that a more resolved 3D method may not be compatible with their recently purchased expensive 3D HDTVs.</p>

<p>Stay tuned to a follow up article about 3D, and also to my coverage of the 2010 Consumer Electronics Show Press Pre-show in NY on November 10.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>October 30, 2009 12:10 PM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(3361)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 3361)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author['bio_short'] != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Rodolfo La Maestra</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2009/10/hd-world-conference-in-ny-3d-ip-online-video-and-mobile-dtv.php" type="text/javascript" charset="utf-8"></script>
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
