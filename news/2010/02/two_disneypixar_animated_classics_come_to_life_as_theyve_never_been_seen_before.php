<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');

	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "$author[title]<br />";

	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3562 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get enclosure info
	$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3562";
	$res_enclosure = mQuery($sql);
	$row_enclosure = mysql_fetch_assoc($res_enclosure);
	$enclosure_url = $row_enclosure['enclosure_url'];

	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author['channel'];

	# Set variables based on entry type. NOTE: Podcasts (& soon Reviews) has its own entry template, so it is not included amongst the choices below.
	switch (7) {
		case 1: # Articles
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-articles" />';
			$feed_tracker = '<script src="http://feeds.feedburner.com/~s/hdtv-articles?i=http://www.hdtvmagazine.com/news/2010/02/two_disneypixar_animated_classics_come_to_life_as_theyve_never_been_seen_before.php" type="text/javascript" charset="utf-8"></script>';
			$container = 'article_container';
			$sub_type = SUB_ARTICLES;
			$sub_label = 'Receive instant notification of new articles';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
			}
			break;
		case 4: # Interviews
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-interviews" />';
			$feed_tracker = '<script src="http://feeds.feedburner.com/~s/hdtv-interviews?i=http://www.hdtvmagazine.com/news/2010/02/two_disneypixar_animated_classics_come_to_life_as_theyve_never_been_seen_before.php" type="text/javascript" charset="utf-8"></script>';
			break;
		case 5: # History
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
			$feed_tracker = '<script src="http://feeds.feedburner.com/~s/hdtv-archive?i=http://www.hdtvmagazine.com/news/2010/02/two_disneypixar_animated_classics_come_to_life_as_theyve_never_been_seen_before.php" type="text/javascript" charset="utf-8"></script>';
			break;
		case 6: # Test
			$container = 'article_container';
			$sub_type = 0;
			$sub_label = 'Receive instant notification of "Stuff"';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of "Stuff" via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of "Stuff" via email as soon as they are published.';
			}
			break;
		case 7: # Bulletins
			$google_links_channel = ''; # Don't count bulletins
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-news" />';
			$feed_tracker = '<script src="http://feeds.feedburner.com/~s/hdtv-news?i=http://www.hdtvmagazine.com/news/2010/02/two_disneypixar_animated_classics_come_to_life_as_theyve_never_been_seen_before.php" type="text/javascript" charset="utf-8"></script>';
			$container = 'bulletin_container';
			$sub_type = SUB_BULLETINS;
			$sub_label = 'Receive instant notification of HDTV Bulletins';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of HDTV Bulletins via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of HDTV Bulletins via email as soon as they are published.';
			}
			break;
		case 8: # Reviews
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-reviews" />';
			$feed_tracker = '<script src="http://feeds.feedburner.com/~s/hdtv-reviews?i=http://www.hdtvmagazine.com/news/2010/02/two_disneypixar_animated_classics_come_to_life_as_theyve_never_been_seen_before.php" type="text/javascript" charset="utf-8"></script>';
			$container = 'article_container';
			$sub_type = SUB_REVIEWS;
			$sub_label = 'Receive instant notification of new reviews';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Reviews via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Reviews via email as soon as they are published.';
			}
			break;
		case 9: # Podcasts
			$container = 'article_container';
			$sub_type = SUB_PODCAST;
			$sub_label = 'Receive instant notification of new episodes';
			if ($userdata['session_logged_in']) {
				$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new episodes of The HDTV Podcast via email as soon as they are published.';
			} else {
				$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new episodes of The HDTV Podcast via email as soon as they are published.';
			}
			break;
		case 10: # Columns
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-columns" />';
			$feed_tracker = '<script src="http://feeds.feedburner.com/~s/hdtv-columns?i=http://www.hdtvmagazine.com/news/2010/02/two_disneypixar_animated_classics_come_to_life_as_theyve_never_been_seen_before.php" type="text/javascript" charset="utf-8"></script>';
			$container = 'article_container';
			$sub_type = SUB_COLUMNS;
			$sub_label = 'Receive instant notification of new columns';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Columns via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Columns via email as soon as they are published.';
			}
			$about = 'HDTV Magazine Columns are written by various personalities within the HDTV industry. They are typically shorter than our standard <a href="/articles">Article</a> and quite often express the opinion of the author(s). And of course, opinions expressed by these authors are not necessarily those of HDTV Magazine.';
			break;
		default:
			$container = 'body_container';
			break;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<? require(BASE_DIR .'/includes/common_header.php'); ?>
	<meta name="keywords" content="toy story, combo pack, pre book, blu ray, walt disney, toy, story, pixar, disney, dvd, feature, combo, english, blu, ray, dolby, buzz, pack, spanish, french, pre, entertainment, book, time, may" />
	<meta name="description" content="Two Disney-Pixar Animated Classics Come to Life as They've Never Been Seen Before TOY STORY &amp;amp; TOY STORY 2 Blasting Off For the First Time Ever in High Definition On Blu-ray(TM) + DVD Combo Pack March 23, 2010 Special Edition..." />
	<title>HDTV Magazine - Two Disney-Pixar Animated Classics Come to Life as They've Never Been Seen Before</title>
	<!--title>HDTV Magazine Bulletins - Two Disney-Pixar Animated Classics Come to Life as They've Never Been Seen Before</title-->
	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript" src="http://www.sphere.com/widgets/sphereit/js?t=classic&amp;p=www.hdtvmagazine.com"></script>
	<script type="text/javascript">
		digg_url = 'http://www.hdtvmagazine.com/news/2010/02/two_disneypixar_animated_classics_come_to_life_as_theyve_never_been_seen_before.php';
		digg_skin = 'compact';
		digg_window = 'new';
		digg_title = 'Two Disney-Pixar Animated Classics Come to Life as They\'ve Never Been Seen Before';
		digg_bodytext = 'Two Disney-Pixar Animated Classics Come to Life as They\'ve Never Been Seen Before TOY STORY &amp; TOY STORY 2 Blasting Off For the First Time Ever in High Definition On Blu-ray(TM) + DVD Combo Pack March 23, 2010 Special Edition...';
