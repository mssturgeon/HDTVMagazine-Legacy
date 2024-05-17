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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 814 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 814
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=A New Approach to Components in a Digital Audio/Video World&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/articles/2007/12/a-new-approach-to-components-in-a-digital-audiovideo-world.php&amp;title=A New Approach to Components in a Digital Audio/Video World">
		<span style="display:none">Remember when Home Entertainment life was much simpler? All you did was to sit back and turn on the TV to watch programs. Or, if you remember TV before the days of remotes you turned on the TV and then sat back unless you had a young son who was your human remote. Carrying this one step further back (as this writer remembers) there was actually a time when you only listened to programs for your nightly entertainment. Fibber McGee and Molly anyone? In any event, in the 1950's to somewhere in the 1980's &quot;Home Theater in a Box&quot; was just that for most people - the family television.

A handful of audiophiles (I was building kits in the early days) had stereos by the late 50's and we knew about audio components - a pre-amplifier, an amplifier and speakers which produced wonderful sounds from our sources - usually records and reel to reel tapes. (Yes, I also had a wire recorder for a time. I also have an Edison cylinder or two gathering dust.) But as interest in components grew so did the demand for a one box solution to audio as well - and the audio receiver was born. Nirvana for the &quot;I don' need no steenkin' wires!&quot; set!

Fast forwarding a bit (through LPs, Audio Cassette Tapes, 8 Track Players, Cable TV, VCRs, PCs, CDs, Satellite TV, DVDs, SACDs, DVD-As and similar) we arrive at the present...</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 814";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download A New Approach to Components in a Digital Audio/Video World" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="A New Approach to Components in a Digital Audio/Video World" />
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
	<title>HDTV Magazine - A New Approach to Components in a Digital Audio/Video World</title>
	<meta name="keywords" content="video processing, video processor, pre pro, home theater, component approach, video, audio, display, processing, pre, dvd, home, processor, components, hdmi, new, receiver, digital, pro, most, time, sources, player, theater, even" />
	<meta name="description" content="Remember when Home Entertainment life was much simpler? All you did was to sit back and turn on the TV to watch programs. Or, if you remember TV before the days of remotes you turned on the TV and then sat back unless you had a young son who was your human remote. Carrying this one step further back (as this writer remembers) there was actually a time when you only listened to programs for your nightly entertainment. Fibber McGee and Molly anyone? In any event, in the 1950's to somewhere in the 1980's &quot;Home Theater in a Box&quot; was just that for most people - the family television.

A handful of audiophiles (I was building kits in the early days) had stereos by the late 50's and we knew about audio components - a pre-amplifier, an amplifier and speakers which produced wonderful sounds from our sources - usually records and reel to reel tapes. (Yes, I also had a wire recorder for a time. I also have an Edison cylinder or two gathering dust.) But as interest in components grew so did the demand for a one box solution to audio as well - and the audio receiver was born. Nirvana for the &quot;I don' need no steenkin' wires!&quot; set!

Fast forwarding a bit (through LPs, Audio Cassette Tapes, 8 Track Players, Cable TV, VCRs, PCs, CDs, Satellite TV, DVDs, SACDs, DVD-As and similar) we arrive at the present..." />
	<meta name="title" content="A New Approach to Components in a Digital Audio/Video World" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/articles/2007/12/a-new-approach-to-components-in-a-digital-audiovideo-world.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=814', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2007/12/a-new-approach-to-components-in-a-digital-audiovideo-world.php">A New Approach to Components in a Digital Audio/Video World</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Robert A. Fowkes</b> on <b>December 13, 2007</b>
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
				<div align="center" style="clear:left">
	<b>Originally published in October, 2006</b><br />
	<a href="http://www.rfowkes.com/index.html"><img src="/images/articles/raf.gif" alt="The RAF Home Theater"></a><br />
	<b>Re-published courtesy of The RAF Home Theater</b><br />
	<a href="http://www.rfowkes.com/html/component_approach.html">http://www.rfowkes.com/html/component_approach.html</a>
</div>
<br />
<B>Introduction</B>

<p>Remember when Home Entertainment life was much simpler? All you did was to sit back and turn on the TV to watch programs. Or, if you remember TV before the days of remotes you turned on the TV and <I>then</I> sat back unless you had a young son who was your human remote. Carrying this one step further back (as this writer remembers) there was actually a time when you only <I>listened</I> to programs for your nightly entertainment. <I>Fibber McGee and Molly</I> anyone? In any event, in the 1950's to somewhere in the 1980's "Home Theater in a Box" was just that for most people - the family television.</p>

