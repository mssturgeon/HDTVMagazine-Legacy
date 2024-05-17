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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 548 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 548
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=BitTorrent, Sign of the Times - BitTorrent, Inc. Inc. Launches the BitTorrent Entertainment Network&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/news/2007/02/bittorrent-sign-of-the-times-bittorrent-inc-inc-launches-the-bittorrent-entertainment-network.php&amp;title=BitTorrent, Sign of the Times - BitTorrent, Inc. Inc. Launches the BitTorrent Entertainment Network">
		<span style="display:none">The following announcement is clearly a sign of the times. The Internet is increasingly used as a delivery platform for entertainment television. Traditional appointment viewing and its business model is being challenged with every capacity added to the net. There are, of course, times when appointment viewing remains victorious--sports and news for example--but a majority of our free time is spent with programming that has no particular time imperative. The announcement below studiously avoids the mentions of HDTV--the big bandwidth hog of the day--but the fact that BitTorent can boasts of 135 million &quot;clients&quot;, whom they call the &quot;BitTorrent generation&quot;, means by default that they are also the &quot;HDTV generation&quot;. You can trust that this “bit” generation will grow weary with anything less than HD and soon a demand for its quality will arise. Will we pay for the bandwidth? We have done so every time we are treated with its reward. _Dale



&lt;strong&gt;BitTorrent, Inc. Launches the BitTorrent Entertainment Network&lt;/strong&gt;

 

MGM is Latest Hollywood Studio to Join the BitTorrent Network, which Offers Thousands of Movies, TV Shows, Music and Games

 

San Francisco, CA - February 26, 2007 - BitTorrent, the global standard for delivering high-quality media over the Internet, today announced the launch of the BitTorrent Entertainment Network at BitTorrent.com. The new network features the most comprehensive library of downloadable digital entertainment ever amassed on the Web, including content from 20th Century Fox, Lions Gate, MTV Networks, Paramount Pictures, Warner Bros. Home Entertainment and BitTorrent's newest partner, Metro-Goldwyn-Mayer Studios, Inc. (MGM). The BitTorrent community will have the flexibility to rent movies, purchase television shows and music videos, and even publish and share their own high-quality content to be displayed alongside titles from the world's largest studios. </span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 548";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download BitTorrent, Sign of the Times - BitTorrent, Inc. Inc. Launches the BitTorrent Entertainment Network" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="BitTorrent, Sign of the Times - BitTorrent, Inc. Inc. Launches the BitTorrent Entertainment Network" />
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
	<title>HDTV Magazine - BitTorrent, Sign of the Times - BitTorrent, Inc. Inc. Launches the BitTorrent Entertainment Network</title>
	<meta name="keywords" content="entertainment network, high quality, powerline networking, bittorrent entertainment, network bittorrent, bittorrent, entertainment, content, upa, technology, network, high, quality, home, powerline, digital, iptv, products, networking, our, new, consumers, standard, world, shows" />
	<meta name="description" content="The following announcement is clearly a sign of the times. The Internet is increasingly used as a delivery platform for entertainment television. Traditional appointment viewing and its business model is being challenged with every capacity added to the net. There are, of course, times when appointment viewing remains victorious--sports and news for example--but a majority of our free time is spent with programming that has no particular time imperative. The announcement below studiously avoids the mentions of HDTV--the big bandwidth hog of the day--but the fact that BitTorent can boasts of 135 million &quot;clients&quot;, whom they call the &quot;BitTorrent generation&quot;, means by default that they are also the &quot;HDTV generation&quot;. You can trust that this “bit” generation will grow weary with anything less than HD and soon a demand for its quality will arise. Will we pay for the bandwidth? We have done so every time we are treated with its reward. _Dale



&lt;strong&gt;BitTorrent, Inc. Launches the BitTorrent Entertainment Network&lt;/strong&gt;

 

MGM is Latest Hollywood Studio to Join the BitTorrent Network, which Offers Thousands of Movies, TV Shows, Music and Games

 

