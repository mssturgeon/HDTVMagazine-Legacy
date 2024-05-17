<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 654";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 654 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# Set variables based on entry type. NOTE: Podcasts has its own entry template, so it is not included amongst the choices below.
	switch (1) {
		case 1: # Articles
			$feed_name = 'hdtv-articles';
			$container = 'article_container';
			$sub_type = SUB_ARTICLES;
			$sub_label = 'Receive instant notification of new articles';
			$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
			$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
			break;
		case 4: # Interviews
			$feed_name = 'hdtv-interviews';
			break;
		case 5: # History
			$feed_name = 'hdtv-archive';
			break;
		case 6: # Test
			$container = 'article_container';
			$sub_type = 0;
			break;
		case 7: # Bulletins
			$google_links_channel = ''; # Don't count bulletins
			$feed_name = 'hdtv-news';
			$container = 'bulletin_container';
			$sub_type = SUB_BULLETINS;
			$sub_label = 'Receive instant notification of HDTV Bulletins';
			$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of HDTV Bulletins via email as soon as they are published.';
			$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of HDTV Bulletins via email as soon as they are published.';
			break;
		case 8: # Reviews
			$feed_name = 'hdtv-reviews';
			$container = 'article_container';
			$sub_type = SUB_REVIEWS;
			$sub_label = 'Receive instant notification of new reviews';
			$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Reviews via email as soon as they are published.';
			$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Reviews via email as soon as they are published.';
			break;
#		case 9: # Podcasts
		case 10: # Columns
			$feed_name = 'hdtv-columns';
			$container = 'article_container';
			$sub_type = SUB_COLUMNS;
			$sub_label = 'Receive instant notification of new columns';
			$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Columns via email as soon as they are published.';
			$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Columns via email as soon as they are published.';
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
	<?require(BASE_DIR .'/includes/common_header.php');?>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<meta name="generator" content="http://www.movabletype.org/" />
	<meta name="keywords" content="dolby digital, digital plus, blu ray, pass feature, according dolby, dolby, Dolby, digital, hdmi, HDMI, audio, Digital, DVD, dvd, player, plus, Plus, pass, feature, players, blu, ray, Blu, soundtrack, format" />
	<meta name="description" content="In April 2007, as part of my analysis about Hi-Def DVD and Multi-channel audio in my annual HDTV Technology Review, I discussed the subject with Craig Eggers and Roger Dressler, Dolby executives. Some of the items discussed were: soundtrack streaming pass-through feature over HDMI in near future players, streamed Dolby Digital Plus not supported by HDMI versions 1.1 and 1.2 (while DTS HD is), audio-mix encoders for legacy connectivity, Dolby Digital at 640 kbps, etc. This article summarizes those conversations as follows:" />
	<title>HDTV Magazine Articles - Hi-Def DVD - Audio Streaming Over HDMI</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/hi-def_dvd_-_audio_streaming_over_hdmi';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Hi-Def DVD - Audio Streaming Over HDMI'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2007/08/hi-def_dvd_-_audio_streaming_over_hdmi.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Rodolfo La Maestra" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Hi-Def DVD - Audio Streaming Over HDMI</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Rodolfo La Maestra</b><br />
				<?=$author_title?>
				Posted on <b>August  1, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=HD DVD & Blu-ray">HD DVD & Blu-ray</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2007/08/hi-def_dvd_-_audio_streaming_over_hdmi.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2007/08/hi-def_dvd_-_audio_streaming_over_hdmi.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2007/08/hi-def_dvd_-_audio_streaming_over_hdmi.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
				<span><img src="/images/save.gif" alt="Save Article" align="absmiddle" /><a target="_blank" href="<?=$save_url?>">Save</a></span>
				<span><img src="/images/email.gif" alt="Email Article" align="absmiddle" /><a href="<?=$email_url?>">Email</a></span>
				<span><img src="/images/print.png" alt="Print Article" align="absmiddle" /><a target="_blank" href="<?=$print_url?>">Print</a></span><br />
				<br /><br />
			</td>
		</tr>
	</table>
	<?if ($sub_type > 0 && ($userdata[subscriptions] & $sub_type) || $_SERVER[HTTP_USER_AGENT] == 'Googlebot') {} else {
		if ($userdata[session_logged_in]) {?>
			<div class="important"><span class="corners-top"><span></span></span>
				<img src="/images/i_inbox.gif" align="left" style="padding-right:10px" />
				<span class="label"><?=$sub_label?>:</span>
				<?=$sub_desc_logged_in?>
			<span class="corners-bottom"><span></span></span></div>
		<?} else {?>
			<div class="important"><span class="corners-top"><span></span></span>
				<img src="/images/i_inbox.gif" align="left" style="padding-right:10px" />
				<span class="label"><?=$sub_label?>:</span>
				<?=$sub_desc_anon?>
			<span class="corners-bottom"><span></span></span></div>
		<?}
	}?>
	<div>
		<div style="float:left; margin:0 5px 5px 0;"><?
			if ($digg_url == '') {
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2007/08/hi-def_dvd_-_audio_streaming_over_hdmi.php&amp;phase=2&amp;title=Hi-Def%20DVD%20-%20Audio%20Streaming%20Over%20HDMI&amp;bodytext=In%20April%202007%2C%20as%20part%20of%20my%20analysis%20about%20Hi-Def%20DVD%20and%20Multi-channel%20audio%20in%20my%20annual%20HDTV%20Technology%20Review%2C%20I%20discussed%20the%20subject%20with%20Craig%20Eggers%20and%20Roger%20Dressler%2C%20Dolby%20executives.%20Some%20of%20the%20items%20discussed%20were%3A%20soundtrack%20streaming%20pass-through%20feature%20over%20HDMI%20in%20near%20future%20players%2C%20streamed%20Dolby%20Digital%20Plus%20not%20supported%20by%20HDMI%20versions%201.1%20and%201.2%20%28while%20DTS%20HD%20is%29%2C%20audio-mix%20encoders%20for%20legacy%20connectivity%2C%20Dolby%20Digital%20at%20640%20kbps%2C%20etc.%20This%20article%20summarizes%20those%20conversations%20as%20follows%3A&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
			} else {
				echo '<script src="http://digg.com/api/diggthis.js"></script>';
			}
		?></div>
		<div id="right" style="float:right; margin:0 0 5px 5px; text-align:center;" align="center">
			<?include(BASE_DIR .'/ads/mrectangle.php');?>
			<br />
			<div align="center">
				<?include(BASE_DIR .'/ads/skyscraper.php');?>
			</div>
		</div>
		<div id="<?=$container?>">
			<p>In April 2007, as part of my analysis about Hi-Def DVD and Multi-channel audio in my annual HDTV Technology Review, I discussed the subject with Craig Eggers and Roger Dressler, Dolby executives. Some of the items discussed were: soundtrack streaming pass-through feature over HDMI in near future players, streamed Dolby Digital Plus not supported by HDMI versions 1.1 and 1.2 (while DTS HD is), audio-mix encoders for legacy connectivity, Dolby Digital at 640 kbps, etc. This article summarizes those conversations as follows:</p>

<p><br />
<B>Streaming Pass-through on HD DVD Players</B></p>

<p>All this started upon my invitation to visit Dolby Labs in NY for their HDMI 1.3 tour in December 2006. One of the slides of the presentation by Dolby made reference to an "audiophile feature" that would allow the Hi-Def DVD player's soundtrack stream read from the disc to bypass all the internal mixing of the player and be outputted undisturbed using the HDMI output of the player.</p>

<p>The stream would certainly have to be decoded externally by an A/V receiver or Pre/pro enabled with the decoders for the soundtrack format of the stream.</p>

<div align="center"><img src="/images/articles/hi-def-dvd-decoding-mixing.jpg" alt="Hi Def DVD Decoding & Mixing" /></div>

<p>One immediate value I saw with this feature was the potential for improved sound quality because the soundtrack would not be submitted to any mixing or audio processing for advanced interactivity features one might not want to have, if the audio would be better without them.</p>

<p>Another value I saw was that a player having this pass-through feature, but not having all the multi-channel advance decoders, could offer a consumer the flexibility to compensate for the lack of decoders by doing the decoding in a newer A/V receiver (if suited with those decoders), without having to replace the player, specially considering that some Blu-ray players cost above $1000.</p>

<p>Even when the new Toshiba player has an HDMI 1.3 output it does not pass-through any native (undecoded) soundtrack streams from the disc over HDMI in Advanced Content mode, regardless of the audio coding format. This is because the interactive advanced content mix is considered a key element of the HD DVD format, which would not be able to be enjoyed to its fullest if the pass-through streaming function is implemented.</p>

<p>In the HD DVD format, the Advanced Content flag in the disc impedes the pass-through function and the player mixing cannot be avoided. It is not a manufacturer's or a consumer's choice.</p>

<p>According to Dolby, in the future, the DVD forum may consider the subject of allowing the pass-through function in HD DVD players, but no action has been taken at this time.</p>

<p>However, there may be opposition from the content makers for such pass-through feature. They may not want to compromise the interactive advance content feature of the format because they invested a lot of time defining that format the way they wanted it.</p>

<p>The software companies, who are also members of the DVD Forum, asked for this capability not to be optional in the HD DVD format, so that they can insure that consumers would not turn it off, and always obtain the full interactive experience of the format.</p>

<div align="center"><img src="/images/articles/hi-def-dvd-bitstream.jpg" alt="Hi Def DVD Bitstream High Resolution Audio" /></div>

<p>If the DVD Forum approves the pass-though feature, its implementation would most probably be done only in future players, and it is not known at this time if a revision to current HD DVD players could be made with a firmware upgrade. A similar implementation scenario could be applicable to Blu-ray as well, more on that below.</p>

<p>According to Dolby, this subject was better to be discussed with the HD DVD Promotion Group. I exchanged emails with them, but since no comments were made I assumed that the statements from Dolby about HD DVD and the DVD Forum were correct.</p>

<p><br />
<B>Streaming Pass-through on Blu-ray Players</B></p>

<p>Dolby stated that technically it is possible to pass-through advanced audio streams thru HDMI 1.3, provided the right protocols are implemented at both ends of the connection.</p>

<p>Dolby commented that it believed that the HDMI 1.3 suited Sony Play Station 3 most likely did not implement the stream-out pass-through feature for its next generation high definition codecs because the needed HDMI 1.3 protocols were still in development at product introduction.</p>

<p>The first HDMI 1.3 enabled Blu-ray players do not have at the present the stream-out functionality implemented. However, later players are expected to support the feature.</p>

<p>As with HD DVD players, Blu-ray players can only have the full interactive experience if they decode the advanced multi-channel audio soundtrack and mix it with the added audio interactive features within the player.</p>

<p>Another key reason for all the Hi-Def DVD players to do the decoding within the player is to assure playback compatibility with current and legacy AV receivers in the marketplace.</p>

<p>When HD DVD and BD players were first introduced, the HDMI protocols to deliver high-resolution audio bit-streams were not completed, nor did very many A/V receivers include an HDMI input. It is only with the advent of HDMI 1.3 that both next generation audio codecs from Dolby could be transported over HDMI.</p>

<p>Unlike HD DVD, the Blu-ray format left it up to the player manufacturer (and to the consumer) whether or not to use the interactivity feature and permit the streaming of the undecoded soundtrack using a pass-through feature.</p>

<p>In summary, if the pass-through feature is eventually used for the advanced audio streaming of only the soundtrack, it will not carry the audio add-on mixes, regardless of the audio format of the soundtrack.</p>

<p><br />
<B>Encoder for Legacy Audio Mix</B></p>

<p>An optional encoder in the player would allow the addition of the audio mixing over the soundtrack to output the legacy Dolby Digital 5.1 using the SPDIF or HDMI outputs streaming at 640Kbps.</p>

<p>However, the manufacturer of the player has the right to build the player without such legacy encoder. If a consumer is interested in the feature of audio mix over legacy audio connections it is recommended for the consumer to verify that such feature has been actually implemented in the player.</p>

<p><br />
<B>Streamed Dolby Digital Plus Not Carried Over HDMI 1.1/1.2 (While DTS HD is)</B></p>

<p>HDMI 1.3 is required to transport streamed Dolby Digital Plus. DTS-HD could be streamed thru HDMI 1.3 as well, but it could be streamed also thru earlier versions 1.1 and 1.2. The reason for the DTS-HD capability of the HDMI 1.1 and 1.2 specifications is because they were revised to carry up to 6 Mbps and to handle the protocols of DTS-HD, but not to support Dolby Digital Plus.</p>

<p>However, even when an A/V receiver might have HDMI 1.1 or 1.2 connections, it would not be able to decode DTS-HD unless it is a new model that is also suited with a new DTS-HD decoder. Otherwise, it will only decode legacy DTS.</p>

<p>Although Dolby Digital Plus has a lower bit rate requirement that the 6Mbps (3Mbps for HD DVD and 4.7 Mbps for current Blu-ray), the format was not included in the revision of the specification per Dolby's choice. To avoid confusing the market, the company preferred to maintain both Dolby advanced audio formats (Dolby Digital Plus and TrueHD) together and only within the same 1.3 specification.</p>

<p>According to Dolby, while it may have been possible to transport Dolby Digital Plus bit-streams over traditional optical or coaxial digital audio outputs, the company made a conscious decision not to license Dolby Digital Plus equipped A/V receivers until the HDMI 1.3 spec was fully implemented.</p>

<p>Dolby's position was that it made more sense ---and the enthusiast would be better served--- to introduce new technologies as a "package", as opposed to releasing Dolby Digital Plus and Dolby TrueHD technologies incrementally into the marketplace in different generations of A/V receivers.</p>

<div align="center"><img src="/images/articles/hi-def-dvd-truehd-bitrate-range.jpg" alt="Dolby TrueHD Bitrate Range" /></div>

<p>The 4.7 Mbps for Dolby Digital Plus for Blu-ray is the maximum data rate needed should the Blu-ray group authorize more than 7.1 channels, in the future. The current maximum data rate for 7.1 channel Dolby Digital Plus in Blu-ray is 1.7 Mbps.</p>

<p>However, Dolby recommends that consumers avoid only using data rate as a measurement of quality. A significant contributor to quality in lossy codecs such as Dolby Digital and Dolby Digital Plus is the efficiency of the technology as well as the density of data.</p>

<p>According to Dolby, not implementing Dolby Digital Plus streaming over commonly used HDMI 1.1/1.2 connections, as DTS does with DTS HD, is actually not an issue, because the existing HD DVD movies carry the Advanced Content flag that would not permit the player to avoid the audio mix over the soundtrack anyway.</p>

<p>On the Blu-ray side, even when the discs do not use such flag, in order to use Dolby Digital Plus, according to Dolby, the soundtrack must have 6.1 or 7.1 discrete signals, and because the few hundred movies available on Blu-ray do not have encoded more than 5.1 channels, there is no multi-channel signal capable to reach the threshold into the Dolby Digital Plus territory.</p>

<p><br />
<B>Dolby Digital Improved at 640kbps</B></p>

<p>As you might have been aware already, a higher bit-rate of 640 kbps for Dolby Digital 5.1 is obtained by a Hi-Def player from the disc, and is outputted using the legacy optical/digital audio connections (and HDMI).</p>

<p>Every Dolby Digital A/V receiver manufactured is capable of decoding the 640kbps Dolby Digital bit-stream, and there is an audible increase in audio quality with 640kbps Dolby Digital, compared to lower bit rate implemented in Standard Definition DVD discs.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Rodolfo La Maestra</b>, <b>August  1, 2007 05:44 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 654
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

 		<div class="item"><span class="corners-top"><span></span></span>
 			<h2>More on HD DVD & Blu-ray</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'HD DVD & Blu-ray'
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
			
 		<?if (1 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 1
 				AND entry_id <> 654
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'Rodolfo La Maestra'
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
 				<h2>About Rodolfo La Maestra</h2>
 				<?=stripslashes($author[bio_short])?>
 			<span class="corners-bottom"><span></span></span></div>
 		<?}?>
		</td><td id="right">

 		<?if ($about != '') {?>
 			<div class="item"><span class="corners-top"><span></span></span>
 				<h2>About Articles</h2>
 				<?=$about?>
 			<span class="corners-bottom"><span></span></span></div>
		<?}?>
		
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2007/08/hi-def_dvd_-_audio_streaming_over_hdmi.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
