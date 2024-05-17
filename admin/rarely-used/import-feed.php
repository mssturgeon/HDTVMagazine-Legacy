<?
	$debug = isset($_GET[debug]);
	if ($debug) {
		header('Content-Type: text/plain');
	}

	require_once('/var/www/html/includes/constants.php');
	require_once('/var/www/html/includes/lib_common.php');
	require_once('/var/www/html/includes/lib_mysql.php');

$topic_id['HDTV Podcast #239'] = 9223;
$topic_id['HDTV Podcast #238'] = 9222;
$topic_id['HDTV Podcast #237'] = 9224;
$topic_id['HDTV Podcast #236'] = 9225;
$topic_id['HDTV Podcast #235'] = 9226;
$topic_id['HDTV Podcast #234'] = 9227;
$topic_id['HDTV Podcast #233'] = 9228;
$topic_id['HDTV Podcast #232'] = 9229;
$topic_id['HDTV Podcast #231'] = 9230;
$topic_id['HDTV Podcast #230'] = 9231;
$topic_id['HDTV Podcast #229'] = 9232;
$topic_id['HDTV Podcast #228'] = 9233;
$topic_id['HDTV Podcast #227'] = 9234;
$topic_id['HDTV Podcast #226'] = 9235;
$topic_id['HDTV Podcast #225'] = 9236;
$topic_id['HDTV Podcast #224'] = 9237;
$topic_id['HDTV Podcast #223'] = 9238;
$topic_id['HDTV Podcast #222'] = 9239;
$topic_id['HDTV Podcast #221'] = 9240;
$topic_id['HDTV Podcast #220'] = 9241;
$topic_id['HDTV Podcast #219'] = 9242;
$topic_id['HDTV Podcast #218'] = 9243;
$topic_id['HDTV Podcast #217'] = 9244;
$topic_id['HDTV Podcast #216'] = 9245;
$topic_id['HDTV Podcast #215'] = 9246;
$topic_id['HDTV Podcast #214'] = 9247;
$topic_id['HDTV Podcast #213'] = 9248;
$topic_id['HDTV Podcast #212'] = 9249;
$topic_id['HDTV Podcast #211'] = 9250;
$topic_id['HDTV Podcast #210'] = 9251;
$topic_id['HDTV Podcast #209'] = 9252;
$topic_id['HDTV Podcast #208'] = 9253;
$topic_id['HDTV Podcast #207'] = 9254;
$topic_id['HDTV Podcast #206'] = 9255;
$topic_id['HDTV Podcast #205'] = 9256;
$topic_id['HDTV Podcast #204'] = 9257;
$topic_id['HDTV Podcast #203'] = 9258;
$topic_id['HDTV Podcast #202'] = 9259;
$topic_id['HDTV Podcast #201'] = 9260;
$topic_id['HDTV Podcast #200'] = 9261;
$topic_id['HDTV Podcast #199'] = 9262;
$topic_id['HDTV Podcast #198'] = 9263;
$topic_id['HDTV Podcast #197'] = 9264;
$topic_id['HDTV Podcast #196'] = 9265;
$topic_id['HDTV Podcast #195'] = 9266;
$topic_id['HDTV Podcast #194'] = 9267;
$topic_id['HDTV Podcast #193'] = 9268;
$topic_id['HDTV Podcast #192'] = 9269;
$topic_id['HDTV Podcast #191'] = 9270;
$topic_id['HDTV Podcast #190'] = 9271;
$topic_id['HDTV Podcast #189'] = 9272;
$topic_id['HDTV Podcast #188'] = 9273;
$topic_id['HDTV Podcast #187'] = 9274;
$topic_id['HDTV Podcast #186'] = 9275;
$topic_id['HDTV Podcast #185'] = 9276;
$topic_id['HDTV Podcast #184'] = 9277;
$topic_id['HDTV Podcast #183'] = 9278;
$topic_id['HDTV Podcast #182'] = 9279;
$topic_id['HDTV Podcast #181'] = 9280;
$topic_id['HDTV Podcast #180'] = 9281;
$topic_id['HDTV Podcast #179'] = 9282;
$topic_id['HDTV Podcast #178'] = 9283;
$topic_id['HDTV Podcast #177'] = 9284;
$topic_id['HDTV Podcast #176'] = 9285;
$topic_id['HDTV Podcast #175'] = 9286;
$topic_id['HDTV Podcast #174'] = 9287;
$topic_id['HDTV Podcast #173'] = 9288;
$topic_id['HDTV Podcast #172'] = 9289;
$topic_id['HDTV Podcast #171'] = 9290;
$topic_id['HDTV Podcast #170'] = 9291;
$topic_id['HDTV Podcast #169'] = 9292;
$topic_id['HDTV Podcast #168'] = 9293;
$topic_id['HDTV Podcast #167'] = 9294;
$topic_id['HDTV Podcast #166'] = 9295;
$topic_id['HDTV Podcast #165'] = 9296;
$topic_id['HDTV Podcast #164'] = 9297;
$topic_id['HDTV Podcast #163'] = 9298;
$topic_id['HDTV Podcast #162'] = 9299;
$topic_id['HDTV Podcast #161'] = 9300;
$topic_id['HDTV Podcast #160'] = 9301;
$topic_id['HDTV Podcast #159'] = 9302;
$topic_id['HDTV Podcast #158'] = 9303;
$topic_id['HDTV Podcast #157'] = 9304;
$topic_id['HDTV Podcast #156'] = 9305;
$topic_id['HDTV Podcast #155'] = 9306;
$topic_id['HDTV Podcast #154'] = 9307;
$topic_id['HDTV Podcast #153'] = 9308;
$topic_id['HDTV Podcast #152'] = 9309;
$topic_id['HDTV Podcast #151'] = 9310;
$topic_id['HDTV Podcast #150'] = 9311;
$topic_id['HDTV Podcast #149'] = 9312;
$topic_id['HDTV Podcast #148'] = 9313;
$topic_id['HDTV Podcast #147'] = 9314;
$topic_id['HDTV Podcast #146'] = 9315;
$topic_id['HDTV Podcast #145'] = 9316;
$topic_id['HDTV Podcast #144'] = 9317;
$topic_id['HDTV Podcast #143'] = 9318;
$topic_id['HDTV Podcast #142'] = 9319;
$topic_id['HDTV Podcast #141'] = 9320;

	include('/var/www/html/includes/lastRSS.php');
	$rss = new lastRSS;
	$rss->cache_dir = 'cache';
	$rss->date_format = 'M d, Y g:i:s A';
	$cacheseconds=(int) $_GET["cachetime"];
	$rss->cache_time = $cacheseconds;
	
	$skip_titles = array('HDTV Podcast #245', 'HDTV Podcast #244', 'HDTV Podcast #243', 'HDTV Podcast #242', 'HDTV Podcast #241', 'HDTV Podcast #240');

	if ($rs = $rss->get('http://feeds.feedburner.com/HdtvPodcast')) {
#		print_r($rs);
		foreach ($rs[items] as $item) {
			if (!in_array($item[title], $skip_titles)) {
   			$created_on = date('Y-m-d H:i:s', strtotime($item[pubDate]));

				$description = strleft($item[description] .'&lt;div class="feedflare"&gt;', '&lt;div class="feedflare"&gt;');
   			$excerpt = (strlen($description) > 400) ? trim(substr($description, 0, 400) .' ...') : $description;
   			$description = '<div align="center" style="height:55px; padding-top:20px">'.
   			'<span style="margin:0 10px">'.
   				'<a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank">'.
   				'<img src="/images/chicklet-itunes.gif" alt="iTunes"></a>'.
   			'</span><span style="margin:0 10px">'.
   				'<a title="" href="zune://subscribe/?HDTV%20and%20Home%20Theater%20Podcast=http://feeds.feedburner.com/HdtvPodcast">'.
   				'<img title="" alt="" src="http://podcast411.com/07img/1click_Zune.gif" border="0"></a>'.
   			'</span></div>'.
   			'<strong>Today\'s Show:</strong><br />'.
   			'<a href="'. $item[enclosure][url] .'">Listen Now - mp3</a><br />'.
   			'<a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a><br />'.
   			'<a href="http://www.htguys.com">Website</a><br /><br />'.
   			'<br>'. $description;
   			$description = addslashes($description);
   			$excerpt = addslashes($excerpt);
   			$title = addslashes($item[title]);
   			
   			# Create Entry
				$sql = "INSERT IGNORE mt_entry
   			(entry_blog_id, entry_status, entry_author_id, entry_allow_comments, entry_allow_pings, entry_convert_breaks, entry_category_id, entry_title,
   			entry_excerpt, entry_text, entry_text_more, entry_to_ping_urls, entry_pinged_urls, entry_keywords, entry_tangent_cache, entry_created_on,
   			entry_modified_on, entry_created_by, entry_modified_by, entry_basename)
   			VALUES (9, 2, 21, 0, 0, '__default__', NULL, '$title',
   			'$excerpt', '$description', '', '', NULL, NULL, NULL, '$created_on',
   			'$created_on', NULL, NULL, '". dirify($title) ."')";
   			if ($debug) {echo "$sql\n";} else {mQuery($sql);}
     			$entry_id = mysql_insert_id();
				
				# Insert category placement
   			$sql = "INSERT IGNORE mt_placement (placement_entry_id, placement_blog_id, placement_category_id, placement_is_primary) 
   			VALUES ($entry_id, 9, 314, 1)";
   			if ($debug) {echo "$sql\n";} else {mQuery($sql);}
				
				# Set notification_sent & topic_id 
   			$sql = "INSERT IGNORE aux_mt_entry (entry_id, blog_id, notification_sent, thread_created, topic_id) 
   			VALUES ($entry_id, 9, 1, 1, $topic_id[$title])";
   			if ($debug) {echo "$sql\n";} else {mQuery($sql);}
				
			}
#			if ($x++ == 6) {exit;}
		}
	} else {
		 echo "Unable to process: $row[xml_link]\n<br />";
	}
?>
