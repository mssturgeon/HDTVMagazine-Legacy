<?
	require('global.php');
	require(BASE_DIR .'/includes/lib_guide.php');

	$email_address = ($userdata['email_address'] == '') ? 'email_address' : $userdata['email_address'];

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Your Guide to High Definition Television</title>
	<meta name="microid" content="1b02ccf82c9800ed14b3369402d6e254e96f3caf" />
	<meta name="description" content="HDTV Magazine is the first consumer publication focused entirely upon the HDTV revolution, covering all HDTV-related topics, including news, reviews, HD programming and related HDTV technologies." />
	<meta name="keywords" content="hdtv,hdtv news,hdtv information,hdtv articles,hdtv blog,hdtv guide,hdtv programming,hdtv listings,hdtv faq,hdtv forum,hdtv forums,hdtv events,hdtv history,hdtv review,hdtv reviews,hdtv reference,hdtv book,hdtv books,hdtv products,hdtv help,hdtv satellite,hdtv cable,hdtv broadcast" />
	<meta name="verify-v1" content="keKX6tzPj4WTma3FTZiejxtTHvW8+Xfu9A1tEQ7pBrE=" >
	<meta name="y_key" content="e471d62197cd0ad7">
	<meta name="msvalidate.01" content="9F793FE8DAAC3966B833AA01CFD4A273" />

	<link rel="stylesheet" type="text/css" href="<?=BASE_IMG_HOST?>/min/?b=css&f=guide.css,prototip.css">

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/prototype/1.6.1.0/prototype.js"></script>
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/scriptaculous/1.8.3/scriptaculous.js"></script>
	<script type="text/javascript" src="<?=BASE_IMG_HOST?>/min/?f=js/prototip/prototip.js"></script>
	<script type="text/javascript">
		// Bind tooltips
		document.observe('dom:loaded', function() {
			$$('div.pPrototip').each(function(element) {
				new Tip(element.parentNode, element.innerHTML, {
					style:'hdtvmagazine',
					title:element.title
				});
			});
		});

		var origText = 'What do you want to know?';
		var question;
		var submittedBy;
		function taFocus(e) {
			if (e.value == origText) {e.value = '';};
			e.style.color = '#000';
			e.style.height = '40px';
			submittedBy.style.display = '';
		}

		function taBlur(e) {
			if (e.value == '') {
				e.style.color = '#888';
				e.style.height = '15px';
				e.value = origText;
				submittedBy.style.display = 'none';
			};
		}

		function emailFocus(e) {
			if (e.value == 'email address') {e.value = '';}
			e.style.color = '#000';
		}

		function emailBlur(e) {
			if (e.value == '') {
				e.style.color = '#888';
				e.value = 'email address';
			};
		}

		function submitQuestion() {
			var email_address = document.forms['qq'].email_address.value;
			var ip_address = document.forms['qq'].ip_address.value;
			if (email_address == 'email address') {email_address = '';}

			url = '<?=BASE_URL?>/qqsubmit.php?question='+escape(question.value)+'&email_address='+escape(email_address)+'&ip_address='+escape(ip_address);
			return ajaxSendRequest(url, 'submitReturn');
		}

		function init() {
			question = document.forms['qq'].question;
			question.value = origText;
			submittedBy = document.getElementById('ds_submittedBy');

			var email_address = document.forms['qq'].email_address;
			email_address.value = '<?=$email_address?>';
		}

		function submitReturn() {
			if (ajaxReq.readyState == 4) {
				if (ajaxReq.status == 200) {
					id = ajaxReq.responseText;
					Effect.BlindDown('ds_thankyou');
					Effect.Appear('ds_thankyou');
					Effect.Fade('ds_body');
					Effect.BlindUp('ds_body');
					var url = '<?=BASE_URL?>/forum/viewtopic.php?t='+id;
					document.getElementById('ds_url').innerHTML = '<a href="'+url+'">'+url+'</a>';
				} else {
					alert('There was a problem with the request.');
					return false;
				}
			}
		}
	</script>
	<style>
		#question {font-size:10pt;overflow:hidden;padding:5px;vertical-align:middle;}
		#email_address {color:#888}
		.blur {color:#888;height:15px;}
		.btn {float:left;clear:both; background:url(<?=BASE_IMG_HOST?>/images/btn_left_20.png) no-repeat; padding:0 0 0 5px; margin:5px 0;}
		.btn a {float:left;height:20px;background:url(<?=BASE_IMG_HOST?>/images/btn_stretch_20.png) repeat-x left top; line-height:20px; padding:0 5px; color:#fff; font-size:1em; text-decoration:none;}
		.btn span {background:url(<?=BASE_IMG_HOST?>/images/btn_right_20.png) no-repeat; float:left; width:5px; height:20px;}
		.btn_green {background-color:#339900;}
	</style>
</head>
<body onload="init()"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>

	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<!-- Important Notice -->
			<!--div class="important"><span class="corners-top"><span></span></span>
				<img src="<?=BASE_IMG_HOST?>/images/i_maintenance.gif" alt="Maintenance" style="float:left; margin-right:10px;" />
				<div style="display:table"><span class="label">System Maintenance Planned:</span>
				Please note that at 12:00am PDT (GMT -0700) on April 2nd, 2008 (tonight), we will be taking hdtvmagazine.com off line for approximately 15 minutes
				while we move to a new server cluster. We apologize for the inconvenience.<br />
				<br />
				- Dale &amp; Shane<br /></div>
			<span class="corners-bottom"><span></span></span></div-->

			<!-- Advertisement -->
			<!--div class="ad"><span class="corners-top"><span></span></span>
				<a href="http://www.tkqlhce.com/click-1683082-10684066" target="_blank"><img src="http://store.discovery.com/img/product/resized/00085199-353403_100.jpg?k=d2ae2dfb&pid=85199&s=catl" border="0" align="left" /></a>
				<h2>Discovery Store 50% Off Blu-ray (This Week Only)</h2>
				<a href="http://www.tkqlhce.com/click-1683082-10684066" target="_blank">BLU-RAY BLOWOUT</a>: Get 50% off select Blu-ray DVDs by entering promo code BLU50 at checkout. <b>Ends Saturday, 8/22!</b>
				<img src="http://www.awltovhc.com/image-1683082-10684066" width="1" height="1" border="0" />
			<span class="corners-bottom"><span></span></span></div-->

			<!-- Fireplace Notice -->
			<!--div class="important"><span class="corners-top"><span></span></span>
				<a href="http://www.hdtvmagazine.com/hdstore/catalog/Fireplace-Visions-Of-Tranquility-HD-DVD-p-16714.html"><img src="<?=BASE_IMG_HOST?>/hdstore/images/T/fireplace-hd-dvd_100.gif" alt="HDScape Fireplace: Visions of Tranquility [HD DVD]" align="left" style="padding-right:5px" /></a>
				<a href="http://www.hdtvmagazine.com/hdstore/catalog/Fireplace-Visions-Of-Tranquility-Blu-ray-p-16715.html"><img src="<?=BASE_IMG_HOST?>/hdstore/images/T/fireplace-blu-ray_100.gif" alt="HDScape Fireplace: Visions of Tranquility [Blu-ray]" align="right" style="padding-left:5px" /></a>
				<span class="label">Fireplace HD DVD/Blu-ray:</span>
				Pop one of these in during your holiday party this year, or wrap one for a special loved one.
				It also makes the perfect Holiday gift for HDTV-owners and those who adore them. Encoded in Dolby&reg; TrueHD,
				they turn a flat and lifeless screen into a roaring fire - without mess, matches, or kindling.<br />
				<br />
				Each only $24.95 - Choose <a href="http://www.hdtvmagazine.com/hdstore/catalog/Fireplace-Visions-Of-Tranquility-HD-DVD-p-16714.html">HD DVD</a>
				or <a href="http://www.hdtvmagazine.com/hdstore/catalog/Fireplace-Visions-Of-Tranquility-Blu-ray-p-16715.html">Blu-ray</a>.
			<span class="corners-bottom"><span></span></span></div-->

			<!-- Study Notice -->
			<!--div class="important"><span class="corners-top"><span></span></span>
				<img src="<?=BASE_IMG_HOST?>/images/i_reports_med.gif" alt="Pie Chart" style="float:left; margin-right:10px" />
				<span class="label"><a target="_blank" href="http://www.surveymonkey.com/s.aspx?sm=_2bL4yJzDtMoGCWvCwhfsw0A_3d_3d">Spring 2009 HDTV Study</a>:</span>
				It's time once again for our periodic HDTV Study. The HDTV Study is intended to assess the current progress of HDTV, and the general atmosphere around new technologies
				like Blu-ray, OLED, Internet Video, etc. Please take 5 minutes to participate in this study.<br />
				<a target="_blank" href="http://www.surveymonkey.com/s.aspx?sm=_2bL4yJzDtMoGCWvCwhfsw0A_3d_3d">http://www.surveymonkey.com/s.aspx?sm=_2bL4yJzDtMoGCWvCwhfsw0A_3d_3d</a>
			<span class="corners-bottom"><span></span></span></div-->

			<form name="qq" method="POST" onsubmit="return false;">
				<input type="hidden" name="ip_address" id="ip_address" value="<?=$_SERVER['REMOTE_ADDR']?>">
				<div class="item"><span class="corners-top"><span></span></span>
					<!--img src="<?=BASE_IMG_HOST?>/images/i_forum.gif" align="left" height="31" width="37" /-->
					<div id="ds_body" name="ds_body" style="padding:0px 10px 0 0">
						<textarea name="question" id="question" class="blur"
							onfocus="taFocus(this)"
							onblur="taBlur(this)"
							onkeydown="adjustAreaSize(this, 40, 200)">
						</textarea>
						<div id="ds_submittedBy" style="display:none;">
							<div class="btn btn_green" style="float:right;"><a href="#" onclick="submitQuestion()">Send</a><span></span></div>
							Email Address *:
							<input type="text" name="email_address" id="email_address" value="" style="width:20em;" class="blur"
								onfocus="emailFocus(this)"
								onblur="emailBlur(this)" />
							<div style="font-size:.8em;padding-top:3px;">
								* - Your email address will be kept confidential. An account will be created for you on our system so that we can notify you when an answer
								is posted. Apart from this notification, you will receive no other email from us or any third parties.
							</div>
						</div>
					</div>
					<div id="ds_thankyou" style="clear:both;display:none">
						<p>
							Thank you for your submission. If you have provided your email address, you will receive an email when your question has received a response.
							If you chose to submit your question anonymously, please make note of the following URL:
						</p>
						<div id="ds_url"></div>
						<p>
							Once your question has been assigned to the proper category, you can use this URL to follow the conversation.
						</p>
					</div>
				<span class="corners-bottom"><span></span></span></div>
			</form>

			<div class="item"><span class="corners-top"><span></span></span>
				<?
					$gmt_time = isset($_GET['s']) ? $_GET['s'] : time();
					if (isset($_GET['primetime'])) {
						$gmt_time = strtotime($userdata['prime_time'] . str_replace(':', '', strright($userdata['time_zone_text'], 'GMT')));
					}

					// Set grid parameters
					$tsSize = 30;

					// This computes time offset based on the users profile settings
					if (access(ACCESS_PREMIUM)) { // Get current time adjusted to user timezone settings
						$user_id = $userdata['user_id'];
						$user_time_offset = $userdata['time_zone_offset'];
						$bufferTime = $gmt_time + 5*MINUTES;
						$start_display_time = $bufferTime - ($bufferTime % ($tsSize*MINUTES));
						$show_sd = $userdata['options'] & OPT_SHOW_SD;
						$user_icons = $userdata['icons'];
					} else { // Set to 8:00pm Eastern, adjusted for DST
						$user_id = 0;
						$user_time_offset = -18000 + (1*HOURS*date("I"));
						$start_display_time = strtotime('Tomorrow 1:00am GMT') - (1*HOURS*date('I'));
						$show_sd = true;
						$user_icons = ICON_SHOW_NEW & ICON_SHOW_DD;
					}

					displayGridGuide($start_display_time, $tsSize, $user_time_offset, 5, $user_id, $show_sd, $user_icons)
				?>
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
					$ts = strtotime($row['entry_created_on']);
					$y = date('Y', $ts);
					$m = date('m', $ts);
					$entry = getEntryInfo($row['entry_blog_id']);

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
			<!-- Become a fan -->
			<!--script src="http://static.ak.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php" type="text/javascript"></script>
			<script type="text/javascript">FB.init("[REDACTED]");</script>
			<fb:fan profile_id="45415877375" stream="0" connections="0" width="300"></fb:fan-->

			<div class="item" ><span class="corners-top"><span></span></span>
				<div class="label">Follow Us:</div>
				<a href="http://twitter.com/HDTVMagazine" target="_blank" style="float:left; margin-right:10px"><img src="<?=BASE_IMG_HOST?>/images/i_twitter_64.png" alt="Twitter" align="left" height="64" width="64" /></a>
				<a href="/rss-feeds.php" style="margin-right:10px; float:left;"><img src="<?=BASE_IMG_HOST?>/images/i_rss_64.png" alt="RSS" align="left" height="64" width="64" /></a>
				<a href="http://www.facebook.com/pages/HDTV-Magazine/45415877375" target="_blank" style="float:left; margin-right:10px"><img src="<?=BASE_IMG_HOST?>/images/i_facebook_64.png" alt="Facebook" align="left" height="64" width="64" /></a>
			<span class="corners-bottom"><span></span></span></div>

			<div class="item"><span class="corners-top"><span></span></span>
				<h2><a href="/equipment/hdtvs-best-rated.php">Best Rated HDTVs</a></h2>
				<ul><?
					$today = date('Y-m-d');
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
				<div style="float:right"><a href="/programming/broadcast.php"><img src="<?=BASE_IMG_HOST?>/images/hdtv-stations.png" alt="Station Map" height="93" width="75" /></a></div>
				<h2><a href="/programming/broadcast.php">HDTV Local Stations</a></h2>
				Select your market below to find out what Free HDTV is available in your area.
				<form name="frm" method="get" action="/programming/broadcast-market.php" target="_blank">
					<?=getSelectBox("SELECT DISTINCT dma_name, dma_name FROM prog_source WHERE dma_name <> '' ORDER BY dma_name", "dma_name[]", '', ' style="font-size:1em" onchange="submit()"')?>
					<input type="submit" value="Go" />
				</form>
			<span class="corners-bottom"><span></span></span></div>

			<div align="center" style="margin:5px 0;">
				<? include(BASE_DIR .'/ads/mrectangle.php');?>
			</div>
			<br />

			<div align="right">
			<? include(BASE_DIR .'/ads/skyscraper.php');?>
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

	<? include(BASE_DIR .'/ads/banner_events.php')?>
	<div align="center"><?=$ad[$x]?></div><br />

	<? include(BASE_DIR .'/includes/body_footer.php');?>
	<br />
</div></body>
</html>