<p>A handful of audiophiles (I was building kits in the early days) had stereos by the late 50's and we knew about audio components - a pre-amplifier, an amplifier and speakers which produced wonderful sounds from our sources - usually records and reel to reel tapes. (Yes, I also had a wire recorder for a time. I also have an Edison cylinder or two gathering dust.) But as interest in components grew so did the demand for a one box solution to audio as well - and the audio receiver was born. Nirvana for the "<I>I don' need no steenkin' wires</I>!" set!</p>

<p>Fast forwarding a bit (through LPs, Audio Cassette Tapes, 8 Track Players, Cable TV, VCRs, PCs, CDs, Satellite TV, DVDs, SACDs, DVD-As and similar) we arrive at the present - a high definition, multi-channel audio video world filled with great sounds and images and the promise of things to come. And a tremendous amount of confusion! I would be remiss if I neglected to single out two significant things that occurred in the last quarter of the 20<sup>th</sup> century which, in my opinion, changed forever the landscape of home entertainment - video cassette recorders and the personal computer. VCRs made us aware that it would be possible (and cost effective) to build (or at least rent) a home movie collection without the limitations of a 35mm projector and bulky media. PCs (and all the associated fallout) provided the technology to produce an ever growing list of devices (displays, audio and video components) that were viable for millions of people. Yes, the introduction of DVDs might also be considered significant, but it was really a logical outgrowth of a slightly less efficient technology, Laserdiscs, and not as ground breaking in its impact as the VCR and the PC. It was, to this author, just the next step in the evolving world of home entertainment (not that I'm attempting to minimize the impact of DVDs which signaled the end of VCRs).</p>

<p>By the end of the 20th century all heads were beginning to turn toward digital media and high definition. New standards were set, new products were introduced and by the end of 2005 it was clear that <I>digital</I> was replacing <I>analog</I> in many ways. In fact, before the first decade of the 21<sup>st</sup> century came to a close, it was proclaimed that the analog TV from the 1950's would be out of luck when the analog lights went out for broadcasting on February 17, 2009. So where are we at this point? Clearly (no pun intended) we have a better picture and better sound at our disposal but the myriad of options also leads to a lot of confusion as to what's the best way to proceed. As always, the choices will depend on individual needs. Some people will still be content with a one box solution - but with a better picture and, hopefully clearer sound. Lots of products out there (or on their way) will take care of that. Others will want to go a little further in the area of home entertainment and for them there is a wide assortment of A/V receivers, speakers and wonderful displays that will provide them with an experience that will completely satisfy them. One thing that CEDIA 2006 pointed out (with 720p front projectors breaking the $1000 price barrier and 3 chip 1080p front projectors offered under $5000 for the first time) is that a serious home theater is no longer limited to those with lots of discretionary funds. Clearly, home theaters are within the means of a very large number of budget conscious households if they choose to go that route, and with bigger screens and more media at their disposal this is a more and more obtainable goal.</p>

<p>So what about the audio/videophile - those of us who tend to be on the cutting (some say <I>bleeding</I>) edge as far as Home Theater is concerned? Where are we heading and what are our options? It's my position that a possible course of action is to re-visit the <B>component approach</B> to audio <I>and</I> video - something that originally surfaced for audio in the 1950's but has all but disappeared for many people in the age of the mega-receiver. In this essay I intend to present some ideas to think about. These thoughts form the framework for my current (and future) plans for my home theater and might even explain a bit the revolving door nature of my components which seem to go from "current" to "other" to "former" at an alarming rate to some. Let's get started.</p>

<p><br />
<B>The Early Days</B></p>

<p><img src="/images/articles/pas3.jpg" alt="" align="left" style="padding:5px"/>As previously mentioned, I got my first taste of audio components in the mid 50's when I built lots of electronic kits. My favorites were the ones from Dynaco.  I assembled a PAS-3 pre-amplifier, an ST-70 Stereo tube amplifier and a Dynaco FM Tuner. I used an AR turntable and AR-2ax speakers and considered myself to have an excellent system for its day (and I saved considerable money by building it myself!). <img src="/images/articles/ARturntable.jpg" alt="" align="right" style="padding:5px"/>While the speakers are long gone, the other components are still functional to this day! By the 1960's audio receivers were emerging. One of the first units that I built was the Heathkit AR-15. It had great features and eliminated a lot of the wires associated with separate components. As the years passed (1960's, 1970's, 1980's) I upgraded my display several times (still called a "television"), added new speakers and went through several generations of receivers. <img src="/images/articles/st70.jpg" alt="" align="left" style="padding:5px"/>By the 1990's the video sources had increased (TV, Cable, Betamax, VHS, LDs) to the point where the receivers were now called "A/V" receivers to reflect the handling of multiple video sources in addition to the audio ones (LPs - fading, CDs, Tapes, etc.) I also owned my first "serious" monitor by that time, a Pioneer Elite Pro-75 45" CRT rear projection monitor and was enjoying my early version of "home theater."<br clear="all" /></p>

<p><br />
<B>My Dedicated Home Theater</B></p>

<p>By 1999 I began to seriously think of building a dedicated home theater for a number of reasons. For one thing there was going to be an addition added to my home and this was the logical time to consider such a project. For another, DVDs had saturated the market to such an extent that the price of media had dropped significantly. Finally, the realm of front projection (vital, in my opinion, for the full big-screen theatrical experience) had been opened to me once the introduction of digital projectors dropped below $10,000. I ended up with a Sony VPL-VW10HT for $5800. I also fitted the theater with M&K speakers (7 speakers and a subwoofer) and decided to purchase the current flagship Denon A/V Receiver, their Model 5700, as the control center for all my sources. It served me well (and is still being used upstairs.) When I got the upgraditis itch, the first modification I made was to add separate amps to the system. This is documented on my HT website and the short version is that separate amps sound better than those in a receiver and also make the pre/pro part of the receiver perform better. The next logical step was to replace the Denon receiver with a dedicated pre-pro (since I wasn't using its amps anyway and it could be used elsewhere). I had the opportunity to beta test the Outlaw 950 Pre/pro (pre-amp/audio processor in one box) and it fit perfectly into my plans and had more functionality. A few years later I fell in love with <I>Logic 7</I> from Lexicon and upgraded my Outlaw 950 to a Lexicon MC-8 which handled a lot of my connection and processing needs a little better than the 950. Note that most of the uses of all these receivers and pre/pros were to handle the audio side of things with video switching thrown in almost as an afterthought. Any video processing was handled by my projector - first the Sony VPL-VW10HT (LCD), and then the Runco CL-710 (DLP), both of them 720p HD designs.</p>

<p>Then along came 1080p, HDMI in all its flavors (and misconceptions) and an increased awareness of video processing (scaling, de-interlacing, etc.) Add to this the emphasis on HD sources (HDTV, HD DVD, and Blu-ray) and it was clear that video processing had moved out of the display devices and into the components (receivers, players, separates). To me, it was time to take stock of the situation and to develop a game plan.</p>

<p><br />
<B>My "New" Approach</B></p>

<p>"New" is actually a misnomer here since I am proposing that people consider going back to something that first evolved over 50 years ago. It is my belief that serious videophiles (people looking for the best bang for the buck while still striving for leading edge images and sound) consider taking a <B>component approach</B> to all of this - especially where video is concerned. This means building your system around a <B>separate video processor</B> instead of relying on the video processing attributes of the other boxes in your HT system (the players, the receiver and the display.) Video processors are not cheap by any means (the one I'm going to talk about, the <a href="http://www.dvdo.com/pro/pro_isvp50.php">DVDO iScan VP50</a>, lists for almost $3000) but, ironically, buying a video processor can actually save you some money in the long run! Before you dismiss this as being a ridiculous statement please read on. <I>Note: While I will be referring to DVDO processors because those are the products I'm most familiar with this doesn't mean that there aren't other comparable products out there from other manufacturers that can also fit the bill. My comments apply to most of these units and choice becomes a Mercedes/BMW type comparison in most cases.</I></p>

<p>And what is the "new" component approach in an A/V world? When we were young (and before some of you were born) components in the audio sense meant:</p>

<p><CENTER><B>SOURCES</B> to <B>PRE-AMP</B> to <B>AMPLIFIER</B> to <B>SPEAKERS</B></CENTER></p>

<p>For more modern A/V systems up until very recently (except for high end projectors) this meant</p>

<p><CENTER><B>SOURCES</B> to <B>PRE/PRO</B><br />
1. to <B>AMPLIFIER</B> to <B>SPEAKERS</B><br />
<u>and</u><br />
2. to <B>DISPLAY</B></CENTER></p>

<p>with the video sources either connected directly to the display or through some rudimentary video switching in the PRE/PRO with very little, if any, processing outside of the display.</p>

<p>In other words there were <B>two devices</B> (components) between the source and the output. Those who chose not to go the component route would substitute a single box (the A/V RECEIVER) for the PRE/PRO and AMPLIFIER components.</p>

<p>Now that video processing has become more prominent in the scheme of things it is being added to all sorts of Home Theater equipment. There are video processors in digital displays (necessary in order to show the picture in the native format of the digital display from other video sources), video processors in source devices (everything from Digital TV receivers to up scaling DVD players in many flavors) and, most recently, video processing is becoming a feature in most of the latest A/V receivers and even some of the pre/pros. While video processing is unquestionably important in the age of digital video we are running the risk, once again, of owning multiple devices that perform <I>the same tasks</I> - and this often leads to paying for features that we don't really need because we already have them in another device in our systems. In addition it should be noted that video processing, like most technologies, is currently improving so that not all video processing is the same. You can't just tell from the description how well a unit will, for example, de-interlace 480i or de-interlace 1080i. You have to rely on performance reviews and your own experience, not on spec sheets. And because video processing is always getting better there's a much greater chance than ever to end up with a component (like an A/V receiver or a display) that has outdated video processing long before it would otherwise be ready to be scrapped for something new.</p>

<p>So what's the solution that I am advocating? <B>Purchase a standalone video processor</B> (like the <a href="http://www.dvdo.com/pro/pro_isvp50.php">DVDO iScan VP50</a> or similar) and avoid, as much as possible, getting other components which contain extensive video processing capabilities. Very often the major difference between a $5000 A/V receiver and a $1000 unit from the same company is the video processing and switching contained within. This varies from manufacturer to manufacturer, but I trust that you see my point. Be aware that you can't avoid video processing entirely. Every digital display must, out of necessity in order to be commercially viable, contain some basic de-interlacing and scaling so that it can handle a variety of input signals and convert them to the native resolution of the display. <I>[As an interesting side note, most really high-end displays (those approaching or in the six figure price range) have always used outboard processors as part of their basic package so this is not really a new concept - just one that has filtered down to the affordable world that most of us live in.]</I> And most DVD players offer some rudimentary de-interlacing (the progressive outputs on even inexpensive DVD players). However, if you carefully choose components where you can bypass the internal video processing then you are on the right track. For example, if you have a 1080p capable display you should insist that it also handles native 1080p input so you can bypass the display's internal de-interlacer. This isn't a foolhardy requirement at all. In response to the statement that "there just isn't much 1080p content" which was made by manufacturers of 1080p sets in 2005 who didn't offer 1080p inputs one can point out that it goes way beyond the availability of 1080p content at that moment. Here's the rationale. With a 1080p input on the display you can use that input now and add an external video processor to do the 1080i to 1080p conversion until 1080p sources are more abundant. And even if the internal de-interlacing of the display is pretty good by today's standards, tomorrow comes very quickly and what was once O.K. quickly becomes passe. The simple act of making sure that your display can accept video input that matches its maximum display resolution means that you can be pretty sure that you have a direct connection from the outside world to the display. (I say "pretty sure" because there have been some reports that certain displays - at least in the beginning - did some 1080p to 1080i internal conversions before finally creating the final 1080p picture. But as more and more people become aware of this practice, I'm betting it will disappear since many manufacturers do it right and word gets around quickly in the A/V world.) Having the ability to by-pass the any internal video processing on a display never hurts and it certainly can help in the future.</p>

<p>On the source side of things, you should look for players that provide for digital output of the video signal <I>in the form that it is stored on the media.</I> For Standard Definition (SD) DVDs, this means 480i and for High Definition (HD) discs, both HD-DVD and Blu-ray, this means 1080p. That way you can be relatively certain that the signal you are getting from the player is a true representation of what's on the disc and not some processed signal tied to the player's electronics. To summarize what you should look for in this regard:</p>

<ul><li>On the player side, look for HDMI 480i output for SD DVD players and HDMI 1080p output for HD-DVD and Blu-ray players.</li><li>On the display side, look for native acceptance of the same resolution and format that the monitor displays (1080p input for 1080p displays, etc.)</li></ul>

<p>Following these guidelines lets the external video processor handle all the scaling and de-interlacing along with a host of other video goodies (and even some audio sync if needed).</p>

<p>So what does the component approach look like in today's A/V digital world? Here's a brief description:</p>

<p><CENTER><B>SOURCE</B> to <B>AUDIO PRE/PRO</B><br />
1. to <B>AMPLIFIER</B> to <B>SPEAKERS</B><br />
<u>and</u><br />
2. to <B>VIDEO PROCESSOR</B> to <B>DISPLAY</B></CENTER></p>

<p>Using this arrangement allows us to focus on the audio pre-pro to handle any HDMI version issues (1.1, 1.2, 1.3, etc.) with sound and audio codecs - present and future - while passing through the unaltered video signals to the video processor. Please note that any flavor of HDMI (from the very start) supports 1080p so you need not concern yourself with which version of HDMI is found in the video processor for the foreseeable future. The increased video bandwidth and color depth written into the HDMI 1.3 spec addresses future video formats that are not even on the horizon. Whenever this occurs, you can deal with the video processor at that time. If you choose a company with liberal <a href="http://www.dvdo.com/faq/faq_upg.php">upgrade policies</a> (like Anchor Bay Technologies, the parent company of DVDO) it will most likely be a painless process if and when the time comes. In the meantime, using the component approach allows you to upgrade only the portion of your system that needs attention, much like the old days of components, but with a few additional pieces. The basic difference between the old model for components and the new one is essentially that there are now <B>three</B> pieces (pre/pro, amp and video processor) between the source(s) and the output device(s) rather than <B>two</B> (pre/pro and amp). The video processor is the new kid in town.</p>

<p>So how does this all play out in a real world scenario? And how does this all lead you to saving money by purchasing a $3000 (or so) Video Processor? Read my current plans below to see one example of what I'm talking about.</p>

<p><br />
<B>My Current Game Plan</B></p>

<p>If you look at my website today (this is being written in October of 2006) you will see that at that time I am (was) using various sources plugged into two major components - A <a href="http://www.lexicon.com/products/overview.asp?ID=2">Lexicon MC-8</a> for all of my audio needs (I'm currently keeping Audio and Video separate but that might change as Pre-Pros change and HDMI matures) and a <a href="http://www.dvdo.com/pro/pro_isvp30.php">DVDO iScan VP30</a> for all digital video switching and processing connected to my display. I've been very pleased with the audio performance and the flexibility of the MC-8 but it only switches component video and has no HDMI (or even DVI) support at all. And as nice as the sound of <a href="http://www.lexicon.com/logic7/index.asp">Logic 7</a> (a proprietary Lexicon/Harman audio format) has been, there are now some more widely available systems (like <a href="http://www.dolby.com/consumer/technology/prologic_IIx.html">Dolby Pro Logic IIx</a>) that provide similar 7.1 sound fields created from other sources. And while the MC-8 does provide analog 5.1 inputs I have to choose between listening to my SACD and DVD-A discs <B><u>or</u></B> accepting the analog output from new audio codecs of the HD players. The 5.1 analog input can't accept both at the same time. <I>[Note to Lexicon MC-8 power users: Yes, I'm aware that I can configure the inputs of the very flexible MC-8 to allow for two sets of 5.1 analog inputs but this would be at the expense of a lot of other input capability. This is not something that would work with all the audio sources at my disposal.]</I> Since the MC-8 doesn't handle HDMI of any flavor there is no digital option for full audio other than through Toslink or Coaxial. As an interim solution, here's the new equipment that I'm in the process of acquiring (or have already acquired depending on when you are reading this - check my HT website for the latest on that):</p>

<ul><li>A <a href="http://www.dvdo.com/pro/pro_isvp50.php">DVDO iScan VP50</a> (<a href="http://www.dvdo.com/faq/faq_upg.php">upgrade</a> from my <a href="http://www.dvdo.com/pro/pro_isvp30.php">VP30</a> for its increased functionality - see the DVDO web site for further details.)</li>
<img src="/images/articles/pro_vp5002.jpg" alt="" align="center" />
<li>A <a href="http://www.usa.denon.com/ProductDetails/623.asp">Denon 3806 A/V Receiver</a> to be used as a Pre/Pro only</li>
<img src="/images/articles/AVR3806_large_front_rdax_175x7403.gif" alt="" align="center" />
<li>A <a href="http://www.usa.denon.com/ProductDetails/3262.asp">Denon 2930ci DVD Player</a></li>
<img src="/images/articles/DVD2930CI_Front_G_rdax_175x4802.gif" alt="" align="center" />
</ul>

<p>Here's the rationale. The VP50 has a host of new features, including some excellent 1080i to 1080p de-interlacing that will serve the output from my Toshiba XA-1 player well. It is also much more easily upgradable than earlier models and I fully expect new features to be added to it several times during its lifespan. The Denon 3806 is a tremendous bargain at under $900 and has everything I need to take care of the immediate future. While it has some HDMI and other video switching (which isn't important to me since I have the VP50) it also contains a lot of the latest audio processing (like <a href="http://www.dolby.com/consumer/technology/prologic_IIx.html"Dolby Pro Logic IIx</a> and many others) and also contains the all important <a href="http://www.audioholics.com/news/pressreleases/DenonLink3rdEdition.php">DenonLinkIII</a> which, when used in conjunction with the Denon 2930ci DVD player allows for a direct connection (using an Ethernet cable) between the 3806 and the 2930ci. This wire passes through all SACD and DVD-A audio signals directly, thus freeing up the analog inputs (7.1 versus 5.1 with the Lexicon MC-8) for HD audio codecs. True, the Denon 3806 has "only" HDMI 1.1 but, like I said, this is an interim solution that should serve me well until HDMI 1.3 is ready for prime time in pre/pros and receivers. Besides, when I'm done with the 3806 I have a fine secondary receiver for use elsewhere in the house. And in the meanwhile I don't have to worry about not being able to hear the latest audio formats that are currently available.</p>

<p>But the most important thing to me is not what the 3806 offers, but what it doesn't offer. It doesn't offer extensive video processing (I don't need it), it doesn't offer as much power and isolated transformers as Denon's flagship $7000 list/$5000 street <a href="http://usa.denon.com/ProductDetails/3157.asp">5805MK2 receiver</a> (also don't need that since I'm using my own amps) and it doesn't have quite as many multi-zone capabilities (not important in my application). In the Denon scheme of model numbers and product lines, once you reach the "3000" level on up (like the 3806, etc.), the quality of the circuitry used is quite comparable, so I'm not opting for a low-end model, just one with fewer features since I already have them elsewhere. If you do the math you will see that I'm already ahead of the game. If I spend $900 for the pre/pro and $2600 (street) for the VP50 I still would save about $1500 compared to the street price of the 5805MK2.</p>

<p>And it gets better. If I didn't need the DenonLinkIII (as explained above) I could actually get the <a href="http://usa.denon.com/ProductDetails/3038.asp">Denon 2807</a> receiver and save even more. Additionally (once again tied to DenonLinkIII) if I didn't need that feature on my DVD player then I could also save even more money on a player. As mentioned, the important specification for a standard DVD player when using an external video processor is HDMI 480i output. <img src="/images/articles/OPPODV-970HD.jpg" alt="" align="left" />That lets the VP50 (or any other quality video processor) deal with the native signal directly from the SD DVD and the results are spectacular. OPPO makes a very nice DVD player (the <a href="http://www.oppodigital.com/dv970hd/dv970hd.html">DV-970HD</a>) with HDMI 480i output for around $150. If you think of the savings involved by not having to purchase a quality up scaling DVD player you can add these to the other cost effective measures already discussed you get a sense what I mean when I say that purchasing an external video processor can actually cost you less in the final analysis.<br clear="all"></p>

<p>Remember, we're not talking about performance that is the same as a run-of-the-mill DVD up scaling player. We are talking about state of the art video performance.</p>

<p><br />
<B>And What Will the Future Bring?</B></p>

<p>Like I said, when you consider the big picture (pun intended this time) money invested in a quality external video processor can actually lead to a lower total cost once you assemble your dream audio/video system. It will probably even look better and it most assuredly will be much more flexible for future needs. There is a lot on the table with Digital Video, HD media of all types, HDMI 1.3 connections and beyond, new audio codecs, probably some video enhancements, and certainly some things that we haven't even considered at this time. While it isn't possible to cover all bases in the ever-evolving world of Home Theater a modular (component) approach at least gives you the flexibility to roll with the punch. (<I>Le Chatelier's Principle</I>, from my Chemistry days. You can look it up.)</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Robert A. Fowkes</b>, <b>December 13, 2007  7:23 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(814)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Robert A. Fowkes', 814)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2007/12/a-new-approach-to-components-in-a-digital-audiovideo-world.php" type="text/javascript" charset="utf-8"></script>
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
