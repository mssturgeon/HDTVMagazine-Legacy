<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 96";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 96 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# Set variables based on entry type. NOTE: Podcasts has its own entry template, so it is not included amongst the choices below.
	switch (4) {
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
	<meta name="keywords" content="hdtv news, camera cable, monday night, night football, letter box, ntsc, NTSC, HDTV, hdtv, cameras, show, problem, truck, something, camera, monitor, shot, different, see, director, lenses, resolution, news, think, NEWS" />
	<meta name="description" content="INTERVIEW Mark Schubin Interviewed by Dale Cripps in 2001. Mark Shubin ranks among the best of the best television engineers today. He brings more than 20 years experience to every production along with a wealth of television history and lore...." />
	<title>HDTV Magazine Interviews - Mark Schubin - Engineer, Historian, Author</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/mark_schubin_-_engineer_historian_author';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Mark Schubin - Engineer, Historian, Author'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/history/interviews/2005/06/mark_schubin_-_engineer_historian_author.php";
		if ($author[img] != '' && 4 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Mark Schubin - Engineer, Historian, Author</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>June 18, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/interviews/2005/06/mark_schubin_-_engineer_historian_author.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/history/interviews/2005/06/mark_schubin_-_engineer_historian_author.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/history/interviews/2005/06/mark_schubin_-_engineer_historian_author.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/interviews/2005/06/mark_schubin_-_engineer_historian_author.php&amp;phase=2&amp;title=Mark%20Schubin%20-%20Engineer%2C%20Historian%2C%20Author&amp;bodytext=INTERVIEW%20Mark%20Schubin%20Interviewed%20by%20Dale%20Cripps%20in%202001.%20Mark%20Shubin%20ranks%20among%20the%20best%20of%20the%20best%20television%20engineers%20today.%20He%20brings%20more%20than%2020%20years%20experience%20to%20every%20production%20along%20with%20a%20wealth%20of%20television%20history%20and%20lore....&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p>INTERVIEW  <br />
Mark Schubin</p>

<p>Interviewed by Dale Cripps in 2001.</p>

<p>Mark Shubin ranks among the best of the best television engineers today. He brings more than 20 years experience to every production along with a wealth of television history and lore. A consultant now to many large communications companies both here and abroad he took time out from his busy schedule to talk to us about the production of an opera produced in 720p with the Panasonic production truck, the same one used for many of the ABC produced Monday Night Football games. Mark talks about some of the characteristics of HDTV which differ from the older standard.</p>

<p> <br />
<strong>HDTV NEWS: You said that HDTV used to be nearly impossible to shoot, and now it isn't; can you elaborate?</strong></p>

<p>We recently shot an opera in Washington. The first Opera I shot in HDTV, which was not the first show I did in HDTV, was Semiramide at the met. We did that completely differently from the way that we would do other operas that we shoot at the met, because the HDTV technology was so restrictive. </p>

<p>The cameras were not placed where the director wanted them; the cameras were placed where the camera cables would allow them to go. We didn't record as many cameras as we would like, because there weren't that many cameras available for HD. </p>

<p>We didn't get the shots that we wanted, because the lenses were very restricted. </p>

<p>We couldn't get very many lenses. The cameras were tremendously insensitive, so we were very restricted in what we could do with lighting, and even then, if somebody moved, and was wearing some jewelry, the jewelry would stay on the screen after the person moved, the cameras were so sticky! </p>

<p>It was an era when everybody not in HD was using solid-state cameras, and in HD you had to use tube cameras. The tape machines that we used were open reel machines, and they recorded wonderful pictures, if you could get them to work, but to get them to work, you had to do something called "burnishing the tape", which meant that you had to run the tape through the machine without recording on it, and then take the reel off, and clean the heads afterwards. </p>

<p>It was a very difficult period to do anything in HD. That period, for the most part, is over.</p>

<p><strong>HDTV NEWS: When did that give way, completely?</strong></p>

<p>It's been a gradual process, but now there is very little that you can do in NTSC </p>

<p>It's been a gradual process, but now there is very little that you can do in NTSC, that you can't also do in HD. The truck that I shot in in Washington is a real TV truck. It's got distribution amplifiers and routing switchers (when we did Semiramide we had to patch with three wire patch cords; that's all done; we just route signals around now). The cameras went exactly where the director wanted them, and there was no problem running camera cable. There's plenty of camera cable available. It's fiber optic camera cable. In fact, because we were using ABC's Monday Night Football truck, and they needed lots of camera cable, and we needed lots of camera cable, we actually got some camera cable from a different source, and the camera cables are all standardized now, so that's pretty easy to look like [??][045]. </p>

<p>The lenses that we used are basically the lenses that the director wanted. We had six very long telephoto zoom lenses, because they are now available for HD. All of that has improved tremendously. It is now possible to do almost anything that you can do in NTSC. There digital effects and there are significant switchers available for HD. There are camcorders available for HD now--two different ones. It's no longer this really, really strange thing that you can't shoot in, and the sensitivity has been improved tremendously.</p>

<p>The one area that still seems to be lacking significantly, is monitoring, but it's not so lacking that you can't do a show. You can certainly do a show in HD.</p>

<p><strong>HDTV NEWS: What do they need to improve in monitoring?</strong></p>

<p>The one area that still seems to be lacking significantly, is monitoring </p>

<p>We need to get affordable, relatively light weight, high quality monitors for HD, and that doesn't exist. There are heavy, gigantic guys from Sony and Panasonic, which make lovely pictures, but it's very difficult to position them, and if you were going to make a monitor wall, as they're doing, say, in the ABC Monday Night Football for Panasonic, the wall (of monitors) would just weigh so much the truck would probably fall over on it's side. So instead, they went with plasma panels, and LCD displays. Those are severely lacking in quality. Even in the video area, this being a 720P truck--somewhat more restricted--they were using Barco computer monitors, rather than video monitors. </p>

<p>Monitors are good--the pictures were fine--but they lack a lot of the facilities that one would normally expect to have in a video monitor.</p>

<p>So, it was a small shortfall--not critical--we were certainly able to do a show. There's down conversion (for) all of that. That works. As I wrote, it's not impossible to do HD shows any more. The big problem is when you're doing an HD show in the NTSC universe.</p>

<p><strong>HDTV NEWS: Is there a kind of a mis-match there? What should the future user of this equipment look for? What do they need to protect themselves against?</strong></p>

<p>That's a very difficult question to answer, but I can give you lots of the problems that we run into. For example, we shot opening night at Carnegie Hall in 1998 in HDTV, and we had previously been shooting opening night at Carnegie Hall every year in NTSC. </p>

<p>Again, the facilities were fine--we had an NHK truck that we were using for that. The cameras were terrific; cables, lenses, everything was fine. We had to do some interesting out-boarding because it was an HDTV truck, but we were going to be editing off-line, so we needed tapes to be able to feed the off line editor. That was NTSC, so we actually had to pull over a second truck, just to deal with the NTSC stuff, but that's sort of an operational difficulty, that's not an insoluble problem. </p>

<p>The insoluble problem came about in this regard: The director, who was in the HDTV truck, was looking at a very good HDTV monitor, and he was seeing very good HDTV pictures. He decided on a shot that had the two featured singers, who were Brian Stokes Mitchell, and Audrey Ann McDonald, and had them in full figure--the conductor on the left side of the frame, and members of the orchestra behind. It was an absolutely glorious shot. You could see the two singers interacting with each other; you could see their faces, and the look in their eyes, and the conductor appreciating them, and the orchestra appreciating them, and it was an absolutely magnificent shot. Everyone who's seen the show in HD says "wow, what a terrific shot, what a great show. This is terrific". </p>

<p>But then we have to air the thing. There is not a great deal of HDTV airing available yet, so we have to convert the show to NTSC. We chose to use letter-box to deal with the aspect ratio problem. That meant that people watching the show on NTSC got to see only 360 lines of television. We started with 1080, so they now have one third of the vertical resolution that we started with. </p>

<p>In horizontal terms, we started with 1920 pixels per line, and a typical NTSC television station can, at best, transmit 440 pixels per line. So we've gone to something like a fifth of the resolution horizontally, a third of the resolution vertically. So now this wonderful, beautiful shot that the director had gotten, and it was such a good shot that he lingered on it, having no reason to change it, in NTSC becomes an establishing shot, and the NTSC viewers are looking at this, and going . . . "hello . . . when are we going to be able to see their faces?"</p>

<p>They could not see any of this interaction. Instead of it being a beautiful shot, it was a boring shot, and one that was lasting for a very long time. So that's one enormous problem. It's an esthetic problem, and the question is, what do you do? Do you shoot in HD . . . go for the HD shot? Unquestionably, it was a glorious HD shot; it was a beautiful HD show. Whether you do that, and say "ok, well, I'm shooting for the best stuff, or do you instead, give the director an NTSC monitor, and say make me a nice NTSC show, and then the HD people get sort of a boring show--maybe a little better looking than NTSC does--or do you try to do some compromise in between, and dissatisfy both sides?</p>

<p>What has been happening in places like when CBS did their coverage of the US That meant that people watching the show on NTSC got to see only 360 lines of television. <br />
Tennis Open, and ABC's coverage of Monday Night Football, is they have actually been doing two completely separate feeds. So ABC has an HD director, and an HD truck, and is creating an HD feed, and they have a separate NTSC director, and an NTSC truck, creating an NTSC feed. CBS did exactly the same thing with the US Open. The HD director is just making the best HD show he or she can make, and the NTSC director is just making the best NTSC show he or she can make. </p>

<p>That's great if you can do that. We don't have the facilities in the kinds of shows that we do to do that, and so we've been suffering.</p>

<p><strong>HDTV NEWS: So there will be a suffering period as we make this transition over the years. Unless people are willing to do dual productions, we're going to have a compromise that is a little bit distracting to both sides.</strong><br />
Yes, we are in compromise territory.</p>

<p>Does that suggest to you that, as David Niles, and others have been saying since the beginning, that HDTV is entirely a different and separate business from the NTSC business, even though it looks an awful lot the same? </p>

<p>I'm not sure which of the David Niles quotes you are referring to. He has said on a number of occasions, and a bunch of other people have said, that HDTV is not film, it is not video, it is something completely different. That, I don't think, is relevant to this discussion, but you used the term "business", and if he has been saying that it's a different business, meaning that you need to deal with it separately; yes, I'd go along with that.</p>

<p><strong>HDTV NEWS: Latency versus quality?</strong></p>

<p>Ok, this is something that we ran into in Washington. This is a different side of the HDTV and NSTC universe. The stuff that I just mentioned is the problem of broadcasting to a NTSC audience. The other problem you run into is if you are going out, and you are shooting some single camera stuff, like the "Over America," "Over Canada," all those series, or you're shooting a studio show, or something like that, you're pretty much self-contained in HD, until you go into editing and have to down-convert for NTSC airing. But what if you're doing a real NTSC live show on the order of, let's say Monday Night Football, and what if you're doing it as the only truck, not with two trucks? </p>

<p>Well, now you have a bunch of situations. You've got an announcer some place, and the announcer needs to see the show, so he needs a monitor to look at. In NTSC, that's a hundred dollar monitor that you stick in front of the him, and run a line up to it. In HDTV that's maybe a $4,000 monitor, and I'm talking about something inexpensive like a consumer-type monitor. It's big, and it's heavy, and the line that you run up there...let's say that you're using the high definition serial digital interface, HDSDI, that's one and a half gigabits of information. You can't run that very far. We ran into that problem in Washington. The lighting director normally sits in an area at the opera house at the Kennedy Center called the translation booth. That's about 700 feet of cable run from the truck. You can't run HDSDI over 700 feet on a piece of coaxial. You could maybe do it with fiber, and if we have time to rig that up, and have the equipment, we could maybe do that. So that's a problem.</p>

<p>We had a separate sound truck in Washington. The sound people are looking at a monitor to see where the singers are, and what's going on, and we fed them something called the low-latency down-converter. It's actually part of the distribution amplifier, and it essentially down-converts at the same timing that the real signal is going out. That meant that they were seeing pretty lousy down-conversion. Well, we had good down-converters available; we had the Panasonic Universal down-converter, which is terrific, and even the down-converters built into the HTD 5 machines are terrific, but they take time to do their work, which meant that if we fed that to the sound-truck, which we ultimately did do, they're looking at something that's out of sync with the audio that we're hearing.</p>

<p>Ok, you can take care of that. The Universal down-converter, and the HTD 5 have separate audio outputs that match the down-conversion, but now you're asking the audio people to be listening to something which is not what they're mixing. So you run into all these complications.</p>

<p>Here's another situation we have...again, there's sort of a paper edit that gets made before an off-line edit on a standard computer non-linear system before the on-line edit. Well, for the paper edit, we prepare a vhs tape that has a quad split in it. The quad split is the four main recordings that we're making. It's an NTSC show; you're degrading the quality already of the NTSC by making the quad split, and then you're putting it on vhs, which degrades stuff still further, so HD, if we did that with letter-box output, would be such tiny pictures , and so degraded, that the editor couldn't really tell anything. </p>

<p>He wanted us to make anamorphic outputs for the quad split. So, ok, fine, no problem, the Panasonic machines will deal with that. But meanwhile, the lighting director wanted to have a VHS of the program feed, which was not anamorphic. He wanted to see that in letter-box, so that he could see what the actual show looked like. So now we have a different mood, and we need a different down-converter. If we were feeding the press, as we've done, say, at Carnegie Hall, then we need to down-convert to something that is neither letter-box nor anamorphic, because the press doesn't want to have letter-box on their news shows, they want to have full-screen, so now we have to come up with a center cut or a pan-and-scan type of thing. </p>

<p>Now we're talking three different kinds of down-conversion...not counting the fact that we have different latencies; not counting the fact that there are different qualities that are associated with latencies, so it just becomes a bit complicated.</p>

<p><strong>HDTV NEWS: It's a bit complicated, but these are problems that are not likely to ever go away, are they?</strong></p>

<p>Not while we are dealing with two different forms of television. As long as we're dealing with both HD and NTSC, these problems are not going to go away.</p>

<p><strong>HDTV NEWS: You mentioned focus being a big issue. How does that trouble you, or not trouble you?</strong></p>

<p>Well, it's troubling! In dealing with focus, we refer to something called the circle of confusion. The circle of confusion is a small circle, anywhere within which you cannot tell whether something is in focus, or out of focus, so you can think of it as being a pixel. If something fits within one pixel, it's in focus, if it doesn't fit within one pixel, it's out of focus. That's a little over-simplified, but it's ok to think of it that way.</p>

<p>An HDTV pixel is much smaller than a NTSC pixel, and so if you work out the formulas for depth of field, and so on, that are all based on this circle of confusion, you find that things are much more difficult to focus on in HDTV than they are in NTSC. That's just straight-forward. That's assuming all else is equal, but all else is not equal, because the cameras are also a little less sensitive. So if you're dealing with a lot of light; if you are in a studio or something like that, then you just have to deal with the circle of confusion issue. If you're dealing with low light, then you also have to face the problem that the lenses for an HD camera are going to be opened up a little wider than the lenses for an NTSC camera. </p>

<p>Then you have a third problem, which is, if you're shooting with an inch and a half viewfinder, you have this little teeny tiny picture tube that you're looking at, and you need to focus in HD on something that may not be able to present you with a true HD picture.</p>

<p>C. R. Caluette gave a really terrific paper about that at the ITS Technology Retreat last year. He said you really need the largest monitor that you can take around with you on your show, because you're not going to be able to tell focus in your tiny viewfinder.</p>

<p>What's interesting is, in the previous generation of cameras, the Sony HDC500, there was a remote focus control, so that the video operator could help out and do fine focus. The latest cameras don't have that. Also, the latest cameras using two thirds inch imaging devices don't have lenses for them that match the quality of the lenses that were available for the one inch cameras. So, in a way, we've come an additional generation forward...there are now HD camcorders, for example, which there weren't before, but we've taken a step backward in terms of picture quality, I think. The latest cameras are not up to the snuff of what the previous possibility of camera and lens were. I'm not saying it's just the camera. It's the camera/lens combination.</p>

<p><strong>HDTV NEWS: As an engineer, are there any serious shortcomings today that you just wish the camera designers would have addressed? Can you help give them a little engineering feedback? </strong></p>

<p>I would like monitoring to be addressed, more than cameras. On the camera issue, I think that the bigger problem is the lens. I think that the cameras are pretty good. There are some minor things that need to be worked out. There were some problems that we had in Washington, but those were prototype cameras of the 720p mode. I think the cameras are pretty good, but having gone through the two thirds inch chip, there is a big problem in terms of detail resolution. It is not as good as the previous generation of cameras was. </p>

<p><strong>HDTV NEWS: Are these lenses that are being used the same lenses that were used for NTSC? </strong></p>

<p>Some people do that. I wouldn't. I think that's a bad idea, but I'm told that that's one of the reasons that the shift in the cameras to two thirds inch was done; so that people could use their NTSC lenses. There's a big difference between an HD lens and an NTSC lens, but again, it depends on what you're looking at it on. A lot of people go out in the field, and will shoot something, and will look at it on some monitor that doesn't really have true HD resolution, and they'll say "oh...I can't see any difference with this lens at all...this looks fine", and then they will air it, or show it someplace where there is a really high quality HD projector, and you're looking at it, and you go "boy, this looks awfully soft, compared to the other stuff you shot", so I think that's a big problem. I don't think that was properly done.</p>

<p><strong>HDTV NEWS: Getting back to the monitor story, some of the producers have said "you know, you really need to produce this on a monitor that offers a 30 degree field of view that you expect the end-viewer to be experiencing. That would suggest a larger monitor.Is that called for? </strong></p>

<p>I have mixed feelings about that. If you look at human vision, and where it pops out in resolution, you find that you actually can't see HD resolution without having a large monitor, or being closer to it. If you were to scale up...In an NTSC truck, the director typically looks at a 20 inch monitor...in an HD facility you'd probably want something on the order of a 50 inch monitor, and nobody's really doing that yet.    <br />
    <br />
  <em>On the camera issue, I think that the bigger problem is the lens  </em></p>

<p><br />
But, there is a second human visual phenomenon, which is known as sharpness. Sharpness is different from resolution. Resolution is being able to distinguish that two lines in a pair are two lines and not one line. Sharpness is a psycho-visual sensation that says "this is very crisp", or something like that. Otto Shade, the researcher at RCA laboratories many years ago, said that the psycho-visual sensation of sharpness is proportional to the square of the area under a curve that plots resolution versus contrast. Even if you cannot see the maximum resolution of HD, you may be influenced by it, if this sharpness is improved for you. </p>

<p>What I found, in sitting in the truck at the Washington Opera, was that the little 17 inch STI LTD panels looked noticeably sharper to me than did the 42 inch platinum panels. Even from considerably farther away, (too far, in my opinion, to see the HD resolution), one of two things was happening. Either the platinum panels were so awful, in the resolution that they were providing, that I would notice it from the STI's, or there was some contribution sharpness being made. So, I'm not prepared to give an absolute answer to your question, at this point, but I think that there are certainly reasons to believe that what you said might be true--that you may have to go for larger monitoring.</p>

<p><strong>HDTV NEWS: In the different cameras on the market today...are they functionally about the same? In other words, a man trained on one could easily move to the other?</strong></p>

<p>Yes, sure. There are slight differences, but nothing special.</p>

<p><br />
<strong>Thank you Mark.</strong></p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>June 18, 2005 11:36 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 96
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
			
 		<?if (4 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 4
 				AND entry_id <> 96
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'Dale Cripps'
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
 				<h2>About Dale Cripps</h2>
 				<?=stripslashes($author[bio_short])?>
 			<span class="corners-bottom"><span></span></span></div>
 		<?}?>
		</td><td id="right">

 		<?if ($about != '') {?>
 			<div class="item"><span class="corners-top"><span></span></span>
 				<h2>About Interviews</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/interviews/2005/06/mark_schubin_-_engineer_historian_author.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
