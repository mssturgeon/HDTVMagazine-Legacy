<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 133";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 133 AND placement_is_primary = 1";
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
	<meta name="keywords" content="digital television, advisory committee, robert graves, high definition, signal strength, HDTV, hdtv, atsc, ATSC, standard, people, digital, television, broadcasters, going, those, get, data, receivers, think, VSB, vsb, time, work, PSIP" />
	<meta name="description" content="Robert Graves served as Chairman of the ATSC from ----- to ------ We talked to Robert on June 15th ofr 2001 in order to give our readers a feel of what the ATSC is all about. He had just returned..." />
	<title>HDTV Magazine Interviews - INTERVIEW: Robert Graves, Chairman, ATSC -June,  2001</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/interview_robert_graves_chairman_atsc_-june_2001';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('INTERVIEW: Robert Graves, Chairman, ATSC -June,  2001'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_robert_graves_chairman_atsc_-june_2001.php";
		if ($author[img] != '' && 4 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">INTERVIEW: Robert Graves, Chairman, ATSC -June,  2001</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>June 26, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_robert_graves_chairman_atsc_-june_2001.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/history/interviews/2005/06/interview_robert_graves_chairman_atsc_-june_2001.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_robert_graves_chairman_atsc_-june_2001.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_robert_graves_chairman_atsc_-june_2001.php&amp;phase=2&amp;title=INTERVIEW%3A%20Robert%20Graves%2C%20Chairman%2C%20ATSC%20-June%2C%20%202001&amp;bodytext=Robert%20Graves%20served%20as%20Chairman%20of%20the%20ATSC%20from%20-----%20to%20------%20We%20talked%20to%20Robert%20on%20June%2015th%20ofr%202001%20in%20order%20to%20give%20our%20readers%20a%20feel%20of%20what%20the%20ATSC%20is%20all%20about.%20He%20had%20just%20returned...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><em>Robert Graves served as Chairman of the ATSC from ----- to ------<br />
We talked to Robert on June 15th ofr 2001 in order to give our readers a feel of what the ATSC is all about. </p>

<p>He had just returned from spectrum hearings in Brazil and interrupted a busy schedule to speak with the HDTV Magazine's Dale Cripps. This interview provides readers with an informed laymen's view of the ATSC...and coming from the ultimate insider. </p>

<p>Graves offers us background on the committee, what role it played in making of the H/DTV standard, the continuing work the ATSC is doing, and some of his observations as a new HDTV consumer himself.</em> </p>

<p><strong>HDTV Magazine: What was the purpose of the ATSC from its earliest inception?</strong></p>

<p>Robert Graves: The ATSC was established to define the standards that are needed to make an advance to digital television a reality. It started in an analog world to replace the existing NTSC standard with a new technology. The original focus was entirely upon high-definition television. In the process digital television was invented. So HDTV is still very important--a focal point--a centerpiece application of digital television. But digital television is a lot more than HDTV. So, we have spent a lot of our efforts over the last three years developing the standards that will enable data broadcast applications and interactive services over digital television.</p>

<p><strong>Who makes up the ATSC?</strong></p>

<p>The ATSC is an international standards organization with members from about 25 countries. The great majority of those are from the United States, but they are often multi-national manufacturing companies. the important thing about the ATSC is that we cover all of the different segments of the TV industry--broadcasters, cable companies, satellite providers, motion picture companies, computer companies, software and hardware companies, and manufacturers both on the professional side and on the consumer electronics side. We also have other key industry organizations and standard bodies that are important members of ATSC, like the National Association of Broadcasters, the Consumer Electronics Association, the society of Motion Picture and Television Engineers, National Cable and Telecommunications Association, and the IEE.</p>

<p><strong>Are there any restrictions which eliminate any? Can people with a legitimate business be a member?</strong></p>

<p>Any one with an interest in digital television standards can participate in our standards work, even without becoming a member. Only firms can be members while individuals can be observers, and pay lesser dues. But to have a vote on a standard you have to be a firm. When it comes to voting on standards, it is only members. Above all you have to have a direct interest in digital television matters.</p>

<p>We also have government user organizations. But we don't invite government policy organizations to be members, though they are very welcome to participate in our activities.</p>

<p><strong>Then the FCC is not a member.</strong></p>

<p>The FCC is not a member, but it is very much aware and frequently involved in some of the nuts and bolts of our work.</p>

<p><strong>The work that went into the current television standard--the digital standard-- was accomplished under the guidance of Dick Wiley and the FCC's Advisory Committee on Advanced Television Services (ACATS). How did the ACATS developed standard become the ATTC standard?</strong></p>

<p>That is an interesting part of the story. First, the people who were involved in the Advisory Committee (ACATS) were by-and-large were the very same people that were involved in ATSC. Much of the work in ATSC went on in parallel with the Advisory Committee. Toward the end of the Advisory Committee process...the digital HDTV Grand Alliance was focused exclusively on high-definition television. The Advisory Committee, under Dick Wiley, decided to add standard definition television formats to the standard before it was presented to the FCC. It was the ATSC that developed the Industry Consensus around the SDTV formats to be combined with the HDTV formats.</p>

<p>The other key thing that the ATSC did was to document the Advisory Committee work into a standard that would then be presented to the FCC. The work of the Advisory Committee came to have the name of ATSC associated with it because it was the ATSC who documented the work.</p>

<p>The Grand Alliance had come and "done a great service to mankind" and gone--it no longer exists. But their work lives on and has continued through the ATSC. People frequently just refer to the standard as being developed by the ATSC, which is not strictly true, but closer to the truth in spirit than in literal fact. It is all the same people, and many more now.</p>

<p>By the time the digital television standard was adopted ATSC had about 50 members and was strictly a United States organization. We became international the first of 1996 after the standard had been recommended by the Advisory Committee to the FCC, but not yet adopted. For more than a year we have had over 200 members. We have grown four fold since the work on the main DTV standard was done.</p>

<p>In addition to data broadcasting work we have done a lot of important work on the PSIP standard and system information protocol, conditional access. Our data broadcastaing standard has been adopted now. We have an implementation subcommittee. That is very important for uncovering the inevitable problems that arise in implementing an entirely new technology, and then working with all sides of the industry to resolve those implementation problems . Things like lip-synch problems and the spotty implementation of PSIP by broadcasters. We are still dealing those problems. Indeed, some of the problems that were laid at the feet of 8-VSB were shown finally to be PSIP problems. The reason you were not getting a signal had nothing to do with RF, the PSIP was not properly handled at the transmitter, or by the receiver.</p>

<p><strong>What does the data broadcast standard do in the ATSC standard?</strong></p>

<p>It is absolutely world-leading technology in terms of the scope that it entailes. It is a very comprehensive standard that has several different fundamental capabilities for delivering data through the ether--giving broadcasters the capability to support a wide range of data broadcasting services. As you know, a vast majority of these services can be provided without any impact at all on the quality of high-definition programming. That is especially true where opportunistic use of the channel is involved. If you are watching the Super Bowl (there are) some scenes requiring the full big rate of the channel to get the best high-definition quality. But then you will have a logo on an advertisement for a few seconds and you can send the New York City Telephone book in that time. So, it's absolutely not a matter of how do you squeeze a few bits through the channel. It is "what sort of services make sense for consumers and broadcasters if you can suddenly send a ton of data?"</p>

<p><strong>What are some of the data suggestions that strike you as interesting?</strong></p>

<p>Two main categories:<br />
1) Data associated with the programs and<br />
2) data that is completely independent.</p>

