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
	$res_author = mQuery("SELECT title, channel, amazon_tracking_id, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Shane Sturgeon" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 906 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 906
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=CES 2008: New HDTV Products and Technology Overview&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/articles/2008/01/ces-2008-new-hdtv-products-and-technology-overview.php&amp;title=CES 2008: New HDTV Products and Technology Overview">
		<span style="display:none">For those of you who have been receiving the &lt;a href=&quot;http://www.hdtvmagazine.com/news/bulletins.php&quot; target=&quot;_blank&quot;&gt;bulletins&lt;/a&gt; from CES over the past two weeks, you will recognize many of the topics below. I've picked some of the highlights and popular themes from this year's show and included a brief comment or two on each. This is not a comprehensive overview, but rather a &quot;highlight reel&quot; from the event. Here is a list of topics covered in this article. These are in no particular order:
&lt;ul&gt;&lt;li&gt;Warner Brothers Chooses to go Blu-ray Exclusive&lt;/li&gt;&lt;li&gt;Blu-ray Getting &quot;Bonus View&quot;&lt;/li&gt;&lt;li&gt;LaserTV&lt;/li&gt;&lt;li&gt;The Shift to Wireless HD&lt;/li&gt;&lt;li&gt;The Rise of Video Download Services&lt;/li&gt;&lt;li&gt;Netflix and LG to Partner on Streaming Video&lt;/li&gt;&lt;li&gt;Dish Network Commits to 100 HD Channels in 2008&lt;/li&gt;&lt;li&gt;Slingbox Pro-HD&lt;li&gt;Microsoft Mediaroom Getting Traction&lt;/li&gt;&lt;li&gt;JVC Lineup&lt;/li&gt;&lt;li&gt;LG Lineup&lt;/li&gt;&lt;li&gt;Panasonic Lineup&lt;/li&gt;&lt;li&gt;Pioneer Lineup&lt;/li&gt;&lt;li&gt;Sharp Lineup&lt;/li&gt;&lt;li&gt;Sony Lineup&lt;/li&gt;&lt;li&gt;Toshiba Lineup&lt;/li&gt;&lt;/ul&gt;</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 906";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download CES 2008: New HDTV Products and Technology Overview" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="CES 2008: New HDTV Products and Technology Overview" />
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
	<title>HDTV Magazine - CES 2008: New HDTV Products and Technology Overview</title>
	<meta name="keywords" content="blu ray, bonus view, hdmi cec, lcd models, announced new, new, announced, lineup, series, year, blu, ray, lcd, models, technology, plasma, hdmi, ces, dvd, sets, products, sony, wireless, feature, bonus" />
	<meta name="description" content="For those of you who have been receiving the &lt;a href=&quot;http://www.hdtvmagazine.com/news/bulletins.php&quot; target=&quot;_blank&quot;&gt;bulletins&lt;/a&gt; from CES over the past two weeks, you will recognize many of the topics below. I've picked some of the highlights and popular themes from this year's show and included a brief comment or two on each. This is not a comprehensive overview, but rather a &quot;highlight reel&quot; from the event. Here is a list of topics covered in this article. These are in no particular order:
&lt;ul&gt;&lt;li&gt;Warner Brothers Chooses to go Blu-ray Exclusive&lt;/li&gt;&lt;li&gt;Blu-ray Getting &quot;Bonus View&quot;&lt;/li&gt;&lt;li&gt;LaserTV&lt;/li&gt;&lt;li&gt;The Shift to Wireless HD&lt;/li&gt;&lt;li&gt;The Rise of Video Download Services&lt;/li&gt;&lt;li&gt;Netflix and LG to Partner on Streaming Video&lt;/li&gt;&lt;li&gt;Dish Network Commits to 100 HD Channels in 2008&lt;/li&gt;&lt;li&gt;Slingbox Pro-HD&lt;li&gt;Microsoft Mediaroom Getting Traction&lt;/li&gt;&lt;li&gt;JVC Lineup&lt;/li&gt;&lt;li&gt;LG Lineup&lt;/li&gt;&lt;li&gt;Panasonic Lineup&lt;/li&gt;&lt;li&gt;Pioneer Lineup&lt;/li&gt;&lt;li&gt;Sharp Lineup&lt;/li&gt;&lt;li&gt;Sony Lineup&lt;/li&gt;&lt;li&gt;Toshiba Lineup&lt;/li&gt;&lt;/ul&gt;" />
	<meta name="title" content="CES 2008: New HDTV Products and Technology Overview" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/articles/2008/01/ces-2008-new-hdtv-products-and-technology-overview.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=906', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2008/01/ces-2008-new-hdtv-products-and-technology-overview.php">CES 2008: New HDTV Products and Technology Overview</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January 17, 2008</b>
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
				<p>There was a fairly significant shift at this years CES in that most of the major manufacturers were beginning to focus on the design or art of the HDTV as much as the technology. Thin is in, as is glossy, curved corners, invisible/detachable speakers, splashes of color, etc. In all, this years sets are gorgeous, both on and off. Also new this year is a focus on products and services &quot;around&quot; the TV. Wireless options are being explored, additional HD services are launching, and peripherals capable of handling HD are becoming more and more common.</p>  <p>For those of you who have been receiving the <a href="http://www.hdtvmagazine.com/news/bulletins.php" target="_blank">bulletins</a> from CES over the past two weeks, you will recognize many of the topics below. I've picked some of the highlights and popular themes from this year's show and included a brief comment or two on each. This is not a comprehensive overview, but rather a &quot;highlight reel&quot; from the event. Here is a list of topics covered in this article. These are in no particular order: </p>  <ul>   <li>Warner Brothers Chooses to go Blu-ray Exclusive</li>    <li>Blu-ray Getting &quot;Bonus View&quot;</li>    <li>LaserTV</li>    <li>The Shift to Wireless HD</li>    <li>The Rise of Video Download Services</li>    <li>Netflix and LG to Partner on Streaming Video</li>    <li>Dish Network Commits to 100 HD Channels in 2008</li>    <li>Slingbox Pro-HD</li>    <li>Microsoft Mediaroom Getting Traction</li>    <li>JVC Lineup</li>    <li>LG Lineup</li>    <li>Panasonic Lineup</li>    <li>Philips Lineup</li>    <li>Pioneer Lineup</li>    <li>Sharp Lineup</li>    <li>Sony Lineup</li>    <li>Toshiba Lineup</li> </ul>  <h2>Warner Brothers Chooses to go Blu-ray Exclusive</h2>  <p>This announcement actually came out on Friday, but I wanted to include it here because there was a lot of talk about it at the show, and on various panels and in a few press conferences. This was apparently a complete surprise to the HD DVD group, who ended up canceling all one-on-one interviews as well as the HD DVD press event at the show so that they could &quot;re-group&quot; with the HD DVD partner companies and assess the situation. </p>  <p>The reason given for Warner Brothers announcement is that it is responding to consumers demand for a single format, and that their decision was for the consumers ultimate benefit. Essentially, Blu-ray was outselling HD DVD for most of the year by a factor of 2-to-1, and the general consensus among the studios was that there was a large segment of the population not buying <strong>any</strong> movies (HD or Standard DVD) as they were waiting to see which would be the &quot;next&quot; format. So in order to spur this non-consuming public to start consuming, WB chose Blu-ray in hopes that HD DVD would then go away and the would-be-consumer's conscience would be clear to make those purchases they've been sitting on for the past year. Only time will tell if that strategy pays off.</p>  <h2>Blu-ray Getting &quot;Bonus View&quot;</h2>  <p>Blu-ray players are now starting to come out with features to compete better with the HD DVD players. <strong>Bonus View</strong> is what is otherwise known as Profile 1.1. The Blu-ray association said they wanted to have a better marketing name for profile 1.1, so they are calling their picture-in-picture functionality Bonus View to make it easier for consumers to identify players and titles with this feature. </p>  <p>Philips have announced their first &quot;Bonus View&quot; player, the BDP7200, at the show. Samsung has also announced their second generation HD Duo dual format HD DVD/Blu-ray player which will be &quot;Bonus View&quot; enabled. The Samsung player is expected to ship in May for $599 suggested retail.</p>  <p><strong>BD Live</strong> is what is also known as Profile 2.0, and is the evolution of the Blu-ray standard that requires ethernet connections on all players. Along with the PS3, the Philips unit also has a built-in ethernet port and will be upgradeable to &quot;BD Live&quot; via firmware when the time comes.</p>  <h2>LaserTV</h2>  <p>Mitsubishi announced this year at CES a new class of televisions: LaserTV. The advantages of LaserTV are in color reproduction and power consumption. From their press release: &quot;Today's HDTVs display less than 40 percent of the color spectrum that the eye can see. Now, for the first time ever, laser produces twice the color. Laser beams provide the widest range of rich, complex colors, along with the most clarity and depth of field.&quot; LaserTV also consumes less power that comparably sized flat panel displays and can be wall-mounted just like plasma and LCD sets. Mitsubishi expects to ship LaserTVs to retailers later this year.</p>  <h2>The Shift to Wireless HD</h2>  <p>Several TV manufacturers have joined together to form the WirelessHD Consortium (WiHD), who's purpose appears to be a replacement for HDMI. WirelessHD delivers copy protected, uncompressed, no-loss, full HD content. The companies that make up this consortium are LG, Panasonic, NEC, Sibeam, Samsung, Sony, Toshiba and Intel. WiHD is able to transmit at data rates of 4Gbit/s at 33 feet which would sustain a full 1080p signal as well as multiple signals at lower resolutions. It can also support DTS HD Master Audio and Dolby TrueHD, making it quite a promising candidate for replacing HDMI someday. They expect the program to be finalized in June or July, so you should expect products this Christmas emblazoned with the WiHD logo.</p>  <h2>The Rise of Video Download Services</h2>  <p>I have an article coming up soon that will outline these in greater detail, but in short there are several new big players in the HD movie download space. VUDU, which was unveiled in September of last year, announced at CES that they will increase their library of HD 1080p content to over 70 titles by the end of January. A newcomer to the download space is XStreamHD. I have an article coming up about them soon too, but in short they plan to offer (starting in October) full HD movies at up to 100Mbit/s and are capable of handling 7.1 DTS HD Master Audio. Lastly, and most recently, Apple announced this week at Macworld that they have signed deals with all the major studios to rent and buy movies via iTunes and the Apple TV. The jury is still out on whether these will be available in 720p or 1080i.</p>  <h2>Netflix and LG to Partner on Streaming Video</h2>  <p>Not much to report on this yet. A deal was announced where LG will be building a box that will supposedly interface with Netflix's streaming movie service. The planned ship date for this box is June/July, but no pricing information is available at this time. It is possible that it may be built in to LG TVs down the road, but there are no current plans to do so. Netflix also announced recently that all but their lowest rental plan now come with unlimited streaming of movies via their &quot;Watch Instantly&quot; service. It remains to be seen what the quality will be like on this content.</p>  <h2>Dish Network Commits to 100 HD Channels in 2008</h2>  <p>Dish Network announced that they will be expanding their lineup of HD channels from 76 to 100 by the end of 2008. To accomplish this, they will be launching three new satellites to handle the load. In addition to the increase in national channels, Dish is making headway into providing local HD channels via satellite by announcing 11 new markets to receive local HD coverage.</p>  <h2>Slingbox Pro-HD</h2>  <p>Sling Media/Dish have announced a new product to their lineup of Slingbox's: The Slingbox Pro-HD. Prior to this, they have had products that would accept an HD signal, but it was still transmitted in SD. With the Pro-HD,&#160; you can now &quot;sling&quot; HD content as well.</p>  <h2>Microsoft Mediaroom Getting Traction</h2>  <p>Microsoft announced a deal with British Telecom (BT) for IPTV delivery via their Mediaroom platform in the UK. They have also announced that they are working with a number of partners here in the US and North America, but have not officially announced any signed partnerships. The Mediaroom platform runs on top of IPTV service from your provider and can reside either on a dedicated set top box or on an Xbox 360 console, should you have one.</p>  <h2>JVC Lineup</h2>  <p>JVC announced 10 new LCD models across 3 model series (P, SL and J), including a new size: 52&quot;. The P series will feature an iPod dock and a USB connection for photo viewing. All but one of these new sets are 1080p sets.</p>  <h2>LG Lineup</h2>  <p>LG has announced 24 new LCD models and 8 new plasma models. 17 of the 24 LCD models will be Full HD and 6 of the 8 plasma models will have 1080p. On their upper end sets (the LG71 series), they feature 120Hz technology which LG calls TruMotion, 802.11n wireless connectivity, and they are support HDMI-CEC, which LG is calling SimpleLink. Step down to the LG70 series and get all the same features except wireless connectivity. There are four other series announced (LG60, LG50, LG40 and LG30) that successively offer less functionality as you go down the line.</p>  <p>Their plasma series are the PG20, PG30, PG60 and PG70. The PG70 series is &quot;wireless ready&quot;, able to support wireless connectivity in the future when an add-on module is available. The PG30, 60 and 70 series are all 1080p sets while the PG20 entry level series is 720p.</p>  <h2>Panasonic Lineup</h2>  <p>Panasonic announced a new line of Full HD 1080p plasmas, but the crowd at the booth was much larger around their new 150&quot; Plasma. In total there are 10 new models, all featuring increased contrast ratio, HDMI-CEC compliance (Viera Link) and longer lasting panels. Their upper end also features THX Certification and 24p input.</p>  <h2>Philips Lineup</h2>  <p>One of the several companies focused on design this year at CES was Philips. They feature a minimalist design and curved bezel resulting in a less &quot;boxy&quot; TV. New this year are their 7400 and 7600 LCD series. Both series are available in 42&quot;, 47&quot; and 52&quot; sizes and feature 120Hz frame rate which they're calling &quot;ClearLCD&quot;. They also both feature four HDMI 1.3 inputs and HDMI-CEC. The 7600 series adds Ambilight back-lighting and features better sound options.</p>  <h2>Pioneer Lineup</h2>  <p>Although there were no new Pioneer Kuro plasma TVs announced at CES, they did debut some new Kuro technology that appears to take contrast ratios to the extreme. This new plasma technology is being marketed as the first plasma that is absolute black, with no measurable light emitting from the screen. No word on when this will be commercially available.</p>  <h2>Sharp Lineup</h2>  <p>Sharp announced 14 new LCD sets across 4 new lines, two new DLP projectors, and a second generation Blu-ray player. Among the new LCDs is a new &quot;Special edition&quot; (SE94) series, featuring full HD, high contrast, HDMI 1.3 and 120Hz frame rate which they're calling &quot;Fine Motion Advanced&quot;. Response time for the special edition series is less than 4ms and the viewing angle is 176 degrees. These SE94 models also include a built-in ethernet port for internet access using a built-in browser.</p>  <h2>Sony Lineup</h2>  <p>Like Toshiba, Sony had also announced previously that they were exiting the rear projection market. Their lineup this year consisted of mainly Bravia LCD models, all of which are HDMI-CEC (Bravia Sync) capable. Sony is equipping several of these new models with their 120Hz technology: MotionFlow. New for this year is a technology Sony calls DMeX (Digital Media eXtender). This is a plug-in technology for sets so equipped that will allow modules to be added on later as additional technologies come out. </p>  <p>The one notable exception to their LCD exclusivity is the first OLED (Organic Light Emitting Diode) TV for the US market. Their entry into the OLED market is an 11&quot; display measuring a mere 3mm in thickness. It has a resolution of 960x540 and boasts of a contrast ratio of 1,000,000:1. The set is available for purchase through Sony Style stores for about $2,500 USD.</p>  <h2>Toshiba Lineup</h2>  <p>In addition to the new TVs announced, Toshiba also announced that they will now be introducing new lines twice a year. Products announced at CES will be shipping in February/March, and products announced at CEDIA will ship in August/September. Since Toshiba announced last year that they will be dropping their plasma and rear projection products, all TVs announced this year were in their LCD line. Like most other LCD manufacturers, they also are incorporating 120Hz technology they are calling ClearFrame. With their ClearFrame feature they are offering both &quot;interpolated&quot; and 5:5 pull-down modes.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January 17, 2008 10:28 PM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(906)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 906)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author['bio_short'] != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Shane Sturgeon</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2008/01/ces-2008-new-hdtv-products-and-technology-overview.php" type="text/javascript" charset="utf-8"></script>
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
