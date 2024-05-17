<?
error_reporting(E_ALL);
	require('global.php');
	require(BASE_DIR .'/includes/lib_guide.php');

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Your Guide to High Definition Television</title>
	<meta name="keywords" content="hdtv,hdtv news,hdtv information,hdtv articles,hdtv blog,hdtv guide,hdtv programming,hdtv listings,hdtv faq,hdtv forum,hdtv forums,hdtv events,hdtv history,hdtv review,hdtv reviews,hdtv reference,hdtv book,hdtv books,hdtv products,hdtv help,hdtv satellite,hdtv cable,hdtv broadcast" />
	<meta name="description" content="HDTV Magazine is the first consumer publication focused entirely upon the HDTV revolution, covering all HDTV-related topics, including news, reviews, HD programming and related HDTV technologies." />
	<meta property="fb:admins" content="618000991" />
	<meta name="microid" content="1b02ccf82c9800ed14b3369402d6e254e96f3caf" />
	<meta name="msvalidate.01" content="9F793FE8DAAC3966B833AA01CFD4A273" />
	<meta name="verify-v1" content="keKX6tzPj4WTma3FTZiejxtTHvW8+Xfu9A1tEQ7pBrE=" >
	<meta name="google-site-verification" content="6CYHdOteD5In6FR862IkWSdWit8nNFZVt1ZgchVISJc" />
	<meta name="y_key" content="e471d62197cd0ad7">
	<meta name="bitly-verification" content="75a7accddd01"/>

	<link rel="stylesheet" type="text/css" href="<?=BASE_IMG_HOST?>/min/?b=css&f=guide.css,prototip.css">

	<? require(BASE_DIR .'/includes/page_header.php');?>
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

			<!-- Study Notice -->
			<!--div class="important"><span class="corners-top"><span></span></span>
				<img src="<?=BASE_IMG_HOST?>/images/i_reports_med.gif" alt="Pie Chart" style="float:left; margin-right:10px" />
				<span class="label"><a target="_blank" href="http://www.surveymonkey.com/s.aspx?sm=_2bL4yJzDtMoGCWvCwhfsw0A_3d_3d">Spring 2009 HDTV Study</a>:</span>
				It's time once again for our periodic HDTV Study. The HDTV Study is intended to assess the current progress of HDTV, and the general atmosphere around new technologies
				like Blu-ray, OLED, Internet Video, etc. Please take 5 minutes to participate in this study.<br />
				<a target="_blank" href="http://www.surveymonkey.com/s.aspx?sm=_2bL4yJzDtMoGCWvCwhfsw0A_3d_3d">http://www.surveymonkey.com/s.aspx?sm=_2bL4yJzDtMoGCWvCwhfsw0A_3d_3d</a>
			<span class="corners-bottom"><span></span></span></div-->

			<!--div class="item"><span class="corners-top"><span></span></span>
				<?/*
					$gmt_time = isset($_GET['s']) ? $_GET['s'] : time();
					if (isset($_GET['primetime'])) {
						$gmt_time = strtotime($user->data['prime_time'] . str_replace(':', '', strright($user->data['time_zone_text'], 'GMT')));
					}

					// Set grid parameters
					$tsSize = 30;

					// This computes time offset based on the users profile settings
					if (access(ACCESS_PREMIUM)) { // Get current time adjusted to user timezone settings
						$user_id = $user->data['user_id'];
						$user_time_offset = $user->data['time_zone_offset'];
						$bufferTime = $gmt_time + 5*MINUTES;
						$start_display_time = $bufferTime - ($bufferTime % ($tsSize*MINUTES));
						$show_sd = $user->data['options'] & OPT_SHOW_SD;
						$user_icons = $user->data['icons'];
					} else { // Set to 8:00pm Eastern, adjusted for DST
						$user_id = 0;
						$user_time_offset = -18000 + (1*HOURS*date("I"));
						$start_display_time = strtotime('Tomorrow 1:00am GMT') - (1*HOURS*date('I'));
						$show_sd = true;
						$user_icons = ICON_SHOW_NEW & ICON_SHOW_DD;
					}

					displayGridGuide($start_display_time, $tsSize, $user_time_offset, 5, $user_id, $show_sd, $user_icons)
				*/?>
			<span class="corners-bottom"><span></span></span></div-->

			<?
				$sql = "
				SELECT DISTINCT entry_blog_id, e.entry_id, entry_excerpt, entry_created_on, entry_title, author_name, topic_replies, aux.topic_id, az.MediumImageURL img
				FROM mt_entry e, mt_author a, aux_mt_entry aux
				LEFT JOIN ". TOPICS_TABLE ." t ON (aux.topic_id = t.topic_id)
				LEFT JOIN az_main az ON (aux.ASIN = az.ASIN)
				WHERE entry_author_id = author_id
					AND e.entry_id = aux.entry_id
					AND e.entry_status = 2
					AND e.entry_blog_id IN (". INCLUDE_BLOGS_HOME .")
				ORDER BY entry_created_on DESC LIMIT 10";
				$result = mQuery($sql);
#				echo $sql;

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
					$entry['image_src'] = $row['img'];

					$entry['catlinks'] = getCatLinksByEntryId($row['entry_id']);

					if ($x == 6) {
						echo '<h2>More ...</h2>';
						$format = 'title-only';
					}

					echo getFormattedEntry($entry, $format);
				}
			?>
		</td><td id="right">
			<? if ($user->data['is_registered'] == false) {?>
				<div id="subscribe_box">
					<div class="important"><span class="corners-top"><span></span></span>
						<div class="label">Free <a href="/daily.php" target="_blank">HDTV Magazine Daily</a>: </div>
						<form name="frmSub" method="post" action="/profile-create.php" onsubmit="if (!isValidEmail(this.email_address)) {return false;} else {return true;}">
							<input type="hidden" name="action" value="create" />
							<input type="hidden" name="type" value="email_only" />
							<input type="text" name="email_address" class="input" maxlength="255" value="email address" onfocus="emailFocus(this)" onblur="emailBlur(this)" />
							 <input type="submit" class="button" value="Subscribe" />
						</form>
					<span class="corners-bottom"><span></span></span></div>
				</div>
			<? }?>

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
				<!--form name="frm" method="get" action="/programming/broadcast-market.php" target="_blank">
					<?=getSelectBox("SELECT DISTINCT dma_name, dma_name FROM prog_source WHERE dma_name <> '' ORDER BY dma_name", "dma_name[]", '', ' style="font-size:1em" onchange="submit()"')?>
					<input type="submit" value="Go" />
				</form-->
			<span class="corners-bottom"><span></span></span></div>

			<div align="center">
				<? include(BASE_DIR .'/ads/mrectangle.php');?>
			</div><br />

			<div align="center">
				<? include(BASE_DIR .'/ads/skyscraper.php');?>
			</div><br />

			<div class="item"><span class="corners-top"><span></span></span>
				<h2><a href="/forum/index.php">Recent Discussion</h2><ul class="brownsquare"><?
					$qry = "
					SELECT topic_title, t.topic_id, username as post_author, post_time, post_id
					FROM ". TOPICS_TABLE ." t, ". USERS_TABLE ." u, ". POSTS_TABLE ." p, aux_phpbb_forums af
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

		</td>
	</tr></table><br />

	<? include(BASE_DIR .'/ads/banner_events.php')?>
	<div align="center"><?=$ad[$x]?></div><br />

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
	<br />

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

		function init() {
		}
	</script>
</div></body>
</html>