//		digg_media = '';
		digg_topic = 'tech_news';

//		tweetmeme_url = 'http://yoururl.com';
		tweetmeme_style = 'compact';
		tweetmeme_source = 'HDTVMagazine';
		tweetmeme_service = 'bit.ly';

//		ReTweet settings
		url = 'http://www.hdtvmagazine.com/news/2010/02/two_disneypixar_animated_classics_come_to_life_as_theyve_never_been_seen_before.php';
		size = 'small';

		function fbs_click() {
			u=location.href;
			t=document.title;
			window.open('http://www.facebook.com/sharer.php?u='+encodeURIComponent(u)+'&amp;t='+encodeURIComponent(t),'sharer','toolbar=0,status=0,width=626,height=436');
			return false;
		}
	</script>
	<style>
		html .fb_share_link {padding:2px 0 0 20px; height:16px; background:url(http://b.static.ak.fbcdn.net/images/share/facebook_share_icon.gif?8:26981) no-repeat top left;}
		/*div.snap_preview div {display:none;}*/
		.header_buttons {text-align:right;}
		.header_buttons span {float:right; margin-right:20px;}
	</style>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/ads/leaderboard.php');
		include(BASE_DIR .'/includes/body_header.php');

		$base_url = strleftback(PHP_SELF, '/') . '/two_disneypixar_animated_classics_come_to_life_as_theyve_never_been_seen_before';
		$title_encoded = rawurlencode(addslashes('Two Disney-Pixar Animated Classics Come to Life as They\'ve Never Been Seen Before'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2010/02/two_disneypixar_animated_classics_come_to_life_as_theyve_never_been_seen_before.php";
		if ($author['img'] != '' && 7 != 7) {
			$img = '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}

	if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), "google") === false) {?>
		<!-- Article Header -->
		<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
			<tr>
				<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
				<td class="article_title" colspan="2">Two Disney-Pixar Animated Classics Come to Life as They've Never Been Seen Before</td>
			</tr><tr>
			<td id="article_byline" nowrap="nowrap">
					By <b>Shane Sturgeon</b><br />
					<?=$author_title?>
					Posted on <b>February 23, 2010</b><br />
					Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
				</td><td id="article_links">
					<span><script type="text/javascript" src="http://w.sharethis.com/button/sharethis.js#publisher=3da06545-0753-46cb-8739-3ffcef208c1f&amp;type=website&amp;send_services=email&amp;post_services=facebook%2Cdigg%2Cdelicious%2Cstumbleupon%2Cblogger%2Cmyspace%2Cybuzz%2Creddit%2Ctechnorati%2Cmixx%2Cwordpress%2Ctypepad%2Cgoogle_bmarks%2Cwindows_live%2Cfark%2Cbus_exchange%2Cpropeller%2Cnewsvine%2Clinkedin"></script></span>
					<span><a href="http://www.facebook.com/share.php?u=<url>" onclick="return fbs_click()" target="_blank" class="fb_share_link">Facebook</a></span>
					<span><img src="http://cdn.stumble-upon.com/images/16x16_su_3d.gif" alt="" align="absmiddle" /><a target="_blank" href="http://www.stumbleupon.com/submit?url=http://www.hdtvmagazine.com/news/2010/02/two_disneypixar_animated_classics_come_to_life_as_theyve_never_been_seen_before.php&title=Two Disney-Pixar Animated Classics Come to Life as They've Never Been Seen Before">StumbleUpon</a></span>
					<span><img src="<?=BASE_IMG_HOST?>/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2010/02/two_disneypixar_animated_classics_come_to_life_as_theyve_never_been_seen_before.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
					<span><img src="<?=BASE_IMG_HOST?>/images/save.gif" alt="Save Article" align="absmiddle" /><a target="_blank" href="<?=$base_url?>-save.php">Save</a></span>
					<span><img src="<?=BASE_IMG_HOST?>/images/email.gif" alt="Email Article" align="absmiddle" /><a href="<?=$email_url?>">Email</a></span>
					<span><img src="<?=BASE_IMG_HOST?>/images/print.png" alt="Print Article" align="absmiddle" /><a target="_blank" href="<?=$base_url?>-print.php">Print</a></span><br />
					<br /><br />
				</td>
			</tr>
		</table>
	<?}?>
	<div>
		<div id="right" style="float:right; margin:0 0 5px 5px; text-align:center;" align="center">
			<? include(BASE_DIR .'/ads/mrectangle.php');?>
			<br />
			<div align="center">
				<? include(BASE_DIR .'/ads/skyscraper.php');?>
			</div>
		</div>
		<div id="<?=$container?>">
			<? if ($sub_type > 0 && ($userdata['subscriptions'] & $sub_type)) {} else {?>
				<div class="important" style="display:table"><span class="corners-top"><span></span></span>
					<img src="<?=BASE_IMG_HOST?>/images/i_inbox.gif" align="left" style="float:left; padding-right:10px" />
					<span class="label"><?=$sub_label?>:</span>
					<?=$sub_desc?>
				<span class="corners-bottom"><span></span></span></div>
			<? }?>
			<div class="header_buttons">
				<? if (7 == 9 || 7 == 6) { # Only show in the test area and for podcasts?>
					<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="<?=BASE_IMG_HOST?>/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle"></a></span>
					<span><a href="<?=$enclosure_url?>"><img src="<?=BASE_IMG_HOST?>/images/chicklet-mp3-podcast.gif" alt="Download Two Disney-Pixar Animated Classics Come to Life as They've Never Been Seen Before" /></a></span>
				<? }?>
				<!--span><script type="text/javascript" src="http://www.retweet.com/static/retweets.js"></script></span-->
				<span><script src="http://tweetmeme.com/i/scripts/button.js"></script></span>
				<span><script src="http://digg.com/api/diggthis.js"></script></span>
				<br />
			</div><br />
			<!-- sphereit start -->
			<p class="prtitle">Two Disney-Pixar Animated Classics Come to Life as They've Never Been Seen Before</p>

<center><i>TOY STORY &amp; TOY STORY 2 Blasting Off For the First Time Ever in High Definition On Blu-ray(TM) + DVD Combo Pack March 23, 2010 Special Edition DVDs Available May 11, 2010</center></i><br />
<br />

<p><strong>BURBANK, Calif., Feb. 23 /PRNewswire/ -- </strong>This Spring, Walt Disney Studios Home Entertainment (WDSHE) proudly presents the eagerly awaited high definition debuts of Disney-Pixar's original animated classics Toy Story and Toy Story 2. In 1995, Toy Story made history as the first feature-length computer animated film and, together with its beloved sequel Toy Story 2, helped establish Disney-Pixar as creators of unrivaled quality family entertainment. Now, viewers can rediscover these wondrous tales of what happens when humans leave the room -- and toys come to life -- as Toy Story and Toy Story 2 debut on Disney Blu-ray(TM) + DVD Combo Pack, followed seven weeks later by the Special Edition DVDs.</p>

<p>For the first time ever, these two groundbreaking films will be presented with eye-popping 1080p high definition picture and 5.1 DTS-HD Master Audio sound, along with hours of innovative new bonus features. Also included are exclusive sneak peeks at Disney-Pixar's Toy Story 3, hitting U.S. theaters on June 18, 2010.</p>

<p>Academy Award&reg;-winner John Lasseter (1996, Special Achievement Award, Toy Story), directs these two action-packed, laugh-filled animated favorites featuring the superstar voice talents of Tom Hanks (Angels &amp; Demons) and Tim Allen (The Santa Clause), along with Don Rickles (Casino), Annie Potts (Ghostbusters), Wallace Shawn (The Princess Bride) and, of course, Pixar favorite John Ratzenberger (TV's "Cheers"). Together, Toy Story and Toy Story 2 launched the Disney-Pixar label and sent audiences to the infinity of family entertainment fun and beyond...as they're sure to do once again on Blu-ray(TM).</p>

<p>TOY STORY Blu-ray(TM) + DVD Combo Pack All-New Bonus Features<br />
<ul><li>The Story: An Exclusive Sneak Peek at Toy Story 3</li><li>Buzz Lightyear Mission Logs:</li><li>Episode One: Blast Off - Buzz reports back about his adventure to the International Space Station.</li><li>Paths to Pixar: Artists - In this series of shorts, Pixar artists talk about their career path and share advice to aspiring filmmakers and animators.</li><li>Studio Stories: A series of amusing anecdotal shorts about life at Pixar.</li><li>"John's Car" recounts how Toy Story director John Lasseter refused to stop driving his beat-up car even after the film's success.</li><li>"Baby AJ" tells the hilarious story of how important Halloween is to the Pixar team and how one Pixar employee dressed up as the oversized baby from the short Tin Toy to win a prize.</li><li>"Scooter Races" takes the audience on a rousing scooter race around the studio with John Lasseter and other employees.</li><li>Buzz Takes Manhattan - Spectacular footage from Buzz Lightyear's premiere as a Macy's Thanksgiving Day Parade balloon.</li><li>Black Friday: The Toy Story You Never Saw - The Toy Story filmmakers discuss the early cut of the film that very nearly shut down production entirely.</li></ul></p>

<p>TOY STORY 2 Blu-ray(TM) + DVD Combo Pack All-New Bonus Features<br />
<ul><li>Characters: An Exclusive Sneak Peek At Toy Story 3</li><li>Director Commentary</li><li>Buzz Lightyear Mission Logs:</li><li>Episode Two: International Space Station - Buzz reports back about his adventure to the International Space Station.</li><li>Paths to Pixar: Technical Artists - In this series of shorts, Pixar artists talk about their career path and share advice to aspiring filmmakers and animators.</li><li>Studio Stories: A series of amusing anecdotal shorts about life at Pixar.</li><li>"Toy Story 2 Sleep Deprivation Lab" gives audiences an inside peek into the intense time pressures under which the Toy Story 2 editors found themselves.</li><li>"Studio Stories: Pinocchio" shows an impromptu competition between the animators to decorate their workspace by throwing toys into the ceiling.</li><li>"Studio Stories: The Movie Vanishes" tells how a mischievous technical error almost erased all of Toy Story 2 from the computer system.</li><li>Pixar's Zoetrope - A look at the creation of the live-action zoetrope that Pixar created to capture the principles of animation in a live sculpture.</li><li>Celebrating our Friend Joe Ranft - A tribute to beloved Disney and Pixar story man Joe Ranft, regarded as one of the industry's most gifted story artists.</li></ul></p>

<p><br />
<strong>Toy Story Synopsis:</strong></p>

<p>Toy Story, the first full-length computer-animated feature film, is "a wonder to behold." - People Magazine. Experience a hilarious fantasy about the lives toys lead when they're left alone. Woody (voiced by Tom Hanks), an old-fashioned cowboy doll, is Andy's favorite. But when Andy gets Buzz Lightyear (voiced by Tim Allen) for his birthday, the flashy new space hero takes Andy's room by storm! Their rivalry leaves them lost with a toy's worst nightmare -- Sid, the toy-torturing boy next door. Woody and Buzz must work together to escape, realizing along the way that they've got a friend ... in each other!</p>

<p><br />
<strong>Toy Story 2 Synopsis:</strong></p>

<p>A Golden Globe&reg; Award winner for Best Motion Picture - Comedy Or Musical, Toy Story 2 has become a favorite all across the world, garnering praise from fans and critics everywhere. It's "an instant classic," raved New York Magazine. While Andy is away at summer camp, Woody is toynapped by Al McWhiggin, a greedy collector who needs Andy's favorite toy to complete his "Woody's Roundup" collection. Together with Jessie (voiced by Joan Cusack), Bullseye, and the Prospector, Woody is on his way to a museum where he'll spend the rest of his life behind glass. It's up to Buzz, Mr. Potato Head, Hamm, Rex, and Slinky Dog to rescue their friend and remind him what being a toy is all about.</p>

<p>The Toy Story and Toy Story 2 Blu-ray(TM) + DVD Combo Packs are priced at an SRP of $39.99. The Special Edition DVDs are priced at an SRP of $29.99.</p>

<pre>
  TOY STORY BD &amp; DVD COMBO PACK PRODUCT DETAILS:

<p>  STREET DATE:     March 23, 2010<br />
  ---------------------------------------<br />
  Direct pre-book:     1/26/10<br />
  Distributor pre-book:    2/09/10<br />
  Suggested Retail Price:  $39.99<br />
  Feature run time:    Approximately 81 minutes<br />
  Rated:  USA:     G<br />
  The following technical specifications may only apply to feature:<br />
  Aspect ratio:    1.78:1<br />
  Video:       1080p, Widescreen<br />
  Sound:       5.1 DTS-HD ES, English 2.0 DTS-HD,       English DVS 2.0 Dolby; French 5.1 Dolby       EX; Spanish 5:1 Dolby EX<br />
  Subtitles:       English SDH, French, Spanish</p>

<p></p>

<p>  TOY STORY 2 BD &amp; DVD COMBO PACK PRODUCT DETAILS:<br />
  STREET DATE:     March 23, 2010<br />
  ---------------------------------------<br />
  Direct pre-book:     1/26/10<br />
  Distributor pre-book:    2/09/10<br />
  Suggested Retail Price:  $39.99<br />
  Feature run time:    Approximately 92 minutes<br />
  Rated:  USA:     G<br />
  The following technical specifications may only apply to feature:<br />
  Aspect ratio:    2.35:1<br />
  Video:       1080p, Widescreen<br />
  Sound:       5.1 DTS-HD ES, English 2.0 DTS-HD,       English DVS 2.0 Dolby; French 5.1 Dolby       EX; Spanish 5:1 Dolby EX<br />
  Subtitles:       English SDH, French, Spanish</p>

<p></p>

<p>  TOY STORY SPECIAL EDITION DVD PRODUCT DETAILS:<br />
  STREET DATE:     May 11, 2010<br />
  -------------------------------------<br />
  Direct pre-book:     3/16/10<br />
  Distributor pre-book:    3/30/10<br />
  Suggested Retail Price:  $29.99<br />
  Feature run time:    Approximately 81 minutes<br />
  Rated:  USA:     G<br />
  The following technical specifications may only apply to feature:<br />
  Aspect ratio:    1:78:1<br />
  Sound:       5.1 EX and 2.0 Dolby Digital English,       French (Canadian only), Dedicated       Spanish Language SKU<br />
  Subtitles:       English SDH, French, Spanish</p>

<p></p>

<p>  TOY STORY 2 SPECIAL EDITION DVD PRODUCT DETAILS:<br />
  STREET DATE:     May 11, 2010<br />
  -------------------------------------<br />
  Direct pre-book:     3/16/10<br />
  Distributor pre-book:    3/30/10<br />
  Suggested Retail Price:  $29.99<br />
  Feature run time:    Approximately 92 minutes<br />
  Rated:  USA:     G<br />
  The following technical specifications may only apply to feature:<br />
  Aspect ratio:    1:78:1<br />
  Sound:       5.1 EX and 2.0 Dolby Digital English,       French (Canadian only), Dedicated       Spanish Language SKU<br />
  Subtitles:       English SDH, French, Spanish<br />
</pre></p>

<p><br />
<strong>About Disney's Combo Pack:</strong></p>

<p>To provide consumers with unprecedented quality, value and portability of their favorite Disney movies, in 2008 WDSHE pioneered the Combo Pack - a Blu-ray Disc(TM) plus a DVD and a DisneyFile Digital Copy of the film in a single package. Current Disney-branded titles available as Combo Packs include High School Musical 3, Beverly Hills Chihuahua, Bolt, Bedtime Stories, Race To Witch Mountain, Jonas Brothers and Hannah Montana The Movie.</p>

<p>Walt Disney Studios Home Entertainment, a recognized leader in the home entertainment industry, is the marketing, sales and distribution company for Walt Disney, Touchstone, Hollywood Pictures, Miramax and Buena Vista product, which includes DVD, Blu-ray Disc(TM) and electronic distribution. Walt Disney Studios Home Entertainment is a division of The Walt Disney Studios</p>

<p>These press materials are available in electronic form at <a target="_blank" href="http://www.WDSHEpublicity.com/">www.WDSHEpublicity.com</a>.</p>

<p>&copy;Walt Disney Studios Home Entertainment, Inc.</p>

<p>Source: Walt Disney Studios Home Entertainment(C)</p>
			<!-- sphereit end -->
			<!--div align="right"><a class="iconsphere" title="Related Blogs &amp; Articles" onclick="return Sphere.Widget.search()" href="http://www.sphere.com/search?q=sphereit:http://www.hdtvmagazine.com/news/2010/02/two_disneypixar_animated_classics_come_to_life_as_theyve_never_been_seen_before.php">Sphere: Related Content</a></div-->
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>February 23, 2010  6:11 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 3562
 				AND a.topic_id = t.topic_id
 				AND t.topic_id = p.topic_id
 				AND p.post_id = pt.post_id
 			ORDER BY post_time";
 			$result = mQuery($sql);
 			$num_comments = mysql_num_rows($result);

 			if ($num_comments > 0) {
 				# Skip the first one, as it's just the excerpt post.
 				$row = mysql_fetch_assoc($result);
 				$thread_url = URL_FORUM_VIEWTOPIC .'?t='. $row['topic_id'];
 				echo '<h2 style="margin-bottom:10px"><a href="'. $thread_url .'">Reader Commentary</a></h2>'.
 				'<div class="item"><span class="corners-top"><span></span></span>'.
 					'<img src="'. BASE_IMG_HOST .'/images/icon_topic.gif" alt="" /><b> See Forum Topic</b>: '.
 					'<a href="'. $thread_url .'">'. $row['topic_title'] .'</a> <span class="grey">('. $row['topic_replies'] .' replies)</span>'.
 				'<span class="corners-bottom"><span></span></span></div>';

 				$x = 0;
 				while ($row = mysql_fetch_assoc($result)) {
 					if ($x == 10) break;
 					$x++;
 					$comment_url = URL_FORUM_VIEWTOPIC .'?p='. $row[post_id] .'#'. $row[post_id];
 					$text = strip_tags(str_replace('[', '<', str_replace(']', '>', $row[post_text])));
 					if ($row[post_subject] != '') {
 						$subject = $row[post_subject];
 					} else {
 						$subject = "Re: $row[topic_title]";
 					}

 					$class = ($x % 2 == 0) ? 'item' : 'item_odd';
 					echo '<div class="'. $class .'"><span class="corners-top"><span></span></span>'.
 						'<div style="font-size:1.2em; font-weight:bold"><a href="'. $comment_url .'">'. $subject .'</a></div>'.
 						'<b>'. $row[poster_id] .'</b> '. date('M j, g:ia', $row[dt]) .'<br />'.
 						$text .
 					'<span class="corners-bottom"><span></span></span></div>';
 				}
 			}
 			if ($num_comments > $x) {
 				echo '<div align="center" class="important"><span class="corners-top"><span></span></span>'.
 				"Showing only excerpts from $x out of $num_comments, <a href='$thread_url'>Read More</a>".
 				'<span class="corners-bottom"><span></span></span></div>';
 			}
 		?><div class="dottedline"></div></div>

 		<div class="item"><span class="corners-top"><span></span></span>
 			<h2>More on </h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = ''
 				AND e.entry_status = 2
 				AND e.entry_blog_id IN (". INCLUDE_BLOGS_ALL .")
 				AND entry_author_id = a.author_id
 			ORDER BY entry_created_on DESC LIMIT 25";
 			$result = mQuery($sql);
 			while ($row = mysql_fetch_assoc($result)) {
 				$ts = strtotime($row[entry_created_on]);
 				$y = date('Y', $ts);
 				$m = date('m', $ts);
 				$entry = getEntryInfo($row[entry_blog_id]);

 				$entry[date] = getDateString($ts);
 				$entry[link] = "/$entry[blog_dir]/$y/$m/". dirify($row[entry_title]) .".php";
 				$entry[title] = $row[entry_title];
 				$entry[author] = $row[author_name];

 				echo '<li><a href="'. $entry[link] .'">'. $entry[title] .'</a> - <span class="grey">'. $entry[author] .'</span> - '. $entry[date] .'</li>';
 			}
 		?></ul><span class="corners-bottom"><span></span></span></div>

 		<? if (7 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 7
 				AND entry_id <> 3562
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'Shane Sturgeon'
 			ORDER BY entry_created_on DESC LIMIT 10";
 			$result = mQuery($qry);

 			if (mysql_num_rows($result) > 0) {
 				$row = mysql_fetch_assoc($result);
 				echo '<div class="item"><span class="corners-top"><span></span></span>'.
 				'<h2><a href="/author.php?author='. urlencode($row[author_name]) .'&id='. $row[author_id] .'">More from '. $row[author_name] .'</a></h2><ul>';
 				mysql_data_seek($result, 0);
 				while ($row = mysql_fetch_assoc($result)) {
 					# Get categories
 					$sql = "
 					SELECT category_label FROM mt_category c, mt_placement p
 					WHERE $row[entry_id] = p.placement_entry_id
 						AND c.category_id = p.placement_category_id";
 					$res_categories = mQuery($sql);
 					$row_categories = mysql_fetch_assoc($res_categories);
 					$category = $row_categories[category_label];

 					$ts = strtotime($row[entry_created_on]);
 					$y = date('Y', $ts);
 					$m = date('m', $ts);
 					$blog_dir = getBlogDir($row[entry_blog_id]);
 					$date = getDateString($ts);
 					$link = "/$blog_dir/$y/$m/". dirify($row[entry_title]) .".php";
 					echo '<li><a href="'. $link .'">'. $row[entry_title] .'</a> - <span class="grey">'. $category .'</span> - '. $date .'</li>';
 				}
 				echo '</ul><span class="corners-bottom"><span></span></span></div>';
				}
			}

 		if ($author[bio_short] != '') {?>
 			<div class="item"><span class="corners-top"><span></span></span>
 				<h2>About Shane Sturgeon</h2>
 				<?=stripslashes($author[bio_short])?>
 			<span class="corners-bottom"><span></span></span></div>
 		<?}?>
		</td><td id="right">

 		<?if ($about != '') {?>
 			<div class="item"><span class="corners-top"><span></span></span>
 				<h2>About Bulletins</h2>
 				<?=$about?>
 			<span class="corners-bottom"><span></span></span></div>
		<?}?>

 		<div class="item"><span class="corners-top"><span></span></span>
 			<h2><a href="/forum/index.php">Other Recent Discussion</h2><ul class="brownsquare"><?
 				$qry = "
 				SELECT topic_title, t.topic_id, username as post_author, post_time, post_id
 				FROM phpbb_topics t, phpbb_users u, phpbb_posts p, aux_phpbb_forums af
 				WHERE
					t.forum_id = af.forum_id
					AND af.exclude_general = 0
 					AND p.poster_id = u.user_id
 					AND t.topic_id = p.topic_id
 					AND t.topic_last_post_id = p.post_id
 				ORDER BY post_time DESC LIMIT 10";
 				$result = mQuery($qry);

 				while ($row = mysql_fetch_assoc($result)) {
   					$last_post = date('n/j g:ia T', $row[post_time]);
   					$title = html_entity_decode($row[topic_title]);

 					echo '<li><a href="'. FULL_URL_FORUM_VIEWTOPIC .'?t='. $row[topic_id] .'">'. $title .'</a> - <span class="grey">'. $row[post_author] .'</span> - '. $last_post .'</li>';
 				}
 			?></ul>
 		<span class="corners-bottom"><span></span></span></div>

 		<div class="item"><span class="corners-top"><span></span></span>
 			<h2>Authors</h2>
 			<ul class="brownsquare"><?
 				$qry = "
 				SELECT author_id, author_name, COUNT(*) num
 				FROM mt_author a, mt_entry e
 				WHERE e.entry_blog_id IN (". INCLUDE_BLOGS_NO_BULLETINS .")
 					AND entry_status = 2
 					AND entry_author_id = author_id
 				GROUP BY author_id, author_name
 				ORDER BY num DESC";
 				$res_authors = mQuery($qry);
 				while ($row_authors = mysql_fetch_assoc($res_authors)) {
 					echo '<li><a href="/author.php?author='. urlencode($row_authors[author_name]) .'&id='. $row_authors[author_id] .'">'. $row_authors[author_name] .'</a><span class="grey"> ('. $row_authors[num] .')</span></li>';
 				}
 			?></ul>
 		<span class="corners-bottom"><span></span></span></div>

 		<div class="item"><span class="corners-top"><span></span></span>
 			<h2>Categories</h2>
 			<ul class="brownsquare"><?
 				$qry = "
 				SELECT category_label label, COUNT(*) num
 				FROM mt_entry e, mt_placement p, mt_category c
 				WHERE e.entry_blog_id IN (". INCLUDE_BLOGS_ALL .")
 					AND entry_status = 2
 					AND entry_id = p.placement_entry_id
 					AND p.placement_category_id = c.category_id
 				GROUP BY label
 				ORDER BY label";
 				$result = mQuery($qry);
 				while ($category = mysql_fetch_assoc($result)) {
 					echo '<li><a href="/category.php?category='. urlencode($category[label]) .'">'. $category[label] .'</a><span class="grey"> ('. $category[num] .')</span></li>';
 				}
 			?></ul>
 		<span class="corners-bottom"><span></span></span></div>

		</td>
	</tr></table>

	<?
		include(BASE_DIR .'/includes/body_footer.php');
	?>
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2010/02/two_disneypixar_animated_classics_come_to_life_as_theyve_never_been_seen_before.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
