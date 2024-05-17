<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1497";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1497 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
*/
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# This template is used only for podcasts
#	$rss_link = '<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/hdtv-bulletins" />';
	$container = 'article_container';
#	$category_page = 'bulletins-category.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<?require(BASE_DIR .'/includes/common_header.php');?>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<meta name="generator" content="http://www.movabletype.org/" />
	<meta name="keywords" content="mid season, saturday night, law order, season america, time talking, season, Mid, mid, Season, AMERICA, america, night, NIGHT, AMERICAN, nbc, NBC, american, NEW, law, big, LAW, home, BIG, FOOTBALL, new" />
	<meta name="description" content="After being chastised for spending too much time talking about HDTV gear and equipment and not enough time talking about what to actually watch in High Definition, we devote an entire episode to just that: what we watch on TV.  As part of the discussion, we cover all the shows that are returning for the 2008-2009 season as well as those that have been canceled." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #300 - 2008-2009 TV Who's in and Who's out</title>
	<?=$rss_link?>
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_300_-_2008-2009_tv_whos_in_and_whos_out';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #300 - 2008-2009 TV Who\'s in and Who\'s out'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/08/hdtv_and_home_theater_podcast_300_-_2008-2009_tv_whos_in_and_whos_out.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #300 - 2008-2009 TV Who's in and Who's out</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>August  8, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/08/hdtv_and_home_theater_podcast_300_-_2008-2009_tv_whos_in_and_whos_out.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/08/hdtv_and_home_theater_podcast_300_-_2008-2009_tv_whos_in_and_whos_out.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/08/hdtv_and_home_theater_podcast_300_-_2008-2009_tv_whos_in_and_whos_out.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
				<span><img src="/images/email.gif" alt="Email Article" align="absmiddle" /><a href="<?=$email_url?>">Email</a></span>
				<br /><br />
			</td>
		</tr>
	</table>
	<?if ($userdata[subscriptions] & SUB_PODCAST) {} else {
		if ($userdata[session_logged_in]) {?>
			<div class="important"><span class="corners-top"><span></span></span>
				<img src="/images/i_inbox.gif" align="left" style="padding-right:10px" /><span class="label">Receive instant notification of new episodes:</span>
				<a href="<?=URL_PROFILE_SUBSCRIPTIONS?>">Modify your subscription profile</a> to receive notification of new
				episodes of The HDTV Podcast via email as soon as they are published.
			<span class="corners-bottom"><span></span></span></div>
		<?} else {?>
			<div class="important"><span class="corners-top"><span></span></span>
				<img src="/images/i_inbox.gif" align="left" style="padding-right:10px" /><span class="label">Receive instant notification of new episodes:</span>
				<a href="<?=URL_PROFILE_CREATE?>">Register Now</a> to receive notification of new
				episodes of The HDTV Podcast via email as soon as they are published.
			<span class="corners-bottom"><span></span></span></div>
		<?}
	}?>
	<div>
		<div style="float:left; margin:0 5px 5px 0;"><?
			if ($digg_url == '') {
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/08/hdtv_and_home_theater_podcast_300_-_2008-2009_tv_whos_in_and_whos_out.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23300%20-%202008-2009%20TV%20Who%27s%20in%20and%20Who%27s%20out&amp;bodytext=After%20being%20chastised%20for%20spending%20too%20much%20time%20talking%20about%20HDTV%20gear%20and%20equipment%20and%20not%20enough%20time%20talking%20about%20what%20to%20actually%20watch%20in%20High%20Definition%2C%20we%20devote%20an%20entire%20episode%20to%20just%20that%3A%20what%20we%20watch%20on%20TV.%20%20As%20part%20of%20the%20discussion%2C%20we%20cover%20all%20the%20shows%20that%20are%20returning%20for%20the%202008-2009%20season%20as%20well%20as%20those%20that%20have%20been%20canceled.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
			} else {
				echo '<script src="http://digg.com/api/diggthis.js"></script>';
			}
		?></div>
		<div style="float:right; margin:0 0 5px 5px;">
			<?include(BASE_DIR .'/ads/mrectangle.php');?>
		</div>
		<div style="clear:right; float:right; margin:0 0 5px 5px;">
			<?include(BASE_DIR .'/ads/skyscraper.php');?>
		</div>
		<div id="<?=$container?>">
			<div align="center" style="height:55px; padding-top:20px"><span style="margin:0 10px"><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="/images/chicklet-itunes.gif" alt="iTunes"></a></span><span style="margin:0 10px"><a title="" href="zune://subscribe/?HDTV%20and%20Home%20Theater%20Podcast=http://feeds.feedburner.com/HdtvPodcast"><img title="" alt="" src="http://podcast411.com/07img/1click_Zune.gif" border="0"></a></span></div>
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-08-08.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<strong>Today's Show:</strong><br>
After being chastised for spending too much time talking about HDTV gear and equipment and not enough time talking about what to actually watch in High Definition, we devote an entire episode to just that: what we watch on TV.&nbsp; As part of the discussion, we cover all the shows that are returning for the 2008-2009 season as well as those that have been canceled.
<br><strong id="eki.">2008-2009 TV Who's in and Who's out</strong><br>
<br><em id="eki.0">Returning:</em><br>
ABC:<br>
<ul id="true">
<li id="tmsn">20/20</li>
<li id="tmsn0">ACCORDING&nbsp;TO&nbsp;JIM (Mid Season)</li>
<li id="tmsn1">AMERICA'S&nbsp;FUNNIEST&nbsp;HOME&nbsp;VIDEOS</li>
<li id="tmsn2">BACHELOR,&nbsp;THE (Mid Season)</li>
<li id="tmsn3">BOSTON&nbsp;LEGAL</li>
<li id="tmsn4">BROTHERS&nbsp;&amp;&nbsp;SISTERS</li>
<li id="tmsn5">DANCING&nbsp;WITH&nbsp;THE&nbsp;STARS</li>
<li id="tmsn6">DESPERATE&nbsp;HOUSEWIVES</li>
<li id="tmsn7">DIRTY&nbsp;SEXY&nbsp;MONEY</li>
<li id="tmsn8">ELI&nbsp;STONE</li>
<li id="tmsn9">EXTREME&nbsp;MAKEOVER:&nbsp;HOME&nbsp;EDITION</li>
<li id="tmsn10">GREY'S&nbsp;ANATOMY</li>
<li id="tmsn11">LOST (Mid Season)</li>
<li id="tmsn12">PRIMETIME (Mid Season)</li>
<li id="tmsn13">PRIVATE&nbsp;PRACTICE</li>
<li id="tmsn14">PUSHING&nbsp;DAISIES</li>
<li id="ajo:">SAMANTHA&nbsp;WHO?</li>
<li id="ajo:0">SATURDAY&nbsp;NIGHT&nbsp;COLLEGE&nbsp;FOOTBALL</li>
<li id="ajo:1">SCRUBS (Mid Season) (Moving from NBC)</li>
<li id="ajo:2">SUPERNANNY</li>
<li id="ajo:3">UGLY&nbsp;BETTY</li>
<li id="ajo:4">WIFE&nbsp;SWAP</li></ul>
<br>
<div id="uj5i0">CBS:</div>
<ul id="uj5i1">
<li id="uj5i2">48&nbsp;HOURS&nbsp;MYSTERY</li>
<li id="uj5i3">60&nbsp;MINUTES</li>
<li id="uj5i4">AMAZING&nbsp;RACE,&nbsp;THE</li>
<li id="uj5i5"> BIG&nbsp;BANG&nbsp;THEORY,&nbsp;THE</li>
<li id="uj5i6"> COLD&nbsp;CASE</li>
<li id="uj5i7"> CRIMINAL&nbsp;MINDS</li>
<li id="uj5i8"> CSI:&nbsp;CRIME&nbsp;SCENE&nbsp;INVESTIGATION</li>
<li id="uj5i9"> CSI:&nbsp;MIAMI</li>
<li id="uj5i10"> CSI:&nbsp;NEW&nbsp;YORK</li>
<li id="uj5i11"> GHOST&nbsp;WHISPERER</li>
<li id="uj5i12"> HOW&nbsp;I&nbsp;MET&nbsp;YOUR&nbsp;MOTHER</li>
<li id="uj5i13"> NCIS</li>
<li id="uj5i14"> NEW&nbsp;ADVENTURES&nbsp;OF&nbsp;OLD&nbsp;CHRISTINE,&nbsp;THE</li>
<li id="uj5i15"> NUMB3RS</li>
<li id="uj5i16"> RULES&nbsp;OF&nbsp;ENGAGEMENT (Mid Season)</li>
<li id="uj5i17"> SURVIVOR (Two Seasons)</li>
<li id="uj5i18"> TWO&nbsp;AND&nbsp;A&nbsp;HALF&nbsp;MEN</li>
<li id="uj5i19"> UNIT,&nbsp;THE</li>
<li id="uj5i20"> WITHOUT&nbsp;A&nbsp;TRACE</li></ul>
<div id="k-uo">&nbsp;</div>
<div id="k-uo1">CW:</div>
<ul id="bp25">
<li id="bp250">  AMERICA'S&nbsp;NEXT&nbsp;TOP&nbsp;MODEL</li>
<li id="bp251">  EVERYBODY&nbsp;HATES&nbsp;CHRIS</li>
<li id="bp252">  GAME,&nbsp;THE</li>
<li id="bp253">  GOSSIP&nbsp;GIRL</li>
<li id="bp254">  ONE&nbsp;TREE&nbsp;HILL (Mid Season)</li>
<li id="bp255">  REAPER</li>
<li id="bp256">  SMALLVILLE</li>
<li id="bp257">  SUPERNATURAL</li></ul>
<div id="bp259">&nbsp;</div>
<div id="bp2511">Fox:</div>
<ul id="hwg7">
<li id="hwg70"> 24 (Mid Season)</li>
<li id="hwg71"> AMERICAN&nbsp;DAD</li>
<li id="hwg72"> AMERICAN&nbsp;IDOL (Mid Season)</li>
<li id="hwg73"> AMERICA'S&nbsp;MOST&nbsp;WANTED:&nbsp;AMERICA&nbsp;STRIKES&nbsp;BACK</li>
<li id="hwg74"> ARE&nbsp;YOU&nbsp;SMARTER&nbsp;THAN&nbsp;A&nbsp;FIFTH&nbsp;GRADER?</li>
<li id="hwg75"> BONES</li>
<li id="hwg76"> COPS</li>
<li id="hwg77"> DON'T&nbsp;FORGET&nbsp;THE&nbsp;LYRICS!</li>
<li id="hwg78"> FAMILY&nbsp;GUY</li>
<li id="hwg79"> HELL'S&nbsp;KITCHEN (Mid Season)</li>
<li id="hwg710"> HOUSE</li>
<li id="hwg711"> KING&nbsp;OF&nbsp;THE&nbsp;HILL</li>
<li id="hwg712"> KITCHEN&nbsp;NIGHTMARES</li>
<li id="hwg713"> MOMENT&nbsp;OF&nbsp;TRUTH,&nbsp;THE</li>
<li id="hwg714"> PRISON&nbsp;BREAK</li>
<li id="hwg715"> SIMPSONS,&nbsp;THE</li>
<li id="fr4v">SO YOU THINK YOU CAN DANCE (Mid Season)</li>
<li id="hwg716"> TERMINATOR:&nbsp;THE&nbsp;SARAH&nbsp;CONNOR&nbsp;CHRONICLES</li>
<li id="hwg717"> 'TIL&nbsp;DEATH</li></ul>
<div id="m0hx">&nbsp;</div>
<div id="m0hx1">NBC:</div>
<ul id="ud:t">
<li id="ud:t0">  30&nbsp;ROCK</li>
<li id="ud:t1">AMERICAN GLADIATORS (Mid Season)</li>
<li id="ud:t2">  BIGGEST&nbsp;LOSER,&nbsp;THE</li>
<li id="lr97">CELEBRITY APPRENTICE 2, THE (Mid Season)</li>
<li id="ud:t3">  CHUCK</li>
<li id="ud:t4">  DATELINE&nbsp;NBC</li>
<li id="ud:t5">  DEAL&nbsp;OR&nbsp;NO&nbsp;DEAL</li>
<li id="ud:t6">  ER</li>
<li id="ud:t7">  FOOTBALL&nbsp;NIGHT&nbsp;IN&nbsp;AMERICA</li>
<li id="ud:t8">  FRIDAY&nbsp;NIGHT&nbsp;LIGHTS (Mid Season)</li>
<li id="ud:t9">  HEROES</li>
<li id="ud:t11">  LAW&nbsp;&amp;&nbsp;ORDER (Mid Season)</li>
<li id="ud:t12">  LAW&nbsp;&amp;&nbsp;ORDER:&nbsp;SPECIAL&nbsp;VICTIMS&nbsp;UNIT</li>
<li id="ud:t13">  LIFE</li>
<li id="ud:t14">  LIPSTICK&nbsp;JUNGLE</li>
<li id="ud:t15">  MEDIUM (Mid Season)</li>
<li id="ud:t16">  MY&nbsp;NAME&nbsp;IS&nbsp;EARL</li>
<li id="ud:t17">  OFFICE,&nbsp;THE</li>
<li id="bn8q">SATURDAY NIGHT LIVE</li>
<li id="ud:t18">  SUNDAY&nbsp;NIGHT&nbsp;FOOTBALL</li></ul>
<div id="gvs.">&nbsp;</div>
<div id="gvs.1"><em id="eki.1">Canceled:</em></div>
<div id="gvs.3"><br id="gvs.5"></div>
<div id="gvs.6">ABC:</div>
<ul id="pia7">
<li id="pia70">   BIG&nbsp;SHOTS</li>
<li id="pia71">   CARPOOLERS</li>
<li id="pia72">   CASHMERE&nbsp;MAFIA</li>
<li id="pia73">   CAVEMEN</li>
<li id="pia74">   DANCE&nbsp;MACHINE</li>
<li id="pia75">   JUST&nbsp;FOR&nbsp;LAUGHS</li>
<li id="pia76">   MEN&nbsp;IN&nbsp;TREES</li>
<li id="pia77">   MISS/GUIDED</li>
<li id="pia78">   NOTES&nbsp;FROM&nbsp;THE&nbsp;UNDERBELLY</li>
<li id="pia79">   OCTOBER&nbsp;ROAD</li>
<li id="pia710">   OPRAH'S&nbsp;BIG&nbsp;GIVE</li>
<li id="pia711">   WOMEN'S&nbsp;MURDER&nbsp;CLUB</li></ul>
<div id="tvzw">&nbsp;</div>
<div id="tvzw1">CBS:</div>
<ul id="dq4m">
<li id="dq4m0">    CANE</li>
<li id="dq4m1">    JERICHO</li>
<li id="dq4m2">    KID&nbsp;NATION</li>
<li id="dq4m3">    MOONLIGHT</li>
<li id="dq4m4">    POWER&nbsp;OF&nbsp;10</li>
<li id="dq4m5">    SECRET&nbsp;TALENTS&nbsp;OF&nbsp;THE&nbsp;STARS</li>
<li id="dq4m6">    SHARK</li>
<li id="dq4m7">    VIVA&nbsp;LAUGHLIN</li>
<li id="dq4m8">    WELCOME&nbsp;TO&nbsp;THE&nbsp;CAPTAIN</li></ul>
<div id="dq4m9">&nbsp;</div>
<div id="dq4m11">CW:</div>
<ul id="d_2r">
<li id="d_2r0">    ALIENS&nbsp;IN&nbsp;AMERICA</li>
<li id="d_2r1">    BEAUTY&nbsp;AND&nbsp;THE&nbsp;GEEK</li>
<li id="d_2r2">    CROWNED:&nbsp;THE&nbsp;MOTHER&nbsp;OF&nbsp;ALL&nbsp;PAGEANTS</li>
<li id="d_2r3">    CW&nbsp;NOW</li>
<li id="trxa">FARMER WANTS A WIFE</li>
<li id="d_2r4">    GIRLFRIENDS</li>
<li id="d_2r5">    LIFE&nbsp;IS&nbsp;WILD</li>
<li id="d_2r6">    ONLINE&nbsp;NATION</li>
<li id="d_2r7">    PUSSYCAT&nbsp;DOLLS&nbsp;PRESENT:&nbsp;GIRLICIOUS</li>
<li id="gk13">WWE&nbsp;SMACKDOWN (Moving to MyNetworkTV)</li></ul>
<div id="spdn">&nbsp;</div>
<div id="spdn1">Fox:</div>
<ul id="spdn2">
<li id="spdn3">    BACK&nbsp;TO&nbsp;YOU</li>
<li id="spdn4">    CANTERBURY'S&nbsp;LAW</li>
<li id="spdn5">    K-VILLE</li>
<li id="spdn6">    NASHVILLE</li>
<li id="spdn7">    NEW&nbsp;AMSTERDAM</li>
<li id="spdn8">    NEXT&nbsp;GREAT&nbsp;AMERICAN&nbsp;BAND,&nbsp;THE</li>
<li id="spdn9">    RETURN&nbsp;OF&nbsp;JEZEBEL&nbsp;JAMES,&nbsp;THE</li>
<li id="spdn10">    UNHITCHED</li></ul>
<div id="h0gl">&nbsp;</div>
<div id="h0gl1">NBC:</div>
<ul id="gmmk">
<li id="gmmk0">     1&nbsp;VS.&nbsp;100</li>
<li id="gmmk1">     AMNE$IA</li>
<li id="gmmk2">     BIONIC&nbsp;WOMAN</li>
<li id="gmmk3">     CLASH&nbsp;OF&nbsp;THE&nbsp;CHOIRS</li>
<li id="gmmk4">     JOURNEYMAN</li>
<li id="gmmk5">     LAS&nbsp;VEGAS</li>
<li id="gmmk6">     MY&nbsp;DAD&nbsp;IS&nbsp;BETTER&nbsp;THAN&nbsp;YOUR&nbsp;DAD</li>
<li id="gmmk7">     PHENOMENON</li>
<li id="gmmk8">     QUARTERLIFE</li>
<li id="gmmk9">     SINGING&nbsp;BEE,&nbsp;THE</li></ul>

		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>August  8, 2008 12:25 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1497
					AND a.topic_id = t.topic_id
					AND t.topic_id = p.topic_id
					AND p.post_id = pt.post_id
				ORDER BY post_time";
				$result = mQuery($sql);
				$num_comments = mysql_num_rows($result);
				
				if ($num_comments > 0) {
					# Skip the first one, as it's just the excerpt post.
					$row = mysql_fetch_assoc($result);
					$thread_url = URL_FORUM_VIEWTOPIC .'?t='. $row[topic_id];
					echo '<h2 style="margin-bottom:10px"><a href="'. $thread_url .'">Reader Commentary</a></h2>'.
					'<div class="item"><span class="corners-top"><span></span></span>'.
						'<img src="/images/icon_topic.gif" alt="" /><b> See Forum Topic</b>: '.
						'<a href="'. $thread_url .'">'. $row[topic_title] .'</a> <span class="grey">('. $row[topic_replies] .' replies)</span>'.
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

			<?if (9 <> 7) {
				# Recent Articles by Author (exclude this one)
				# Do not show recent articles for Bulletins.
				$qry = "
				SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
				FROM mt_entry e, mt_author a
				WHERE entry_blog_id = 9
					AND entry_id <> 1497
					AND entry_status = 2
					AND entry_author_id = a.author_id
					AND a.author_name = 'The HT Guys'
				ORDER BY entry_created_on DESC LIMIT 10";
				$result = mQuery($qry);
				
				if (mysql_num_rows($result) > 0) {
					$row = mysql_fetch_assoc($result);
					echo '<div class="item"><span class="corners-top"><span></span></span>'.
					'<h2><a href="../../author.php?author='. urlencode($row[author_name]) .'&id='. $row[author_id] .'">More from '. $row[author_name] .'</a></h2><ul>';
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
					<h2>About The HT Guys</h2>
					<?=stripslashes($author[bio_short])?>
				<span class="corners-bottom"><span></span></span></div>
			<?}?>
		</td><td id="right">
			<div class="item"><span class="corners-top"><span></span></span>
				<h2><a href="/forum/index.php">Other Recent Discussion</h2><ul class="brownsquare"><?
					$qry = "
					SELECT topic_title, t.topic_id, username as post_author, post_time, post_id
					FROM phpbb_topics t, phpbb_users u, phpbb_posts p
					WHERE
						t.forum_id NOT IN (". EXCLUDE_FORUMS .")
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
					WHERE entry_blog_id IN (1)
						AND entry_status = 2
						AND entry_author_id = author_id
					GROUP BY author_id, author_name
					ORDER BY num DESC";
					$res_authors = mQuery($qry);
					while ($row_authors = mysql_fetch_assoc($res_authors)) {
						echo '<li><a href="../../../articles/author.php?author='. urlencode($row_authors[author_name]) .'&id='. $row_authors[author_id] .'">'. $row_authors[author_name] .'</a><span class="grey"> ('. $row_authors[num] .')</span></li>';
					}
				?></ul>
			<span class="corners-bottom"><span></span></span></div>

		</td>
	</tr></table>

	<?
		include(BASE_DIR .'/includes/body_footer.php');
	?>
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/08/hdtv_and_home_theater_podcast_300_-_2008-2009_tv_whos_in_and_whos_out.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
