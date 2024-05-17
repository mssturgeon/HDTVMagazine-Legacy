<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 104";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 104 AND placement_is_primary = 1";
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
	<meta name="keywords" content="being digital, aspect ratio, open architecture, point view, media lab, going, digital, think, people, want, system, those, see, television, right, say, because, time, years, consumer, could, technology, might, things, why" />
	<meta name="description" content="Nicholas Negraponte is the author of Being Digital. He is a founder and the director of the Massachusetts Institute of Technology's uniquely innovative Media Laboratory. The Media Lab is an interdisciplinary, multi million dollar research center of unparalled intellectual and..." />
	<title>HDTV Magazine Interviews - INTERVIEW - Nicholas Negraponte. Media Lab (1994)</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/interview_-_nicholas_negraponte_media_lab_1994';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('INTERVIEW - Nicholas Negraponte. Media Lab (1994)'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_nicholas_negraponte_media_lab_1994.php";
		if ($author[img] != '' && 4 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">INTERVIEW - Nicholas Negraponte. Media Lab (1994)</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>June 20, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_nicholas_negraponte_media_lab_1994.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_nicholas_negraponte_media_lab_1994.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_nicholas_negraponte_media_lab_1994.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_nicholas_negraponte_media_lab_1994.php&amp;phase=2&amp;title=INTERVIEW%20-%20Nicholas%20Negraponte.%20Media%20Lab%20%281994%29&amp;bodytext=Nicholas%20Negraponte%20is%20the%20author%20of%20Being%20Digital.%20He%20is%20a%20founder%20and%20the%20director%20of%20the%20Massachusetts%20Institute%20of%20Technology%27s%20uniquely%20innovative%20Media%20Laboratory.%20The%20Media%20Lab%20is%20an%20interdisciplinary%2C%20multi%20million%20dollar%20research%20center%20of%20unparalled%20intellectual%20and...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><em>Nicholas Negraponte is the author of <u>Being Digital</u>. He is a founder and the director of the Massachusetts Institute of Technology's uniquely innovative Media Laboratory. The Media Lab is an interdisciplinary, multi million dollar research center of unparalled intellectual and technological resources. It is focused exclusively on study and experimentation with future forms of human communication, from entertainment to education. Programs include: Television of Tomorrow, School of the Future, Information and Entertainment Systems, and Holography. Media Lab research is supported by Federal contracts as well as by more than seventy-five corporations worldwide. Negroponte is also co-founder and back-page columnist for Wired magazine.</p>

<p>Negraponte studied at MIT, where as a graduate student he specialized in the then-new field of computer aided design. He joined the Institute's faculty in 1966, and for several years thereafter divided his teaching time between MIT and visiting professorships at Yale, Michigan, and the University of California at Berkeley.</p>

<p>In 1968 he also founded MIT's pioneering Architecture Machine Group, a combination lab and think tank responsible for many radically new approaches to the human-computer interface. In 1980, he served a term as founding chairman of the International Federation of Information Processing Societies' Computers in Everyday Life program. Two years later, Negroponte accepted the French government's invitation to become the first executive director of the Paris-based World Center for Personal Computation and Human Development, an experimental project originally designed to explore computer technology's potential for enhancing primary education in underdeveloped countries.</em></p>

<p>INTERVIEWED in 1994 by Dale Cripps<br />
<strong><em>HDTV Newsletter</em></strong></p>

<p>In re-reading this interview today it became clear to me how far ahead of many of the television and motion picture strategist Nicholas Negraponte had grown. His ideas, once thought radical, can be seen today throughout the entire communications landscape. At the time of this interview the HDTV standards setting work was under a great deal of pressure to once again change itself into something else, this time all-digital. That move to all-digital by all of the proponents of HDTV, sparked by General Instrument's demonstration, had not yet occured. Mr. Negroponte's interview was not entirely welcome by most of those working on HDTV standards here and abroad as he devalued in a sentance or two everything analog. He speaks of both the European HD-MAC system (an analog satellite transmission system once destined for Europe) and MUSE (an analog satellite transmission system once destined for Japan) as being dead. He was right. I think some of his ideas are just now coming into focus for many in telephone and cable. HIs comments with respect to how motion pictures will be distributed reads like today's headlines. I hope you enjoy this rare interview with a true visionary. _Dale Cripps<br />
  <br />
<strong>Dale Cripps:</strong> Nicholas, you had been drawing the circles of print, video and computer closer together. In the early days there was a great deal of skepticism among the large manufacturers (about your vision). I see that skepticism subsiding rapidly. Where are we on the closing of these circles? (Negroponte was fond of drawing three circles that overlapped on their way to converging.)</p>

<p><strong>Negraponte:</strong> All you need to do is look at acquisitions and mergers over the past couple of years. It doesn't take much effort to see that people are beginning to realize that you want to be in more than one medium. Whether this is the right reason to be converging or not you have people like Newscorp and others who are getting into all three arenas. It is not often known but Newscorp owns eTech, the computer mapping data base company. From that perspective they are certainly coming together. This is one of several but a reasonable bellwether because those decisions are business decisions not just technology predictions. </p>

<p><strong>In that they are business decisions they must have some technology support. What areas of that do you see completed and what are need to be completed in short and long term?</strong></p>

<p>What is bringing them together is very simple. Everything is going digital. That is such a banal comment today but I remember as recently as two years ago I would say things like "the future of television is digital and people like Bill Shreiber and Jae Lim would say the opposite. In fact Bill is still reticent about going fully digital. Jae Lim, as recently as a year and one half ago in the New York times was quoted as saying he didn't think it would work. Since then everyone has changed. I am much happier to see people change then be stubborn. </p>

<p>The Japanese, according to my intelligence are really at the edge of abandoning Hi-Vision. The Europeans HD-MAC dead in the water too. They are all going digital.</p>

<p><strong>Let's explore that further. I recently returned from Japan and there are complaints from those not invested in MUSE. Yet, Mori Morizono of Sony is skeptical that the digital system will work to meet the business requirements of the U.S. broadcasters. </strong></p>

<p>I am under a lot of non-disclosure agreements and I think I would be violating them if I say why he is saying that. The truth is that a lot of companies in Japan, especially Sony, have more or less concluded that it is better to switch to digital sooner rather than later. What they can or cannot say is very complicated. I am not trying to jeopardize our friends at Sony who are really caught between a rock and a hard place.</p>

<p><strong>You say that HD MAC is dead?</strong></p>

<p>Yes. It will hobble through Barcelona (the Olympics) and then pack up and go away. There is just no question about that.</p>

<p><strong>Do you think that what we are doing here in this country in testing systems and validating them will give to those regions of the world a system that they can adopt?</strong></p>

<p>No. What we are doing in this country is very, very important because it will change the face of history and basically bring digital techniques to everyone's attention -- prove they are possible. But I don't believe any of the systems in front of the FCC would be adopted by us or anyone else. </p>

<p>My bet, if you ask me to say what will happen, is that MPEG II, if done right, will turn out to be the de facto television standard of the world. The reason for that is not just because I think if they did it right I would be enthusiastic. I am saying it for another reason. That is very often when we criticize Hi-Vision MUSE and HD MAC, both of which are extraordinary vulnerable to technical criticism, we forget that the biggest criticism is the one we never mention, or, at least not as openly, is that it is Japanese. HD-MAC is European. If one of the "contestants" were to be selected, which I don't believe will happen-, it would per force be American. Television has historically been a very nationalistic phenomenon. The French have still not recovered from the fact that SECAM was not accepted and that 825 line television was not accepted. </p>

<p>MPEG II has this wonderful attribute of being A) International; and B) the work of some 75 or so companies from all over the world from many different disciplines--computer, television, etc., etc. It could be adopted with pride. People could adopt it without sort of agonizing whether it was Japanese or European. I am more interested in seeing the three regions of the world--Japan, Europe, and the U.S.--I think it is very important that they do come together. MPEG II is the only hope I see at the moment. </p>

