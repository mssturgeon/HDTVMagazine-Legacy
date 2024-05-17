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
	$res_author = mQuery("SELECT title, channel, amazon_tracking_id, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Robert A. Fowkes'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Robert A. Fowkes" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1710 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 1710
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=Revisiting the Component Approach to Audio/Video in Today&rsquo;s Home Theater Market&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/articles/2009/04/revisiting-the-component-approach-to-audiovideo-in-todays-home-theater-market.php&amp;title=Revisiting the Component Approach to Audio/Video in Today&amp;rsquo;s Home Theater Market">
		<span style="display:none">Over two years ago I wrote an article titled &lt;a href=&quot;http://www.hdtvmagazine.com/articles/2007/12/a_new_approach_to_components_in_a_digital_audiovideo_world.php&quot;&gt;&lt;b&gt;&lt;i&gt;A New Approach to Components in a Digital Audio/Video World&lt;/i&gt;&lt;/b&gt;&lt;/a&gt; to share my thoughts regarding equipment options in Home Theater and related electronics. At that time I outlined the reasons that components might provide a better overall solution to an all-in-one box in terms of upgradability, performance and sometimes even price. Basically, the upside focused on the ability to upgrade as needed without replacing perfectly adequate and functional parts and the downside involved more boxes and wires than the alternative. While that's a gross simplification of the whole hypothesis you can review the entire argument by referring to the original article.

The basic principles of that paper still apply but a lot has changed over the past two years...</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1710";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Revisiting the Component Approach to Audio/Video in Today&rsquo;s Home Theater Market" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Revisiting the Component Approach to Audio/Video in Today&rsquo;s Home Theater Market" />
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
	<title>HDTV Magazine - Revisiting the Component Approach to Audio/Video in Today&rsquo;s Home Theater Market</title>
	<meta name="keywords" content="pre pro, home theater, voltage threshold, threshold error, component approach, hdmi, devices, video, component, components, threshold, approach, pre, voltage, might, signal, pro, box, receivers, theater, home, using, boxes, audio, years" />
	<meta name="description" content="Over two years ago I wrote an article titled &lt;a href=&quot;http://www.hdtvmagazine.com/articles/2007/12/a_new_approach_to_components_in_a_digital_audiovideo_world.php&quot;&gt;&lt;b&gt;&lt;i&gt;A New Approach to Components in a Digital Audio/Video World&lt;/i&gt;&lt;/b&gt;&lt;/a&gt; to share my thoughts regarding equipment options in Home Theater and related electronics. At that time I outlined the reasons that components might provide a better overall solution to an all-in-one box in terms of upgradability, performance and sometimes even price. Basically, the upside focused on the ability to upgrade as needed without replacing perfectly adequate and functional parts and the downside involved more boxes and wires than the alternative. While that's a gross simplification of the whole hypothesis you can review the entire argument by referring to the original article.

The basic principles of that paper still apply but a lot has changed over the past two years..." />
	<meta name="title" content="Revisiting the Component Approach to Audio/Video in Today&amp;rsquo;s Home Theater Market" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/articles/2009/04/revisiting-the-component-approach-to-audiovideo-in-todays-home-theater-market.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1710', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2009/04/revisiting-the-component-approach-to-audiovideo-in-todays-home-theater-market.php">Revisiting the Component Approach to Audio/Video in Today&rsquo;s Home Theater Market</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Robert A. Fowkes</b> on <b>April 16, 2009</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=11&category=Technology">Technology</a></b>
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
				<h2>Introduction</h2> <p>Over two years ago I wrote an article titled <a href="http://www.hdtvmagazine.com/articles/2007/12/a_new_approach_to_components_in_a_digital_audiovideo_world.php"><b><i>A New Approach to Components in a Digital Audio/Video World</i></b></a> to share my thoughts regarding equipment options in Home Theater and related electronics. At that time I outlined the reasons that components might provide a better overall solution to an all-in-one box in terms of upgradability, performance and sometimes even price. Basically, the upside focused on the ability to upgrade as needed without replacing perfectly adequate and functional parts and the downside involved more boxes and wires than the alternative. While that's a gross simplification of the whole hypothesis you can review the entire argument by referring to the original article. <h2>So what's different today?</h2> <p>The basic principles of that paper still apply but a lot has changed over the past two years which allows me to re-address my original hypothesis to present some viable alternatives. In fact, my recent audio/video purchases (more about this later) reflect this new information. So what are some of the factors that have changed? <ul> <li>HDMI has matured. With HDMI 1.3a now firmly established some of the problems of earlier versions have just about disappeared. And the industry has taken steps to see that all parties follow the standards as written.</li> <li>More manufacturers are "playing by the rules" concerning HDMI, partly due to standards monitoring but just as importantly because trouble free devices have a much smaller need for customer support. And providing support costs money.</li> <li>Prices for many very capable A/V devices have fallen to the point where the one-box approach is no longer as restrictive as it once was. While I still advocate separate amplifiers for several reasons including their longevity, relative freedom from obsolescence and the fact that internal amps in receivers tend to stifle performance in those units it has become almost as cheap to replace a good A/V receiver (even just used as a preamp-processor - or "pre-pro") as it once was to replace an individual component.</li> <li>The performance of the audio decoders and the video processors in receivers/pre-pros now rivals the quality found in standalone devices for the same task.</li> <li>The processing ability (video scaling and audio processing) of many source devices like DVD and Blu-ray (HD) players has gotten surprisingly good as more chips are available to be incorporated into these units at low cost.</li> <li>More inputs (and outputs) are now available on a wide range of Home Theater equipment including pre/pros and displays. This gives additional installation flexibility with fewer components.</li></ul> <p>On the component side of things prices have dropped dramatically as well. A standalone video processor which might have cost in excess of $2000 a very short time ago can now be had for under $600 (as of this writing). One such example is the <a href="http://www.anchorbaytech.com/dvdo_edge/product.php">DVDO Edge</a>. While more expensive units offer greater video flexibility the performance of these new cost-effective units is remarkable and would meet the needs of most home theater enthusiasts. So the original component approach still applies if you want total flexibility at a lower cost than ever. <h2>An Important Consideration</h2> <p>But there's one cloud looming over the component approach and HDMI that must be taken into consideration and carefully monitored if going this route. Yes, more boxes mean more connectors and the increased shelf space required might limit this method in some installations but that's not the real problem. In 2007 I wrote an article called <a href="http://www.hdtvmagazine.com/articles/2008/02/the_wonderful_and_sometimes_confusing_world_of_hdmi_connections.php"><b><i>The Wonderful and Sometimes Confusing World of HDMI Connections</i></b></a> where I provided a comprehensive overview of HDMI and some of the potential pitfalls. That article should bring you up to speed on HDMI and point you in the right direction regarding this now maturing technology. We are closer than ever to the "one-wire solution" now that most of the industry is complying with the standards. There is still the occasional "blue screen" as an HDMI device or two temporarily has to undergo a handshake sequence to stay connected (most noticeably in some Blu-ray devices which, I'm told, is due to some copy protection issues within that format's specifications) but in almost every case it's not the annoying delays of years past. <p>However, there is one potential problem with HDMI that might surface if using a multi-box (component) approach in one's home theater. My friend Jano Banks (co-inventor of HDMI) pointed out to me that the more devices you add to the HDMI chain the greater your chance of experiencing handshaking delays of some magnitude. He cautioned that you might encounter this problem even if the individual components each comply with the HDMI specifications separately. The reason can be explained as follows (in simplified terms). The digital HDMI signal involves voltage thresholds which determine the difference between the "0" (off) and "1" (on) state. Suppose, for argument's sake, that the HDMI spec allows a compliant component to have no more than a 30% voltage threshold error in order to be certified. This would assume that 0% voltage threshold error indicates digital perfection in signal transfer and 100% voltage threshold error indicates a failed digital signal transfer. Remember, a digital signal transfer is an on/off proposition so that if the signal is below the threshold a "0" is the result and if above the threshold a "1" is the result. (Analog analogies do not apply here - no pun intended.) The problem is that these threshold errors are cumulative so that the more HDMI devices in the chain, the better the chance that you may experience some signal problems - at least momentarily as connectivity is established. In other words if you connect four HDMI devices in series then the total voltage threshold error could actually approach 120%! (30% times 4). In actual experience you might get away with four HDMI components connected in series (I did for quite some time) because the 30% voltage threshold specification is a maximum and many components fall way below that. Also, there are ways to reinforce the HDMI signal using devices such as Repeaters that lower the threshold error even more. Jano told me that you can definitely connect three HDMI compliant devices with no problems but once you reach four or more devices you might run into some problems. As mentioned previously, the scenario I just presented does not represent the actual voltage specifications but is intended to give the reader an idea of what we are up against here. The more HDMI boxes involved, the greater the chance for some unpredictable behavior. There's another matter of wire length with HDMI devices which also involves voltage thresholds but well designed HDMI cables usually allow for that with the proper electronics. (<i>Remember, "well designed" doesn't necessarily mean "expensive!")</i> In general you have no problem with a simple well constructed HDMI cable up to 20 feet or so - sometimes even longer. But that's another subject for another time. <h2>Alternatives and My Personal Solution</h2> <p>With all this in mind, one can currently choose either the modular (component) or the integrated (pre-pro/receiver) approach using HDMI connections and come up with a reliable and great performing system. My previous articles (which see) listed the components that I was using and after viewing the latest offerings at CEDIA I made the decision to re-do the electronics of my home theater to drastically reduce the number of boxes involved. Instead of a four box (source, pre-pro, video processor, HDMI switch) path I now have a two box path (source, pre-pro) to my displays. But before anyone thinks that I have compromised on quality and flexibility let me discuss the new components and the reasons that I made the switch. In recent years I have been using Denon receivers (first the 3806 and then the 3808ci) in "pre-pro" mode because I liked the feature set on these devices. I still used my Marantz MA-700 monoblock amplifiers and my 5 channel Outlaw 755 amp (all spec'ed at 200W into 8 ohms) for power and bypassed the internal amps of the receiver. I also used a DVDO VP-50 video processor in the chain (after the receiver, with the receiver's incoming video set to pass-through) and then took the single HDMI output of the VP-50 (video only at this point) into a quality HDMI repeater/switch (Radiient Repeat-6) to feed two display devices - a JVC RS-1 1080p LCoS front projector and an HP MD5880n 58" 1080p DLP rear projection monitor. My earlier articles explain my choices of this equipment and their features in more detail. <p>Then came the <a href="http://www.usa.denon.com/ProductDetails/3922.asp">Denon AVP-A1HDCI</a> pre-pro! This was Denon's first entry into the separates field this century. Many years ago they introduced the AVP-8000 which was legendary at the time and this new unit is a state of the art AV preamplifier-processor with just about every imaginable feature included. One may gag at the $7500 list price and I realize that this puts it out of the price range of a lot of HT fans but when considering the list prices of all the components I replaced (and nobody except my mom ever paid list price) it's not completely crazy. Remember, a lot of what this behemoth contains is state of the art with some great upgradable features to ward off obsolescence. For one thing, it has Ethernet connectivity which allows for firmware upgrades which occur on a regular basis. There have already been several major upgrades since I purchased this unit (including Audyssey Dynamic Volume) and shortly DenonlinkIV will be added. And the fact that the AVP-A1HDCI is an Internet device allows me to back up the many, many configuration settings on my PC in case of an electronic disaster. Add to that the remarkable Audyssey Equalization and the Silicon Realta Video processing chips along with all the latest audio codecs and more inputs and outputs than you can shake a stick at and I have more functionality than I had in my previous three boxes combined. And the immediate bonuses for me were <ul> <li>No more "redundant" amplifiers. I have amplifiers so I'm not paying for something I'm not using as I have been recently with receivers. (But that was actually a blessing because now my former Denon 3808ci now resides upstairs where the seven channel amp is finally being used.)</li> <li>More importantly, far fewer wires by a long shot so HDMI is now much more stable than it has ever been.</li></ul> <h2>Wrapping it All Up</h2> <p>It is not my intention to provide a review of the AVP-A1HDCI here (I think you can tell what my sentiments are) as there are many well written articles on that score. Let Google be your friend in this matter. Nor am I advocating that you run out and spend your child's college fund on HT equipment. While the Denon AVP represents a unit on stereo steroids, you can find many other receivers that are incorporating the most important features of separate AV components. The point that I am trying to make is that the recent rash of high quality, multi-featured, affordable receivers from many manufacturers has made it possible to provide yourself with a fully functional HDMI A/V system without having to resort to a lot of boxes. This was not the case just a few years ago. <p>So, in conclusion, the industry has responded to the early HDMI problems with a series of stable products - whether you wish to go the component route or the single box approach. With components you have a bit more flexibility and with receivers the reliability of the HDMI circuitry (when properly implemented - read the reviews!) plays in its favor. And the modern AV Pre-pro is a hybrid: one box for all the processing and separate amps for the component enthusiast. In some ways I've come full cycle back to my Dynaco days in the '50s as described in my original component article.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Robert A. Fowkes</b>, <b>April 16, 2009  9:24 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(1710)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Robert A. Fowkes', 1710)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author['bio_short'] != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Robert A. Fowkes</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2009/04/revisiting-the-component-approach-to-audiovideo-in-todays-home-theater-market.php" type="text/javascript" charset="utf-8"></script>
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
