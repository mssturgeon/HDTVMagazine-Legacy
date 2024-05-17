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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3383 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 3383
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=Who Needs Content Protection?&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/articles/2009/11/who-needs-content-protection.php&amp;title=Who Needs Content Protection?">
		<span style="display:none">The subject of “content protection” continues to be a complicated issue. While some firmly think that one should be able to legally make a copy of lawfully acquired content, if such content is protected to avoid exactly that and the protection is circumvented to perform such copy, how can that action be right when it is actually violating the right of the content creator? 

The content production industry needs...</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3383";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Who Needs Content Protection?" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Who Needs Content Protection?" />
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
	<title>HDTV Magazine - Who Needs Content Protection?</title>
	<meta name="keywords" content="content protection, component analog, blu ray, aacs content, analog connections, content, analog, consumers, video, protection, digital, ”, aacs, equipment, audio, may, outputs, quality, component, connections, protected, “, hdmi, ict, copy" />
	<meta name="description" content="The subject of “content protection” continues to be a complicated issue. While some firmly think that one should be able to legally make a copy of lawfully acquired content, if such content is protected to avoid exactly that and the protection is circumvented to perform such copy, how can that action be right when it is actually violating the right of the content creator? 

The content production industry needs..." />
	<meta name="title" content="Who Needs Content Protection?" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/articles/2009/11/who-needs-content-protection.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3383', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2009/11/who-needs-content-protection.php">Who Needs Content Protection?</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>November 19, 2009</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=331&category=Digital Rights Management (DRM)">Digital Rights Management (DRM)</a></b>
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
				<p>The subject of “content protection” continues to be a complicated issue. While some firmly think that one should be able to legally make a copy of lawfully acquired content, if such content is protected to avoid exactly that and the protection is circumvented to perform such copy, how can that action be right when it is actually violating the right of the content creator?  <p>The content production industry needs the audio/video equipment industry for their content to be consumed. Equipment can only be sold if appealing content is produced. Consumers need both industries to survive so they can continue getting entertainment content, and both industries need consumers to buy their products. A delicate balance of their relationship has to be maintained to coexist harmoniously while respecting each other’s rights, especially when the rules and systems are still evolving.  <p>Consumers are not all pirates and content creators have the right to an income so they can make more content. Because of the imperfect rules and the ever-evolving standards, consumers and creators of content are tested continuously to demonstrate to each other their mutual respect, without excesses, greed, or abuses on their side of the market. Equipment manufacturers follow thru by adapting their designs to include one more connection and one more content protection mechanism to help maintain that harmony, at a very high cost to consumers.  <p>We know that, throughout the years, the approach has not worked well, but how could it be improved? With so many constraints to maintain backward compatibility to legacy hardware and software, and with self-replacing connections, cables, content protection protocols, etc. the baggage seems larger than the problem to solve. Would more transformation over the same base be more efficient and effective than starting from scratch?  <p><b></b> <h2>A Corporate Mission of Challenging Content Protection </h2> <p>A <a href="http://www.hdtvmagazine.com/columns/2009/08/hdtv_almanac_dvd_archiving_at_risk.php">recent column</a> mentioned very briefly the legal subject of content protection with companies such as Kaleidescape and RealDVD. <a href="http://www.cepro.com/article/understanding_the_kaleidescape_and_realdvd_cases/%5b">This article</a> goes deeper into the matter with those companies. Violating a contract regarding a DVDCCA license (Kaleidescape) is a different legal issue than helping others systematically violate the protection of content (RealDVD), but the spirit of the “content protection” concept links them both at the hip. Ironically, this is not even for content of HD quality.  <p>A few years ago, a number of rules were discussed at the FCC for the distribution of protected HD content over satellite and cable to allow/prohibit the equipment’s ability to copy once/never, and the viewing. The subject was further complicated with the idea of establishing the "Broadcast Flag" to protect some premium content broadcasted by terrestrial DTV to deter its illegal distribution over the Internet, but it was later rejected by the court.  <p><a href="http://www.hdtvmagazine.com/articles/2006/02/analysis_of_dtv_content_protection_rulings_and_agreements.php">Here</a> is an analysis of that subject. The graph at the end gives a general overview.  <p><b></b> <h2>Reconciliation of Common Sense </h2> <p>I am primarily a consumer of content, but I also produce it.  <p>As a consumer I consider it fair having to pay for every piece of content I want to view or collect, protected or not. However, I am a 60-years-old-timer. New generations are accustomed to think all content should be free because they generally “find a way” to get it free, and many times this is not due to lack of money. For young generations, living in the digital world has evolved this way since their childhood.  <p>The same digital world made the younger generations of audio/video consumers to be satisfied with lower quality due to the convenience of MP3, YouTube, and video over-compressed on a PC/cell phone, while unable (or unwilling) to appreciate the merits of hi-end audio and high quality video, as experienced and preferred by earlier generations for decades.  <p>Part of the problem is that demonstrations of hi-end audio and video by dedicated brick-and-mortar establishments are becoming endangered species, precluding younger generations from personally experiencing what quality is about.  <p>Wearing my hat of content producer, I was able to view a different perspective upon writing my <a href="http://www.displaysearch.com/cps/rde/xchg/SID-0A424DE8-F7544E93/displaysearch/hs.xsl/pr_242.asp">fifth book</a>. It was almost a year of work written for the HDTV industry. I learned to appreciate the content-creator side of the equation when someone had the nerve to request a free PDF (of a $1000 book) with the intention to distribute free copies within the organization. A reputable publication asked for a courtesy copy of the 560-page book to mix its content with theirs, and make a profit, their profit. From those experiences, I realized that what could be considered common sense to one person is certainly not to another, and there cannot be mutual understanding and respect if content rules are not in place and enforced.  <p>The digital era had made the content protection monster larger than ever. New generations are accustomed not to realize nor care for the effort a content creator endures to make a living.  <p>Although the ripping and copying of protected content for personal use may look okay to some that see it as a harmless action, it could easily grow out of control even in a civilized and democratic society. It actually happened already in several places across the globe, I witnessed piracy personally in Europe, China, Argentina, and other countries, in an open market, in front of the police, and we know we are not immune in the US either.  <p>This does not condemn the idea of allowing granddad to innocently make a copy of his purchased Cinderella DVD to been able to play the movie at his cabin when his grandchildren visit him over the weekend. His intention is to be less worried with damage when the kids handle the disc, rather than to sell the copy to boost his 401K. However, where should the line be drawn?  <p><b></b> <h2>Piracy Complicates Consumer Lives </h2> <p>Piracy has prompted content producers not to trust anyone in the digital world. Therefore, regardless if a person is good or not concerning content creation, all consumers pay some kind of price, including granddad, and unfortunately in this case we are all suspected to be pirates before we can prove our innocence.  <p>Content that is protected comes with a two-edge blade that negatively affects law obedient consumers and creators in one way or another. If all people obeyed the various laws of humankind the police and the court systems may not need to exist, and good people would not have to contribute their taxes to pay for their salaries and infrastructure.  <p>Bad apples that violate protected content with the purpose of illegal business are notorious to have the means to invest in the necessary equipment to re-digitize an analog HD signal, and that complicates the lives of millions of consumers (more on it below).  <h2>Blu-ray, AACS, and 11 Million HDTV Owners at Risk </h2> <p>When Blu-ray was made commercially available to consumers in 2006, discussions were held to implement the Image Constraint Token (ICT). The ICT is a feature of the Advanced Access Content System (AACS) of the Blu-ray format that could disenfranchise the 11 million early adopters that have purchased HDTVs since 1998. The ICT would disable the viewing of a purchased HD movie at its full resolution because the TVs above were designed with only component analog connections (DVI and HDMI were implemented later in HDTVs).  <p>If the content creator implements the ICT Token on a Blu-ray disc it would instruct the player to take the HD 1080i/p image read from the disc and down-res it to SD quality when sent to the component analog HD output of the player.  <p>This 2007 HDTV technology Report <a href="http://www.displaysearch.com/cps/rde/xchg/SID-0A424DE8-F7544E93/displaysearch/hs.xsl/pr_242.asp">Industry Edition</a> briefly covered the subject as follows:  <p>---------------------------  <p>“<i>Six times of quality reduction (to 16%) of the 1080i HD original version, whereby a 1080ix1920 = 2,073,600 pixels of video frame are down converted to 480ix704 = 337,920 pixels, both at the same rate of 30 frames per second.”</i>  <p><i>“Having a program with 100% quality reduced to 16% is not certainly an incentive to buy high quality HDTVs, a key ingredient for the success of the DTV transition mandated by the government.“</i>  <p><i>“Likewise an HDTV and an HD-STB not having protected digital connections might also run the risk of eventually not been able to view premium content in full HD if the content provider (i.e. HBO, VOD) requests it.”</i>  <p><i>“The digital outputs (HDMI or DVI) will still carry the full resolution of the disc because the outputs are protected by HDCP. Component analog connections cannot carry that protection, reason by which the resolution would be downgraded.” </i> <p><i>“The agreement also affects a large number of PC monitors/video cards used to watch Hi-def DVD that are not DVI/HDMI HDCP compliant; the vast majority of them are connected with regular VGA analog connections.”</i>  <p><i>“However If the content provider studio sets the flag to “off” the player would supply the full resolution of the content to the analog outputs. The package of the pre-recorded movie must indicate if the flag was used on the movie, so the buyer can be made aware before the purchase.”</i>  <p><i><u>Hollywood</u></i><i><u> and the ICT Token</u></i>  <p><i>“In March 2006, Sony’s announced their position not to implement the down-res feature of "Image Constraint Token" (ICT) that is built into the AACS standard for the majority of its Blu-ray content and allow Hi Def players to playback HD as 1080i over component analog connections.”</i>  <p><i>“Other Hollywood studios declared that they were following Sony’s position as well; 20th Century Fox (NWS), Disney (DIS), Universal, and Paramount (VIA) said they initially would not use the ICT Token on their releases.”</i>  <p><i>“However, Warner Brothers said that the studio most likely would release some HD-DVD titles through April implementing the ICT.”</i>  <p><i>-------------------------</i>  <p>In other words, an unprotected analog connection is viewed as a risk because an HD analog signal can be re-digitized using an analog-to-digital converter. Pirates may use such equipment to make illegal digital versions of the content using the HD 1080i component analog outputs from Blu-ray players, a connection that cannot carry the High-bandwidth Digital Content Protection (<a href="http://www.hdtvmagazine.com/glossary.php#HDCP+%28High-bandwidth+Digital+Content+Protection%29">HDCP</a>) embedded into DVI or HDMI (a matter known as “analog hole”).  <p>Such illegal copy may not have the bit-by-bit digital quality of the original content because it was subjected to the steps of the conversion process, but a market for such level of quality may exist if the price is right, and such distribution is viewed as a considerable revenue loss by content producers.  <p>On June 19, 2009, an update of the AACS license conditions was made:  <p><a href="http://www.aacsla.com/license/AACS_Content_Participant_Agrmt_090619.pdf"><b>http://www.aacsla.com/license/AACS_Content_Participant_Agrmt_090619.pdf</b></a><b></b>  <p>I include below an excerpt of some relevant paragraphs from pages E-16 and E-17:  <p><i>-----------------------------</i>  <p><i>“2.2 Analog Outputs. A Licensed Player shall not pass, or direct to be passed Decrypted AACS Content to an analog output except:</i>  <p><i>2.2.1 An analog output of audio, or of the audio portions of other forms of Decrypted AACS content; or</i>  <p><i>2.2.2 An analog output of video delineated in Table A1, AACS Analog Authorized Outputs, in accordance with any associated restrictions and obligations specified therein and in the Agreement, and subject to the following sunset requirements:</i>  <p><i>2.2.2.1 Analog Sunset – 2010. With the exception of Existing Models, any Licensed Player manufactured after December 31, 2010 shall limit analog video outputs for Decrypted AACS Content to SD Interlace Modes only. Existing Models may be manufactured and sold by Adopter up until December 31, 2011. Notwithstanding the foregoing, Adopter may continue to manufacture and sell an Existing Model in which the implementation of AACS Technology is a Robust Inactive Product after December 31, 2010 provided that when such Robust Inactive Product is activated through a Periodic Update, such Periodic Update results in a Licensed Player that limits analog video outputs for Decrypted AACS Content to SD Interlace Modes only. Nothing in this section shall be interpreted to override limitations or obligations stated in any other section of this Agreement.</i>  <p><i>For purposes of this section, “SD Interlace Modes” shall mean composite video, s-video, 480i component video and 576i video.</i>  <p><i>2.2.2.2 Analog Sunset – 2013. No Licensed Player that passes Decrypted AACS Content to analog video outputs may be manufactured or sold by Adopter after December 31, 2013.” </i> <p><i>------------------------- </i> <p>The “analog sunset” deadlines mentioned above confirm again that millions of HDTVs will eventually be at risk when not able to display the HD image for which their TVs were designed. Additionally, that would affect many in-wall wiring installations made by professionals for home theaters and whole-house audio/video systems to distribute HD using component analog connections, <a href="http://www.cepro.com/article/hdmi_or_component_integrators_weigh_in">viewed by most</a> as a more reliable connection than DVI and HDMI (due to HDCP, and cable length issues).  <p>I personally double up all my video installations, digital and component analog cabling in parallel. It may cost more in wiring but the labor for in-wall installations is more expensive, not to mention the drywall repairs on an already built house, and more expensive would be to perform a labor repeat if the component analog connections become disabled, or the HDMI/DVI cables become unreliable.  <p><b></b> <h2>The Collateral Damage on A/V Equipment </h2> <p>Over the past decade, consumers experienced a variety of content protection methods designed to deter the making of illegal digital copies of protected content. The industry made consumers go thru DVD/CSS, 1394/<a href="http://www.hdtvmagazine.com/glossary.php#DTCP+%28Digital+Transmission+Content+Protection%29">DTCP</a>, <a href="http://www.hdtvmagazine.com/glossary.php#DVI+%28Digital+Visual+Interface%29">DVI</a>/<a href="http://www.hdtvmagazine.com/glossary.php#HDCP+%28High-bandwidth+Digital+Content+Protection%29">HDCP</a>, <a href="http://www.hdtvmagazine.com/glossary.php#HDMI">HDMI</a>/<a href="http://www.hdtvmagazine.com/glossary.php#HDCP+%28High-bandwidth+Digital+Content+Protection%29">HDCP</a>, Selectable Output Controls (SOC), Broadcast Flag, Blu-ray managed copy, and soon <a href="http://www.hdtvmagazine.com/articles/2008/06/high_definition_movies_before_they_hit_bluray_only_if_your_hdtv_permits_it.php">what Hollywood was recently lobbying for</a>.  <p>On a similar vein, consumers were subjected to the protection of 7.1 digital channels of multi-channel audio using analog outputs, and the endless battles of lossless and lossy audio codecs. Consumers were also subjected to the multi-channel competition with extra surround speakers the industry deems consumers “have to have” to hear the 5-second helicopter flight scene over their heads with a ceiling speaker, a reborn concept <a href="http://www.hdtvmagazine.com/glossary.php#Ceiling+Surround+channel%2Fspeaker">implemented by ADS in the seventies</a>. Not to mention the ever “evolving” HDMI versions (1.0 to 1.4 or is soon 1.5/.6/.7/.8?) of specification and microchips, with options within those versions implemented by manufacturers as they want, “motivating” consumers to replace expensive equipment that was just purchased, which is generally obsolete the minute it leaves the store.  <p>Audio/video receivers, DTVs, players, etc. are continuously forced to carry an old bag of backward compatibility obligations within their design due to unresolved connectivity and content protection issues. Equipment is subjected to self-inflicted obsolescence that provoke early replacement of an otherwise perfectly functional audio/video piece when is unable to be upgraded seamlessly with firmware. Equipment transformations are no longer purposed to improve their primary function, image, or sound, rendering it incapable to be a medium-lasting product to justify the investment.  <h2>Finding the Solution </h2> <p>The <a href="http://www.hdtvmagazine.com/articles/2006/02/is_hdtv_complex_enough.php">connectivity confusion</a> affects consumers but also affects content creators. Exploring each other’s reasoning can be a healthy endeavor if done constructively to reach a mutually beneficial agreement, rather than letting the FCC and Congress once again do the “reasoning” under Hollywood’s pressure, and increase everyone’s blood pressure.  <p>So what exactly is the appropriate solution for the ever-evolving analog/digital connection that seems not capable to prevent piracy? Should the industry ignore piracy and concentrate on consumers? Is the loss of revenue attributed to piracy justly estimated? Could that estimate justify so much complexity? Should the connection/protection be the solution or should another content distribution model be implemented? Should the price of content include an overhead for the product to be unprotected and still produce a reasonable ROI including an estimated revenue loss due to piracy? That would certainly facilitate the connectivity complexity of law obedient consumers and equipment manufacturers, but it would make everyone pay extra, could that be agreeable to consumers? Is it justified the complexity and the higher cost of equipment that has to be redesigned to augment the floor plan of back panels to be able to offer expanded backward compatibility with all possible connections?  <p>Frankly, it does not seem I would be able to see an amicable and balanced solution before my days are over. I recall all this started long before the consumer had access to digital. Many years ago, it was to disallow the making of analog copies from analog originals, considered a big problem even when multiple analog generations suffered cumulative degradation when cascading down from the original.  <p>The possibility of mass producing illegal bit-per-bit/ lossless digital copies that may look and sound as good as the original feeds more negative energy every time a new style of delivery of content is proposed, such as the <a href="http://www.hdtvmagazine.com/articles/2008/06/high_definition_movies_before_they_hit_bluray_only_if_your_hdtv_permits_it.php">Hollywood proposal</a> for a new delivery of compelling content.  <p>Without solving the problem of content protection at its roots first and without implementing a well-planned vision and technology for an equal protection of consumers and creators of content, the matter only becomes increasingly complicated for the media, the electronics, and the consumers.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>November 19, 2009  2:55 PM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(3383)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 3383)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2009/11/who-needs-content-protection.php" type="text/javascript" charset="utf-8"></script>
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