<p>On the independent side you might have stock quotes, sports scores, even from every obscure minor league team in the country. It could be sent in a few seconds time. Then, if you think of data associated with a program, you can be watching a baseball game and pull up the lifetime statistics of your favorite player and see them change in real time as he strikes out or hits a home run.</p>

<p><strong>DASE</strong><br />
We are nearing completion of another fundamental addition to the capability called DASE--DTV Applications Software Environment--which will eventually allow interactive services. Broadcasters can write very sophisticated data applications that can be associated with their video programs. They write to a common middle ware layer of software so these applications will play uniformly on a wide variety of receivers. By establishing a middle ware software layer both broadcasters and receiver manufacturers can innovate in a way that will still provide compatible services.</p>

<p><strong>Can you give me a practical illustration of how that works if I am watching say David Letterman in HDTV?</strong></p>

<p>Well, if the ten reasons why were not up long enough you could put that back up on the screen from data. It really talks (the DASE standard) about standardized ways for presenting data.</p>

<p>There is one simpler aspect called declarative data applications. They talk about standardized ways to put text and other information on a screen. Then there are more complicated procedural applications, which, at least in the proposals they are looking at now (not finalized as yet), involves Sun JAVA machine language. You could say I want to order more information about that Jaguar you just showed or of the ten dealers closest to me for the Chrysler car I just saw an ad for. Or, I want to play fantasy baseball with all of the other viewers somehow. It is a very complex undertaking. We are breaking DASE up into 8 different pieces. Five of those pieces have received tentative approval in the technical committees of the ATSC. There are three other pieces yet to go.</p>

