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
	$res_author = mQuery("SELECT title, channel, amazon_tracking_id, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Lee Wood'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Lee Wood" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 159 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 159
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=More News For July 25, 2005 from Lee Wood&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/news/2005/07/more-news-for-july-25-2005-from-lee-wood.php&amp;title=More News For July 25, 2005 from Lee Wood">
		<span style="display:none">524 Days Until the Scheduled End of Analog Television Broadcasting The Digital Picture Needs Fine-Tuning It is time for Congress and the Federal Communications Commission to lay out a clear plan for the transition from analog to digital television that...</span></a>
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

	switch (7) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 159";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download More News For July 25, 2005 from Lee Wood" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="More News For July 25, 2005 from Lee Wood" />
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
	<title>HDTV Magazine - More News For July 25, 2005 from Lee Wood</title>
	<meta name="keywords" content="engineering broadcastengineering, broadcast engineering, broadcastengineering newsletters, newsletters rfupdate, tvtechnology features, digital, news, article, broadcast, cable, television, technology, via, tech, new, broadcasting, features, newsletters, broadcastengineering, display, engineering, hdtv, high, chieftain, shtml" />
	<meta name="description" content="524 Days Until the Scheduled End of Analog Television Broadcasting The Digital Picture Needs Fine-Tuning It is time for Congress and the Federal Communications Commission to lay out a clear plan for the transition from analog to digital television that..." />
	<meta name="title" content="More News For July 25, 2005 from Lee Wood" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />

	<script type="text/javascript">
		// Digg Script
		(function() {
			var s = document.createElement('SCRIPT'), s1 = document.getElementsByTagName('SCRIPT')[0];
			s.type = 'text/javascript';
			s.src = 'http://widgets.digg.com/buttons.js';
			s1.parentNode.insertBefore(s, s1);
		})();

		function init() {
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/news/2005/07/more-news-for-july-25-2005-from-lee-wood.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=159', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2005/07/more-news-for-july-25-2005-from-lee-wood.php">More News For July 25, 2005 from Lee Wood</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Lee Wood</b> on <b>July 25, 2005</b>
							</td><td id="article_category">
								Categories: 
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
				<p><strong>524 Days Until the Scheduled End of Analog Television Broadcasting</strong><br />
 </p>

<p><strong>The Digital Picture Needs Fine-Tuning</strong><br />
It is time for Congress and the Federal Communications Commission to lay out a clear plan for the transition from analog to digital television that includes protections for consumers, a compromise path for broadcast and cable over the tricky issue of must-carry, a plan for helping low-income viewers, clear labeling for all TV sets and a plan for public interest broadcasting.</p>

<p>(TelevisionWeek)</p>

<p><u>http://www.tvweek.com/article.cms?articleId=28436</u><br />
 </p>

<p><strong>JupiterResearch Forecasts Digital Video Recorders Will be in Nearly Half of All U.S. Households Within Five Years</strong><br />
JupiterResearch, a division of Jupitermedia Corporation, today released its annual digital television (DTV) forecast entitled, "U.S. DTV Forecast, 2005-2010," which presents JupiterResearch's latest projections of the growth of digital television, including HDTV, in U.S. households.</p>

<p>(Business Wire via Yahoo News)</p>

<p><a href="http://biz.yahoo.com/bw/050721/215469.html?.v=1">http://biz.yahoo.com/bw/050721/215469.html?.v=1</a><br />
 </p>

<p><strong>Picture bright for U.S. digital TV industry</strong><br />
Makers of high-definition television sets and digital video recorders are looking at a windfall, with nearly half the households in the United States expected to get a DVR by decade's end, a study says.</p>

<p>(CNET News.com / ZDNet News)</p>

<p><a href="http://news.com.com/Picture+bright+for+U.S.+digital+TV+industry/2100-1041_3-5800183.html">http://news.com.com/Picture+bright+for+U.S.+digital+TV+industry/2100-1041_3-5800183.html</a><br />
http://news.zdnet.com/2100-1040_22-5800183.html </p>

<p><strong>DTV to NTSC - Issues Remain in U.S. Transition</strong><br />
What seemed like a never-ending story may actually come to a conclusion.</p>

<p>(Broadcaster Magazine)</p>

<p><a href="http://www.broadcastermagazine.com/article.asp?id=45580&issue=07192005">http://www.broadcastermagazine.com/article.asp?id=45580&issue=07192005</a><br />
 </p>

<p><strong>Digital TV: Tech sees new uses in old spectrum</strong></p>

<p>A two-decade debate in Washington, D.C., over the television spectrum now occupied in the U.S. by some analog TV stations may come to an end this year, with IT vendors and broadband users the beneficiaries.</p>

<p>(IDG News Service via IT World)</p>

<p><a href="http://www.itworld.com/Tech/2987/050721digitaltv/">http://www.itworld.com/Tech/2987/050721digitaltv/</a><br />
 </p>

<p>Spectrum Battle Heats Up</p>

<p><strong>First responders need bandwidth for comms </strong><br />
(TV Technology)</p>

<p><a href="http://www.tvtechnology.com/features/news/n_spectrum_battle.shtml">http://www.tvtechnology.com/features/news/n_spectrum_battle.shtml</a><br />
 </p>

<p><strong>Fritts appeals to Congress for multicasting support on cable</strong><br />
Cable association leader Kyle McSlarrow says doing so would be harmful to consumers. </p>

<p>(Broadcast Engineering)</p>

<p><a href="http://broadcastengineering.com/newsletters/rfupdate/20050722/#Fritts">http://broadcastengineering.com/newsletters/rfupdate/20050722/#Fritts</a><br />
 <br />
 </p>

<p><strong>Shapiro tells committee "disenfranchisement" claims are exaggerated</strong><br />
The CEA chief told lawmakers that a survey revealed 87 percent of the 110 million U.S. TV households are receiving local and national broadcast programming via cable or satellite. </p>

<p>(Broadcast Engineering)</p>

<p><a href="http://broadcastengineering.com/newsletters/rfupdate/20050722/#Shapiro">http://broadcastengineering.com/newsletters/rfupdate/20050722/#Shapiro</a><br />
 </p>

<p><strong>Don't throw out that old TV yet</strong><br />
(Pueblo, CO Chieftain)</p>

<p><a href="http://www.chieftain.com/business/1122213601/2">http://www.chieftain.com/business/1122213601/2</a><br />
 </p>

<p><strong>Crossing the digital TV divide</strong><br />
Cable TV joins in offering first true high-def package</p>

<p>(Pueblo, CO Chieftain)</p>

<p><a href="http://www.chieftain.com/business/1122213601/1">http://www.chieftain.com/business/1122213601/1</a><br />
 </p>

<p><strong>New TVs don't pass FCC test</strong><br />
Despite FCC mandates, many digital sets on the market don't have the required tuners included.</p>

<p>(Broadcast Engineering)</p>

<p><a href="http://broadcastengineering.com/newsletters/bth/20050724/#test">http://broadcastengineering.com/newsletters/bth/20050724/#test</a><br />
 </p>

<p><strong>HD May Be too Good to Last</strong><br />
I've gone gaga over HDTV, even though there still isn't a lot of programming available. As when color TV began, you find yourself watching something because of the process in which it's being transmitted, not because of the content.</p>

<p>(Television Week)</p>

<p><a href="http://www.tvweek.com/article.cms?articleId=28464">http://www.tvweek.com/article.cms?articleId=28464</a><br />
 </p>

<p> </p>

<p><strong>HDNet reups with PanAmSat  [Paid Subscription Required]</strong><br />
HDNet and PanAmSat have agreed on a long-term extension of its current multi-transponder deal that sends out HDNet to U.S. cable and DBS operators. The deal now runs through 2021.</p>

<p>(Broadcasting & Cable)</p>

<p><a href="http://www.broadcastingcable.com/article/CA628939?display=Breaking+News">http://www.broadcastingcable.com/article/CA628939?display=Breaking+News</a><br />
 </p>

<p><strong>UPN gives HD a 10  [Paid Subscription Required]</strong><br />
UPN says it will broadcast 10 of its series in high-definition this fall, the network's largest HD offering to date.</p>

<p>(Broadcasting & Cable)</p>

<p><a href="http://www.broadcastingcable.com/article/CA628548?display=Breaking+News">http://www.broadcastingcable.com/article/CA628548?display=Breaking+News</a><br />
 </p>

<p><strong>NFL Network airs 15 preseason games in HDTV</strong><br />
(NFL.com)</p>

<p><a href="http://www.nfl.com/nflnetwork/story/8664135">http://www.nfl.com/nflnetwork/story/8664135</a><br />
 </p>

<p> <br />
 <br />
<strong>Event Distributors Begin to Jab at HD  </strong>[Paid Subscription Required]</p>

<p>Production Costs Have Prevented A Full Embrace For the Format</p>

<p>(Multichannel News)</p>

<p><a href="http://www.multichannel.com/article/CA628692.html?display=Pay+Per+View">http://www.multichannel.com/article/CA628692.html?display=Pay+Per+View</a><br />
 </p>

<p> <strong>High Definition Comes To Videogames at E3</strong><br />
This year's E3 blast in Los Angeles in mid-May offered conclusive proof of why the videogame industry can claim its revenues of about $12 billion are "bigger than Hollywood." </p>

<p>(TV Technology)</p>

<p><a href="http://www.tvtechnology.com/features/tuning_in/f_gary_arlen.shtml">http://www.tvtechnology.com/features/tuning_in/f_gary_arlen.shtml</a><br />
 </p>

<p><strong>What About the Tiny TVs?</strong>  [The Masked Engineer: Mario Orazio]</p>

<p>You might not have noticed that there are numbers smaller than 13. I point this out because the FCC, Our Beloved Commish, seems to have just made the discovery. </p>

<p>(TV Technology)</p>

<p><a href="http://www.tvtechnology.com/features/Masked-Engineer/f_mario_orazio.shtml">http://www.tvtechnology.com/features/Masked-Engineer/f_mario_orazio.shtml</a><br />
 </p>

<p><strong>Several Issues Cloud Picture With New HDTV Set</strong><br />
The digital-TV transition can still seem a fuzzy, far-off thing, even though it's now been almost seven years since the first customer took home a high-definition digital set.</p>

<p>(Washington, DC Post via Hartford, CT Courant)</p>

<p><a href="http://www.courant.com/technology/hc-homehdtv0722.artjul22,0,3302292.story?&track=rss">http://www.courant.com/technology/hc-homehdtv0722.artjul22,0,3302292.story?&track=rss</a><br />
 </p>

<p><strong>New LCD and plasma TVs show their strengths</strong><br />
(Independent Online)</p>

<p><a href="http://www.iol.co.za/index.php?set_id=1&click_id=115&art_id=qw1122194342656R131">http://www.iol.co.za/index.php?set_id=1&click_id=115&art_id=qw1122194342656R131</a><br />
 </p>

<p><strong>Samsung and Sony, the Clashing Titans, Try Teamwork</strong><br />
(New York Times via Lakeland, FL Ledger)</p>

<p><a href="http://www.theledger.com/apps/pbcs.dll/article?AID=/20050725/ZNYT01/507250368/1001/BUSINESS">http://www.theledger.com/apps/pbcs.dll/article?AID=/20050725/ZNYT01/507250368/1001/BUSINESS</a><br />
 </p>

<p><strong>Dell UltraSharp 2405FPW 24-inch LCD Display</strong><br />
Big, wide flat panel offers exquisite output, low price, unique features</p>

<p>(Broadcast Newsroom / HDTV Buyer)</p>

<p><a href="http://www.broadcastnewsroom.com/articles/viewarticle.jsp?id=33448">http://www.broadcastnewsroom.com/articles/viewarticle.jsp?id=33448</a><br />
<a href="http://www.hdtvbuyer.com/articles/viewarticle.jsp?id=33448">http://www.hdtvbuyer.com/articles/viewarticle.jsp?id=33448</a><br />
 </p>

<p> </p>

<p><br />
<strong>Yamaha DPX-1200 DLP projector</strong><br />
(Ultimate AV)</p>

<p><a href="http://www.guidetohometheater.com/videoprojectors/705yamaha/">http://www.guidetohometheater.com/videoprojectors/705yamaha/</a><br />
 </p>

<p><strong>MSTV, NAB extend set top box RFQ deadline</strong><br />
The RFQ includes the target parameters and specifications for a prototype terrestrial digital converter box. </p>

<p>(Broadcast Engineering)</p>

<p><a href="http://broadcastengineering.com/newsletters/rfupdate/20050722/#MSTV">http://broadcastengineering.com/newsletters/rfupdate/20050722/#MSTV</a><br />
 </p>

<p> <br />
<strong>Tech-Note #131  [Adobe PDF File]</strong><br />
(Tech-Notes)</p>

<p><a href="http://www.tech-notes.tv/Archive/tech_notes_131.pdf">http://www.tech-notes.tv/Archive/tech_notes_131.pdf</a><br />
 </p>

<p><br />
<strong>The Superheterodyne Concept and Reception  [Charles W. Rhodes]</strong><br />
Today we don't use vacuum tubes in receivers, but all radio and TV receivers use Armstrong's superheterodyne receiver principle. The strengths and weaknesses of this invention are important to the future of terrestrial TV broadcasting, so please read on ; you can quickly become an expert on superheterodyne receivers and amaze your boss. </p>

<p>(TV Technology)</p>

<p><a href="http://www.tvtechnology.com/features/digital_tv/f_charles_Rhodes.shtml">http://www.tvtechnology.com/features/digital_tv/f_charles_Rhodes.shtml</a><br />
 </p>

<p> </p>

<p><strong>ETSI approves DMB standard for mobile TV</strong><br />
The European Telecommunications Standard Institute (ETSI) has approved the Digital Multimedia Broadcasting (DMB) standards for the delivery of mobile television services.</p>

<p>(Digital TV Group)</p>

<p><a href="http://griffin.dtg.org.uk/news/news.php?class=countries&subclass=193&id=1028">http://griffin.dtg.org.uk/news/news.php?class=countries&subclass=193&id=1028</a><br />
 </p>

<p><strong>'Olympics could put back London switchover'  [UK]</strong><br />
(Digital TV Group)</p>

<p><a href="http://griffin.dtg.org.uk/news/news.php?class=countries&subclass=193&id=1031">http://griffin.dtg.org.uk/news/news.php?class=countries&subclass=193&id=1031</a> </p>

<p>Digital Switchover Statement by the Secretary of State  [UK]</p>

<p>(Department for Culture, Media and Sport)</p>

<p>http://www.digitaltelevision.gov.uk/press/2005/statement_ds.html</p>

<p> </p>

<p><strong>Welsh analogue-to-digital TV switchover trial is a success  [UK]</strong><br />
(PublicTechnology.net)</p>

<p><a href="http://www.publictechnology.net/modules.php?op=modload&name=News&file=article&sid=3322">http://www.publictechnology.net/modules.php?op=modload&name=News&file=article&sid=3322</a><br />
 </p>

<p><br />
<strong>DVB-H test to begin in Spain  [Spain]</strong><br />
The pilot project will take place in Madrid and Barcelona between September 2005 and February 2006. </p>

<p>(Broadcast Engineering)</p>

<p><a href="http://broadcastengineering.com/newsletters/rfupdate/20050722/#DVB">http://broadcastengineering.com/newsletters/rfupdate/20050722/#DVB</a><br />
 </p>

<p><strong>Focus - Digital divide  [Malta]</strong><br />
Many are wondering what the hype surrounding digital TV is all about. MASSIMO FARRUGIA looks into some aspects of the new technology and why some of the broadcasters are unhappy with the latest policies.</p>

<p>(Times of Malta)</p>

<p><a href="http://www.timesofmalta.com/core/article.php?id=194093">http://www.timesofmalta.com/core/article.php?id=194093</a><br />
 </p>

<p><strong>Italy regulator sees digital TV loosening Mediaset, RAI mkt grip  </strong>[Italy]</p>

<p>(Interactive Investor)</p>

<p><a href="http://www.iii.co.uk/news/?type=afxnews&articleid=5359017&subject=companies&action=article">http://www.iii.co.uk/news/?type=afxnews&articleid=5359017&subject=companies&action=article</a><br />
 </p>

<p><strong>Mediaset launches DTT movies on demand  [Italy]</strong><br />
(Digital TV Group)</p>

<p><a href="http://griffin.dtg.org.uk/news/news.php?class=countries&subclass=0&id=1033">http://griffin.dtg.org.uk/news/news.php?class=countries&subclass=0&id=1033</a><br />
 </p>

<p><strong>Analysis: China Allows Private Investment in March to Digital TV  </strong>[China]</p>

<p>(BBC Monitoring Media Services via RedNova)</p>

<p><a href="http://www.rednova.com/news/display/?id=183160&source=r_technology">http://www.rednova.com/news/display/?id=183160&source=r_technology</a><br />
 </p>

<p><strong>Gov't to help land-based digital broadcasters outside big cities  </strong>[Japan]</p>

<p>(Kyodo via Yahoo News)</p>

<p><a href="http://asia.news.yahoo.com/050722/kyodo/d8bgb9vg1.html">http://asia.news.yahoo.com/050722/kyodo/d8bgb9vg1.html</a><br />
 </p>

<p> </p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Lee Wood</b>, <b>July 25, 2005  9:32 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(159)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Lee Wood', 159)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author['bio_short'] != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Lee Wood</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2005/07/more-news-for-july-25-2005-from-lee-wood.php" type="text/javascript" charset="utf-8"></script>
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
