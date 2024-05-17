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
	$res_author = mQuery("SELECT title, channel, amazon_tracking_id, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Dale Cripps" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 88 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 88
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=Technology, Television, and Competition : The Politics of Digital TV&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/articles/2005/06/technology-television-and-competition-the-politics-of-digital-tv.php&amp;title=Technology, Television, and Competition : The Politics of Digital TV">
		<span style="display:none">For those of you who have a fascination with the evolution of HDTV I want to bring to your attention one book in particular. The author, Dr. Jeffery Hart, and I have been friends for 20 years. His insights into the global electro-political landscape will open your eyes to the difficulty that HDTV had in taking root here in the United States. This book is available through Amazon.com and through our bookstore. It's a fascinating read and not enough have...</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 88";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Technology, Television, and Competition : The Politics of Digital TV" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Technology, Television, and Competition : The Politics of Digital TV" />
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
	<title>HDTV Magazine - Technology, Television, and Competition : The Politics of Digital TV</title>
	<meta name="keywords" content="advanced television, definition television, digital television, united states, consumer electronics, television, broadcasting, association, digital, hdtv, national, advanced, european, research, technology, chapter, japan, book, satellite, telecommunications, high, industries, electronics, europe, committee" />
	<meta name="description" content="For those of you who have a fascination with the evolution of HDTV I want to bring to your attention one book in particular. The author, Dr. Jeffery Hart, and I have been friends for 20 years. His insights into the global electro-political landscape will open your eyes to the difficulty that HDTV had in taking root here in the United States. This book is available through Amazon.com and through our bookstore. It's a fascinating read and not enough have..." />
	<meta name="title" content="Technology, Television, and Competition : The Politics of Digital TV" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/articles/2005/06/technology-television-and-competition-the-politics-of-digital-tv.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=88', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2005/06/technology-television-and-competition-the-politics-of-digital-tv.php">Technology, Television, and Competition : The Politics of Digital TV</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>June 17, 2005</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=13&category=Education">Education</a></b>
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
				<p>For those of you who have a fascination with the evolution of HDTV I want to bring to your attention one book in particular.  The author, Dr. Jeffery Hart, and I have been friends for 20 years. His insights into the global electro-political landscape will open your eyes to the difficulty that HDTV had in taking root here in the United States. This book is available through Amazon.com and through our <a href="http://www.hdtvmagazine.com/store/book.php?asin=0521826241">bookstore</a>. It's a fascinating read and not enough have read it due, perhaps, to its academia heritage and credentials. But don't let its pedigree stop you. HDTV is a grand subject that is on the thresholds of delivering an enormous impact around the world. It has a cultural value that makes it more significant than any other communications tool yet devised. Of course, you have to take my word on that since we have only a tiny proof of the pudding yet showing.  </p>

<p>I am including in this column the chapters in Jeff's fascinating book. It's not for everyone, of course, but for those who must take on leadership roles in the 21st century it is indispensable I more than urge you to acquire it from your local library or from our book store. We will be using many of its chapters as tutorials on topics to be further discussed here on the HDTV Magazine blog. I have also included a very useful list of Acronyms straight out of the book, which I suggest you save.<br />
 </p>

<p><strong>Technology, Television, and Competition: The Politics of Digital TV</strong></p>

<p><em>Not a Pretty Picture:<br />
The Politics of Advanced Television</em></p>

<p>by<br />
Jeffrey A. Hart</p>

<p><br />
Contents of Jeff's book.</p>

<p>Acronyms</p>

<p>Preface</p>

<p>Acknowledgements<br />
Chapter 1. Introduction                             1<br />
Chapter 2. The Institutional Setting for HDTV      23<br />
Chapter 3. Digital Convergence                     85<br />
Chapter 4. HDTV in Japan                          118<br />
Chapter 5. HDTV in the United States              139<br />
Chapter 6. HDTV in Europe                         162<br />
Chapter 7. Digital Television in the United States 203<br />
Chapter 8. Digital Television in Europe and Japan  244<br />
Chapter 9. Conclusions                             279</p>

<p>Preface<br />
High definition television (HDTV) became a contentious issue in American politics after the European Community rejected a bid in 1986 by the Japanese national broadcasting company, Nippon Hoso Kyokai (NHK), to have its HDTV production method adopted as an international standard.  The U.S. government supported the Japanese effort initially, but after the European rejection, many people in the United States began the question that support.  For some, the Japanese HDTV initiative raised concerns about the relative decline in U.S. competitiveness, even in high technology industries, and the need to respond more effectively to the increased competition from Japan and Western Europe.  For others, HDTV was important because it might affect a wide range of industries -- broadcasting, film, video, consumer electronics, computers, and telecommunications -- and therefore needed to be considered more carefully before buying into the Japanese approach.  As a result, the United States began a process to choose a standard for advanced TV that took until April 1997 to reach its conclusion.  The U.S. choice of a digital television (DTV) standard forced both Japan and Europe to reexamine their earlier decisions on HDTV.  This book is about the forces behind these events.<br />
 <br />
<strong>Acknowledgements</strong><br />
The research for this book was supported by research grants and contracts from the Office of Technology Assessment of the U.S. Congress, Motorola, Inc., the Berkeley Roundtable on the International Economy, the Electronic Industries Association, the National Center for Manufacturing Sciences, the Advanced Research Projects Administration of the Department of Defense, the Technology Transfer Institute, the College of Arts and Sciences of Indiana University, the West European Studies Department of Indiana University, the Alfred Sloan Foundation, and Stanford Resources, Inc.</p>

<p>I would like to thank the following individuals for their help and encouragement: François Bar, Michael Borrus, Joel Brinkley, Joseph Castellano, Alan Cawson, Stephen Cohen, <strong>Dale Cripps</strong>, Warren Davis, Joseph Donahue, Darcy Gerbarg, Larry Irving, Greg Kasza, Hans Kleinsteuber, Ellis Krauss, Stefanie Lenway, Junji Matsuzaki, David Mentley, Jörg Meyer-Stamer, Ed Miller, Tom Murtha, Russ Neuman, Elie Noam, Greg Noble, Aseem Prakash, Jerry Pearlman, Harmeet Sawhney, William Schreiber, Pete Seel, Gary Shapiro, Marko Slusarczuk, Alvy Ray Smith, Sid Topol, Laura Tyson, Adam Watson-Brown, and John Zysman.</p>

<p>Research assistance for this book was provided by the following current and former students at Indiana University: George Candler, Sangbae Kim, Mark Marone, Craig Ortsey, Khalil Osman, Aseem Prakash, and Robert Reed.  John Thomas co-authored an earlier version of Chapter 6.</p>

<p>List of Acronyms</p>

<p>ABC	American Broadcasting Corporation<br />
ACATS	Advisory Committee on Advanced Television Services<br />
ACTV	Advanced Compatible Television<br />
AEA	American Electronics Association<br />
ALTV	Association for Low-Power Television<br />
AMST	Association of Maximum Service Telecasters<br />
ANSI	American National Standards Institute<br />
ARD	Arbeitsgemeinschaft der Rundfunkanstalten Deutschlands<br />
ARPA	Advanced Research Projects Agency (see also DARPA)<br />
ATM	asynchronous transfer mode<br />
ATRC	Advanced Television Research Consortium<br />
ATSC	Advanced Television Standards Committee<br />
AT&T	American Telephone and Telegraph<br />
ATTC	Advanced Television Testing Center<br />
ATV	advanced television<br />
BBC	British Broadcasting Company<br />
BCG	Boston Consulting Group<br />
BBC	British Digital Broadcasting<br />
BIB	British Interactive Broadcasting<br />
BRITE	Basic Research in Industrial Technologies for Europe<br />
BSB	British Satellite Broadcasting<br />
BskyB	British Sky Broadcasting<br />
BSS	broadcast satellite services<br />
BTA	Broadcasting Technology Association<br />
CATV	community antenna television<br />
CBS	Columbia Broadcasting System<br />
CCD	charge-coupled device<br />
CCIR	Comité Consultatif International de Radio-Diffusion<br />
CECC	Consumer Electronics Capital Corporation<br />
CEMA	Consumer Electronics Manufacturers Association<br />
CENELEC	European Committee for Electrotechnical Standardization<br />
CEO	chief executive officer<br />
CICATS	Computer Industry Committee on Advanced Television Standards<br />
CIF	common image format<br />
CLT	Compagnie Luxembourgeoise de Télévision<br />
CNCL	Commission Nationale de la Communication et des Libertés<br />
COFDM	coded orthogonal frequency division multiplex<br />
COO	chief operating officer<br />
CRT	cathode ray tube<br />
DARPA	Defense Advanced Research Projects Agency (see also ARPA)<br />
DBS	direct broadcast satellite<br />
DG	Directorate General (European Union)<br />
DIVINE	digital video narrowband emission<br />
DM	deutsche mark<br />
DSS	digital satellite service<br />
DTH	direct to home<br />
DTN	Digital Television Network<br />
DTV	digital television<br />
DTVJ	DirecTV Japan<br />
DVB	Digital Video Broadcasting<br />
DVD	digital versatile disk<br />
EACEM	European Association of Consumer Electronics Manufacturers<br />
EBU	European Broadcasting Union<br />
EDTV	enhanced definition television<br />
EIA	Electronic Industries Association (new name: Electronic Industries Alliance)<br />
EIAJ	Electronic Industries Association of Japan (new name: see JEITA)<br />
EPG	electronic program guide<br />
ESPRIT	European Strategic Program for Research and Development in <br />
	Information Technology<br />
ETSI	European Telecommunications Standards Institute<br />
EU	European Union<br />
Eureka	European Research Coordinating Agency<br />
FCC	Federal Communications Commission<br />
FSS	fixed satellite services<br />
GA	Grand Alliance<br />
GDL	Grand Duchy of Luxembourg<br />
GI	General Instrument<br />
HBO	Home Box Office<br />
HD-MAC	high definition multiplexed analog components<br />
HDTV	high definition television<br />
IBA	Independent Broadcasting Authority (UK)<br />
IDTV	improved definition television<br />
IEEE	Institute of Electrical and Electronics Engineers<br />
INTV	Association of Independent Television Stations<br />
ISDB	integrated services digital broadcasting<br />
ISDN	integrated services digital network<br />
ITC	Independent Television Commission<br />
ITU	International Telegraphic Union<br />
JEITA	Japan Electronics and Information Technology Industries Association<br />
JESSI	Joint European Semiconductor Silicon Initiative<br />
JSAT	Japan Satellite Broadcasting<br />
JSB	Japan Satellite Broadcasting<br />
LPTV	low power television<br />
MAC	multiplexed analog components<br />
MHz	megahertz<br />
MIT	Massachusetts Institute of Technology<br />
MITI	Ministry of International Trade and Industry<br />
MMBG	Multimedia Betriebsgesellschaft<br />
MOU	memorandum of understanding<br />
MPAA	Motion Picture Association of America<br />
MPEG	Motion Picture Experts Group<br />
MPT	Ministry of Posts and Telecommunications<br />
MST	Association of Maximum Service Telecasters<br />
MSTV	Association for Maximum Service Television<br />
MUSE	multiple sub-Nyquist sampling encoding<br />
MTV	Music Television<br />
NAB	National Association of Broadcasters<br />
NACB	National Association of Commercial Broadcasters<br />
NACS	National Advisory Committee on Semiconductors<br />
NBC	National Broadcasting Company<br />
NCTA	National Cable Television Association<br />
NHK	Nipon Hoso Kyokai (Japan National Broadcasting)<br />
NII	National lnformation Infrastructure<br />
NPRM	Notice of Proposed Rule Making<br />
NTIA	National Telecommunications and Information Administration<br />
NTL	National Telecommunications Limited<br />
NTN	Nippon Television Network<br />
NTSC	National Television Standards Committee<br />
OfTel	Office of Telecommunications (UK)<br />
OMB	Office of Management and Budget<br />
ORTF	Office de Radio-diffusion Télévision Française<br />
PAL	phased alternation by line<br />
PBS	Public Broadcasting System<br />
PTT	postal, telegraphic, and telecommunications agency<br />
QAM	quadrature amplitude modulation<br />
RACE	Research and Development for Advanced Communications Technology <br />
	in Europe<br />
RAI	Radio Audironi Italiane, later Radiotelevisione Italiane<br />
RCA	Radio Corporation of America<br />
RTL	Radiodiffusion-Télévision Luxembourgeoise<br />
SC	Spectrum Compatible<br />
SDTV	standard definition television<br />
SECAM	séquential couleur à mémoire<br />
Sematech	Semiconductor Manufacturing Technology<br />
SES	Société Européenne des Satellites<br />
SMATV	satellite master antenna television<br />
SMPTE	Society of Motion Picture and Television Engineers<br />
TDF	Télédiffusion de France<br />
UHF	ultra high frequency<br />
VCR	videocassette recorder<br />
VHF	very high frequency<br />
VHSIC	Very High-Speed Integrated Circuits<br />
VSB	vestigial sideband<br />
WARC	World Administrative Radio Conference<br />
WAZ	Westfälisch Algemeine Zeitung</p>

<p><br />
</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>June 17, 2005  9:02 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(88)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 88)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author['bio_short'] != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Dale Cripps</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2005/06/technology-television-and-competition-the-politics-of-digital-tv.php" type="text/javascript" charset="utf-8"></script>
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