<p><strong>HDTV-The Killer Application</strong><br />
As you know, Dale, I am a huge fan of HDTV. I think it is the killer application of digital applications--the one that will make people invest in digital television technology for their home. Fifteen years from now we will look back and see that this is a revolution which includes HDTV, but goes well beyond it. All of these other data applications are going to become very, very important, but by themselves I don't think they are the killer applications. They are not the VISICALC that brought about the PC revolution, but HDTV is.</p>

<p><strong>We see HDTV as the destination for most data applications that require audio or visual presentation. Do you see it that way?</strong><br />
 <br />
I think it will work that way. It not that HDTV is necessarily tied to all of these things, but I believe we are all going to be watching HDTV in the next five to ten years the same way we are all watching color TV instead of black and white. When you are watching TV, you will be watching HDTV for all of the most popular programming. All of these other data applications don't depend upon a particular resolution of the video. They could be provided by themselves not associated at all with the video formats. You could have some with multiple programs in SDTV. It is not my favorite application, but a lot of people talk about seeing different camera angles of a sporting event. I have a feeling that most of us who tried that for five minutes would understand why people have devoted their careers learning how to do it right. I am happy to rely upon the pros.</p>

<p>A certain amount of this can be done in parallel with HDTV. People often say that you can only do one HDTV program in the channel. For most content that is too limiting. Frequently, as always does PBS in Washington, you can watch HDTV and one channel of standard television whiteout any problem. For some material--at least 720p HDTV film-based material--you should be able to have two simultaneous HDTV programs in 6MHz channels.</p>

<p><strong>Where are you doing in the conditional access part of the standard? (Conditional access allows for pay-per-view or subscription channels that are "authorized" remotely.)?</strong></p>

<p>That is in place, and for some time now. I don't know if anyone is using it yet. As you know, free over the air television has been the [predominant model thus far in the US.. But conditional access is a tool ATSC has provided so that if broadcasters want to make certain programming available for a fee, they can do that. It is allowed under the FCC rules.</p>

<p><strong>PSIP has been somewhat confusing. Can you explain it in clear terms?</strong></p>

<p>I will give it my best try. We have lots of experts in the ATSC that write books about this and spend a lot of time explaining it. It is rather complicated. That is because all of this great capability and flexibility that comes with a digital television systems requires that kind of complexity to keep track of it. With digital television there are pieces of programs that are put together at a receiver. Information that receivers have gathered in order to put together properly to present a program. It is not like analog, where everything was nailed down and put into a single package and the receiver operated in lock-step with the transmitter and originating material;</p>

<p>PSIP performs three primary functions:</p>

<p>1) naming<br />
2) numbering<br />
3) Navigation</p>