San Francisco, CA - February 26, 2007 - BitTorrent, the global standard for delivering high-quality media over the Internet, today announced the launch of the BitTorrent Entertainment Network at BitTorrent.com. The new network features the most comprehensive library of downloadable digital entertainment ever amassed on the Web, including content from 20th Century Fox, Lions Gate, MTV Networks, Paramount Pictures, Warner Bros. Home Entertainment and BitTorrent's newest partner, Metro-Goldwyn-Mayer Studios, Inc. (MGM). The BitTorrent community will have the flexibility to rent movies, purchase television shows and music videos, and even publish and share their own high-quality content to be displayed alongside titles from the world's largest studios. " />
	<meta name="title" content="BitTorrent, Sign of the Times - BitTorrent, Inc. Inc. Launches the BitTorrent Entertainment Network" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/news/2007/02/bittorrent-sign-of-the-times-bittorrent-inc-inc-launches-the-bittorrent-entertainment-network.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=548', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2007/02/bittorrent-sign-of-the-times-bittorrent-inc-inc-launches-the-bittorrent-entertainment-network.php">BitTorrent, Sign of the Times - BitTorrent, Inc. Inc. Launches the BitTorrent Entertainment Network</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>February 26, 2007</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=270&category=Programming">Programming</a></b>
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
				<p><em>The following announcement is clearly a sign of the times. The Internet is increasingly used as a delivery platform for entertainment television. Traditional appointment viewing and its business model is being challenged with every capacity added to the net. There are, of course, times when appointment viewing remains victorious--sports and news for example--but a majority of our free time is spent with programming that has no particular time imperative. The announcement below studiously avoids the mentions of HDTV--the big bandwidth hog of the day--but the fact that BitTorent can boasts of 135 million "clients", whom they call the "BitTorrent generation", means by default that they are also the "HDTV generation". You can trust that this "bit" generation will grow weary with anything less than HD and soon a demand for its quality will arise. Will we pay for the bandwidth? We have done so every time we are treated with its reward. _Dale</em></p>

<p></p>

<p><strong>BitTorrent, Inc. Launches the BitTorrent Entertainment Network</strong></p>

<p> </p>

<p>MGM is Latest Hollywood Studio to Join the BitTorrent Network, which Offers Thousands of Movies, TV Shows, Music and Games</p>

<p> </p>

<p>San Francisco, CA - February 26, 2007 - BitTorrent, the global standard for delivering high-quality media over the Internet, today announced the launch of the BitTorrent Entertainment Network at BitTorrent.com. The new network features the most comprehensive library of downloadable digital entertainment ever amassed on the Web, including content from 20th Century Fox, Lions Gate, MTV Networks, Paramount Pictures, Warner Bros. Home Entertainment and BitTorrent's newest partner, Metro-Goldwyn-Mayer Studios, Inc. (MGM). The BitTorrent community will have the flexibility to rent movies, purchase television shows and music videos, and even publish and share their own high-quality content to be displayed alongside titles from the world's largest studios. </p>

<p> </p>

<p>"The BitTorrent Entertainment Network is created by and for the BitTorrent Generation, which has a vast appetite for high-quality, on-demand entertainment," said Ashwin Navin, President and Co-founder of BitTorrent. "BitTorrent.com engages our community to contribute in profound ways - whether it's by evangelizing their favorite titles; by submitting content they've created; or by contributing their bandwidth to enable faster downloads and an improved entertainment experience. Our uniqueness lies in the strength of our community, delivery technology, and the industry's most comprehensive catalog of digital content."</p>

<p> </p>

<p>"As a leading entertainment content supplier across all media platforms, MGM is pleased to have such a significant role in launching BitTorrent's groundbreaking and valuable online consumer destination," said Rick Sands, COO, Metro-Goldwyn-Mayer Studios, Inc.  "MGM's library, the world's largest modern library of high quality film and television entertainment programming, appeals to a wide range of consumers and should drive significant traffic to the new BitTorrent Entertainment Network."</p>

