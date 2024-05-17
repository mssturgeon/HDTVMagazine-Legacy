<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 548";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 548 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# Set variables based on entry type. NOTE: Podcasts has its own entry template, so it is not included amongst the choices below.
	switch (7) {
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
	<meta name="keywords" content="entertainment network, high quality, powerline networking, bittorrent entertainment, network bittorrent, bittorrent, BitTorrent, entertainment, content, upa, technology, UPA, network, high, quality, home, powerline, Powerline, digital, iptv, products, IPTV, networking, our, new" />
	<meta name="description" content="The following announcement is clearly a sign of the times. The Internet is increasingly used as a delivery platform for entertainment television. Traditional appointment viewing and its business model is being challenged with every capacity added to the net. There are, of course, times when appointment viewing remains victorious--sports and news for example--but a majority of our free time is spent with programming that has no particular time imperative. The announcement below studiously avoids the mentions of HDTV--the big bandwidth hog of the day--but the fact that BitTorent can boasts of 135 million &quot;clients&quot;, whom they call the &quot;BitTorrent generation&quot;, means by default that they are also the &quot;HDTV generation&quot;. You can trust that this “bit” generation will grow weary with anything less than HD and soon a demand for its quality will arise. Will we pay for the bandwidth? We have done so every time we are treated with its reward. _Dale



&lt;strong&gt;BitTorrent, Inc. Launches the BitTorrent Entertainment Network&lt;/strong&gt;

 

MGM is Latest Hollywood Studio to Join the BitTorrent Network, which Offers Thousands of Movies, TV Shows, Music and Games

 

San Francisco, CA - February 26, 2007 - BitTorrent, the global standard for delivering high-quality media over the Internet, today announced the launch of the BitTorrent Entertainment Network at BitTorrent.com. The new network features the most comprehensive library of downloadable digital entertainment ever amassed on the Web, including content from 20th Century Fox, Lions Gate, MTV Networks, Paramount Pictures, Warner Bros. Home Entertainment and BitTorrent's newest partner, Metro-Goldwyn-Mayer Studios, Inc. (MGM). The BitTorrent community will have the flexibility to rent movies, purchase television shows and music videos, and even publish and share their own high-quality content to be displayed alongside titles from the world's largest studios. " />
	<title>HDTV Magazine Bulletins - BitTorrent, Sign of the Times - BitTorrent, Inc. Inc. Launches the BitTorrent Entertainment Network</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/bittorrent_sign_of_the_times_-_bittorrent_inc_inc_launches_the_bittorrent_entertainment_network';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('BitTorrent, Sign of the Times - BitTorrent, Inc. Inc. Launches the BitTorrent Entertainment Network'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2007/02/bittorrent_sign_of_the_times_-_bittorrent_inc_inc_launches_the_bittorrent_entertainment_network.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">BitTorrent, Sign of the Times - BitTorrent, Inc. Inc. Launches the BitTorrent Entertainment Network</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>February 26, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Programming">Programming</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2007/02/bittorrent_sign_of_the_times_-_bittorrent_inc_inc_launches_the_bittorrent_entertainment_network.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2007/02/bittorrent_sign_of_the_times_-_bittorrent_inc_inc_launches_the_bittorrent_entertainment_network.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2007/02/bittorrent_sign_of_the_times_-_bittorrent_inc_inc_launches_the_bittorrent_entertainment_network.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2007/02/bittorrent_sign_of_the_times_-_bittorrent_inc_inc_launches_the_bittorrent_entertainment_network.php&amp;phase=2&amp;title=BitTorrent%2C%20Sign%20of%20the%20Times%20-%20BitTorrent%2C%20Inc.%20Inc.%20Launches%20the%20BitTorrent%20Entertainment%20Network&amp;bodytext=The%20following%20announcement%20is%20clearly%20a%20sign%20of%20the%20times.%20The%20Internet%20is%20increasingly%20used%20as%20a%20delivery%20platform%20for%20entertainment%20television.%20Traditional%20appointment%20viewing%20and%20its%20business%20model%20is%20being%20challenged%20with%20every%20capacity%20added%20to%20the%20net.%20There%20are%2C%20of%20course%2C%20times%20when%20appointment%20viewing%20remains%20victorious--sports%20and%20news%20for%20example--but%20a%20majority%20of%20our%20free%20time%20is%20spent%20with%20programming%20that%20has%20no%20particular%20time%20imperative.%20The%20announcement%20below%20studiously%20avoids%20the%20mentions%20of%20HDTV--the%20big%20bandwidth%20hog%20of%20the%20day--but%20the%20fact%20that%20BitTorent%20can%20boasts%20of%20135%20million%20%22clients%22%2C%20whom%20they%20call%20the%20%22BitTorrent%20generation%22%2C%20means%20by%20default%20that%20they%20are%20also%20the%20%22HDTV%20generation%22.%20You%20can%20trust%20that%20this%20%E2%80%9Cbit%E2%80%9D%20generation%20will%20grow%20weary%20with%20anything%20less%20than%20HD%20and%20soon%20a%20demand%20for%20its%20quality%20will%20arise.%20Will%20we%20pay%20for%20the%20bandwidth%3F%20We%20have%20done%20so%20every%20time%20we%20are%20treated%20with%20its%20reward.%20_Dale%0A%0A%0A%0A%3Cstrong%3EBitTorrent%2C%20Inc.%20Launches%20the%20BitTorrent%20Entertainment%20Network%3C%2Fstrong%3E%0A%0A%20%0A%0AMGM%20is%20Latest%20Hollywood%20Studio%20to%20Join%20the%20BitTorrent%20Network%2C%20which%20Offers%20Thousands%20of%20Movies%2C%20TV%20Shows%2C%20Music%20and%20Games%0A%0A%20%0A%0ASan%20Francisco%2C%20CA%20-%20February%2026%2C%202007%20-%20BitTorrent%2C%20the%20global%20standard%20for%20delivering%20high-quality%20media%20over%20the%20Internet%2C%20today%20announced%20the%20launch%20of%20the%20BitTorrent%20Entertainment%20Network%20at%20BitTorrent.com.%20The%20new%20network%20features%20the%20most%20comprehensive%20library%20of%20downloadable%20digital%20entertainment%20ever%20amassed%20on%20the%20Web%2C%20including%20content%20from%2020th%20Century%20Fox%2C%20Lions%20Gate%2C%20MTV%20Networks%2C%20Paramount%20Pictures%2C%20Warner%20Bros.%20Home%20Entertainment%20and%20BitTorrent%27s%20newest%20partner%2C%20Metro-Goldwyn-Mayer%20Studios%2C%20Inc.%20%28MGM%29.%20The%20BitTorrent%20community%20will%20have%20the%20flexibility%20to%20rent%20movies%2C%20purchase%20television%20shows%20and%20music%20videos%2C%20and%20even%20publish%20and%20share%20their%20own%20high-quality%20content%20to%20be%20displayed%20alongside%20titles%20from%20the%20world%27s%20largest%20studios.%20&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><em>The following announcement is clearly a sign of the times. The Internet is increasingly used as a delivery platform for entertainment television. Traditional appointment viewing and its business model is being challenged with every capacity added to the net. There are, of course, times when appointment viewing remains victorious--sports and news for example--but a majority of our free time is spent with programming that has no particular time imperative. The announcement below studiously avoids the mentions of HDTV--the big bandwidth hog of the day--but the fact that BitTorent can boasts of 135 million "clients", whom they call the "BitTorrent generation", means by default that they are also the "HDTV generation". You can trust that this "bit" generation will grow weary with anything less than HD and soon a demand for its quality will arise. Will we pay for the bandwidth? We have done so every time we are treated with its reward. _Dale</em></p>

<p></p>

<p><strong>BitTorrent, Inc. Launches the BitTorrent Entertainment Network</strong></p>

<p> </p>

<p>MGM is Latest Hollywood Studio to Join the BitTorrent Network, which Offers Thousands of Movies, TV Shows, Music and Games</p>

<p> </p>

<p>San Francisco, CA - February 26, 2007 - BitTorrent, the global standard for delivering high-quality media over the Internet, today announced the launch of the BitTorrent Entertainment Network at BitTorrent.com. The new network features the most comprehensive library of downloadable digital entertainment ever amassed on the Web, including content from 20th Century Fox, Lions Gate, MTV Networks, Paramount Pictures, Warner Bros. Home Entertainment and BitTorrent's newest partner, Metro-Goldwyn-Mayer Studios, Inc. (MGM). The BitTorrent community will have the flexibility to rent movies, purchase television shows and music videos, and even publish and share their own high-quality content to be displayed alongside titles from the world's largest studios. </p>

<p> </p>

<p>"The BitTorrent Entertainment Network is created by and for the BitTorrent Generation, which has a vast appetite for high-quality, on-demand entertainment," said Ashwin Navin, President and Co-founder of BitTorrent. "BitTorrent.com engages our community to contribute in profound ways - whether it's by evangelizing their favorite titles; by submitting content they've created; or by contributing their bandwidth to enable faster downloads and an improved entertainment experience. Our uniqueness lies in the strength of our community, delivery technology, and the industry's most comprehensive catalog of digital content."</p>

<p> </p>

<p>"As a leading entertainment content supplier across all media platforms, MGM is pleased to have such a significant role in launching BitTorrent's groundbreaking and valuable online consumer destination," said Rick Sands, COO, Metro-Goldwyn-Mayer Studios, Inc.  "MGM's library, the world's largest modern library of high quality film and television entertainment programming, appeals to a wide range of consumers and should drive significant traffic to the new BitTorrent Entertainment Network."</p>

<p> </p>

<p>BitTorrent's newest entertainment network partner is MGM, the legendary Hollywood studio and owner of the world's largest modern film library. With a roster of over 35 content partners, BitTorrent is offering a breadth and depth of content not found in any other download service. At launch, the network at BitTorrent.com will feature over 5,000 titles of movies, TV shows, PC games and music content, as well as over 40 hours of high-definition (HD) programming. Consumers will be able to enjoy both new releases and catalog movie titles such as "Superman Returns," "Mission: Impossible III," "World Trade Center," "Jackass: Number Two," "An Inconvenient Truth," "Napoleon Dynamite," "Sideways," and "Thomas Crown Affair." TV programming will include hits such as "24" and "Prison Break" from 20th Century Fox; "Laguna Beach" from MTV:  Music Television; "Celebrity Deathmatch" from MTV2; "Muscle Car" and "Xtreme 4x4" from Spike TV; Emmy and Peabody-Award winning "South Park" and "Mind of Mencia" from COMEDY CENTRAL; "Hogan Knows Best" and "Breaking Bonaduce" from VH1; "SpongeBob SquarePants" and "Avatar: The Last Airbender" from Nickelodeon; "Skyland" from Nicktoons Network</p>

<p> </p>

<p>"BitTorrent has the infrastructure, technology and established user base to significantly move the needle on digital distribution with quick, easy and affordable delivery," said Thomas Lesinski, President, Paramount Pictures Digital Entertainment. "The final piece of the puzzle is a wide array of content and Paramount is very pleased to be providing a vast selection of filmed entertainment to the site."</p>

<p> </p>

<p>"MTV Networks is constantly seeking new avenues for our consumers to access our content and to deliver it to them across multiple platforms," said Mika Salmi, President, Global Digital Media, MTV Networks. "With BitTorrent's new entertainment network, our content gains another high-quality, user-friendly touchpoint to connect audiences with their favorite programming."</p>

<p> </p>

<p>BitTorrent's entertainment network can also be utilized as a distribution platform for independent content creators. Be it film, TV programming, music or podcasts, BitTorrent's self-publishing capability offers artists an instant global audience through a personalized BitTorrent URL and online presence. "We're leveling the playing field for independent artists who have been turned away by publishers who are traditionally bound by scarce distribution alternatives and limited shelf space. Our entertainment network is a true marketplace that embraces and welcomes contribution from the independents, allowing them to reach a vast user base with their high-quality creative expression," said Navin. </p>

<p> </p>

<p>Despite the breadth and depth of BitTorrent's entertainment library, consumers can expect a very simple and straightforward pricing model. The site offers content for free, for rent and for purchase. Movie rentals are $3.99 and $2.99 for new release and catalog titles, respectively. TV shows and music videos are download-to-own at $1.99 each. There will also be a significant collection of HD titles available. Furthermore, a wide variety of entertainment content will be offered for free and without digital rights management (DRM), designed to be distributed across all platforms. </p>

<p> </p>

<p>"Digital distribution represents a significant new revenue stream for the entertainment industry, but up until now, it has been hindered by the combination of long download times and the lack of good content for people to download," said Rob Enderle, Principal Analyst for the Enderle</p>

<p>Group. "BitTorrent has aggressively addressed both problems; first with their unique technology, which moves content closer to the customer and dramatically lowers the amount of time it takes to acquire it, and second with their unusually strong content library. In addition, the fact that they start with 135 million existing clients is incredibly powerful and makes them, at launch, a real force to be reckoned with in this market."</p>

<p><br />
BitTorrent is the global standard for delivering high-quality files over the Internet. Millions of users worldwide are using BitTorrent's leading peer-assisted content delivery platform to publish, discover and download digital entertainment content quickly, easily and securely. Founded in 2004, BitTorrent is a privately held company headquartered in San Francisco, California. visit www.bittorrent.com. </p>

<p> __________________________________________________</p>

<p><em>In still another announcement we learn that streaming of HDTV is more than possible:</em></p>

<p><strong>DS2 announces flicker-free High Definition (HD) IPTV home video streaming</strong></p>

<p><br />
    High performance 'UPA Plugtested' products to hit international market<br />
    <br />
Chano Gomez announces major advance in Powerline Networking, IPTV Conference 2007 San Jose, 27th February</p>

<p>Valencia, 26 February, 2007 - DS2, the world's leading Powerline Chipset provider, will announce that the development of Universal Powerline Association (UPA) technology has reached the point where it is now the dominant provider for the home networking needs of the IPTV industry. The advance has been made possible by the UPA Digital Home Standard compliant DS2 200Mbps chip that powers IPTV Powerline applications. It makes available a new range of high performance products that allow international users to watch flicker-free High Definition (HD) home video streaming via standard wall sockets.</p>

<p>Chano Gomez, DS2's Vice President for Technology and Strategic Partnerships, will deliver a speech at the IPTV 2007 Conference that shows why key European IPTV operators have invested in the UPA 200Mbps DS2 chip for commercial roll-outs, and how this model is applicable around the world. IPTV providers in Belgium, France, Italy, Spain and Sweden already use UPA technology because of its speed, reliability and all-round performance. The UPA also has a range of application partners that provide Powerline solutions to consumers around the world. Netgear and D-Link, worldwide providers of technologically advanced network products, use UPA technology in products to enable video and gaming content to be used throughout the home.</p>

<p>"As the options for home entertainment grow, image quality and ease of delivery will become the key drivers for consumers," commented Chano Gomez, Vice President for Technology and Strategic Partnerships, DS2. "This means that the technology powering IPTV and other Powerline networking products becomes more important. It has to be powerful, resilient, and inter-operable and there must be commercial confidence in it, otherwise consumers won't be able to get what they want from their applications. At the Conference I will discuss these issues in more detail, which will kick-off a time of rapid proliferation of the UPA Powerline Networking technology."</p>

<p>There are three key areas for Powerline Networking technology and home networking, all of which significantly impact the end-user experience as well as the ability of service providers to deliver a broad range of products and service offers:</p>

<p>Consumers demand flicker-free images</p>

<p>HDTV needs much greater bandwidth and quality than standard video streams, which makes it important to have a powerful standard supporting the Powerline applications. Low performance technology can result in flickering images, or a complete breakdown in streaming. The UPA 200Mbps technology enables room-to-room HD streaming, which gives operators a high performance and quality service at a reasonable cost, and gives users the best entertainment experience when and where they want it.</p>

<p><strong>'UPA Plugtested' Label ensures compatibility for all home applications<br />
</strong><br />
The UPA Digital Home Standard is the global standard for Powerline Networking and Communications, and applications that adhere to this are stamped with the "UPA Plugtested" mark. Without this, the system may not be fully backwards compatible or future-proof. Leading IPTV operators and Consumer Electronics companies worldwide choose UPA as the preferred solution for Home Networking over Powerlines.</p>

<p>Robust and proven technology comes with a pedigree<br />
There are technology providers that have been in the market since it began and some that have been attracted to the growth. During 2006, UPA shipped over one million HD-ready 200Mbps chips to manufacturers of Powerline applications, which shows a rise in the number of manufacturers investing in the industry, and as it grows, we will see this trend develop. Those companies that use 200Mbps chips in their products have done much work in the market to develop and offer high specification UPA certified products. This investment guarantees performance, interoperability, simplicity and reliability of products.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>February 26, 2007 09:42 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 548
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
 			<h2>More on Programming</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'Programming'
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
			
 		<?if (7 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 7
 				AND entry_id <> 548
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
 				<h2>About Bulletins</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2007/02/bittorrent_sign_of_the_times_-_bittorrent_inc_inc_launches_the_bittorrent_entertainment_network.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