<p><strong>One of the proponents uses, of course, MPEG++. This is an international company made up of Europeans and Americans. Is that enough?</strong></p>

<p>Well, those people are right here at the Media Lab as we speak today and we are spending the entire day with them. I was delighted when I saw them use the name MPEG ++. I have not looked at the details. I think the consortium is a very interesting one. Again, there is going to be many political forces to not select them for nationalistic reasons. AT&T and General Instruments, with or without MIT, are by definition in a stronger position. But MPEG++ may be a step in the right direction. I want to underline that it is a <em>maybe </em>because I don't know the details.</p>

<p>What I do know is that all of the submissions to the FCC are extraordinarily handicapped because they all started off with this unfortunate emphasis on high-definition. </p>

<p><strong>Where should the emphasis have been placed?</strong></p>

<p>Being digital.</p>

<p><strong>At any line rate?</strong></p>

<p>At variable line rates. If I made a list for you of all the advantages of being digital it would be a list that would have a dozen advantages written on it and high-definition would be very nearly at the bottom of that list. </p>

<p><strong>Is high-definition, in fact, an important evolution of television or is it more overkill?</strong></p>

<p>It is absolutely unimportant. Really not important by comparison to being digital. I can't tell you how strongly I believe that. </p>

<p>We are talking about the ability to embed digital information which isn't displayed. That may be indexing what is coming -- control characters for peripherals -- doing all sorts of things to the signal. The signal may be carrying the algorithm to decode it. There is just a world of things you can do with digital video and annotations you can make to the signal are extraordinary. </p>