<p> </p>

<p>BitTorrent's newest entertainment network partner is MGM, the legendary Hollywood studio and owner of the world's largest modern film library. With a roster of over 35 content partners, BitTorrent is offering a breadth and depth of content not found in any other download service. At launch, the network at BitTorrent.com will feature over 5,000 titles of movies, TV shows, PC games and music content, as well as over 40 hours of high-definition (HD) programming. Consumers will be able to enjoy both new releases and catalog movie titles such as "Superman Returns," "Mission: Impossible III," "World Trade Center," "Jackass: Number Two," "An Inconvenient Truth," "Napoleon Dynamite," "Sideways," and "Thomas Crown Affair." TV programming will include hits such as "24" and "Prison Break" from 20th Century Fox; "Laguna Beach" from MTV:  Music Television; "Celebrity Deathmatch" from MTV2; "Muscle Car" and "Xtreme 4x4" from Spike TV; Emmy and Peabody-Award winning "South Park" and "Mind of Mencia" from COMEDY CENTRAL; "Hogan Knows Best" and "Breaking Bonaduce" from VH1; "SpongeBob SquarePants" and "Avatar: The Last Airbender" from Nickelodeon; "Skyland" from Nicktoons Network</p>

<p> </p>

<p>"BitTorrent has the infrastructure, technology and established user base to significantly move the needle on digital distribution with quick, easy and affordable delivery," said Thomas Lesinski, President, Paramount Pictures Digital Entertainment. "The final piece of the puzzle is a wide array of content and Paramount is very pleased to be providing a vast selection of filmed entertainment to the site."</p>

<p> </p>

<p>"MTV Networks is constantly seeking new avenues for our consumers to access our content and to deliver it to them across multiple platforms," said Mika Salmi, President, Global Digital Media, MTV Networks. "With BitTorrent's new entertainment network, our content gains another high-quality, user-friendly touchpoint to connect audiences with their favorite programming."</p>

<p> </p>

<p>BitTorrent's entertainment network can also be utilized as a distribution platform for independent content creators. Be it film, TV programming, music or podcasts, BitTorrent's self-publishing capability offers artists an instant global audience through a personalized BitTorrent URL and online presence. "We're leveling the playing field for independent artists who have been turned away by publishers who are traditionally bound by scarce distribution alternatives and limited shelf space. Our entertainment network is a true marketplace that embraces and welcomes contribution from the independents, allowing them to reach a vast user base with their high-quality creative expression," said Navin. </p>

<p> </p>

<p>Despite the breadth and depth of BitTorrent's entertainment library, consumers can expect a very simple and straightforward pricing model. The site offers content for free, for rent and for purchase. Movie rentals are $3.99 and $2.99 for new release and catalog titles, respectively. TV shows and music videos are download-to-own at $1.99 each. There will also be a significant collection of HD titles available. Furthermore, a wide variety of entertainment content will be offered for free and without digital rights management (DRM), designed to be distributed across all platforms. </p>

<p> </p>

<p>"Digital distribution represents a significant new revenue stream for the entertainment industry, but up until now, it has been hindered by the combination of long download times and the lack of good content for people to download," said Rob Enderle, Principal Analyst for the Enderle</p>

<p>Group. "BitTorrent has aggressively addressed both problems; first with their unique technology, which moves content closer to the customer and dramatically lowers the amount of time it takes to acquire it, and second with their unusually strong content library. In addition, the fact that they start with 135 million existing clients is incredibly powerful and makes them, at launch, a real force to be reckoned with in this market."</p>

<p><br />
BitTorrent is the global standard for delivering high-quality files over the Internet. Millions of users worldwide are using BitTorrent's leading peer-assisted content delivery platform to publish, discover and download digital entertainment content quickly, easily and securely. Founded in 2004, BitTorrent is a privately held company headquartered in San Francisco, California. visit www.bittorrent.com. </p>