<p>The naming of programs and numbering of those progress and naming channels, and navigating a huge bit stream that is available at a receiver any given time from the different TV channels on the air (each with 19.3 MB/s coming from every local broadcaster carrying not just one program in the old analog world, but potentially a number of programs and a vast number of data services in addition to those programs. So, it is necessary to have the capability to keep track of these different programs. There are two parts of PSIP--1) system information that gives you very necessary information for making this whole thing work.</p>

<p>When I go channel up or channel down I find the right digital TV programs. The program guide aspects of PSIP, where this information can be used to develop a program guide. We are familiar with programs guides from cable and satellite. Those are much more straight forward because there is a single provider assembling the programming and he or she can prepare a program guide and send it to you. It is not so simple with digital television because you have a number of independant stations sending independant programming. The model that has been adopted is that stations send their own information in standardized and reliable way and receiver manufacturers know what to expect. They are aware of the same PSIP standard. That gives them (manufacturers) an ability to innovate and make a better program guide than someone else, and to distinguish their products.</p>

<p>That is how the system works. It gives it tremendous flexibility. It also makes it susceptible. There is no iron-clad FCC requirement that broadcasters send PSIP information. We are engaged right now in a concerted campaign working with the NAB and others about the importance of PSIP, and the importance of doing it right.</p>

<p>That leads to the next problem. Some broadcasters send it, but they don't send it correctly. This can create havoc with receivers. One thing that we have done in the ATSC is organize "plugfest," where various equipment providers on the production side--encoder manufacturers, and PSIP generator manufacturers--get together with receiver manufactures and play their equipment with one another. You find out how your receiver works with someone else's encoder. Obviously, every receiver has to work with every encoder that might be encountered in the marketplace. These "plugfests" have been very useful in smoking out these compatibility problems.</p>

<p><strong>What are the typical symptoms of a PSIP problem?</strong></p>

<p>There have been instances when certain receivers suddenly have not been able to get sound on a certain channel. On this particular receiver (that I have) I get every one's video signal, but suddenly one day without me having done anything I am not getting sound on channel X. Why> Well, they started doing something a little bit different with PSIP and we have to iron that out. Or, they might be just starting with PSIP, and you had the receiver that didn't handle it rightly. Not all of the problems have been with broadcasters. I think all of the problems are getting straightened out.</p>

<p><strong>Let's talk about the controversial 8-VSB transmission system and some of the reason it was chosen in the first place.</strong></p>

<p>VSB was chosen through a very lengthily competition process. It was one of the most important elements of the entire Advisory Committee process. Even when the Grand Alliance was formed, and I was a part of it then, we had not chosen. One of the first tasks that the Grand Alliance had to do was choose the final elements of the Grand Alliance system. The modulation system was one of the things up in the air.</p>

<p>The VSB was chosen because it provided the best performance, In many important respects it still has a substantial advantage over any other modulation system used in the world. It has a much better raw coverage capability than the COFDM used in Europe and Japan.</p>

<p><strong>By "raw Coverage' you mean what?</strong></p>

<p>As signals get weaker and weaker the further out you go...or in buildings where signal attenuation is caused by going through building materials...the VSB will keep working with signals that are less than one half the strength of the signals required for COFDM systems. It is like the Energizer Bunny, it just keeps going long after the other one has stopped. (Raw coverage is all of the area covered by radiating signals.)</p>

<p>We did have some problems in the earliest VSB receivers. They did not handle multipath signals very well at all. These are signal reflections. Even the best VSB receivers had underestimated the difficulty with multipath. So, the industry has undertaken a concerted effort over the last two years to improve multiplath performance in receivers. Substantial progress has been made in that regard.</p>

<p>The ATSC has had a full review of the performance of VSB and that group issued its report about two months ago. That report found that the most important factor for insuring good receptions is signal strength. Multipath, with the progress we have made with receivers, is not the show-stopper in terms of improving reception It is signal strength. That again proves that 8-VSB is the right tool we have for solving this problem.</p>

<p>We had a huge controversy and thirty major US broadcasters conducted the most extensive comparative test every conducted in the world comparing the European DVB/COFDM system to the ATSC VSB system. To a large part the VSB performed better by a substantial margin then COFDM.</p>