<p><strong>Let us paint a vision of the world in ten years. </strong></p>

<p>Let's break it into four constituencies--equipment, broadcasters, program makers, and the consumers. One has to look at all of them, which has not been the case up to now. Primarily we have looked at the equipment manufacturers. Quite frankly, the broadcasters are very unenthusiastic and I think rightfully so. </p>

<p>I think from the equipment manufacturer's point-of-view the opportunity to build with what Bill Shreiber coined as "open architecture." Even while everyone bellyaches about it I think that open architecture, from the manufacturer's point of view, will be a very, very interesting market. Then you can start innovating with some peripheral, accelerators, and things you can do - upgrading incrementally and letting people buy in over time and "grow" their system.</p>

<p>The broadcaster's as soon as they are in a digital world are in what I call the "bit radiation" business. All of a sudden what those bits represent is so flexible and so variable... Let me give you a specific example:</p>

<p>The FCC is reviewing 20 Mb/s solutions right now. Let's say they select one and give you as a broadcaster a 20 Mb/s license. What are you going to do with that license? You are not going to broadcast HDTV. You are going to broadcast 3 or 4 channels of NTSC. Then if you are clever you are going to broadcast 3 channels of NTSC, one radio program, two pagers, digital newspaper and some other unknown data broadcast service. Then all of a sudden on a Saturday afternoon in your local area there is an important college football game you might devote 8 Mb/s to the football game, discontinue one of your NTSC channels (may be not run the newspaper). Then in the middle of the night you might be broadcasting 6 or 7 newspapers. In other words all of a sudden you with your 20 Mb/s space be your own micro FCC allocating your spectrum as you see fit. From the broadcaster's point of view that is really very, very interesting business opportunities. </p>

<p><strong>Channels from time-to-time are moved and arouse viewer distress. Their program is not where it once was. Are we not apt to run into this same sort of thing with flexible use of spectrum?</strong></p>

<p>In order to be that flexible the receiver has to be designed accordingly. It won't happen overnight. The receiver just has to attend to that. The signal carries with it again in some of those non-visible bits the kind of information that automatically attends to that. </p>

<p>From the programmers point-of-view I think it will be very exciting in terms of the fact that you will be able to download programs to receivers. The people making programming are still divided. This is where the circles are most divided today. TV people and print people overtly keep them separated.</p>

<p><strong>Why are they doing that?</strong></p>

<p>The belief that they are sufficiently different media that reporting in one versus the other doesn't have a cross over effect. I think that is not true and it will take awhile to prove itself. </p>

<p><strong>Is this the same stance that print saw in radio broadcasting?</strong></p>

<p>Exactly. I was driving a rented car the other day and turned on the radio to whatever channel I was tuned to. I was picking up the sound track to CNN television on the radio. It is a small example, but it is already happening. </p>