<p> __________________________________________________</p>

<p><em>In still another announcement we learn that streaming of HDTV is more than possible:</em></p>

<p><strong>DS2 announces flicker-free High Definition (HD) IPTV home video streaming</strong></p>

<p><br />
    High performance 'UPA Plugtested' products to hit international market<br />
    <br />
Chano Gomez announces major advance in Powerline Networking, IPTV Conference 2007 San Jose, 27th February</p>

<p>Valencia, 26 February, 2007 - DS2, the world's leading Powerline Chipset provider, will announce that the development of Universal Powerline Association (UPA) technology has reached the point where it is now the dominant provider for the home networking needs of the IPTV industry. The advance has been made possible by the UPA Digital Home Standard compliant DS2 200Mbps chip that powers IPTV Powerline applications. It makes available a new range of high performance products that allow international users to watch flicker-free High Definition (HD) home video streaming via standard wall sockets.</p>

<p>Chano Gomez, DS2's Vice President for Technology and Strategic Partnerships, will deliver a speech at the IPTV 2007 Conference that shows why key European IPTV operators have invested in the UPA 200Mbps DS2 chip for commercial roll-outs, and how this model is applicable around the world. IPTV providers in Belgium, France, Italy, Spain and Sweden already use UPA technology because of its speed, reliability and all-round performance. The UPA also has a range of application partners that provide Powerline solutions to consumers around the world. Netgear and D-Link, worldwide providers of technologically advanced network products, use UPA technology in products to enable video and gaming content to be used throughout the home.</p>

<p>"As the options for home entertainment grow, image quality and ease of delivery will become the key drivers for consumers," commented Chano Gomez, Vice President for Technology and Strategic Partnerships, DS2. "This means that the technology powering IPTV and other Powerline networking products becomes more important. It has to be powerful, resilient, and inter-operable and there must be commercial confidence in it, otherwise consumers won't be able to get what they want from their applications. At the Conference I will discuss these issues in more detail, which will kick-off a time of rapid proliferation of the UPA Powerline Networking technology."</p>

<p>There are three key areas for Powerline Networking technology and home networking, all of which significantly impact the end-user experience as well as the ability of service providers to deliver a broad range of products and service offers:</p>

<p>Consumers demand flicker-free images</p>

<p>HDTV needs much greater bandwidth and quality than standard video streams, which makes it important to have a powerful standard supporting the Powerline applications. Low performance technology can result in flickering images, or a complete breakdown in streaming. The UPA 200Mbps technology enables room-to-room HD streaming, which gives operators a high performance and quality service at a reasonable cost, and gives users the best entertainment experience when and where they want it.</p>

<p><strong>'UPA Plugtested' Label ensures compatibility for all home applications<br />
</strong><br />
The UPA Digital Home Standard is the global standard for Powerline Networking and Communications, and applications that adhere to this are stamped with the "UPA Plugtested" mark. Without this, the system may not be fully backwards compatible or future-proof. Leading IPTV operators and Consumer Electronics companies worldwide choose UPA as the preferred solution for Home Networking over Powerlines.</p>

<p>Robust and proven technology comes with a pedigree<br />
There are technology providers that have been in the market since it began and some that have been attracted to the growth. During 2006, UPA shipped over one million HD-ready 200Mbps chips to manufacturers of Powerline applications, which shows a rise in the number of manufacturers investing in the industry, and as it grows, we will see this trend develop. Those companies that use 200Mbps chips in their products have done much work in the market to develop and offer high specification UPA certified products. This investment guarantees performance, interoperability, simplicity and reliability of products.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>February 26, 2007  9:42 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(548)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 548)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2007/02/bittorrent-sign-of-the-times-bittorrent-inc-inc-launches-the-bittorrent-entertainment-network.php" type="text/javascript" charset="utf-8"></script>
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