<p><strong>Mobile?</strong></p>

<p>Some broadcasters around the world are showing a lot of interest in mobile applications. The ATSC system was specifically not designed to satisfied or reach moving receivers. This was not an oversight. When you try to satisfy mobile reception, it comes at a huge trade off in terms of bit rate. You can't have everyone in the service area getting beautiful HDTV pictures and at the same time having people drive around in their Mercedes getting good SDTV pictures. You can't have your cake and eat it too.</p>

<p><strong>Why is there a sacrifice of bit rate?</strong></p>

<p>The signal has to be much more robust in handling the Doppler shift effect of a moving vehicle. By the time part of the image gets there you have moved. You are out-of-phase. You have to send a more robust signal for any reception in a moving vehicle. Just to have a prayer of sufficient robustness, you are talking about one fourth the bit rate. You are talking about 4 to 5 Mb/s payload rather than 19. 3. That means HDTV is out the window.</p>

<p>It remains to be seen just how successful a mobile service could be with a single antenna. When you go behind a building or a hill and lose signal strength, you have no service. No signals strength, no service, That is why with mobile radio systems we have a cellular structures. If you lose the signals from one transmitter you have a shot from the other two transmitters at 120 degree angle offsets. Many people doubt that there could be any kind of mobile service anyway without a cellular structure. Then there are those who say that even with the cellular structure it is no where near reliable enough to provide the kind of DTV services that people are interested in.</p>

<p>There are some broadcasters in countries around the world, and some US broadcasters, that are interested in mobile applications. As you know the result of the 30 broadcasters study was to reaffirm their support for VSB. That is now a 'done deal.' There is no question any longer over whether DTV will use the ATSC standard in the United States That is good. The industry is focused on that. At the same time broadcasters weren't completely happy with the reception that they were getting.</p>

<p><strong>Wasn't there a challenge to these findings? Wasn't the impression given that neither system was replicating the old NTSC service areas due to lower-than-expected success rates in these tests?</strong>  </p>

<p>This was never reported very well. Those tests were done intentionally by selecting difficult sites. Those saying, "Oh, 30% or 50% -- that is just not good enough." If it is 50% of one percent of the worst sites, that would be terrific.</p>

<p><strong>Had anyone made a more comprehensive test where all receive sites in a territory were evaluated?</strong></p>

<p>That is a very expensive proposition to do if it is to adequately have real statistical meaningfulness. I don't think anyone has done such a full blown study. Iblast had 340 test sites in four cities and their paper is on the web--iblast.com--showing from random selection of sites (neither hard nor easy points) they got over 90% reception results. This was consistent in the four cities. Indoor reception was 91%! One of the things that gripes me most is this conventional wisdom that I think was perpetuated by the publicity around the Sinclair "demonstrations" that COFDM is much better on indoor reception. The ATSC found in its 8-VSB study that the biggest factor for successful indoor reception is signal strength. ATSC is much better at delivering adequate signal strength then is COFDM. IBlast played around with new antennas and have several interesting ones.</p>

<p>Just to be clear. We are not done in improving 8-VSB. We have an effort underway at the ATSC now looking at enhancements to 8-VSB. The primary purpose of that effort is to improve the reception by fixed receivers, both outdoor and indoor. That is the top priority. Then we also will provide better reception by portable receivers. Portable to us means something with a small antenna that can be carried around, even pedestrians</p>

<p>When you say I want to put it in my car and go 100 miles an hour down the highway, that is a much more challenging task. I like to talk about a boat-car. You can have a sleekly designed boat and you can have the best car in the world, but if you say I want my car to be able to drive into the Potomic River and cruise around for awhile, you can do it, but it is very expensive, and not a good boat nor good car. If you try to make a digital television system be all things to all people for all applications for all times, you are going to suboptimize. We in the USA think the prime application of digital television--a huge bit rate to do HDTV and a lot more--to the greatest number of fixed receivers possible, both indoors and outdoors. is what we should continue to be focused upon. We do expect these enhancements to provide mobile capability--limited, but no more than COFDM standards--but we are keeping our eye on the ball. Our goal is to improve reception by fixed receivers, indoors and outdoors.</p>

