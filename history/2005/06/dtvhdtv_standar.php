<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 73";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 73 AND placement_is_primary = 1";
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
	<meta name="keywords" content="high definition, full hdtv, production exchange, wide screen, definition television, HDTV, hdtv, television, digital, standard, definition, ITU, itu, world, World, high, broadcasting, line, today, new, should, quality, programs, technical, production" />
	<meta name="description" content="Today, we are passing through momentous times in the television industry - the revolutionary transition from analog to digital techniques and HDTV throughout the World. From the camera in the studio to the home display, television is being reinvented. This change is not merely an improvement; it is truly a reinvention of television technology.
" />
	<title>HDTV Magazine Archive &amp; History - DTV/HDTV Standards - The Route To World Communications - Dr. Joseph Flaherty - 1998</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/dtvhdtv_standards_-_the_route_to_world_communications_-_dr_joseph_flaherty_-_1998';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('DTV/HDTV Standards - The Route To World Communications - Dr. Joseph Flaherty - 1998'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/history/2005/06/dtvhdtv_standards_-_the_route_to_world_communications_-_dr_joseph_flaherty_-_1998.php";
		if ($author[img] != '' && 5 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">DTV/HDTV Standards - The Route To World Communications - Dr. Joseph Flaherty - 1998</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>June 12, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/06/dtvhdtv_standards_-_the_route_to_world_communications_-_dr_joseph_flaherty_-_1998.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/history/2005/06/dtvhdtv_standards_-_the_route_to_world_communications_-_dr_joseph_flaherty_-_1998.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/history/2005/06/dtvhdtv_standards_-_the_route_to_world_communications_-_dr_joseph_flaherty_-_1998.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/06/dtvhdtv_standards_-_the_route_to_world_communications_-_dr_joseph_flaherty_-_1998.php&amp;phase=2&amp;title=DTV%2FHDTV%20Standards%20-%20The%20Route%20To%20World%20Communications%20-%20Dr.%20Joseph%20Flaherty%20-%201998&amp;bodytext=Today%2C%20we%20are%20passing%20through%20momentous%20times%20in%20the%20television%20industry%20-%20the%20revolutionary%20transition%20from%20analog%20to%20digital%20techniques%20and%20HDTV%20throughout%20the%20World.%20From%20the%20camera%20in%20the%20studio%20to%20the%20home%20display%2C%20television%20is%20being%20reinvented.%20This%20change%20is%20not%20merely%20an%20improvement%3B%20it%20is%20truly%20a%20reinvention%20of%20television%20technology.%0A&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p>We bring you another in a series of addresses given by Dr. Joseph Flaherty of CBS. He is often referred to as the father of HDTV, at least in the U.S.A. He proved to be a tough competitor in the "standards wars" which raged throughout a very long standard setting process (9 years). _Dale Cripps<br />
  <br />
<strong><em>Presented in Moscow, Russia, November 4, 1998</em></strong></p>

<p>It is a great honor to be invited to address this HAT Symposium in Moscow. Sadly, I am unable to be here in person, because I am at CBS putting our first four digital HDTV stations in New York, Philadelphia, Los Angeles, and San Francisco on-the-air this week. I am happy, however, that my good friend, Henry Yushkiavitshus, is here and able to present my paper.</p>

<p>Today, we are passing through momentous times in the television industry - the revolutionary transition from analog to digital techniques and HDTV throughout the World. From the camera in the studio to the home display, television is being reinvented. This change is not merely an improvement; it is truly a reinvention of television technology.</p>

<p>The World's scientists and engineers built this new digital TV medium on the foundations of radio, television, and color TV. It was these same scientists and engineers who made today's television the World's most important communications medium. Now they have leapt beyond analog television and created 21" century television -- digital TV and HDTV.</p>

<p>As digital techniques reinvent television, so will they also reinvent the business of broadcasting. In the first decade of the new century, digital TV, and especially HDTV, will bring an entirely new viewing experience into the home, and analog television will be doomed worldwide. Digital television and HDTV will provide a diversity of services and a technical quality as different from today's television as was the introduction of color from the early mechanical television experiments of Baird in England and the work of Popov and Eisenstein in Russia.</p>

<p>Popov, who demonstrated wireless transmission in 1895 and used the technology in a practical form in the Russian navy in 1900, was a man larger than the times in which he lived. Few who have made such outstanding contributions to science and technology, could match the culture and spirit of Popov who refused to take out patents on his wireless invention, contending that the discovery should benefit mankind the world over.</p>

<p>America, too, is indebted to other Russians and Russian descendants who made their home in America, and gave us the benefit of their extraordinary abilities. Most notably was David Sarnoff the President of RCA who introduced 2oth century television and color TV to America, and Vladimir Zworykin, the inventor of the iconoscope and the developer of the kinescope, who, thus, enabled the development of modern electronic television.on June 15, 1936, David Sarnoff was about to open the first experimental television transmitter atop the Empire State building in New York. At that time he wrote:</p>

<p>"Of the future industries now visible on the horizon, television has gripped the public imagination most firmly. To bring television to the perfection needed for public service our work proceeds under high pressure at great cost.Such experiments call for.imagination of the highest order and for the courage to follow where that imagination leads. It is in this spirit that our laboratories and our scientists are diligently and devotedly engaged in a task of the highest service to humanity."</p>

<p><br />
From such work television was born, and today, that same genius and dedication in the service of mankind gave birth to digital HDTV. Yet there is much work to be done. You and I have a major role to play in advancing this digital HDTV technology and bringing it into widespread use,</p>

<p>Much can be learned of our tasks by a backward look. It was the philosopher Santayana who observed that:<br />
This statement on the adoption of a unique standard for production and exchange of high definition programs was reaffirmed at the 1998 meeting of the WBU-TC in Krakow, Poland on April 26, 1998. </p>

<p>"Those who cannot remember the past are condemned to repeat it."</p>

<p><br />
Our television past is not unblemished! Not unblemished, but not without some reason.</p>

<p>At television's birth, it was the era of vacuum tubes and of narrow bandwidth equipment. There were no VTRS, and no electronic way to record television signals. There were no geostationery satellites and no way to flash television signals around the World. In short, there was no international television.</p>

<p>Thus, television systems evolved as national or regional services, each with different standards -- standards that were frequently incompatible with one another. And so it was when color television emerged. The chaos continued despite the development of video recorders, international satellites, and early digital television equipment.</p>

<p>This Tower of Babel would have been made worse and ever more confusing were it not for another Russian engineer and the International Telecommunications Union, or ITU. It was through the monumental efforts of your Professor Mark Krivocheev, who, during the most difficult political times, guided the technical community of the ITU through hosts of technical issues vital to world communications. would single out but two of these, the ITU Recommendation 601-4 which led to practical digital component video tape standards worldwide and the ITU Recommendation ITU BT-709-2 that created a unique HDTV Common Image Format (CIF) for the production and exchange of HDTV programs worldwide.</p>

<p>This important International Telecommunications Union (ITU) Recommendation states in part:</p>

<p><br />
"Considering:</p>

<p><br />
that parameter values for HDTV production standards should have maximum commonality;</p>

<p><br />
that an active image format of 1920 pixels by 1080 lines provides square pixel sampling, with attendant advantages for interoperab4.1ity between various applications including digital television and computer imagery;</p>

<p><br />
Recommends:</p>

<p><br />
that for new implementations, particularly where interoperability with other applications is important, systems described in Part II (of document ITU-BT-709) are preferred."</p>

<p><br />
Part II describes the CIF system as:</p>

<p><br />
1080/60/2:1; 1080/60/1:1 and 1080/50/1:1; 1090/50/2:1 with 1920 samples per active line at an aspect ratio of 16:9.</p>

<p><br />
Thus, the ITU and its Radiocommunications Study Group 11, under the direction of Professor Krivocheev, has prepared the World for its first unique television standard for HDTV program production and program exchange.</p>

<p>The Technical Committee of the World Broadcasting Unions (WBU-TC), is composed of a membership from all the World's eight Broadcasting Unions, the:</p>

<p><br />
- Asia Pacific Broadcasting Union (ABU)<br />
- Arab States Broadcasting Union (ASBU)<br />
- Caribbean Broadcasting Union (CBU)<br />
- European Broadcasting Union (EBU)<br />
- International Association of Broadcasters (IAB)<br />
- North American National Broadcasters Association (NANBA)<br />
- Organisacion de Television Ibero-Americana (OTI)<br />
- Union des Radiodiffusions et Televisions Nationales d'Afrique (URTNA)</p>

<p><br />
This WBU-TC endorsed the ITU BT-709 Recommendation in 1997. Its statement declared:</p>

<p><br />
'The World Broadcast Unions Technical Committee strongly supports the adoption of a unique standard for program production and exchange of high definition television. This will lead to easier and better exchange of HDTV programs and lower equipment costs. It will accelerate the move to hiah definition throughout the World."</p>

<p><br />
"The WBU-TC recommends that the unique standard should be the socalled HD-CIF standard which has a 1080 line by 1920 sample by 50HZ/6OHz scanning system. This standard should be used for HDTV production equipment. Studio equipment manufacturers are being encouraged to set in motion the means to provide equipment to this standard."</p>

<p><br />
"The WBU-TC warmly recognizes the achievement of the ITU Study Group 11 in including the HO-CIF standard 4.-i its Recommendation BT-709-2. This recommendation should form the universally accepted parameter set for high definition television production."</p>

<p><br />
This statement on the adoption of a unique standard for production and exchange of high definition programs was reaffirmed at the 1998 meeting of the WBU-TC in Krakow, Poland on April 26, 1998.</p>

<p>The technical and political envirorment has never been better to ;chieve this worldwide digital HDTV standard, and it will never again be so favorable. Only you, 1, and broadcasters like us, around the World can make this standard a reality, and we simply must do so</p>

<p>instant worldwide communications by radio, telephone, fax, and the Internet are a reality. In this new Information Age, where more people watch TV than are literate, television cannot wallow in its incompatible past. 21st century television must be a worldwide phenomenon, available to all mankind without technical constraints!</p>

<p>If you remember but one thought from this lecture, this .is it. Adopt HDTV and adopt the ITU BT-709-2 HF-CIF format of 1080 lines, interlace and progressively scanned, by 1920 pixels-per-line, at a 16:9 aspect ratio in both the 50 and 60 Hertz frame rates! Russia, whose work in the ITU contributed so greatly to this standard, needs to now help lead the World into this unique HDTV format for the production and exchange of HDTV programs!</p>

<p>Lest anyone think that HDTV may be too good to serve the need, another look to the past is informative.</p>

<p>In 1974 the International Telecommunications Union, through its CCIR, began the study of HDTV by adopting a high definition Study Question stating:</p>

<p><br />
"Considering:: That high definition television systems will require a resolution which is approximately equivalent to that of 35mm film and corresponds to at least twice the horizontal and twice the vertical resolution of present television systems:"</p>

<p><br />
"The CCIR UNANIMOUSLY DECIDES that this question should be studied: What standards should be recommended for high definition television systems intended for broadcasting to the general public?'</p>

<p><br />
By 1977, the SMPTE Study Group on High Definition Television was formed, and in 1980 the SMPTE Journal published that group's HDTV report.</p>

<p>The report stated:</p>

<p><br />
"The appropriate standard of comparison (for HDTV) is the current and prospective optimum performance of the 35mm release print as projected on a wide screen.'</p>

<p><br />
The SMPTE HDTV Study Group concluded:</p>

<p><br />
"The appropriate line rate for HDTV is approximately 1100 lines-per-frame, and the frame rate should be 60 fields per second, interlaced 2-to-l...".</p>

<p><br />
I Indeed, rather than being better than necessary, high definition was to finally put television resolution on a par with cinema quality.  <br />
ndeed, rather than being better than necessary, high definition was to finally put television resolution on a par with cinema quality. Today, this has been achieved! HDTV equals the quality of 35mm film. Thus, our HDTV is not too good, it's simply catching up -- catching up to a quality most widely accepted by the creative community and by the World's viewers. Through full HDTV, and only through full HDTV, television will finally achieve its technical maturity. </p>

<p>if HDTV is not too good, then is it good enough?</p>

<p>Today HDTV is better than the display devices. These displays are the 'Limiting quality factor. While improvements are being made by the month, as of today, no display has achieved the full quality potential of the HDTV system. Recently, Fujitsu announced a new 42-inch, 16:9 wide screen, flat panel display with 1024 pixels per line, approaching full HDTV quality. This development in displays is as it should be! The HDTV system needs to provide the headroom for improvement and the challenge for further near term development. No new standard should ever be fully encompassed by the existing state-of-the-art, nor should it be so futuristic as not to have its potential achievable in a foreseeable time. The ITU and WBU-TC HDTV standard is beyond the present quality of displays, but not beyond the scope of their rapid development.</p>

<p>In considering the importance of HDTV broadcasting, it is vital to understand that wide screen high definition is not just pretty pictures for today's small screen TV sets. Rather, it is a wholly new digital platform that will support the larger and vastly improved displays now in commercial development.</p>

<p>On November 21st, 1985, with apologies to Arthur C. Clarke for plagiarizing his title, I delivered a lecture entitled, -2001, A Broadcasting Odyssey". In that lecture 1 said:</p>

<p><br />
"As we evaluate tomorrow's TV and HDTV and plan for its implementation, we must bear in mind that today's standard of service enjoyed by the viewer will not be his level of expectation tomorrow. Good enough is no longer perfect, and may become wholly unsatisfactory."</p>

<p><br />
"Quality is a moving target, both in programs and in technology. Our judgements as to the future must not be based on today's performance, nor on minor improvements thereto."</p>

<p><br />
Today, twenty five years after NHK began its pioneering work, high definition as defined by the ITU and the WBU-TC is, and will be, a system employing at least 1000 active lines, interlace or progressively scanned. Lesser formats may be improvements over present TV, but are not high definition!</p>

<p>The digital era has begun, and every broadcaster will feel the impact of this digital revolution. Digital technology will radically change television's means of communication, its quality, its flexibility, the conduct of the business, the scope and effectiveness of the service, and every aspect of the medium. While some may still consider this historic invention unfortunate, its application is, at the same time, inevitable.</p>

<p>CBS fully supports the ITU and WBU-TC digital standard, and plans to use the 1080 line, 1920 pixel, wide screen 16:9 aspect ratio, 60 Hz format, interlaced scanned for electronically produced programs and progressively scanned for 24 and 30 frame film programs. This format is in full compliance with the ITU BT-709-2 Recommendation and the WBUTC unique HDTV production standard.</p>

<p>It is likely that HDTV will become the medium of choice by producers, programmers, the distribution media, and by the viewing public. Major cable and DBS programmers have declared their intent to provide HDTV program services, and this will provide another incentive for the public to invest in digital TV and HDTV receivers. Additionally, regardless of the transmission format used, programming to be saleable in the international market will need to be produced in full HDTV in accordance with the ITU Recommendation BT-709-2.</p>

<p>In the United States, following nine years of study, debate, design, construction, testing and rulemaking, the FCC digital TV and HDTV transmission standards and service rules were set on April 3, 1997. The ATSC - the Advanced Television Systems Committee - standard supports a hierarchy of open, non-proprietary, scanning formats with full RDTV at the highest level, and includes Standard definition Digital TV, or SDTV, multi-program compressed SDTV, a computer VGA format, and a large digital data transmission capacity.</p>

<p>All the digital TV and HDTV receivers to be built in America will decode all of the ATSC transmission formats including full HDTV. Thus, American broadcasters will be able to use any, or all, of the ATSC scanning formats. Their digital bouquet will extend from SDTV to full HDTV.</p>

<p>As it applies to the digital decisions that you have to make, I would suggest that those who believe, or want to believe, that the viewers will never want wide screen HDTV when it is offered, are taking a 'bet-the-business" gamble. More of the same standard definition 525 or 625 line digital transmissions will not capture the market, will not sell digital receivers in quantity, and will give way to HDTV worldwide. Standard definition and multiplexed standard definition services alone will not be sufficient to compete effectively for tomorrow's viewers. European broadcasters, like their American counterparts, must have the ability to deliver full HDTV to their viewers, and those viewers must be able to receive HDTV programs as readily as they will receive digital 625 line programs.</p>

<p>Both the American and European technical communities, under the leadership of the ITU, the ATSC, and the DVB, deserve the highest praise for achieving a single worldwide common image format of 1080 lines and 1920 pixels-per-line with an aspect ratio of 16:9, and for devising two, and only two, digital TV transmission systems able to deliver standard TV and HDTV 4 n the present restricted bandwidth terrestrial TV channels.</p>

<p>While it was a torturous and time consuming process, plagued with politics and inept bureaucracies with their visions of years of evolution, sneaking up on digital TV and HDTV, our technical communities prevailed. Digital TV and HDTV are realities today, and tne World is the better for it.</p>

<p>Europe survived the MAC era; abandoned the HD-MAC proposal; launched the digital DVB project; came to the 1080 line, 1920 pixel-per-line, 16:9 aspect ratio, HD-CIF standard for program production and exchange; and Europe devised the DVB terrestrial digital standard to support HDTV as well as standard 625 line television.</p>

<p>I would suggest that those who believe, or want to believe, that the viewers will never want wide screen HDTV when it is offered, are taking a 'bet-the-business" gamble. <br />
At the present time, however, the Western European consumer equipment industry is still ignoring HDTV in its digital receiver plans. This, in my opinion, is a major mistake. European broadcasters, with the ability to broadcast HDTV through the DVB or ATSC systems, will be prevented from doing so by the inability of European digital receivers to decode the HDTV signal. Yet in America, it is the same European consumer electronics companies, Thomson and Philips, who are building all-mode digital receivers, able to decode and display all formats, including HDTV.</p>

<p>European digital receivers sold without the ability to decode and display HDTV, will either go "black" or will require new, and expensive converters, when, and not if, HDTV broadcasting becomes a reality in Europe. Programs of international interest, especially important sporting events, such as the Olympics and the World Cup, are already produced in HDTV, and they have produced a most enthusiastic reaction from viewers.</p>

<p>The DVB and ATSC transmission systems accommodate HDTV. Terrestrial broadcasters in Russia must not be denied the ability to broadcast HDTV to their viewers just because the receivers fail to include HDTV decoders.</p>

<p>In short, receivers for the new digital HDTV program service cannot be planned and implemented solely by consumer equipment manufacturers who believe that ',-here will be no consumer demand for high definition.</p>

<p>Before adopting any DTV transmission system-DVB or ATSC, Russia and its broadcasters need to insist that receivers with HDTV decoders be readily available at practical prices from the outset.</p>

<p>Meanwhile, the digital TV and HDTV service is rolling out in America, with stations in the top ten television markets beginning their digital TV and HDTV broadcast service this week. Other markets will begin DTV services in May, 1999. By 2003, the FCC requires that all 1650 U.S. television stations shall have made the transition to digital broadcasting. Further, it is planned that all analog NTSC broadcasting will cease in 2006.</p>

<p>As I said at the beginning of this lecture, the digital revolution is a momentous event in the history of our industry. As the English writer H.G. Wells put it:</p>

<p><br />
"The past is but the beginning of a beginning, and all that is and has been, is but the twilight of the dawn."</p>

<p><br />
But change is the nature of life and often irresistible. As Victor Hugo observed:</p>

<p><br />
"An invasion of armies can be resisted; but not an idea whose time has come."</p>

<p><br />
It's time that, together, we lead the World into digital TV and HDTV.</p>

<p>_____________________________________________<br />
About Joseph Flaherty</p>

<p><em>Joseph Flaherty is senior vice president of technology at CBS. In this position, he advises CBS management on issues and strategies related to broadcast technology, and represents CBS nationally and internationally with major manufacturers and on government and industry committees and organizations. Flaherty joined CBS in 1957, and has directed the Engineering and Development Department since 1967—first as general manager, then, since 1977, as vice president and general manager. During his career, he has received many prestigious broadcast industry awards, including several Emmys for technical achievement; the David Sarnoff Gold Medal for progress in television engineering; the NAB Engineering Award; the Progress Medal of the SMPTE; and the International Montreux Achievement Gold Medal. Flaherty also received France's Chevalier de l'Ordre des Arts et des Lettres, and in 1985 was awarded France's highest decoration, the Chevalier de l'[Ordre National de la Legion d'Honneur, by French President François Mitterand. He is a Fellow of the British Institution of Electrical Engineers; the British Royal Television Society; and SMPTE. Flaherty holds a degree in physics and an honorary doctorate of science from Rockhurst College in Kansas City, Missouri.</em><br />
 <br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>June 12, 2005 10:36 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 73
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
 				AND entry_id <> 73
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/2005/06/dtvhdtv_standards_-_the_route_to_world_communications_-_dr_joseph_flaherty_-_1998.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