<p>When you think of television as downloading then something like the 6 O'clock news, especially if you are using cable or fiber systems, you start compressing the signal so you can deliver one hour video in about 5 seconds loaded into your receiver and you start randomly accessing it in very, very simple ways. You do some of the classic "tell me more" techniques.</p>

<p><strong>Will this be down loaded to disc, tape, and solid state..? </strong></p>

<p>It could be any. Again, in an open architecture we might find that big magnetics or magneto optics as a way one might go for the next five years and before the turn of the century it will certainly be solid state memory.</p>

<p><strong>Are going to download movies?</strong></p>

<p>Absolutely. Whether you download them or "trickle charge" them using very low bandwidth or whether you see them in real time - all three options are going to exist. When the phone company provides the one way 1.5 Mb/s line into your home, then you will be looking at it in real time. If you are using a lower bandwidth you have to "trickle charge" it. If you are using high bandwidth you will "burst" it in there. </p>

<p><strong>Do you forecast the day when the consumer will be charged by the bit?</strong></p>

<p>That is a good point. Yes, it will be pay-per-view per bit. If you are looking at a 10,000 line flat panel display ten feet high in your living room with friends looking at a football game in the afternoon and I am looking it on my kitchen counter on an 8 inch diagonal I suspect I will pay $.30 and you will pay $3.00. </p>

<p><strong>If I were a programmer and distributor today should I be looking for ways to be sending the highest quality?</strong></p>

<p>That depends on the program and it is, again, a business decision. How much the channel is being used for other things, etc. But if your channel capacity wasn't a variable of any significance then yes, you would want to broadcast the highest quality and then let people take any piece of it they want. </p>

<p><strong>That provokes an image of a stratum of signal providers. Some might be local with lower origination while others, perhaps the existing networks of today, passing through the highest bit rate rates or quality. </strong></p>

<p>Exactly. As long as it is the same salable architecture that is used to represent the signal it all just makes so much sense.</p>

<p><strong>You mention the telephone company. Do you think they will end up with the rights for creating content?</strong></p>

<p>I hope so. I believe they will for sure be in the delivery business. I am a great advocate of that because I think it should be switching phenomena. I really believe that each receiver should be able to receive separate signals throughout the entire nation so all 150 million sets could have a 150 million different TV programs running at any one point in time. It might be the some program in some cases, like the news just being run offset at funny times. It might be people accessing different movie data bases. It could be downloading and people looking at different sub-sets of the evening news. It is one you do by switching and not by loops and not by trying to run a fiber system through Queens, New York that has a 150 channels on it. I don't want a 150 channels. Nobody wants 150 channels. We want one channel. It just happens to be that we want the one channel we want at the time. The way you get it is not to select one out of 150 channels but have a system that allows you to specify your channel.  </p>

<p><strong>In effect we are program picking anytime we see fit?</strong><br />
Absolutely. </p>

<p>I was told by various Hollywood studios that a few months ago surveys were being run by computer companies with the view of digitizing all the studios' film vaults. These vaults would have access from all delivery services as well as consumers from this master center and be sold on some transactional basis.</p>

<p>Even more important in those systems is that you can use the same system to look at trailers to help you decide what you want. One of the killers when you go to the video rental store is that you can never find what you want. I often walk out with something I don't want to watch or have already seen. If I can call up some trailers and see them postage stamp size on the screen--wondering through a data base--I can make an informed choice. </p>

<p>We have moved in to point four--the consumer benefit. That is an enormous benefit. The selection process is normally not discussed. It is the delivery process that gets discussed. </p>

<p>A lot of this business of advanced television has been driven by the idea of what the consumer might want. But with a few experiments of late where in Europe the Space wide-screen (625 line) system is marketed and in Japan with the MUSE HDTV there has not been a tremendous interest. </p>

<p>I think wide aspect ratio is a bazaar subject. I listen to people like Jae Lim (MIT) who say the only thing that everyone agrees on is wide aspect ratio of 16:9. I have to wonder if that is not just another red herring. It should be a variable aspect ratio. It is unclear to me if I want to see curtains on the right and left for 50% of my programming or I want to see letterboxes on 50% of my programming.</p>