<p><strong>You recently looked at ten responses in response to your request for proposal for VSB improvements. Are you winnowing those down to lesser numbers?</strong></p>

<p>We are evaluating those now. They are not all mutually exclusive. They deal with different aspects of improvements. It's very possible we may end-up picking aspects and features from more than one of them, and moving ahead to developed enhancements.</p>

<p><strong>Are they all on the receiver side, or are some related to the transmission side?</strong></p>

<p>These are changes to the standard itself. That is what makes them different from the changes that have been going on right along with receiver manufactures. That reminds me to say something else very important about this. There is huge premium placed on backwards compatibility. The request for proposal we issued did not rule out the ability to consider something not backwards compatible, but made clear that a huge priority would be given to the backwards compatible approaches. Backward compatible by means that the people who have already invested in DTV receivers one day don't get up and find they don't work.</p>

<p><strong>Many have expressed this very concern in our forums.</strong> </p>

<p>I am one of those receiver owners and share their concerns. I also will tell them that I am not worried, and they should not be worried.</p>

<p>It is conceivable we might develop something mobile for different channels or for different countries.</p>

<p><strong>Are there any areas where 8-VSB is not receivable?</strong><br />
 <br />
Sure. There are always going to be some of those places. They may not all be out in the middle of the prairie. If your apartment is three layers under ground surrounded by concrete and you don't have some kind of indoor antenna system. you are going to be out-of-luck with any of these systems. Your best chance is with the ATSC system, but no system can guarantee perfect reception by every receiver in every location, as it has been with analog TV.</p>

<p>The original goal was to replicate wherever you had a half way decent analog you would get a perfect DTV picture. There will be some cases where that is not true. But there will be a lot more cases where people could not get NTSC who will now get digital. That was our goal and we have largely achieved that. Of course, we intend to do better as receivers improve.</p>

<p><strong>Has this standard cost us more money. Will it have a continuing added cost, or will it get very inexpensive in the long haul?</strong><br />
 <br />
I think it has happened already to a tremendous degree. You know me, Dale, I have an optimistic view on everything. I underestimated the rate at which prices have fallen for high-definition television. I also underestimated the amount of products that would be available in the market right now. We have more than 250 HDTV products--either displays or integrated receivers or set top boxes. Every one of them can handle an HDTV signal. All of the DVB (COFDM) receivers cannot.</p>

<p><strong>You are saying prices for mass markets are approaching?</strong><br />
 <br />
That is my prediction now. CEA is better qualified for making such predictions. I think the trend to larger screens over he last ten years will continue. That is because HDTV looks terrific on a big screen, and NTSC looks awful.</p>

<p><strong>You are saying that inch-per-inch in the big screen sizes there will be a very slight cost differential?</strong></p>

<p>Yes, the price of electronics is falling like a rock. That leaves displays and screens--those are the (nearly) same for analog or high-definition.</p>

<p><strong>The industry moans over two things these days, the amount of compelling programming and cable readiness. Where does the ATSC stand in their relationship with cable and the implementation of cable-ready HDTV receivers?</strong></p>

<p>I would love to sic you on the CEA and cable industry and let them take a shot at answering this question. One thing I say as often as I can is that the cable industry is too smart to be left offering a technically inferior service. I believe they will come to the table more forcefully than they have. There are a few systems around the country doing a fantastic job with HDTV, but not enough. Eventually they will come to the party because they will have to. It appears to me that they are losing their best customers to satellite. I have never been an HBO subscriber. But when they started offering HBO in HDTV I started giving them many bucks a month. DirecTV's revenue doubled (from me) because they put one HDTV channel on the air. Maybe I am not the average customer, but there is money in them thar hills.</p>

<p><strong>We see wholesale abandonment of cable in favor of satellite for those equipping themselves with HDTV.</strong></p>

