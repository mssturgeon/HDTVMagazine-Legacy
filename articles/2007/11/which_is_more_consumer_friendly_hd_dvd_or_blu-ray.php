<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 800";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 800 AND placement_is_primary = 1";
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
	<meta name="keywords" content="blu ray, dvd blu, high definition, dvd players, standard dvd, DVD, dvd, Blu, blu, ray, players, support, standard, consumer, buy, format, high, player, features, both, definition, any, formats, titles, DVDs" />
	<meta name="description" content="No, this is not the standard HD DVD vs. Blu-ray article that you may be used to reading. I am not declaring a &amp;quot;winner&amp;quot; because I think we are at a point now where neither camp is going away. Instead, this article explains which format I believe is the better choice for the consumer (you) this holiday season. Could that change a year from now? Sure, but I want to help you decide what to buy this year.

This article is not written in an attempt to convince anyone who has already made an investment one way or the other, for that is an almost impossible feat. It was written for those that are still &amp;quot;on the fence&amp;quot;, as they say. It is for those who are either undecided, or are waiting to see which one will come out ahead (or which will be first to waive the white flag). It's time to hop down off of that fence." />
	<title>HDTV Magazine Articles - Which is More Consumer Friendly: HD DVD or Blu-ray?</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/which_is_more_consumer_friendly_hd_dvd_or_blu-ray';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Which is More Consumer Friendly: HD DVD or Blu-ray?'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2007/11/which_is_more_consumer_friendly_hd_dvd_or_blu-ray.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Which is More Consumer Friendly: HD DVD or Blu-ray?</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>November 26, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=HD DVD & Blu-ray">HD DVD & Blu-ray</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2007/11/which_is_more_consumer_friendly_hd_dvd_or_blu-ray.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2007/11/which_is_more_consumer_friendly_hd_dvd_or_blu-ray.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2007/11/which_is_more_consumer_friendly_hd_dvd_or_blu-ray.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2007/11/which_is_more_consumer_friendly_hd_dvd_or_blu-ray.php&amp;phase=2&amp;title=Which%20is%20More%20Consumer%20Friendly%3A%20HD%20DVD%20or%20Blu-ray%3F&amp;bodytext=No%2C%20this%20is%20not%20the%20standard%20HD%20DVD%20vs.%20Blu-ray%20article%20that%20you%20may%20be%20used%20to%20reading.%20I%20am%20not%20declaring%20a%20%26quot%3Bwinner%26quot%3B%20because%20I%20think%20we%20are%20at%20a%20point%20now%20where%20neither%20camp%20is%20going%20away.%20Instead%2C%20this%20article%20explains%20which%20format%20I%20believe%20is%20the%20better%20choice%20for%20the%20consumer%20%28you%29%20this%20holiday%20season.%20Could%20that%20change%20a%20year%20from%20now%3F%20Sure%2C%20but%20I%20want%20to%20help%20you%20decide%20what%20to%20buy%20this%20year.%0A%0AThis%20article%20is%20not%20written%20in%20an%20attempt%20to%20convince%20anyone%20who%20has%20already%20made%20an%20investment%20one%20way%20or%20the%20other%2C%20for%20that%20is%20an%20almost%20impossible%20feat.%20It%20was%20written%20for%20those%20that%20are%20still%20%26quot%3Bon%20the%20fence%26quot%3B%2C%20as%20they%20say.%20It%20is%20for%20those%20who%20are%20either%20undecided%2C%20or%20are%20waiting%20to%20see%20which%20one%20will%20come%20out%20ahead%20%28or%20which%20will%20be%20first%20to%20waive%20the%20white%20flag%29.%20It%27s%20time%20to%20hop%20down%20off%20of%20that%20fence.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p>No, this is not the standard HD DVD vs. Blu-ray article that you may be used to reading. I am not declaring a &quot;winner&quot; because I think we are at a point now where neither camp is going away. Instead, this article explains which format I believe is the better choice for the consumer (you) this holiday season. Could that change a year from now? Sure, but I want to help you decide what to buy this year.</p>  <p>This article is not written in an attempt to convince anyone who has already made an investment one way or the other, for that is an almost impossible feat. It was written for those that are still &quot;on the fence&quot;, as they say. It is for those who are either undecided, or are waiting to see which one will come out ahead (or which will be first to waive the white flag). It's time to hop down off of that fence.</p>  <h2>Why Choose Either Format?</h2>  <p>First let's take a look at the benefits that these formats have over standard DVD and even HDTV.</p>  <ul>   <li><strong>Increased resolution</strong>. Both HD DVD and Blu-ray support video at 1080 lines of <a href="/glossary.php#Vertical+Resolution" target="_blank">vertical resolution</a>, compared to standard DVDs' 480. The <a href="/glossary.php#Horizontal+Resolution" target="_blank">horizontal resolution</a> is also greater at 1920 lines vs standard DVDs' 720. In total, high definition DVD will display 2 million pixels on the screen at any given time, compared to about 350,000 with standard DVD. That's 6x the resolution in the same area. </li>    <li><strong>Higher bitrate.</strong> Resolution is the easy one to put your finger on, but the secret to better picture quality is in the bitrate, or amount of information sent to your TV each second. Standard DVD is limited to about 11Mbit/s (Megabits per second) while cable, satellite and broadcast (over-the-air) can be delivered at up to 19Mbit/s (although 12-13Mbit/s is more common). Both HD DVD and Blu-ray can support bitrates in excess of 36Mbit/s. The result is a much more detailed picture, even during fast motion scenes that can wreak havoc on the over-compressed signals of cable &amp; satellite. </li>    <li><strong>Better audio.</strong> Next on the list has to be audio. Both HD DVD and Blu-ray support more advanced audio codecs than standard DVD, including the <a href="/glossary.php#Lossless" target="_blank">lossless</a> <a href="/glossary.php#Dolby+TrueHD" target="_blank">Dolby TrueHD</a> and <a href="/glossary.php#DTS-HD+%28%2B%2B%2C+and+Master+Audio%29" target="_blank">DTS-HD Master Audio</a>. Lossless codecs provide sound exactly as the content creator intended, with nothing lost due to compression. </li>    <li><strong>Features and Interactivity (Extras)</strong>. Standard DVD has some basic interactivity. I've seen some of my kids' DVDs that include rudimentary games, etc. But with high definition DVD, a whole new world opens up. I'll gloss over the gory details and just say that with these next generation formats it will be more like browsing the web than just clicking the down arrow twice and Play. Another big difference is that these next generation players have secondary video processors. This in essence gives you the ability to toggle on a picture-in-picture display while watching the movie. This secondary video stream can include any number of features like director's commentary, out-takes, unedited footage ... the possibilities are nearly endless. </li>    <li><strong>What about download?</strong> Most download services available today don't support high definition video. Those that do have HD available don't &quot;sell&quot; the content, they &quot;rent&quot; it. And until you are able to &quot;buy&quot; a digital copy to store on your computer and play back to any of your TVs at your leisure, I can't recommend it as an adequate next step for home movie viewing. Another thing to consider is that the hard drive space required to store these downloadable movies in the same quality as HD DVD and Blu-ray would cost between $10 and $15 per movie. When all is said and done, it would cost you almost twice current HD DVD and Blu-ray prices to buy movies via download.       <p></p>      <p>In my searching, I did find one high definition movie download service that allowed you to <strong>buy </strong>movies. It's called <a href="http://www.vudu.com" target="_blank">Vudu</a>, and they have a selection similar to most movie stores. With Vudu, you first buy a set top box for $399.99. This set top box can store up to 100 hours of purchased movies, which can be purchased for $20 - $25 (for new releases). Also, in order to have instant viewing of movies, they recommend an internet connection speed of 2-3 Mbit/s. This is a definite step in the right direction, but quite a bit more expensive than HD DVD and Blu-ray, and not very practical if you plan on having a large collection of movies.</p>   </li>    <li><strong>What about combination players?</strong> A good universal option. LG has one out this year that is fully compliant with both specifications and Samsung is supposed to have one out this year as well. The problem is that they are much more expensive ($999 MSRP), which is more than you would pay if you bought both HD DVD and Blu-ray players. I therefore cannot recommend dual format players to consumers quite yet. </li> </ul>  <p>Now that I've given you a few reasons to consider investing in these formats, let's hear what consumers have to say who have already made the leap to high definition DVD:</p>  <ul>   <li>According to our <a href="http://www.hdtvmagazine.com/studies/index.php" target="_blank">Fall 2007 HDTV Study</a>, more than 42% of respondents have made the investment<sup>1</sup> </li>    <li>90% of consumers who have invested are &quot;highly satisfied&quot; with their purchase<sup>2</sup> </li>    <li>Those that have a high definition player plan to replace 25% of their DVD library with their high definition counterparts<sup>2</sup> </li>    <li>Another figure from our <a href="http://www.hdtvmagazine.com/studies/index.php" target="_blank">Fall Study</a> shows that for respondents that are upconverting their standard DVD content, only about 15% think it's &quot;Good enough.&quot; </li> </ul>  <p>If none of that convinces you that you need to have one of these formats in your living room, you can stop reading. If you want to know why I think HD DVD is the best option, read on dear friend.</p>  <h2>Why HD DVD?</h2>  <p>My reasoning below is not based on which format has higher bitrate or more capacity, nor is it based on which one has more studios in its pocket, or more titles on the shelves ... as all those are about equal when you're looking at the screen. The reason I am recommending HD DVD is for the benefit to the consumer ... you.</p>  <ul>   <li><strong>Standardization</strong> - No matter what player you buy, it will play all HD DVD titles with full features. Since HD DVD players began shipping, they have had one <strong>standard</strong> set of requirements for their players. 100% of the HD DVD players on the market today must support a minimum set of features. I've listed below some of the features that are guaranteed to be on <strong>all</strong> HD DVD players, but might not be on all Blu-ray players:       <ul>       <li>Support of <a href="/glossary.php#Dolby+Digital+Plus" target="_blank">Dolby Digital Plus</a> </li>        <li>Support of Dolby TrueHD </li>        <li>2nd video decoder </li>        <li>2nd audio decoder </li>        <li>Internet support (network connection) </li>        <li>Region Free </li>     </ul>   </li>    <li><strong>Less Copy Protection</strong> - The HD DVD specification requires no copyright enforcement. The Advanced Access Content System (AACS) is mandatory for Blu-ray and optional for HD DVD, although many studios are using it. Blu-ray also includes additional content protection schemes such as BD+ and ROM-Mark watermarking. Each of these layers of protection add a level of complexity to the players and increased production and licensing costs of both players and media. It has also been <a href="http://www.highdefdigest.com/news/show/1035" target="_blank">reported by High-Def Digest</a> that additional copy protection may result in more lengthy load times. </li>    <li><strong>Features &amp; Interactivity</strong> - I've never been one to make use of the &quot;Extras&quot; on standard DVDs. Well, maybe the deleted scenes and out-takes, but that's it. With HD DVD, I find myself actually looking at these features before I buy a movie to see if there's anything original. This is a highly subjective point, but my argument here is that the consumer benefits by having these features and interactivity available to them, should they happen to enjoy them. </li>    <li><strong>Internet Updating</strong> - To date, the <strong>only</strong> Blu-ray player that can update its software/firmware via network connection is the Sony Playstation 3.<p class="editorial"><b>Editorial Note:</b> It was pointed out to me that the Samsung models BDP-1400 and BDP-1200 can also update via network connection. I apologize for the oversight (11/27, 12:01am EST)</p> Other Blu-ray players require you to either order a DVD with the update or download and burn your own update DVD. Sony's BDP-S300 recently had a firmware release and I was <a href="/forum/viewtopic.php?t=8693" target="_blank">attempting to help</a> someone on our <a href="/forum/index.php" target="_blank">forums</a> download and install it. I checked the page and there were about 25 steps to follow to get it updated, along with another dozen or so &quot;Important Notes&quot; of things to make sure you do (or not do) when updating ... not very consumer friendly. </li>    <li><strong>Better Price</strong> - I mention this last because I want to stress that there are a lot of other reasons to choose HD DVD than just the price, but it can't be ignored. With street prices of Blu-ray players around <a href="/equipment/model.php?man=Sony&amp;model=BDPS300" target="_blank">$357</a> ($499 MSRP) and street prices of HD DVD players around <a href="/equipment/model.php?man=Toshiba&amp;model=HDA2" target="_blank">$169</a> ($299 MSRP) ... it's just icing on the cake. HD DVD players have even sold <a href="/news/2007/11/toshiba_hd-a2_hd_dvd_player_drops_below_100.php" target="_blank">as low as $99</a> this month in various sales at retailers like Wal-mart and Best Buy. </li> </ul>  <h2>From the That's-Not-Quite-True Department</h2>  <p>There are a lot of &quot;facts&quot; and figures that get thrown around whenever someone sticks their neck out in favor of one format or another. In this case, those that have already invested in Blu-ray may throw up some strongly-worded arguments to my recommendation. Let me attempt to disarm some of them by stating below some things you're likely to hear/read, and why they're &quot;Not Quite True&quot;:</p>  <ul>   <li><strong>Blu-ray has more studio support than HD DVD</strong> - Of the six big movie studios in North America, three of them are Blu-ray exclusive, two of them are HD DVD exclusive, and one (Warner Bros) is producing in both formats. But what we're really talking about here is the number of titles available, not the number of studios supporting it.&#160; According to Wikipedia, as of October 31st, 2007, 332 titles are available in the US on Blu-ray and 328 on HD DVD<sup>3</sup>. And as of November 6th, 2007, Netflix has 378 Blu-ray titles and 345 HD DVD titles. Sounds about even. That being said, you also must take into account whether there are titles available from only one format that you must have. That alone can make all other advantages of one format over the other irrelevant.</li>    <li><strong>Blu-ray has more manufacturer support than HD DVD</strong> - This one is true, but I include it for what it means. Usually, more manufacturers mean more competition, which leads to lower prices. HD DVD is far less expensive than Blu-ray, so what good are all those manufacturers doing for the Blu-ray format? </li>    <li><strong>Blu-ray has higher capacity/bitrate than HD</strong> <strong>DVD </strong>- I'll give you that. Blu-ray players currently support discs with a capacity of up to 50GB while HD DVD is limited to 30GB (although 51GB HD DVDs were recently approved). Also, Blu-ray bitrates can run to 54Mbit/s while HD DVD is limited to 36Mbit/s. That being said, show me how that makes a difference with a side-by-side comparison of picture quality. I doubt it's $200 better from any consumer's point of view, and that is the guiding principle of my recommendation. </li>    <li><strong>Blu-ray can do all that added feature and interactivity stuff too</strong> - Yes, but only certain players can support it, and only certain disks have it. It should not be up to the consumer to keep track of whether a player can take advantage of a specific feature they see on the back of the package ... they should <strong>know</strong> it's supported regardless of their player. </li>    <li><strong>Target went Blu-ray exclusive, the end is near</strong> - Actually, Target just bought an end-cap. A quick check in their online store shows that they are selling both the <a href="http://www.target.com/Toshiba-HD-DVD-Player-HDA30/dp/B000U6AHYS/sr=1-2/qid=1195622207/ref=sr_1_2/601-5215395-5382501?ie=UTF8&amp;index=target&amp;rh=k%3Ahd%20dvd&amp;page=1" target="_blank">Toshiba HDA30</a> and the new <a href="http://www.target.com/Venturer-HD-DVD-Player-SHD7000/dp/B000W7O43U/sr=1-3/qid=1195622207/ref=sr_1_3/601-5215395-5382501?ie=UTF8&amp;index=target&amp;rh=k%3Ahd%20dvd&amp;page=1" target="_blank">Venturer HD DVD player</a>. Also, since when is Target a bellwether in retail consumer electronics? </li>    <li><strong>Blockbuster went Blu-ray exclusive, the end is near</strong> - Again, Blockbuster's announcement was not quite that far reaching. The Blu-ray exclusivity is limited to about 87% of their stores, and they are still making HD DVD available via online rental. Also, Blockbuster later issued a <a href="http://blockbuster.mediaroom.com/index.php?s=press_releases&amp;item=727" target="_blank">press release</a> that indicated that they would continue to stock more HD DVDs in their stores as demand increases. </li>    <li><strong>Paramount got paid $150 million for HD DVD support</strong> - True, but let's not pretend money is not changing hands all over the place in this contest. It's business, and that's how business is done. I hardly think this is a reason to dislike HD DVD. </li>    <li><strong>HD DVDs scratch more easily because they don't have the hard coating that Blu-ray has</strong> - Blu-ray does utilize a hard coating on the surface of their media that resists scratches. This had to be done because the data layer in a Blu-ray disc is so much closer to the surface than in HD DVD. Regardless, this does not mean that HD DVD's are more susceptible to scratching and damage. I contacted a popular online rental company and asked them about damage reports and disc durability of the two formats. According to them, there is no appreciable difference in the number of returns for either format. </li> </ul>  <h2>Conclusion</h2>  <p>I'll restate what I've said above, but without all the detail. Here is why I believe HD DVD is the best choice for the consumer this holiday season:</p>  <ul>   <li>All HD DVD players are standard, and you can feel confident that you will not have any issues playing back any HD DVD title on any HD DVD player. </li>    <li>Since all HD DVD players are internet-capable, any updates that you may have to do to your player can be done without complicated downloads, DVD burns and upgrade routines. </li>    <li>HD DVD is region-free, meaning that no matter in which country you buy your HD DVD, it will play in your player. </li>    <li>HD DVD media has less copy protection. Less copy protection means faster disc load times. </li>    <li>Sale prices for HD DVD players this holiday are around $100-$200, much more consumer- (and wallet-) friendly than sale prices for Blu-ray players, which are around $400. </li> </ul>  <p>I expect (dare I say hope) that this will generate a lot of conversation. It remains to be seen how much of it will be in opposition to the recommendation I'm making. I will close this article with a recent quote that I came across that seems to be quite apropos:</p>  <blockquote>   <p>Human beings are perhaps never more frightening than when they are convinced beyond doubt that they are right.      <br />      <br />- Laurens van der Post, explorer and writer (1906-1996)</p> </blockquote>  <p>With that said, I welcome your comments.</p>  <p>&#160;</p>  <p><font size="1"><sup>1</sup> Source: HDTV Magazine's </font><a href="http://www.hdtvmagazine.com/studies/index.php" target="_blank"><font size="1">Fall 2007 HDTV Study</font></a><font size="1">. The Study is still in-progress, but the data above is based on 1600+ respondents.</font></p>  <p><font size="1"><sup>2</sup> Source: The NPD Group, a leading retail market research firm</font></p>  <p><font size="1"><sup>3</sup> Source: Wikipedia article: </font><a href="http://en.wikipedia.org/wiki/Comparison_of_high_definition_optical_disc_formats" target="_blank"><font size="1">Comparison of high definition optical disc formats</font></a></p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>November 26, 2007 05:58 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 800
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
 				AND entry_id <> 800
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2007/11/which_is_more_consumer_friendly_hd_dvd_or_blu-ray.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
