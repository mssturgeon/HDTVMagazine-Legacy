<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 113";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 113 AND placement_is_primary = 1";
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
	<meta name="keywords" content="private sector, richard wiley, copy protection, going happen, congressional meetings, HDTV, hdtv, going, think, see, programming, need, people, those, get, FCC, cable, being, good, fcc, big, chairman, Chairman, Wiley, say" />
	<meta name="description" content="&lt;strong&gt;HDTV Magazine: What are the most important unresolved issues with respect to the H/DTV movement today at the end of 2001?&lt;/strong&gt;

Richard Wiley: There are some major impediments for the digital television transition. I would identify four. Some of them are on the way to being greatly improved. One (of those) is the equipment. We now have over 350 models in all sizes and shapes, all of the highest quality. The prices are falling faster than anyone expected. The chicken in the 'chicken and egg' dilemma is being solved as we go.  Now the egg--that being the programming--is the biggest impediment left. The compelling programming is still in short supply. CBS has clearly done a great job (with their prime time). I am pleased as punch to see ABC coming along. I would like to see Monday Night Football a part of it. I don't understand the plan for the others. When NBC went to all-color in the 60s the whole transition to color took off I would love to see that kind of leadership once more. Obviously, going to HDTV is something they must decide for themselves. I am just a K Street lawyer. I do know this: Without compelling programming we do not have a driving force.
" />
	<title>HDTV Magazine Interviews - INTERVIEW - 2001 - Richard Wiley, Chairman of ACATS</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/interview_-_2001_-_richard_wiley_chairman_of_acats';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('INTERVIEW - 2001 - Richard Wiley, Chairman of ACATS'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_2001_-_richard_wiley_chairman_of_acats.php";
		if ($author[img] != '' && 4 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">INTERVIEW - 2001 - Richard Wiley, Chairman of ACATS</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>June 24, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_2001_-_richard_wiley_chairman_of_acats.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_2001_-_richard_wiley_chairman_of_acats.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_2001_-_richard_wiley_chairman_of_acats.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_2001_-_richard_wiley_chairman_of_acats.php&amp;phase=2&amp;title=INTERVIEW%20-%202001%20-%20Richard%20Wiley%2C%20Chairman%20of%20ACATS&amp;bodytext=%3Cstrong%3EHDTV%20Magazine%3A%20What%20are%20the%20most%20important%20unresolved%20issues%20with%20respect%20to%20the%20H%2FDTV%20movement%20today%20at%20the%20end%20of%202001%3F%3C%2Fstrong%3E%0A%0ARichard%20Wiley%3A%20There%20are%20some%20major%20impediments%20for%20the%20digital%20television%20transition.%20I%20would%20identify%20four.%20Some%20of%20them%20are%20on%20the%20way%20to%20being%20greatly%20improved.%20One%20%28of%20those%29%20is%20the%20equipment.%20We%20now%20have%20over%20350%20models%20in%20all%20sizes%20and%20shapes%2C%20all%20of%20the%20highest%20quality.%20The%20prices%20are%20falling%20faster%20than%20anyone%20expected.%20The%20chicken%20in%20the%20%27chicken%20and%20egg%27%20dilemma%20is%20being%20solved%20as%20we%20go.%20%20Now%20the%20egg--that%20being%20the%20programming--is%20the%20biggest%20impediment%20left.%20The%20compelling%20programming%20is%20still%20in%20short%20supply.%20CBS%20has%20clearly%20done%20a%20great%20job%20%28with%20their%20prime%20time%29.%20I%20am%20pleased%20as%20punch%20to%20see%20ABC%20coming%20along.%20I%20would%20like%20to%20see%20Monday%20Night%20Football%20a%20part%20of%20it.%20I%20don%27t%20understand%20the%20plan%20for%20the%20others.%20When%20NBC%20went%20to%20all-color%20in%20the%2060s%20the%20whole%20transition%20to%20color%20took%20off%20I%20would%20love%20to%20see%20that%20kind%20of%20leadership%20once%20more.%20Obviously%2C%20going%20to%20HDTV%20is%20something%20they%20must%20decide%20for%20themselves.%20I%20am%20just%20a%20K%20Street%20lawyer.%20I%20do%20know%20this%3A%20Without%20compelling%20programming%20we%20do%20not%20have%20a%20driving%20force.%0A&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><strong>This interview with Richard Wiley was conducted by Dale Cripps and first published in HDTV Magazine in December of 2001</strong></p>

<p><em>No one has made more of a contribution to the HDTV movement than has Richard E Wiley. Dick Wiley is a former Chairman, Commissioner and General Counsel of the Federal Communications Commission (1970-77) and is now a senior partner in the Washington, D.C. law firm of Wiley, Rein & Fielding. During his time with the Commission Dick played a leading role in fostering new competition and less regulation. </p>

<p>Consistently recognized as one of the nation's 100 "most influential" lawyers by The National Law Journal, he has also been the subject of recent profiles in the New York Times ("Telecommunications' Ubiquitous Man of Influence"), the American Lawyer and the National Law Journal. He was the 1996 recipient of the Electronic Industries Association's Medal of Honor and was recently admitted into Broadcasting & Cable magazine's Hall of Fame. Since 1987, Mr. Wiley served as Chairman of the FCC's Advisory Committee on High Definition Television (ACATS). </p>

<p>As pro bono Chairman of ACATS he drew together a blue ribbon group of industry giants from manufacturing, signal providers, and programming to produce what has come to be known as the ATSC standard.  </p>

<p>We talked this week by phone and covered a wide range of HDTV-related issues including the government's increasing support for the transition. </em><br />
_________________________________________<br />
 <br />
<strong>HDTV Magazine: What are the most important unresolved issues with respect to the H/DTV movement today at the end of 2001?</strong></p>

<p>Richard Wiley: There are some major impediments for the digital television transition. I would identify four. Some of them are on the way to being greatly improved. One (of those) is the equipment. We now have over 350 models in all sizes and shapes, all of the highest quality. The prices are falling faster than anyone expected. The chicken in the 'chicken and egg' dilemma is being solved as we go.  Now the egg--that being the programming--is the biggest impediment left. The compelling programming is still in short supply. CBS has clearly done a great job (with their prime time). I am pleased as punch to see ABC coming along. I would like to see Monday Night Football a part of it. I don't understand the plan for the others. When NBC went to all-color in the 60s the whole transition to color took off I would love to see that kind of leadership once more. Obviously, going to HDTV is something they must decide for themselves. I am just a K Street lawyer. I do know this: Without compelling programming we do not have a driving force.</p>

<p>You and I tend to be nuts about HDTV. I will say this about it: It IS something different; It IS a different viewing experience. We have a lot of channels here in the United States. I can understand why the Europeans are not focusing upon HDTV as they still seek more channels. But just more channels here in the United States is not going to get it for me in my home. </p>

<p>What I want is a different level of entertainment. Clearly, HDTV offers that. I have a HDTV set. I can "feel it" in my own eyes and ears. The sound and the picture are just fabulous. But we need (more) sports. We need (more) movies, which goes to the third impediment. </p>

<p>We need a solution to the copy protection issue. I know everyone is working on it. But they need a solution that covers broadcasting too (in addition to satellite and cable). I am not an expert in this field, but they need something (a watermark, perhaps) that will allow for it. I have some sympathy for Hollywood on this (problem of unauthorized copying). The fact is that with HDTV you can make perfect copies at home. Content providers have a right to be concerned over that, but we need to find a solution.</p>

<p><strong>Do you think a solution is on the horizon?</strong><br />
 <br />
Progress is being made. People understand the problems better. There is still one more (unresolved) major impediment--"cable interoperability" and/or compatibility. It is not an inconsequential purchase for most of us when we spend $2 or $3 thousand on a new set. We should expect to plug that into a wall just as you can now with the analog TV. </p>

<p>Cable has to also step-up. HBO is giving us nearly 14 hours a day of HDTV programming. In most markets, however, you need to get that wonderful programming via satellite dish. In my estimation that is unfortunate </p>

<p><strong>Why is that unfortunate?</strong><br />
 <br />
Nearly 70% of the population are already hooked to the cable. It is a big impediment to the buyer of that wonderful HD equipment when the salesman responds to his/her questions about cable compatibility with "well, not in most markets." That has to be an inhibition for people who then think that they must install either a satellite or terrestrial antenna. </p>

<p><strong>We find that people are willing to make those installations once they are aware of the great benefits to come with HD.</strong><br />
 <br />
Yes, true, but it is a little "retro." We have moved beyond the day of antennas. Yes, I have an antenna and was one of those willing to do it. But I would like to see more programming and be able to get it in an all-transmission media.</p>

<p>Gary Shapiro (president of the Consumer Electronics Association) is quite correct in saying that digital sales are going up every month. I think that is exciting. It still pales in comparison to the sales of legacy analog sets and boxes that don't pass through HDTV programming. That is why I would love to see the industry ON ITS OWN put digital tuners in a growing number of the big sets.</p>

<p><strong>Do you embrace the National Association of Broadcaster's position that a digital tuner should be mandated by the FCC into every new set sold?</strong><br />
 <br />
The question of being forced to do it is another matter. I would like to see the industry phase it in first in the big sets. That would be a very good thing.</p>

<p><strong>From our field investigations it looks like big-screen SDTV sets are losing ground and buyers are increasingly turning to the HDTV big-screen sets.</strong><br />
 <br />
That is good to hear. I think sales are beginning to pick up, but we do need more programming, cable operability, and the copy protection matters settled. Those are the big three (impediments).</p>

<p><strong>Can you give us some background on the closed Congressional meetings that have been held recently in Washington, D.C.?</strong><br />
 <br />
To his credit Congressman Billy Tauzin (R-LA), joined by Congressman Fred Upton (R-MI), John Dingell (R-MI)., and Ed Markey (D-MA), and Cliff Stearns (R-FL)--the five most important people in the communications arena--have been having round table discussions with leading members of the industry. They have asked a series of questions like,"What's the problem? How can we solve those problems?" </p>

<p>They are not brow-beating anyone. They are constructive in the extreme. These guys should get all the credit in the world for showing the interest they have. At the last meeting they sent for FCC Chairman Michael Powell. The Chairman heard two hours of questions and all of the discussion. To his credit he brought along Rick Chesson (who heads the FCC DTV Task Force.) You get the feeling that government, be that the Commission, the Commerce Department, and Congress all want this transition to get going.</p>

<p><strong>What are the current goals of these Congressional meetings?</strong></p>

<p>Clearly to find out what the problems are. Once those are identified they are then rying to encourage private sector solutions, or, as Rep. Tauzin has publicly put it, "If they can't get a private sector agreement, Congress will consider if some kind of legislation will be necessary."</p>

<p>The point is, Dale, that we have committed the country to a digital television future.</p>

<p><strong>Can we raise this (transition) to the level of a national agenda, formal or not?</strong><br />
 <br />
I don't think it needs be quite so dramatic an action but I do think it has got to move. We have had five years with stops and starts. We had the big concern over progressive and interlace scanning. That is gone. We had the big concern about COFDM and 8-VSB. That is gone. The industry is together. There doesn't seem to be any technical or philosophical dispute. It is only a question of getting these remaining issues, which everyone agrees need to be resolved, to actually get done. I think the FCC is committed (to that). I commend Chairman Powell for his leadership. I think the Congress is committed to it. I praise them. I am more encouraged now than I have been in a long time. I can be accused of being an optimist in these things. You can read a lot of negatives such as, "There is no market there. It is not going to happen." You have seen it all.</p>

<p> <strong>I don't see how that point-of-view can prevail with any kind of correct investigation. </strong><br />
 <br />
It is going to happen! Who we kidding? We are not going back to analog. You have these 225 stations on the air. They may be doing upconversions now, but they are out there. They put a lot of money into it. All of these people who are now producing programming and the public is beginning to see it. It is going to take off. </p>

<p>Then there is a payoff down the road which we have not talked about yet. The tie-in of the Internet and the TV set--the tremendous profusion of data services that will be out there...</p>

<p>I am a unreconstructed HDTV fan, but I want to say that data will be a killer app of the future as well. </p>

<p><strong>As well? But not in the replacement of HD?</strong><br />
 <br />
The thing that moves the market is high-definition. It is something the public has not seen before in their living room. Everyone I show my 64 inch HD set want to get it.</p>

<p>Then they ask, "How much of this programming is there?" You have to say, "well, CBS has done a heck-of-a job of giving us sports every weekend until Christmas." We need to do that at the other networks as well. I certainly don't make the business plans for them but they have to step up to the plate. I was pleased to see NBC announce the Olympics in conjunction with HDNet. I would like to see Fox do the SuperBowl in HDTV and not just in 480 digital. That (480P) is good, but not good enough, frankly. The 720P, if one wants progressive scanning for sports, is what they have to do. </p>

<p><strong>How can we effectively influence Fox in this direction?</strong><br />
 <br />
Visibility is one means. The Congress understanding what is being done and what is not being done (will help). Rick Chesson (at the FCC) is spending full time on DTV. He is a very competent staff member. This is his full time job due to Chairman Powell's leadership. He has the resources, like Dr. Robert Pepper, Bruce Fransa, and Amy Nathan and a lot of other veteran FCC staff members who understand these issues. They are meeting with the industry and asking why these issues are not resolved.</p>

<p>I will not be naive. These issues are not going to be resolved tomorrow. But if we are in the same place a year from now you are going to see some legislation. Those people (government) are determined to make this happen. Anyone who thinks it will be drifting like it has been will be dissuaded of that notion.</p>

<p><strong>What about the White House? What should they or can they do to aid this transition?</strong><br />
 <br />
I don't think it is the White House's issue right now. But I do think it's chosen instrument--the FCC--has the message. I have been over to the Commerce Department. They have the message. The State Department also has the message. So, we have executive branch agencies that are working in harmony with the FCC to find solutions. The bottom line is this. It is not a government problem. It is a private sector problem. The government has set up the frame work. The private sector has to step up.</p>

<p><strong>What can the private sector do of its own? Is it promotional?</strong></p>

<p>The latter is one thing we have not yet talked about. We need, of course, to solve the cable compatibility and copy protection issues, but we may also see this promotional campaign from the combined CEA/NAB effort. No one knows how to promote things better than the broadcast and cable operators and Hollywood. Yet nobody really promotes it. I can't pick up the evening paper and see a star (or other) indicating that this or that program is in HDTV. There is nothing really capturing the public's imagination and telling them that something better is out there for them.</p>

<p><strong>Perhaps you and I should go on a speaking tour.</strong></p>

<p>I speak at weddings, bar mitzvahs, and even funerals to tell the tale! Having put ten years of my life pro bono into it I think I have earned the right to be called a national cheer leader.</p>

<p>I read your publication every day. It is always cheery reminder of what is going on. Even though I say there needs to be more, there is more programming than people think there is. Your publication shows that. </p>

<p><strong>Yes, and when supplemented by DVD the investment is well worth while.</strong></p>

<p>DVD's are fine. I think DVD is a transitional product because it is not as good as the real thing-HDTV. But yes, why not see movies in a better format? What's wrong with that? They are very cheap now. It shows what the DTV sets are going to be. It is the genius of the free enterprise system. We have had a lot of people sell this whole thing short, but it is going to happen.</p>

<p><strong>It appears that Hollywood's biggest possible future is in the home and with HDTV.</strong> </p>

<p>Yes, and you know the digital cinema is coming too. There is no reason why HDTV cannot come to the motion picture theater. But I think you are right. Hollywood is going to get so much more bang for their buck from the fact that their movies are being seen as they were produced--brilliant widescreen 35mm cinematography--which is the HDTV equivalent. </p>

<p>Advertiser will wake up pretty soon. I can't agree with people who say that there is no more money in advertising. The (SDTV) ads look pretty puny when you are watching something like the widescreen SuperBowl. One minute you are watching the wonderful widescreen programming and then you go back to narrow screen in conventional TV. Pretty soon those advertisers are going to say, "Let's make those commercials in HDTV too." </p>

<p>This is the reverse of Gresham's Law. The good is going to drive out the bad (or even the fairly good). Television is awfully good as it is today, but it is not as good as HDTV.</p>

<p>I am not opposed to standard definition either. If broadcasters can figure out, likely at the local level, how to program additional SDTV programs without undermining their major signal, fine. </p>

<p>While there is some of that now, the name-of-the-game today is HDTV. I thought it was in 1989. I think it is today in 2001. You and I will be proved right ultimately. We have had a lot of nay-sayers who think we are crazy. You bring the public into my living room and then tell me they are crazy. The people who see it say, "I would like to have one of those."</p>

<p><strong>Thank you Dick.</strong><br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>June 24, 2005 02:00 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 113
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
 				AND entry_id <> 113
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_2001_-_richard_wiley_chairman_of_acats.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