<p>Broadcasters are complaining saying they must have "must carry" as it is vital to their future. But I think cable has a very compelling comeback by saying to broadcasters, "We look at what you have on the air and, with the exception of CBS and a little bit of ABC, we mostly see the very same programming. When you put compelling programming on your digital channel we will carry it because our customers will demand it."</p>

<p>I have a list of things that are holding it all back from taking off like a rocket. I have predicted that it would take off like a rocket and so far it has taken off like a WWII fighter. The first on my list is the lack of compelling content. No one is going to pay a premium price for TV with a dark screen on it. They want to see compelling digital programming, which can mean HDTV, but could mean a lot of other things. What it clearly doesn't mean is the very same programming upconverted. Often that upconversion is more mangled than on the analog channel. Nobody is going to pay for that. CBS has done a great job. I am glad to see that they are doing even more this year. Once we can see all major sporting events in HDTV we will be selling millions of HDTVs. In short order the 25 million TVs we sell in the US today will move to be predominately HDTVs. That is my opinion, which is not shared by all.</p>

<p><strong>is certainly shared by those owning HDTV today.</strong></p>

<p>I think it is shared by anyone who has seen sports. When they see it, they have to have it. I am that way. The three most important things about digital television are content, content, and content.</p>

<p><strong>Hollywood waves content at us but says it should not be copied when still valuable. While I know not all copy protection issues are in your area, but can you tell us from your professional observation post if all of the copy protection issues are now settled?</strong><br />
 <br />
I don't think they are yet settled. A lot of good people are working hard on them. They have made good progress. I am sure that is a problem in securing programming, but one of the programs NBC did do in HDTV was Titanic. If James Camera (it's producer) can let it be broadcast, who else has content that is more valuable than that in the marketplace? HBO is showing a lot of movies.</p>

<p><strong>Are there any myths which you face daily that you can clear up here and now?</strong><br />
Robert Graves:</p>

<p>One of my favorites is that there is no money in HDTV. That is a horrible myth. CBS is doing everything they are doing and making money doing it. Most of their (HDTV-related) expenses are being paid by advertisers--equipment manufacturers interested in promoting HDTV. As Marty Franks has said, " we don't have any kind of exclusive hold on this." He has invited other broadcasters to get on the bandwagon and turn a modest profit doing it. I am anxious to see what will happen when we turn the creative people lose on HDTV. You can tell stories better whether they're advertisements or dramas or comedies -- you can do better with a wider screen and higher resolution. Ultimately that is going to be more valuable to people. More people will watch it. If your stories are more compelling, more people will watch. Advertisers should be willing to pay for that. So, I get disgusted with people who say this is all expense and no revenue. My favorite broadcaster in the world is Jim Goodman (President of Capitol Broadcasting, the owners of the first HDTV-outfitted and transmitting broadcaster--WRAL--in Raleigh, North Carolina.). He says his business plan is to stay in business. I firmly believe this is an opportunity for broadcasters to be relevant a decade from now.</p>

<p><strong>We see that people who have not used antenna in a good long time are willing to re-install one for HDTV. Is this the opportunity you were seeing?</strong><br />
Robert Graves:</p>

<p>I don't predict that as a major trend. I don't view this as broadcaster's chance to regain half of the share they lost over the last 20 years to cable.</p>

<p><strong>Why not?</strong></p>

<p>The cable industry is too smart to be left offering an inferior service. They are going to wake up one of these very first days and realize they are losing their most profitable customers to satellite--not so much the terrestrial--but to satellite--and in part because of HDTV. They are going to say, "now is the time to get on the bandwagon and provide more compelling programming."</p>

<p>We mentioned the myths of the 8-VSB reception and how we have resolved a lot of those. It is getting better and better...every few months somebody has a new and better chip that handles multipath better and improves receivers in other respects. So, if you can't already tell I am very bullish on the prospects.</p>

<p><strong>You are a busy man. What is your HDTV viewing experience?</strong></p>

