<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 158";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 158 AND placement_is_primary = 1";
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
	<meta name="keywords" content="hdtv magazine, frank eory, jeff davis, nat ostroff, motorola frank, VSB, vsb, motorola, cofdm, COFDM, chip, Sinclair, sinclair, •, multipath, those, HDTV, hdtv, magazine, nat, signal, Magazine, Nat, data, been" />
	<meta name="description" content="This is one of many articles written about the once considerable 8-VSB vs. COFDM controversy. That controversy is laid to rest in this nation with 8-VSB clearly being the choice to nearly everyone's satisfaction. I say nearly everyone, because in..." />
	<title>HDTV Magazine Archive &amp; History - 1999 -- Chips Ahoy (VSB vs. COFDM)</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/1999_--_chips_ahoy_vsb_vs_cofdm';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('1999 -- Chips Ahoy (VSB vs. COFDM)'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/history/2005/07/1999_--_chips_ahoy_vsb_vs_cofdm.php";
		if ($author[img] != '' && 5 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">1999 -- Chips Ahoy (VSB vs. COFDM)</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>July 24, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/07/1999_--_chips_ahoy_vsb_vs_cofdm.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/history/2005/07/1999_--_chips_ahoy_vsb_vs_cofdm.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/history/2005/07/1999_--_chips_ahoy_vsb_vs_cofdm.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/07/1999_--_chips_ahoy_vsb_vs_cofdm.php&amp;phase=2&amp;title=1999%20--%20Chips%20Ahoy%20%28VSB%20vs.%20COFDM%29&amp;bodytext=This%20is%20one%20of%20many%20articles%20written%20about%20the%20once%20considerable%208-VSB%20vs.%20COFDM%20controversy.%20That%20controversy%20is%20laid%20to%20rest%20in%20this%20nation%20with%208-VSB%20clearly%20being%20the%20choice%20to%20nearly%20everyone%27s%20satisfaction.%20I%20say%20nearly%20everyone%2C%20because%20in...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><em>This is one of many articles written about the once considerable 8-VSB vs. COFDM controversy. That controversy is laid to rest in this nation with 8-VSB clearly being the choice to nearly everyone's satisfaction. I say nearly everyone, because in this field perfect agreement without qualification has never proven to be the case. I will be adding more of the articles penned until the whole story is laid out for you.</em> _Dale Cripps <br />
_____________________________________________</p>

<p>Reports raced throughout the Internet on August 19th (1999) that a "new chip" (dubbed by cynics as the "Mystery Chip") had been developed in Pennsylvania to adequately handle dynamic multipath reception for 8-VSB receivers. "I must say the timing is 'perfect.'" offered Sinclair Broadcast Group's Nat Ostroff. A news release surfaced from Nxtwave Communications in PA on the 24th.</p>

<p>Nxtwave Communications Inc., of New Town is a spin-off of the David Sarnoff Research Center, themselves no stranger to consumer product innovations. "Nxtwave began 3 years ago as a Sarnoff incubated company," said Sarnoff's CEO, Jim Carnes. </p>

<p>Not only Nxtwave, but Motorola also released on August 23rd a press release heralding their new chips capable of handling dynamic multipath for 8-VSB. This chip also came from a partnership between Motorola and Sarnoff.</p>

<p>"The real mystery to me is where these people were when Sinclair was begging for 8VSB receivers that worked." asked Microsoft's Tom McMahon. </p>

<p>Less skeptical was Lynn Claudy, vice-president of Technology of the National Association of Broadcasters. "They have been working quite unnoticed for several years, Only recently have they come to anyone's attention." </p>

<p>Nat Ostroff (Sinclair) had also heard something of them and invited Nxtwave to take part in the Sinclair "tests". He received no response. "It seems a little bit early to make claims for your chip when it has not yet been tested, let alone in the field," he was quoted saying then. "Others came to us with 'solutions' that did not prove out in field test." He adds "But having said that, I truly hope they have what they say they have."</p>

<p>Lab test of the new chip use signals from simulated environments. Many conditions were taken from recorded field locations in Baltimore. "That is a lot different than from real world," acknowledges Claudy. He is not alone in looking forward to a tested solution to the paralyzing challenge laid down by Sinclair. "It would be a disaster to dismiss 8-VSB at this late date," says Sarnoff's Jim Carnes, adding, "and it won't be necessary." Carnes believes that all of the worry "will be DTV history to look back upon in 4 months time." Claudy agrees.</p>

<p>Bob Graves, Chairman of the ATSC, certainly hopes these devices work. He is faced with marketing the US 8-VSB transmission system abroad and acknowledges that Sinclair's work has done a great deal of damage to his international mission. </p>

<p>Broadcom is another equalizer company who will surface soon with their solution on a chip. The chips from each vendor had been kept under tight security wraps until now. Some think the timing of these announcement is just too cozy and is setting up the industry with new complicated considerations so manufacturers can get past the Christmas season before the other shoe drops. But these chips have been under development for over a year. They are following current product introductory patterns. Few outside of essential parities were aware of the work going on. Just last Wednesday a high-level meeting was held in Washington, DC by those in support of 8-VSB. They met to determine what would be the best response to the Sinclair initiative. ATSC, MSTV, NAB, CEMA and others debated without conclusion on how or with what they could respond. The chips had not been disclosed to even those in attendance..</p>

<p>Nat Ostroff, the architect of the side-by-side test of 8-VSB and COFDM at Sinclair in Baltimore, has said repeatedly that the American system should have the "same" receiving characteristics as any system available anywhere in the world, i.e.,like COFDM. Ostroff has also declared repeatedly his neutrality as to which system finally prevails, just as long as it works. COFDM is clearly a competitor, but like all competing things they offer competing features or design promises. Ostroff has been favoring the inclusion of COFDM in the FCC standard as an option to use rather than excluding 8-VSB in favor of COFDM exclusively. With ample co-signers from other group broadcasters, Sinclair is within days of being ready to issue a petition to the FCC asking for COFDM inclusion. </p>

<p>With the announcements from Motorola and Nxtwave Sinclair's petition plans are left pending a further detailed examination of the claims of these chip makers. The petition has not been dropped as there are still thorny issues with the receiver manufacturers that may have to be addressed formally. The FCC has advised consultants today that these equalization chip announcements alter their view from within the Commission, which had been growing in receptivity to accepting a petition from Sinclair and issuing a Notice. If proven in field test, these chips will change the way everyone is viewing the question, including and potential signers of the petition. Getting them used will be the other companion story.</p>

<p>So, what can broadcasters expect? We contacted Motorola on the 24th. I talked to Jeff Davis, Vice President of Global Sales, Imaging and Entertainment Solutions group, Jim Farrell, Manager of Marketing Communications, Imaging and Entertainment Solutions group, and Frank Eory, Designer, Digital TV Operations, Imaging and Entertainment Solutions group about the performance of the chip.</p>

<p></p>

<p><strong>INTERVIEW</strong></p>

<p><em>Motorola has developed an equalizer chip for "sub $20 in quantity." Drawing liberally from a vast experience in equalization technology from their industrial background Motorola claims they are delivering the most advanced equalizer ever be be deployed in a consumer electronics package. </em></p>

<p><strong>HDTV Magazine:</strong> What have you done to solve the problem illuminated by Sinclair?</p>

<p><strong>MOTOROLA--FRANK EORY:</strong> We attacked the problem of large dynamic and static echoes head-on with what I believe to be the world's most advanced equalizer ever to be deployed in a consumer receiver. This is to elemenate multipath from the equation so that from the broadcasters point-of-view the only issue is signal power in determining coverage. </p>

<p>We spent several weeks in lab testing. Field test are in progress as we speak. We are throwing all kinds of ugly VSB signals at this thing, including the Sinclair-Lombard Street and Sinclair Harbor Apartments scenarios--at least the lab emulation's of those. We went even beyond those scenarios once we saw how much margin we had with those multipath ensembles. </p>

<p>Basically, at least from laboratory measurements, we have demonstrated that multipath is not a problem, both very large static echoes as high as .1 db below the desired signal. With the Sinclair scenario quoted 10 Hz flat fading and 10 Hz dynamics on those strongest echoes, and in the ATSC Grand Alliance echoes, we have gone as high as 20 Hz on dynamic multipath. Quite honestly we are still trying to characterize the whole performance space of echo amplitude and dynamic phase rate to explore the extreme limits of what this thing can do. </p>

<p>HDTVMagazine: Then you confidently claim that you are doing all that COFDM is doing?</p>

<p>The one thing that COFDM is claiming, which I think is an area of challenge for 8-VSB receivers in general, is mobile reception. Different people define that different ways. We have clear evidence we can support pedestrian mobility--a guy walking around with a lap top with an antenna poking out the back--sort of low speed mobility. But the stuff they are doing in Europe in trying to demonstrate reception at 200 MPH down the Autobahn. That is more challenging for the ATSC system because we don't have the luxury of falling back to some very low data rate. In Europe they go to QPSK and put in extreme amounts of FEC (forward error) encoding and, yes, they can demonstrate some high speed reception, but not very much data at those speeds. That is all a "flexibility of standards" issue, but not something the ATSC was designed for.</p>

<p>The 20Hz dynamic is a lot more than leafs blowing in the wind or people walking around the room. Like I say, it easily satisfies pedestrian mobility, but falls something short of high speed freeway traffic. </p>

<p><strong>HDTV Magazine:</strong> Nat Ostroff has continued to say that he is neutral as to which system is finally used as long as it delivers "the same" as the one he can now have, i.e., COFDM. Does your chip satisfy this condition, or a percentile of that condition? How can we rightly compare it? Is there a set of testing procedures that will allow an apples to apples comparison?</p>

<p><strong>MOTOROLA, JEFF DAVIS:</strong> When you say the same, we have a lot of variables there that relates not only to the type of terrain you need to deal with, but the factor in power and coverage from the transmitter. </p>

<p><strong>HDTV Magazine:</strong> Yes, I am just wondering out loud how you factor in whatever trade offs are left?</p>

<p>What was really demonstrated in Baltimore was the inadequacy of the early generation of the receivers. Clearly there were some issues from the UHF propagation point of view with indoor reception. What is the static and multipath environment really like with a set top and bow tie antenna? Some of those things were not addressed by the Grand Alliance test and not really addressed by first generation receivers. Nat drove home that point that these receivers have got to get better. We have been working for more than a year now to answer that question. </p>

<p><strong>HDTV Magazine:</strong> Did you move from Nat's earliest initiative, now over a year ago? </p>

<p><strong>MOTOROLA, JEFF DAVIS:</strong> We have been working with our partner Sarnoff on a number of devices for over two years now. This is actually the second chip to have come out of that partnership. We are very pleased that Nat gave us some additional difficult scenarios thanks to the data capture that was done and posted on the web by Oak Technology. That gave us real world signals that we could throw at this thing, and then say, "yeah, it can handle those with no problem. What's next?" We are out now working with various broadcasters and some of the consultants that know where the nasty spots in various cities are. We will go out and demonstrate with a bow tie antenna in a Manhattan apartment soon, etc.</p>

<p><strong>HDTV Magazine:</strong> Does your approach require training signals?</p>

<p><strong>MOTOROLA--FRANK EORY:</strong> We are not worried about them at all. One of the key points that a lot of receiver designers recognized a long time ago is that the training signal sequence in the ATSC signal itself doesn't happen often enough--some 25 milliseconds. That is not enough to cope with the dynamics, so you have to go to blind techniques and the training signal is just some other data that is nothing special. </p>

<p><strong>HDTV Magazine:</strong> What is left to do?</p>

<p><strong>MOTOROLA--FRANK EORY:</strong> Quite frankly for the home receiver or low speed portable, we have taken the multipath equation completely out of the picture. So, the only challenge left is to improve the speed of those dynamics, which translates to vehicle speeds. That is really the only problem left to solve. </p>

<p><strong>HDTV Magazine:</strong> Is that foreseeable as we advance in silicon?</p>

<p><strong>MOTOROLA--FRANK EORY:</strong> As far s COFDM doing that today...it is not clear just how much value that is. As I mentioned, it is extremely low data rate (from COFDM). It seems to be enough for a single SDTV program in an 8 MHz TV channel, but what does it mean if it was in a 6 MHz US channel? </p>

<p>Something else that Nat and other COFDM proponents have not really addressed is: Can you do HD in a 6 MHz channel? I am very familiar with the COFDM standards--more than you might realize--and there are some entries in the table in terms of spec code rates that do support HD kind of data rates, but if you actually do one of those, how robust is it? No one has demonstrated that yet.</p>

<p><strong>HDTV Magazine:</strong> You will have no data rate penalty using your VSB chip? </p>

<p><strong>MOTOROLA--FRANK EORY:</strong> No, it is in the standard at 19.39 Mb/s</p>

<p><strong>HDTV Magazine:</strong> Take this opportunity to address all of the broadcasters in the US and Canada who might have concerns from the Baltimore initiative. What do you want them to know? </p>

<p>I would summarize it this way: This chip COMPLETELY removes the multipath issue from the equation.</p>

<p><strong>HDTV Magazine:</strong> "Completely" is a big word.</p>

<p><strong>MOTOROLA, JEFF DAVIS:</strong> Yes, COMPLETE with the caveat that it (the usage) is home (for) reception and pedestrian portable. I am not going out on the limb saying it will handle mobile reception. But it completely eliminates the dynamic and multipath issues. For the broadcaster the only significant variable becomes that of transmitter power to determine coverage. </p>

<p><strong>HDTV Magazine:</strong> Does this, then, support Bob Graves' contention that 8-VSB is the superior of the two?</p>

<p><strong>MOTOROLA, JEFF DAVIS:</strong> This is still to be demonstrated. Nat has his data and we will have our data, but in theory it is better on the fringe areas because it should require less signal-to-noise than COFDM. The more extended of the Sinclair test--those further out sites--claim that they saw no real advantage of 8-VSB over COFDM. But again, those were early generation receivers where they didn't deal with multipath adequately. So who can say (they were optimized for distance)? But we have an equalizer now that makes everything look like a gausian noise channel essentially. It cancels the multipath so that the rest is straight forward text book--how much signal power do you have? How much signal-to-noise ratio do you have at x miles from the transmitter. That should be the only variable that the broadcaster should be concerned with from this point on. </p>

<p>Thank you very much. </p>

<p>Hearing of these claims Ostroff said, "We are very excited about the prospects of these new chips." </p>

<p>Now comes another issue. The fact that there are chips to solve these problems does not guarantee they will be used, or if used, will not discriminate against terrestrial broadcasting with a higher box price than for a cable or DBS box. "We cannot tolerate that," says Ostroff. Lynn Claudy thinks it's good to be mindful of these remaining implementation issues and encourages someone to be the authority that mandates receiver performance. He first looks to the ATSC, and the NAB's<a href="http://www.hdtvmagazine.com/articles/articles-author.php?id=5">Eddie Fritts</a>has already suggested that the FCC get involved. Nat Ostroff does not want a two-tiered price structure with DBS or cable boxes being cheaper. With both the Nxtwave and Motorola chips entering the market at under $20 ($22 in quantities of ten thousand for Nxtwave) Moore's law would suggest that price will be very trivial in a few years time. In the mean time the cost of electronics is not the big issue in HDTV, where display and cabinet are so dominant in end pricing. But for boxes that convert DTV to an NTCS receiver, or a DTV SDTV receiver, an added cost at retail of $60 to $100 could be market-impacting. More on this topic at a later time.</p>

<p>More important now is to keep heads cool, evaluate the claims in a setting (why not back to Baltimore? Nat extends the invitation.) that will not provoke additional controversy, and heal the rift that has grown between broadcasting and their essential partners--the manufacturers. </p>

<p><br />
Dale E. Cripps</p>

<p><br />
 <br />
 <br />
 <br />
From Motorola <br />
 </p>

<p><strong>Feature Set --</strong></p>

<p>• High performance robust complex equalizer<br />
• Glueless interface with 10-bit industry standard<br />
• A/D converters, accepts pass-band<br />
• samples at 25 MHz as VSB input<br />
• Processor communication through an I 2 C serial interface<br />
• Digital on-chip timing recovery - no external crystal required<br />
• Provides gain control signals<br />
• Reed-Solomon decoder, Trellis/Viterbi decoder, deinterleaver<br />
• Signal interface for glueless connection to the<br />
• MCT4000 or a PCMCIA card for conditional access systems<br />
• Transport Stream interface with error checking and bit setting<br />
• All digital architecture for cost effective silicon implementation<br />
• 1.8 V operation voltage<br />
• 160 QFP packaging</p>

<p><br />
The MCT2100 is a single-chip all digital demodulator implementing Vestigial Sideband (VSB) demodulation and Forward Error Correction (FEC) functions for the reception of digital terrestrial broadcasts. It complies with the FCC 96- 493 Report and Order for terrestrial DTV broadcast that specifies the VSB modulation system. The MCT2100 achieves extremely high performance using a minimum number of standard, low cost external components to provide a complete 8 VSB demodulation system at an extremely competitive cost. A VSB demodulator must efficiently compensate for all the factors affecting the digital terrestrial broadcast. It must handle artifacts such as multipath signals and gain variation. At the same time, fast aquisition and low error rate are mandatory. </p>

<p>The MCT2100 uses a unique implementation of novel algorithms to meet all of these requirements and much more. It features low power dissipation in a low cost, industry standard, leaded surface mount package. Because of its high integration, simplicity of external design and straightforward interface, the MCT2100 enables fast design cycles and time to market. Motorola's VSB demodulation solution was designed with system effectiveness in mind. The MCT2100 consists of an active front end for timing recovery, AGC and pilot tracking together with an integrated back end for deinterleaving, error correction, serial or parallel data output and status reporting. Pins for gain control and IF data allow the MCT2100 to easily interface with other system devices. A host interface through an I 2 C bus is included along with an event interrupt signal to provide simple glueless control from the processor. Using years of experience in digital signal processing, Motorola has developed a solution, which is very reliable, design efficient and cost effective, thus providing customers the best solution for market success. </p>

<p>Copyright 1999 - 2005</p>

<p> <br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>July 24, 2005 02:59 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 158
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
 				AND entry_id <> 158
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/2005/07/1999_--_chips_ahoy_vsb_vs_cofdm.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