<p><strong>Aspect ratio was thought to be the differentiating thing even with the set turned off. I didn't see it too differentiated in Europe or Japan with the set on.</strong></p>

<p>Right. It differentiates best with the set off. I like letterboxes because it makes for a nice crisp horizontal line at top and bottom if you have a good receiver.</p>

<p><strong>What are the other consumer benefits? Programming has to lead technology. What are we apt to create for the consumer in "being digital" in an understandable "sound bite" that he is able to quickly comprehend and say, "yes, this is what I have to have"?</strong></p>

<p>There are some simple ones. Any consumer that is told, "listen, you can have movies on demand." There are 50,000 movies (not counting those from India and Hong Kong" and "you can have anyone you want for your $3 or $4 and it will help you select which one. That is one way and is passive and doesn't do much. The sound bites get a little bit longer and harder. Those consumers aren't really into the transaction side of computing in the home. Most of the big payoffs will start in more of the transaction oriented information providing. For example. The Yellow pages are used and they include not only the opportunity to consummate a transaction but they also get personalized. Low and behold on your screen you see the driving structure from your house to this business place because the program provider like Newscorp owns the data base that can automatically show you how you can get there.</p>

<p><strong>That suggests a print out device as well. </strong></p>

<p>Absolutely. Hard copy will be a very big piece of it. A lot of consumers if you told them this was the medium that if when using the open architecture concept with a few dollars will provide you the way to edit all these 8mm video cassettes you have in a shoeboxes... I mean there are all sorts of things that start emerging. </p>

<p><strong>How would the editing work?</strong></p>

<p>I can't for the life of me figure out why no one has come out with a simple two cassette editing system for the consumer. I can't believe that everyone is so asleep at the wheel on that one. But, it is going to happen sooner or later and consumers really want it. We have several people here at the lab working on prototypes of that sort. But I have got to believe industry is also doing it. </p>

<p><strong>I still haven't heard that one strong sound byte that sells the consumer the moment it is heard.</strong></p>

<p>It doesn't work that way in the consumer world. Take the fax machine. There is not a person who could live without one once they have it. Yet most people five years ago had not even heard of it. Consumer marketplace doesn't quite behave that way. It took audio CDs 4 or 5 years to get off the ground. Now they don't have vinyl anymore.</p>

<p><strong>But that was a crystalline benefit. Everyone said "oh I get it, audio is improving and it is more convenient. Do you see any opportunity for some all embracing term. Is there is phrase or ketch word?</strong></p>

<p>I use the term "personalized television", which sounds like an oxymoron right now with everyone thinking that television is the supreme mass medium and definitely not personalizable. But that is a sound bite that isn't going to make the market pick up and pay attention as quickly as some manufacturers would like. But I think that the personalization of television is the general umbrella. </p>

<p><strong>Do you think that business plans such as SkyPix are viable?</strong></p>

<p>Do I believe that direct broadcast satellite for applications like that one are going to make it; my answer is yes. But their life-span will be ten to fifteen years. It is interim technology.</p>

<p><strong>Is digital the last technology we are going to have to grapple with? Have we reached a threshold where the fundamental technology upon which we have for building is final?</strong></p>

<p>Once you are in the digital it says more about representation than about future invention. One of the very long term - ten to twenty years - opportunities is the machines ability to understand the information and filter it and sift through so as not to provide bandwith expansion of information but bandwidth compression for you, the overloaded.</p>

<p>It starts to look at TV for you and starts reducing some of this massive amounts of information as humans do today if you have a secretary or friends who look at things and tell you about them. </p>

<p>So, it becomes a large filter with your aims and interest in tact and all others discarded. </p>

<p><strong>You have talked about the newspaper of the future. One can scan efficiently the Wall Street Journal in twenty minutes a day. I can't scroll that fast. </strong></p>

<p>Right. What you are doing with your eyeballs personalizing the WSJ by reading headlines. We are looking at most 5% or less of the information. You are right that today's newspaper "technology" lends itself to personalization by virtue of your human perceptual system. That is one way of reading the newspaper. It is what happens to me frequently. People cut things out and paste them together and send me a little piece of email. This is another form of personalization. Quite frankly those stories are very interesting. I get a weekly publication from Japan that way and when I travel I get all sorts of clips that way and they put them together. Those "personalized newsletter" are like the WSJ that I do read from cover to cover because some human has done some very good filtering. So, those are the two alternatives. It is not that we can make the scrolling function as good as the eyeball function - I don't think it will ever be.</p>

<p><strong>You are suggesting some form of template?</strong></p>

<p>I am not suggesting that in the sense of a list of interests, etc. I am talking about the sort of thing that a good secretary does. My calendar is one of the most revealing pieces of changing information about me -- who I will see tomorrow and who I saw yesterday. Just looking at something as specific as someone's diary or schedule is very dynamic form of personalization as well as the fact, yes, I would be interested that my first cousin, who I have not seen in a long time, has just published a book on something or other that is reviewed in some remote journal.</p>

<p><strong>There has been in some circles a clear resistance to Negroponte. Some will stand up in meetings, some in private discussion where your name creates come agitation. Those involved with the early days of HDTV felt they suffered from the fact that your ideas were, perhaps, strong enough to be carried forward as ideas, but not strong enough to be realized while at the same time dissembling their activities.</strong></p>

<p>That may be true. But since the whole world has gone digital all I can say to those people is, "I told you so." Now that everyone agrees it should be digital my next hobby horse is that it be scalable. There is a great deal of resistance to that.</p>

<p><strong>Why?</strong></p>

<p>I am not sure why. I was never sure why there was so much resistance to digital except that people had vested interest. I think scalability is the same thing. All of these people who have submissions to the FCC and other places are really expecting people to trash their HDTV systems and start all over again when we have 2000 and 3000 line displays. It is mind boggling. </p>

<p><strong>Do you think 2000 line displays are every going to be in demand? One could logically believe that 1000 lines are enough forever.</strong></p>

<p>Let's recall where the 1000 lines came from. It came from CRT technology and nothing else. There were no studies what-so-ever on what resolution people would want on screen size, or anything. It was basically a random number based on what people felt they could do economically with CRTs.</p>

<p><strong>That isn't the way I read that story. I see that the work done by NHK and the seminal work by Bill Glenn determined that you couldn't improve visual sharpness at three times the height of the picture.</strong> </p>

<p>You could argue that if I am going to stay ten feet away from the white board I have on my wall, 2000 lines is fine. But if I am going to go right up to it and interact with specific parts... I need to think of television as a lines per inch medium, not a lines per picture height media. </p>

<p><strong>As we do with computer screens?</strong></p>

<p>Exactly. If you are really a hard core broadcaster and believe that people are going to sit on couches looking at tubes, then you could argue that some number - and I don't think it is 1000 - may be the maximum for the so called 30 degree experience. I think that is real old fashioned thinking. It is not the right way to go.</p>

<p><strong>When people here you say things like this they say, "what he is trying to do is delay us and we don't know why he is trying to delay us. What we need to do is to take this $5 billion investment we have and produce some revenue with it so we can go on. Why doesn't that man just shut up and let us go on with the technology we have available now to the public." </strong></p>

<p>Two answers. Needless to say I have no vested interest in having a company loose money or try to delay something that would be in the public interest, or anything like that. The first answer by analogy... do you remember SelectaVision, the RCA capacitive disc? I remember Mr. Griffiths, the CEO of RCA at that time. That disc was coming out at then. Public occasion after public occasion I said it was just absolutely the wrong thing to be doing and that it was a dead technology and one had to look to optics and not these capacitive discs. RCA were strong with me. They said, "Why are you sabotaging this? We spent $50 million developing this system and we have got to bring it to market to recuperate our costs." I said to one of them if you bring it to market you are going to loose ten times your investment. Low and behold when they pulled it off the market they had lost $500 million. The same thing is going to happen with Hi-Vision and HD-MAC, if they do it. So when people are asking why I am delaying them they should be very thankful because if they do bring that stuff to market they are going to loose billions. It is just not going to fly.</p>

<p>That is one answer. The other is: let's pretend I am wrong and it is going to fly. It is going to fly at the expense of the consumer. If Philips or Thomson, for example, says we have invested so much in this analog system and we want to bring it to the market to recoup our costs, by definition that has to delay digital introduction because they need a window in order to recuperate their costs. These broadcasters all have to tool up. The consumers have to buy things. It is not only going to delay, but it is going to be in the worst interest of the consumer. Nobody is on the consumers ‘side. I think the window of opportunity for analog television has closed. </p>

<p><strong>Some might say that there are people right now who might say, "I will not live long enough to see a digital solution and would appreciate HDTV now even if soon faced with a obsolescence. </strong></p>

<p>The reason is because you will have digital on the market in less than three years. We are not talking about an enormous delay. At this point in history it will take just as much time to introduce HD-MAC in its analog form as it will to introduce a proper digital system. </p>

<p><strong>That is interesting and provocative because the Europeans have said that even if a perfect solution were to be had it takes ten years to reach pan European agreements.</strong> </p>

<p>That is because they have been looking at it not as an industrial standard but as a political arena. I think MPEG I is a disaster, but note it only took about 12 months. MPEG II is going to be decided upon before the end of this year and could be going into manufacturing. You could see chips and sets come out within less than three years. You ask about SkyPix. Hughes has just signed an enormous deal with Thomson to provide digital receivers for their direct broadcast satellites. Cable Labs has an RFP out for a digital system and will be in the home in less than 24 months. Indeed, when it gets there it will be converted to analog to fit the current receiver. It is moving fast. </p>

<p><strong>So, your recommendation is very clear. But there is also a perceived window of marketing opportunity from the publicity generated the last 6 years that cannot be wasted. Can they allow that to pass?</strong></p>

<p>It is harder for governments to cut their losses than companies. It turns out that in this case fate will play its hand perfectly. One can just sit back now because there is no way in Hell that Hi-Vision or HD-MAC is going succeed. So we can focus now in doing it properly and the Europeans know that. </p>

<p><strong>I believe we are on the threshold of global broadcasting. Can we get a global system?</strong></p>

<p>Absolutely. That is one of the whole reasons to go digital. It can be scan line independent, frame rate independent. and aspect ratio independent. All of those things will make the programmers absolutely delighted. That is exactly where we are going and I don't think we are going to miss the target. We are going to finally make it. </p>

<p>In the digital world standards take on a very different nature. You can think about them more on what I call meta-standards. We can agree on basically how we will transmit encoding algorithms so the receiver is somewhat standards independent. It says, oh, that is one of the Italian programs and it decodes it. If you don't have the software to do that you might have to go to your Radio Shack and but a little IC card or diskette that loads that in. If I am a program manufacturer I might even broadcast in some weird standard and force you to go and buy that decoder as the way I make the income from my program. I can use this in very, very creative ways. </p>

<p>I think we have to start letting the machine do the decoding and transcoding of these systems much more automatically. </p>

<p><strong>Is this a programmable CPU? </strong></p>

<p>Always, say the TV set makers, you are adding a burden of cost to make this flexibility universal. The consumer may use but a fraction of these features and therefore, the argument goes, why should everyone bear the burden?</p>

<p>It is just inaccurate. If you are talking about somebody buying a vanilla open architecture receiver that does nothing more or less what they are doing today. The chance that you are adding cost is very real. However, I now give you a "pause" feature with a little card you can slip in. As soon as the phone rings I push the pause button it starts recording and when I return it continues from where I left off. It is a shift device to allow me not to miss something. I also have now an electronic still camera or a VCR editing system - another option. I can now print out hard copies. When you start adding, and not too many functions, the cost of adding that function will be so much less than if you had to do it with a whole other box. Right now in my home I have sitting side by side a laser printer, a copier, and a fax machine. Those are very expensive. If I told you I can sell you a device that does all three functions that device probably will cost more than any one of those three sitting there, but certainly less than the cost of the three. So, the cost arguments are gratuitous. They are not accurate.</p>

<p>Thank you Mr. Negroponte.</p>

<p></p>

<p><br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>June 20, 2005 09:09 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 104
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
 				AND entry_id <> 104
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_nicholas_negraponte_media_lab_1994.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
