<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 173";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 173 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# Set variables based on entry type. NOTE: Podcasts has its own entry template, so it is not included amongst the choices below.
	switch (5) {
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
	<meta name="keywords" content="copy protection, pay price, hdtv programming, hdtv today, high value, HDTV, hdtv, our, market, new, any, still, even, upon, protection, good, world, being, copy, going, doing, everyone, last, make, everything" />
	<meta name="description" content="Last time on these pages I said HDTV was a love affair between a box of lights and wires and we humans. Replacing NTSC television with a completely incompatible HDTV standard is like changing the side of the road upon which we drive for the sake of the view. This nation is doing that at a cost of hundreds of billions of dollars. Not before a leisurely evening of enjoyment with HDTV in their own home do people know why this transition is fully under way, and why it is needed.
" />
	<title>HDTV Magazine Archive &amp; History - 2001 - Thoughts on HDTV</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/2001_-_thoughts_on_hdtv';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('2001 - Thoughts on HDTV'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/history/2005/08/2001_-_thoughts_on_hdtv.php";
		if ($author[img] != '' && 5 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">2001 - Thoughts on HDTV</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>August  4, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/08/2001_-_thoughts_on_hdtv.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/history/2005/08/2001_-_thoughts_on_hdtv.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/history/2005/08/2001_-_thoughts_on_hdtv.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/08/2001_-_thoughts_on_hdtv.php&amp;phase=2&amp;title=2001%20-%20Thoughts%20on%20HDTV&amp;bodytext=Last%20time%20on%20these%20pages%20I%20said%20HDTV%20was%20a%20love%20affair%20between%20a%20box%20of%20lights%20and%20wires%20and%20we%20humans.%20Replacing%20NTSC%20television%20with%20a%20completely%20incompatible%20HDTV%20standard%20is%20like%20changing%20the%20side%20of%20the%20road%20upon%20which%20we%20drive%20for%20the%20sake%20of%20the%20view.%20This%20nation%20is%20doing%20that%20at%20a%20cost%20of%20hundreds%20of%20billions%20of%20dollars.%20Not%20before%20a%20leisurely%20evening%20of%20enjoyment%20with%20HDTV%20in%20their%20own%20home%20do%20people%20know%20why%20this%20transition%20is%20fully%20under%20way%2C%20and%20why%20it%20is%20needed.%0A&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><em>Take the time to read this thought provoking article. It gained a large following on the forums shortly after being posted in our daily outbound HDTV Magazine. Its message is contemporary, even timeless.</em></p>

<p>__________________________________</p>

<p>Last time on these pages I said HDTV was a love affair between a box of lights and wires and we humans. Replacing NTSC television with a completely incompatible HDTV standard is like changing the side of the road upon which we drive for the sake of the view. This nation is doing that at a cost of hundreds of billions of dollars. Not before a leisurely evening of enjoyment with HDTV in their own home do people know why this transition is fully under way, and why it is needed.</p>

<p>Most executives from broadcast, cable, satellite, programming, manufacturing, and especially Congress still don't own or have use of a HDTV set in their home. As a result they wrestle with issues before them using theory and logic rather than an impassioned understanding that defies words. Indeed, the progress of HDTV, now in its third year of commercialization, has not been as brisk as its core of supporters had hoped. It has been given the amber light in most press reports, some even the red light. That has released naysayers to gloat about being right about their dire predictions of HDTV's ultimate casualty.</p>

<p>But HDTV is not a market failure. The latest figures drawn from the Consumer Electronics Association this last week show sales of DTV products for this May rose 110% over the same period last year. It is a defiant survivor who has withstood all the forces of Hell unleashed against it (daily for the last 20 years in which I have covered the story). Ghastly and discouraging retail demonstrations have done the business no good and giving it the duty of paying off the national debt has decidedly added a burden upon its broad shoulders. True, no part of the television market is enjoying lush profits from HDTV yet, though manufacturers are doing well enough..sort of. I just laid down today's Wall Street Journal after I read of the deep losses within the Japanese electronics manufacturers. Is that good news? I don't think so. With every failure in Japan you will loose something from the HDTV world. I am not saying it will not be replaced by emerging countries, like China, but what's the point? I was told by the head of the NHK Laboratories at this year's National Association of Broadcasters convention in Las Vegas, who was traveling with an an old friend of mine, Morio Kumabe, who for ten years headed the HDTV broadcast services from satellite under the flag of the High-Definition Promotion Association--a group of about 100 major Japanese companies first brought together by the former head of Mitsui. Kumabe said that a "huge success" for HDTV in this country would save Japan. I had heard from colleagues of mine earlier that these Japanese companies were in deep trouble and this WWJ article today rather confrims it. If Japan comes to believe that we have any kind of value in the marketing of their salvation--a sucessful market for HDTV--what can't we ask of them to help us? It would seem to me it is becoming in everyone's best interest to capitalize on the demand that this product produces in everyone who sees it and focus everyone involved to make it THE product to seek out and buy. As the consumer sector of the market we in this community of interest are the ONLY strength in which they can rally around.</p>

<p>There are things to be done before we court Japan. The big job is first convincing signal providers in the depths of their being that HDTV is good for them. They need and want to know that the public is out there doing their part to seek their signals and, more importantly, are now a conscious factor in this transition rather than an endless horde being dragged by the feet to who knows where. But the evidence in support of this view is not overly convincing...yet. High-scan monitors--over three million of them now sold--work mostly from DVDs signals. Decoders capable of making sense out of a terrestrial or cable signal have been a fraction of the unit sales, amounting to something just over 15% to that of the monitors. While a decoder for an HDTV program will deliver a kick-butt image people are frightened off from buying them. They hear old out-dated horror stories about reception or not-so-outdated rumors about impending changes in the standard to satisfy Hollywood. These issues, plus the lack of their being cable-ready, has too many consumers walking out of the retailer showrooms without buying a thing. Even worse, customers are not drawn into the stores to see the new image due to negative press accounts saying 'its still too early ."</p>

<p>Such slow decoders sale do nothing to raise the spirits for terrestrial signal providers. While relieved this year by excellent up-front advertising sales for their NTSC business, they have this involuntarily command to carry the pioneering water for H/DTV while dismantling the only business that is doing well (again). They have been badgered to provide still-more HDTV programming to fuel the interest, and still market impediments keep thwarting the efforts they do make in this direction.</p>

<p>Satellite providers have set top boxes, including the large dish folks. While they suffer from the same copy protection conundrum their reputation isn't quite as sullied as it is for over-the-air. They are capturing a good deal of the receiver market. Cable is entering with more aggressive moves than were expected, though far from convincing to many analysts. I just learned today, however, of Cox Cables aggressive plans to roll out in ten markets next fall. Time-Warner has been aggressive and doing the right moves. Once thought of as the biggest market impediment cable is turning out to be one of the better friends of HDTV.</p>

<p>But still, it is broadcasting who all look to to drive the 'real business' of HDTV. The networks still command half the audience in primet time. If you are a manager of a publicly held television enterprise today you live under unrelenting pressure from Wall Street who wants you to run your stock to the ceiling. Very unlikely that HDTV would be high on your list to do that. In an industry which lives or dies by daily ratings HDTV is nearly invisible. Only a handful of people represent the implementation of HDTV today within broadcasting.</p>

<p>Manufacturers of receiving equipment have been saying that more compelling HDTV programming is the key to fostering a faster market uptake, especially for the decoders. Once the market has attained a certain "velocity," they predict, the pressure on executives from financial institutions is lifted. Things can shift rapidly then to their opposite. New content then gets released for competitive reasons, which causes retail sales to soar even more. It then becomes obvious to everyone that a great big hit is in the making. "It should be backed," the financial managers will advise. "Anyone who is a stakeholders in television should jump in." More fuel is added by this lead to drive retail markets to the point where even the stickiest of the market impediments (used until then as a safety brake) are boldly swept away as everyone sprints forward to gain a solid foothold in the new 'success story.' At least that is my take on this phenomenal story.</p>

<p>This 'exuberant' condition has yet to show itself. We are still mired in a phase where highly paid managers are focused to squeezing out the last remaining profits from the old standard. Those who inhabit the world of HDTV today are still thought of as 'fringies', yours truly included. This 'fringe' is actually a highly dedicated group of souls who believe that HDTV means much more to civilization than just a new technical gadget. HDTV brings a new way of seeing to the world that embraces it. It can be seen as a new carrier for all other visions. As one dear friend puts it, "It lets the real world in"</p>

<p>While not all of my colleagues share this lofty view I can say without equivocation that there is a growing belief that HDTV is at least a healthy and positive contribution to our times. It is so attractive that once installed a new respect for quality must follow. That seems especially important in times dominated by coarseness in social intercourse and when degenerate institutions face deadly global conflicts. If the world is to find a way past its present meltdown it will come only with a NEW vision which, as Dr. Maya Angelou said a few years back, "can be followed with our hearts unafraid."</p>

<p>Such sweeping social visions only come through inspired artists. It makes some sense to say that the best of them will choose a medium that "makes everything new again" all from the (jump) start. These light bearers learn to transcend old dying conditions and offer up an alternate view of life -- like seedlings sprouting from a charred forest. Civilizations gone mad restore order in one of two ways--a passionate movement for freedom and godly abundance driven by recognizably good people, or, diving headfirst into a dark dictatorial world where we all find ourselves enslaved by paranoid characters leading all to an inescapable misery. That dark shadow has descended upon us more often than history likes to re-tell.</p>

<p>So far HDTV is the best means going for an artist to deliver the better of those two options, and to the widest possible audience. The Moher Film Market Analysis of 2001 has tuned to this theme last year when they said in their report, "It has become obvious that audiences around the world have grown weary of the violence and crime which plagues the streets--children shooting one another in schools, and the simple truth that we, as a people, have lost our way in the world. As society searches for the answers that will nourish their souls, they will be looking to the storytellers who influence and educate society through entertainment."</p>

<p>Of course lofty goals were held out for radio too, but it was a bruising Dempsy boxing match piped into the Waldorf Astoria Ballroom in New York City that finally "enlightened" and motivated the investors of the day to jump on board. So, I am not saying that HDTV and saving the world go hand in hand...but one thing I feel strongly about is the idea of not squandering any of the potential public good that HDTV can deliver, especially for any superficial reasons. I, for one, would hate to make an accounting of myself to civilization for NOT finishing what I started. This is a rare opportunity made possible from the integration of all technical and cultural knowledge to date. All of that makes some heady shoulders upon which we can stand. In a phrase--don't blow it.</p>

<p>Every market impediment is an enemy of this last idea. They do horrendous damage to the retail side of HDTV, which grievously impacts everything from engineering to content. Buyers do do their research. After all, love it as we may, HDTV is still expensive for all but a few to be an impulse item. It takes no time, however, to find those nasty blocks raised, like these copy protection circuits which have yet to be settled upon and included into the decoder/monitor package. Over the years I had learned from my consumer electronic mentors, and have so reported, that ANY confusion introduced into the marketplace causes that market apply its breaks and to stall or suffer a regression. A major retail chain executive told me during the COFDM/8-VSB debate that if the standard were to be changed they would no longer handle HDTV because the cost of re-educating and reestablishing the trust of the consumers would outweigh any other of the retailer's benefits.</p>

<p>By far the copy protection issue, which is initiated and pushed by Hollywood's financial backers, has done more damage to the HDTV retail set top box/decoder movement than any other issue. It lingers there like an old haunting memory saying over and over to buyers, "Don't waste your time and money. Everything is going to change." But those in the debate are so fiercely competitive that no agreement can be found--no idea can be declared so superior that a counter movement cannot be made equally effective. No government legislation can cause it. It is only finalized with the outburst and outrage of the public, who you remember love HDTV. Their love is spurned and they will not take it beyond reason. Once the public is for an issue, no industry can stand against them. With this understanding we have organized in Washington the High-Definition Television Association of America, which is designed to be the collective force of the public voice in Washington and any where else where there appears to an obstruction to our love affair with this remarkable "box of lights and wires."</p>

<p>These rights owners of high-value content (and let's call them new movies, which is what they are) believe they have to protect their assets against a hoard of miscreant citizens--you and me, by the way--whom they believe in the depths of their deluded hearts are bent upon mishandling their product as well as undermining their/our own welfare. This accusation from your chief of all entertainment sources has manifested itself in tricky hardware and software measures which simply leave the average consumer doing what they have always done--turning on and off their TV set. The average American has a VCR, but who really uses it for time shifting? Who uses it to make copies of even PBS programs, for example. I offer this example because people who own VCRs purchase these tapes right after their showing! PBS makes a handsome return selling what they give away un-encrypted every day.</p>

<p>We should, of course, have sympathy, along with a good deal of respect, for anyone who takes a risk that, in the final analysis, is to our benefit. We can bitch to high heaven about talent being over-paid, but our lives would only be poorer if they didn't take the chances they do and deliver the products to us that they do. We are not denied their mansions or their so-called glamorous life because of the few dollars a year we spend with them. No mansion of theirs becomes mine if they should make less reward or more. All of their activity is, bottom line (even the titillating scandals that have replaced the court intrigues of old) are to my enrichment, and I want to honor that.</p>

<p>One in five movies (at least it used to be this ratio) make money enough to pay for the rest, which either break-even or lose their shirt. Many movies would never recoup their investment without non-theatrical sales. Our respect for this fact is the only thing that is going to keep the flow of such productions coming our way.</p>

<p>I was advised by a former council to the MPAA that it is the financial institutions, upon which they all urgently depend, who most-rule the copy protection mission. Those institutions know less about the entire field of digital and entertainment than any others in the program supply chain, and they live in a perpetual state of conservative paranoia. No analyst I am aware of has looked favorably upon HDTV as a real business. No analyst I know has ever sent out a glowing report on the economic benefits of HDTV to ANYONE, much less the movie business, where a hit is pure gold. This gold is not transported now in an armored car, but rather down phone lines, the air, over cable wires, and raining down from satellites. I don't want to steal anyone's gold, and I am having a little trouble with being accused of doing that kind of thing, of condoning it in the very least. I would be horrified if anyone in my family were to steal their neighbors goods. Yet, I am asked to pay the price for locking-up Hollywood's gold. Why don't they think of me as a responsible custodian of their goods? Why am I thrown in with some other lot, and asked to pay the price?</p>

<p>There is talk that legislation will be needed to put to rest this copy protection issue, at least technically speaking. But critics of such a proposal far out weigh and outclass those seeking that remedy. No one sees any legislation that would provide the kind of flexibility that fast moving technical innovations must have to remain competitive. The NTSC standard is more than 50 years old without barely a revision. That is a clear enough of an example of what happens to innovation when nailed down by government decree. And the NTSC standard was not that securely nailed down at that, but what was, nailed the stanard to the wall. There is some kind of legislation thougths going on proposed from Senator Hollings office and, correctly if I am wrong, Billy Tauzin's office. And, I suppose others, have legislative ideas and agendas for copy protection solutions. It's not that government is bad, but rather it is the time it takes to resolve matters where many interests are given space to jockey for advantage, just as it should. This freedom to win the prize in a competitive culture is to me one of greats payoffs we havewith a Democracy. Democracy supports all directions in life with conscious limits set for those who otherwise smash into the walls. You only want to anchor by government decree that which will not morph into something else quickly on its own.</p>

<p><br />
Even in their most optimistic forecasts the Consumer Electronics Association shows HDTV at only a 12% penetration of US households by the year 2006 (a theoretical date for when the old analog services would be shut down and that spectrum recovered for FCC/Government auction). That is still an insignificant percentage in the consumer realms. We need to exceed that forecast if we are going to be beneficiary of a well-propelled growth in new, high value content aimed at this medium.</p>

<p>While there are some in the major television networks who believe that the FCC can light a torch to the feet of broadcasters and make them toe the FCC deadlines for installing DTV transmitters, no executive I know believes that any legislation from Congress can do any good in spurring the consumer market. That is a voluntary market which may be led a bit by some action of the Commission, but in cities where every TV station is DTV operational market take-up rates are not significantly greater than areas where satellite is available. I hope I am wrong on that observation. Anyone have hard numbers?</p>

<p>Fears over the copying of original material has been with us since the printing press. Scribes sought its annihilation, seeing in it the finish of their careers. Zerox suffered countless attacks from publishers and audio recordings and video recordings each drew their fair share of blood in rights management battles. Many will remember Jack Valenti, president of the MPAA, predicting a disaster for the movie making industry from all the unchecked copying that VHS would do. The outcome we all know is different. It is likely that some of Mr. Valenti's sustenance comes from his seven constituents amply afforded by the enormous pot of gold the VHS introduction has provided to Hollywood. No dire consequence occurred.</p>

<p>Since digital makes perfect copies generation after generation the fear is greater than with the VHS case (where 3 generations deep made it unwatchable). Digital could be endlessly propagated. More copies are then copyable, and on and on it goes down the line. It's 'word-of-mouth,' only instead of sharing words, you share movies and that denies Hollywood the unfilled marketplace where they can sell their movie I gave away. That, Hollywood wants to protect against becoming an established habit among millions of otherwise decent citizens in our nation. This fear of our being bad has fathered the copy protection initiative. If you are not bad, you should be a bit offended by what you are being asked to do, as well as quite understanding that not all of our fellow citizens are all that good. You got to do something to either protect against or really stop theft. So, until someone else comes up with a better solution, the guy risking his/her money builds a vault.</p>

<p>The first thing everyone needs to understand is that no copy protection scheme--no vault--is going to outlast the hardware in which it is implemented.</p>

<p>Let me take you somewhere: Last night I had one of the strangest dreams in my entire life. In this dream all structure in life was made void. Everything from mathematical formulas to axioms in science to dogma and laws of social order were entirely dissolved. We were completely free agents in a new form of social and material stuff that operated complimentarily, leaving everyone free to navigate throughout it at will and without restraint. At first I thought there were no consequences to anything one might do in this new found freedom. But as the dream unfolded I could see that the consequences for doing wrong, while not immediately generating personal opprobrium, were just as devastating as ever upon the whole.</p>

<p>I began to learn .from the dream's premise that regardless of the freedoms we gain or whether law and governance are absent to enforce a decision, we still have to make that same right choice as if it were the death penalty not to. Even though our free will had grown in this dream beyond any material order-making, fences, barriers, or restraints our lives were dependant upon our acting as if they were still there.</p>

<p>Frankly, it was a disturbing dream. I began to realize that I had entered into the pure digital age where everything is possible with nothing being stoppable. Any and everything was doable at my will. I could run through an intersection. I could take from a shelf what I wanted. No rules forced compliance to any kind of behavior. But there were consequences to be seen. If I failed to stop at a corner I might cause a wreck. If I took goods from the shelf without compensation, I undermined my own economy. In my dream I had gone to an era where only personal self-governance was the backbone and the salvation rather than laws and locks. No barriers, no walls, no restraints outside of my own mind would work to keep order in this strange new, and infinitely free land.</p>

<p>What we are arguing over, fundamentally, in copy protection is the method for preserving a viable economic system. As in my dream there are no more effective walls, barriers, nor restraints in the high-speed digital era hard on the threshold. What I realized with this dream is that we have no choice but to construct a behavioral order which is geared to live in such a super-free world. While this is easier said than done, it is never done until said. Societies do right themselves once they understand that the payoff for doing so is far superior to the spoils of thieves. It's as obvious as the value of clean water. We stop polluting it and everyone can drink long and satisfyingly from the well again.</p>

<p>Going back to today where locks and laws prevail we have to realize that no lock is going to stay latched long in this digital universe. There is always a work around, especially when speaking about the Internet. It was designed, after all, to survive and to keep on delivering the 'goods' even in all out war. No copy protection system is ever going to be left uncompromised. Bernie Lechner, the honoree of this year's NAB lifetime of distinguished service to broadcast and television engineering said to me that if Hollywood doesn't want its movies copied they will have to keep them in a vault until a showing, frisk all who enter the theater for a camera, and after the showing return it under armed guard to the vault. That, said this distinguished American fixture of television science, was the ONLY way it could be protected. Everything else is destined to fail. I say everything but a deep social realization that we should act as the agent for our content providers and protect the value of their property by encouraging the economic engine that produces these pieces we covet. And besides, who wants a gaggle of entertainment dependents showing up at your door every Friday night to partake of your largess? I don't. Invite them in for a drink. Show them HDTV. Spread good cheer and go to bed with a clean conscious. Otherwise you will be treated by Hollywood like criminals, frisked at the store and watched as in a George Orwell nightmare...and it cost you a bundle and they go to the bank with an entirely negative view of their best customers--all seen as thieves in the night protected by temporary locks we must change periodically at our own expense just to receive the voice of the arts we will need to pull us past 9|11. Get real. Get sensible. Pledge to support the economy rather than steal from it.<br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>August  4, 2005 05:45 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 173
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
			
 		<?if (5 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 5
 				AND entry_id <> 173
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
 				<h2>About Archive &amp; History</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/2005/08/2001_-_thoughts_on_hdtv.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
