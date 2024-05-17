<?
	require('global.php');
	header('Cache-Control: no-store'); // HTTP/1.1

	$today = date('Y-m-d');
	$mgtz = isset($_GET['mgtz']) ? $_GET['mgtz'] : 'Eastern';
	switch($mgtz) {
		case 'Central':
			$minigrid_url = 'http://listings2go.tvguide.com/PartnerGrid/grids?partnerid=124&profileid=812';
			break;
		case 'Mountain':
			$minigrid_url = 'http://listings2go.tvguide.com/PartnerGrid/grids?partnerid=124&profileid=813';
			break;
		case 'Pacific':
			$minigrid_url = 'http://listings2go.tvguide.com/PartnerGrid/grids?partnerid=124&profileid=814';
			break;
		default: # Eastern
			$minigrid_url = 'http://listings2go.tvguide.com/PartnerGrid/grids?partnerid=124&profileid=662';
			break;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>HDTV Magazine - Your Guide to High Definition Television</title>
	<meta name="microid" content="1b02ccf82c9800ed14b3369402d6e254e96f3caf" />
	<meta name="description" content="HDTV Magazine is the first consumer publication focused entirely upon the HDTV revolution, covering all HDTV-related topics, including news, reviews, HD programming and related HDTV technologies." />
	<meta name="keywords" content="hdtv,hdtv news,hdtv information,hdtv articles,hdtv blog,hdtv guide,hdtv programming,hdtv listings,hdtv faq,hdtv forum,hdtv forums,hdtv events,hdtv history,hdtv review,hdtv reviews,hdtv reference,hdtv book,hdtv books,hdtv products,hdtv help,hdtv satellite,hdtv cable,hdtv broadcast" />
	<!--meta name="verify-v1" content="wrMxlFQvx39zneu8NDVf3Q9qvqL3TGhobVEplDYRCa8=" /-->
	<meta name="verify-v1" content="keKX6tzPj4WTma3FTZiejxtTHvW8+Xfu9A1tEQ7pBrE=" >
	<!--meta name='blogburst-verify' content='B8cbpW2QVXsmz9dDVAipOWMg'/-->
	<? require(BASE_DIR .'/includes/common_header.php'); ?>
	<script src="/scripts/rssticker.js" type="text/javascript"></script>
	<script type="text/javascript">
/*
		digg_skin = 'compact';
		digg_window = 'new';
		digg_topic = 'tech_news';
		digg_bgcolor = '#ecf3f7';

		tweetmeme_style = 'compact';
		tweetmeme_source = 'HDTVMagazine';

		function fbs_click() {
			u=location.href;
			t=document.title;
			window.open('http://www.facebook.com/sharer.php?u='+encodeURIComponent(u)+'&amp;t='+encodeURIComponent(t),'sharer','toolbar=0,status=0,width=626,height=436');
			return false;
		}
*/

		function submitQuestion() {
			var question = document.forms['qq'].question.value;
			var email_address = document.forms['qq'].email_address.value;
			var ip_address = document.forms['qq'].ip_address.value;
			if (email_address == 'email address') {email_address = '';}

			return ajax_makeRequest('qqsubmit.php', 'question='+escape(question)+'&email_address='+escape(email_address)+'&ip_address='+escape(ip_address));
		}

		function ajax_exec(httpRequest) {
			if (httpRequest.readyState == 4) {
				if (httpRequest.status == 200) {
					id = httpRequest.responseText;
					Effect.Fade('ds_body');
					Effect.BlindUp('ds_body');
					Effect.BlindDown('ds_thankyou');
					Effect.Appear('ds_thankyou');
					var url = 'http://www.hdtvmagazine.com/forum/viewtopic.php?t='+id;
					document.getElementById('ds_url').innerHTML = '<a href="'+url+'">'+url+'</a>';
				} else {
					alert('There was a problem with the request.');
				}
			}
		}
	</script>
	<style>
		.tzoption {font-size:1.25em; font-weight:bold; float:left; margin:3px 5px 3px 0; padding:3px 5px}
		.tzselected {border:2px solid #<?=PRIMARY_COLOR?>; font-size:1.25em; font-weight:bold; float:left; margin:3px 5px 3px 0; padding:3px 5px}
	</style>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/ads/leaderboard.php');
		include(BASE_DIR .'/includes/body_header.php');
	?>

	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<div class="item" ><span class="corners-top"><span></span></span>
				<!--span class="label">Follow Us: </span-->
				<a class="twitter_share_link" href="http://twitter.com/HDTVMagazine" target="_blank">Follow us on Twitter</a>
				&bull; <a class="rss_share_link" href="rss-feeds.php">Subscribe to RSS Feeds</a>
				&bull; <a class="fb_share_link" href="http://www.facebook.com/pages/HDTV-Magazine/45415877375" target="_blank">Fan us on Facebook</a>
			<span class="corners-bottom"><span></span></span></div>

			<!-- Study Notice -->
			<!--div class="important"><span class="corners-top"><span></span></span>
				<img src="/images/i_reports_med.gif" alt="Pie Chart" style="float:left; margin-right:10px" />
				<span class="label"><a target="_blank" href="http://www.surveymonkey.com/s.aspx?sm=_2bL4yJzDtMoGCWvCwhfsw0A_3d_3d">Spring 2009 HDTV Study</a>:</span>
				It's time once again for our periodic HDTV Study. The HDTV Study is intended to assess the current progress of HDTV, and the general atmosphere around new technologies
				like Blu-ray, OLED, Internet Video, etc. Please take 5 minutes to participate in this study.<br />
				<a target="_blank" href="http://www.surveymonkey.com/s.aspx?sm=_2bL4yJzDtMoGCWvCwhfsw0A_3d_3d">http://www.surveymonkey.com/s.aspx?sm=_2bL4yJzDtMoGCWvCwhfsw0A_3d_3d</a>
			<span class="corners-bottom"><span></span></span></div-->

			<!-- Fireplace Notice -->
			<!--div class="important"><span class="corners-top"><span></span></span>
				<a href="http://www.hdtvmagazine.com/hdstore/catalog/Fireplace-Visions-Of-Tranquility-HD-DVD-p-16714.html"><img src="http://<?=SERVER_NAME?>/hdstore/images/T/fireplace-hd-dvd_100.gif" alt="HDScape Fireplace: Visions of Tranquility [HD DVD]" align="left" style="padding-right:5px" /></a>
				<a href="http://www.hdtvmagazine.com/hdstore/catalog/Fireplace-Visions-Of-Tranquility-Blu-ray-p-16715.html"><img src="http://<?=SERVER_NAME?>/hdstore/images/T/fireplace-blu-ray_100.gif" alt="HDScape Fireplace: Visions of Tranquility [Blu-ray]" align="right" style="padding-left:5px" /></a>
				<span class="label">Fireplace HD DVD/Blu-ray:</span>
				Pop one of these in during your holiday party this year, or wrap one for a special loved one.
				It also makes the perfect Holiday gift for HDTV-owners and those who adore them. Encoded in Dolby&reg; TrueHD,
				they turn a flat and lifeless screen into a roaring fire - without mess, matches, or kindling.<br />
				<br />
				Each only $24.95 - Choose <a href="http://www.hdtvmagazine.com/hdstore/catalog/Fireplace-Visions-Of-Tranquility-HD-DVD-p-16714.html">HD DVD</a>
				or <a href="http://www.hdtvmagazine.com/hdstore/catalog/Fireplace-Visions-Of-Tranquility-Blu-ray-p-16715.html">Blu-ray</a>.
			<span class="corners-bottom"><span></span></span></div-->

			<!-- Important Notice -->
			<!--div class="important"><span class="corners-top"><span></span></span>
				<img src="/images/i_maintenance.gif" alt="Maintenance" style="float:left; margin-right:10px;" />
				<div style="display:table"><span class="label">System Maintenance Planned:</span>
				Please note that at 12:00am PDT (GMT -0700) on April 2nd, 2008 (tonight), we will be taking hdtvmagazine.com off line for approximately 15 minutes
				while we move to a new server cluster. We apologize for the inconvenience.<br />
				<br />
				- Dale &amp; Shane<br /></div>
			<span class="corners-bottom"><span></span></span></div-->

			<!-- Important Notice -->
			<!--div class="important"><span class="corners-top"><span></span></span>
				<img src="/images/i_bulletins.gif" align="left" / style="padding-right:10px"><b>Notice - 23 Sept, 2008 05:30am: </b>We experienced a server outage this morning
				that resulted in a permanent loss of data on our servers. The time period affected goes back to 26 July, 2008. We are working frantically to rebuild and restore
				this information. Please check back later, or fill out the form below to let us know of any information that may be missing.<br />
				<br />
				<b>If you created a user account</b> during this period, that information has been permanantly lost. Please <a href="/profile-create.php">register again</a>
				and <a href="<?=URL_HELP_FEEDBACK?>">send us an email</a> ... we will do something to make it up to you!<br />
				<br />
				Thank you,<br />
				<br />
				Dale Cripps &amp; Shane Sturgeon<br />
				Publishers, HDTV Magazine
			<span class="corners-bottom"><span></span></span></div-->

			<div class="item"><span class="corners-top"><span></span></span>
				<span class="label" style="height:2em"><a href="/news/index.php">Latest News</a>:</span>
				<span class="newsticker"><script type="text/javascript">
					//rssticker_ajax(RSS_id, cachetime, divId, divClass, delay, optionalswitch)
					//new rssticker_ajax("http://feeds.hdtvmagazine.com/hdtv", 600, "newstickerid", "newsticker", 5000, "date+source")
					new rssticker_ajax("http://www.hdtvmagazine.com/news/internet-news_xml.php", 600, "newstickerid", "newsticker", 5000, "date+source")
				</script></span>
			<span class="corners-bottom"><span></span></span></div>

			<div class="item"><span class="corners-top"><span></span></span>
				<img src="/images/i_forum.gif" align="left"/>
				<div class="label" style="font-size:1.25em; margin:.25em 0 0 .5em">
					<a href="javascript:expand_collapse('ds', false);" onmouseover="window.status = 'Have a Question? Get an Answer!'; return true;"
						onmouseout="window.status = ''; return true;">Have a Question? Get an Answer!</a></div>
				<div id="ds_body" name="ds_body" style="clear:both; display:none; padding-top:5px">
					<form name="qq" method="POST" onsubmit="return false;">
						<input type="hidden" name="ip_address" id="ip_address" value="<?=$_SERVER[REMOTE_ADDR]?>">
						<textarea name="question" id="question" style="color:#AAA" onfocus="if (this.value == 'Please type your question here!') {this.value = ''; this.style.color = '#000'};">Please type your question here!</textarea>
						<input type="button" class="button_green" name="submit_question" value="SUBMIT" onclick="submitQuestion()" style="float:right"/>
						<?if ($userdata[session_logged_in]) {?>
							<input type="hidden" name="email_address" id="email_address" value="<?=$userdata[user_email]?>">
							Your question will be submitted under your present username: <b><?=$userdata[username]?></b>.
						<?} else {?>
							Email Address *:
							<input type="text" name="email_address" id="email_address" value="email address" style="width:20em; color:#AAA" onfocus="if (this.value == 'email address') {this.value = ''; this.style.color = '#000';}">
							<div style="font-size:.8em; padding-top:3px;">
								* - Your email address will be kept confidential. An account will be created for you on our system so that we can notify you when an answer
								is posted. Apart from this notification, you will receive no other email from us or any third parties.
							</div>
						<?}?>
					</form>
				</div>
				<div id="ds_thankyou" style="clear:both; display:none">
					<p>
						Thank you for your submission. If you have provided your email address, you will receive an email when your question has received a response.
						If you chose to submit your question anonymously, please make note of the following URL:
					</p>
					<div id="ds_url"></div>
					<p>
						Once your question has been assigned to the proper category, you can use this URL to check for an answer.
					</p>
				</div>
			<span class="corners-bottom"><span></span></span></div>

			<div class="item"><span class="corners-top"><span></span></span>
				<img src="/images/i_guide.gif" align="left" style="margin-right:2px"/>
				<div class="label" style="font-size:1.25em; margin:.25em 0 0 .5em;">
					<a href="javascript:expand_collapse('mg', true)" onmouseover="window.status = 'HDTV Program Guide'; return true;"
						onmouseout="window.status = ''; return true;">HDTV Program Guide</a></div><br clear="all" />
				<div id="mg_body" name="mg_body" style="clear:both; display:<?$_COOKIE[mg_showhide] == 'show' ? print 'inline' : print 'none';?>; margin-top:5px">
					<a name="TZ" id="TZEastern" class="<?$mgtz == 'Eastern' ? print 'tzselected' : print 'tzoption';?>" href="?mgtz=Eastern">Eastern</a>
					<a name="TZ" id="TZCentral" class="<?$mgtz == 'Central' ? print 'tzselected' : print 'tzoption';?>" href="?mgtz=Central">Central</a>
					<a name="TZ" id="TZMountain" class="<?$mgtz == 'Mountain' ? print 'tzselected' : print 'tzoption';?>" href="?mgtz=Mountain">Mountain</a>
					<a name="TZ" id="TZPacific" class="<?$mgtz == 'Pacific' ? print 'tzselected' : print 'tzoption';?>" href="?mgtz=Pacific">Pacific</a>
					<iframe id="minigrid" name="minigrid" src="<?=$minigrid_url?>"
						frameborder="0" marginheight="0" marginheight="0" border="0" width="100%" height="450" scrolling="no"></iframe>
					<?=$_COOKIE['mg_cookie']?>
				</div>
			<span class="corners-bottom"><span></span></span></div>

			<?
				$sql = "
				SELECT DISTINCT entry_blog_id, e.entry_id, entry_excerpt, entry_created_on, entry_title, author_name, topic_replies, aux.topic_id
				FROM mt_entry e, mt_author a, aux_mt_entry aux, phpbb_topics t
				WHERE entry_author_id = author_id
					AND e.entry_id = aux.entry_id
					AND aux.topic_id = t.topic_id
					AND e.entry_status = 2
					AND e.entry_blog_id IN (". INCLUDE_BLOGS_HOME .")
				ORDER BY entry_created_on DESC LIMIT 20";
				$result = mQuery($sql);
#				$result = $db->sql_query($sql);

				$x = 0;
				$format = 'excerpt';
				while ($row = mysql_fetch_assoc($result)) {
					$x++;
					$ts = strtotime($row[entry_created_on]);
					$y = date('Y', $ts);
					$m = date('m', $ts);
					$entry = getEntryInfo($row[entry_blog_id]);

					$entry['date'] = getDateString($ts);
					$entry['link'] = "/$entry[blog_dir]/$y/$m/". dirify($row['entry_title']) .".php";
					$entry['title'] = $row['entry_title'];
					$entry['author'] = $row['author_name'];
					$entry['excerpt'] = $row['entry_excerpt'];
					$entry['topic_replies'] = $row['topic_replies'];
					$entry['topic_id'] = $row['topic_id'];

					# Get categories
					$sql = "
					SELECT c.category_label, c.category_id FROM mt_category c, mt_placement p
					WHERE $row[entry_id] = p.placement_entry_id
						AND c.category_id = p.placement_category_id";
					$res_categories = mQuery($sql);
					$entry['catlinks'] = array();
					while ($row_category = mysql_fetch_assoc($res_categories)) {
						$entry['catlinks'][] = '<a href="/category.php?id='. $row_category['category_id'] .'&category='. urlencode(stripslashes($row_category['category_label'])) .'">'. stripslashes($row_category[category_label]) .'</a>';
					}

					if ($x == 9) {
						echo '<h2>More ...</h2>';
						$format = 'title-only';
					}

					echo getFormattedEntry($entry, $format);
				}
			?>
		</td><td id="right">
			<!--script src="http://static.ak.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php" type="text/javascript"></script>
			<script type="text/javascript">FB.init("[REDACTED]");</script>
			<fb:fan profile_id="45415877375" stream="0" connections="0" width="300"></fb:fan-->

			<div class="item" ><span class="corners-top"><span></span></span>
				<div class="label">Follow Us:</div>
				<a href="http://twitter.com/HDTVMagazine" target="_blank" style="float:left; padding-right:10px"><img src="/images/i_twitter_64.png" alt="Twitter" align="left" /></a>
				<a href="/rss-feeds.php" style="padding-right:10px; float:left;"><img src="/images/i_rss_64.png" alt="RSS" align="left" /></a>
				<a href="http://www.facebook.com/pages/HDTV-Magazine/45415877375" target="_blank" style="float:left; padding-right:10px"><img src="/images/i_facebook_64.png" alt="Facebook" align="left" /></a>
			<span class="corners-bottom"><span></span></span></div>

			<div class="item"><span class="corners-top"><span></span></span>
				<h2><a href="/equipment/hdtvs-best-rated.php">Best Rated HDTVs</a></h2>
				<ul><?
					$sql = "
					SELECT a.ASIN, Manufacturer, Model, Title, ListPrice, LowestNewPrice, LowestUsedPrice, TotalReviews
					FROM az_main m, az_attributes a, az_aux aux
					WHERE m.ASIN = a.ASIN
						AND a.ASIN = aux.ASIN
						AND type = 'HDTVs'
						AND ListPrice > 0
						AND TotalReviews >= 2
						AND date_updated > '$today' - INTERVAL {$admindata['amazon_update_window']} DAY
					ORDER BY AverageRating DESC, TotalReviews DESC LIMIT 10";
					$result = mQuery($sql);

					while ($row = mysql_fetch_assoc($result)) {
						$price = ($row['LowestUsedPrice'] > 0) ? $row['LowestUsedPrice'] : $row['LowestNewPrice'];
						$price = ($price > 0) ? $price : $row['ListPrice'];
						$title = $row['Manufacturer'] .' '. $row['Model'];
						echo '<li>'.
						'<a href="'. URL_EQUIPMENT_MODEL .'?a='. $row['ASIN'] .'&man='. $row['Manufacturer'] .'&model='. $row['Model'] .'">'. $title .'</a>'.
						' - $'. number_format($price/100, 2) .'	'.
						'<a href="'. URL_EQUIPMENT_MODEL_REVIEWS .'?a='. $row['ASIN'] .'&man='. $row['Manufacturer'] .'&model='. $row['Model'] .'">'. $row['TotalReviews'] .' reviews</a>'.
						'</li>';
					}
				?></ul>
			<span class="corners-bottom"><span></span></span></div>

			<div class="item"><span class="corners-top"><span></span></span>
				<div style="float:right"><a href="/programming/broadcast.php"><img src="/images/hdtv-stations.png" alt="Station Map" /></a></div>
				<h2><a href="/programming/broadcast.php">HDTV Local Stations</a></h2>
				Select your market below to find out what Free HDTV is available in your area.
				<form name="frm" method="get" action="/programming/broadcast-market.php" target="_blank">
					<?=getSelectBox("SELECT DISTINCT dma_name, dma_name FROM prog_source WHERE dma_name <> '' ORDER BY dma_name", "dma_name[]", '', ' style="font-size:1em" onchange="submit()"')?>
					<input type="submit" value="Go" />
				</form>
			<span class="corners-bottom"><span></span></span></div>

			<div align="center" style="margin:5px 0;">
				<?include(BASE_DIR .'/ads/mrectangle.php');?>
			</div>
			<br />

			<div align="right">
			<?include(BASE_DIR .'/ads/skyscraper.php');?>
			</div>

			<div class="item"><span class="corners-top"><span></span></span>
				<h2><a href="/forum/index.php">Recent Discussion</h2><ul class="brownsquare"><?
					$qry = "
					SELECT topic_title, t.topic_id, username as post_author, post_time, post_id
					FROM phpbb_topics t, phpbb_users u, phpbb_posts p, aux_phpbb_forums af
					WHERE
						t.forum_id = af.forum_id
						AND af.exclude_general = 0
						AND p.poster_id = u.user_id
						AND t.topic_id = p.topic_id
						AND t.topic_last_post_id = p.post_id
					ORDER BY post_time DESC LIMIT 20";
					$result = mQuery($qry);

					while ($row = mysql_fetch_assoc($result)) {
							$last_post = date('n/j g:ia T', $row['post_time']);
							$title = html_entity_decode($row['topic_title']);

						echo '<li><a href="'. FULL_URL_FORUM_VIEWTOPIC .'?t='. $row['topic_id'] .'">'. $title .'</a> - <span class="grey">'. $row['post_author'] .'</span> - '. $last_post .'</li>';
					}
				?></ul>
			<span class="corners-bottom"><span></span></span></div>

			<!--div class="item"><span class="corners-top"><span></span></span>
			<?
				# Get random ad
#				echo getSelfAd();
			?>
			<span class="corners-bottom"><span></span></span></div-->

		</td>
	</tr></table><br />

	<?include(BASE_DIR .'/ads/banner_events.php')?>
	<div align="center"><?=$ad[$x]?></div><br />

	<?include(BASE_DIR .'/includes/body_footer.php');?>

	<!--div align="center"><?include(BASE_DIR .'/ads/alexa_468x60.php');?></div-->
	<br />
</div></body>
</html>