<p>Longing to spend more time with it. I am so busy that I don't get that much time . When I do get a chance to watch a NBA playoff game I become broken hearted that I can't watch it on my HDTV. Well, I can, but it is not in HDTV.</p>

<p><strong>Are NTSC programs of lesser interest now that you have had your HDTV?</strong></p>

<p>You know. I am a little bit spoiled. For me it works more the positive way that...well, I will give you a little vignette. I am not a big tennis fan. I like golf and, like a lot of people, can watch golf for hours at a time, but I'm not that interested in tennis.</p>

<p>But since the US Open tennis meet was going to be on in HDTV I turned it on one weekend. I watched 7 hours of tennis. That is practically my week's worth of my normal TV time. And I watched it all that weekend in beautiful HDTV. I don't know if the CBS bean counters were keeping track appropriately but they had one viewer they had never had before...one household they had never had watching their tennis meet only because it was entirely in HDTV. I was becoming a tennis fan. It is not that I was doing it because I like new technology, it made a difference in watching it in HDTV. It is more satisfying to watch it in HDTV. My reaction is, why would I ever want to watch NTSC again?</p>

<p>I had a Super Bowl party and we watched the Super Bowl on my Zenith 64w. Everyone loved it. The game was awful, but they loved pictures. We turned it over to analog and satellite SDTV just as a little demo. We had about 30 people ready to buy HDTVs at the end.</p>

<p><strong>Provokes the idea of selling HDTV like Tupperware. Buy one for your home, sell a 100 or so before you get tired of it.</strong></p>

<p>That is not the problem (getting them sold). What is needed is more Super Bowl-like programming to watch in HDTV. It will sell itself.</p>

<p><strong>Who moves the money for that programming?</strong><br />
Robert Graves:</p>

<p>It is sort of this chicken and egg thing. The thing that gets my goat the most is broadcasters who say "were not doing anything with our digital channels because there are no viewers out there." We knew this was a chicken and egg situation and that the chickens had to come before we could get any eggs. CBS is right. They know they have got to prime the pump. But to me the only question is when, not whether? It is slower than I had hoped and expected. But it is coming. It is inevitable, it's unavoidable because we are going to have HD-DVD media, even DVDs look better on HDTV displays though it is not HDTV. And you know, there is programming, and those that don't step up to HDTV are going to live to regret it. It is happening. It is inevitable. I would just rather see it happen a little faster.</p>

<p>One of my greatest complaints in life is--well, ABC is doing more than some in the way of HDTV. They did some--Monday Night Football a season ago and now NYPD Blue with 5.1 surround sound. I have that (5.1) too, so this is great for me except for one thing. Even though digital since 1998 (WJLA-TV, Irides LLC), Allbritton Communications Company has not seen fit in the Washington DC area to show one frame of HDTV. So even though ABC makes an effort to help move things along it is no use to me because the local affiliate here in the Nation's capital, where the people live who loaned 6 MHz to this broadcaster for something. You can debate what that something was--but I am pretty sure it was not for another copy of what is already on the air. Speaking now as one of your consumers I think that is a travesty.</p>

<p><strong>Richard Wiley wrote recently that a closer relations and alliances between government and industry were essential. Does government have a role here? Or is it still a private market driven thing?</strong><br />
 <br />
It is primarily market driven. I think it is going to remain that way for the foreseeable future. I do think that Congress and the FCC can do some jaw boning--some cheering from the sidelines. They can remind broadcasters forcefully of the commitments that they made in terms of the provision of HDTV in return for being loaned 6 MHz of prime real estate in the electro-magnetic spectrum. So, you know, I think there is a role there that might be accomplished. But I think primarily it is going to be market forces, and they're taking hold--maybe not as fast as some of us might have wished, but they are taking hold. Resistance is futile. We are going to move into this digital video revolution and HDTV is going to be an important part of it. Those who do not get behind it are going to get run over by it.</p>

<p><strong>Thank you very much Robert</strong></p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>June 26, 2005 02:18 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 133
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
 				AND entry_id <> 133
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_robert_graves_chairman_atsc_-june_2001.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
