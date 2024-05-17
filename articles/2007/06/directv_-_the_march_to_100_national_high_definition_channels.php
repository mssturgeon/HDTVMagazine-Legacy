<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 611";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 611 AND placement_is_primary = 1";
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
	<meta name="keywords" content="sunday ticket, nfl sunday, ticket nfl, comcast sportsnet, national high, channels, directv, DirecTV, NFL, nfl, channel, Ticket, ticket, Sunday, sunday, Channel, west, West, fsn, east, FSN, East, networks, national, those" />
	<meta name="description" content="Unless you've been under a rock, or subscribed to Cable, you are likely intimately aware of DirecTV's promise of having 100 national high definition channels in service by the end of 2007. Sounds good, doesn't it? &lt;B&gt;100 National channels of HD content!&lt;/B&gt; As you might imagine, this raises more than a few questions. Some of the more frequent ones I've heard are:
&lt;ul&gt;
&lt;li&gt;What channels will these be?&lt;/li&gt;
&lt;li&gt;Will &lt;I&gt;{insert favorite channel here}&lt;/I&gt; be among them?&lt;/li&gt;
&lt;li&gt;How will they carry 100 HD channels if there aren't 100 networks with HD content?&lt;/li&gt;
&lt;li&gt;How much will this cost me?&lt;/li&gt;
&lt;/ul&gt;" />
	<title>HDTV Magazine Articles - DirecTV - The March to 100 National High Definition Channels</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/directv_-_the_march_to_100_national_high_definition_channels';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('DirecTV - The March to 100 National High Definition Channels'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2007/06/directv_-_the_march_to_100_national_high_definition_channels.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">DirecTV - The March to 100 National High Definition Channels</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>June 11, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Programming">Programming</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2007/06/directv_-_the_march_to_100_national_high_definition_channels.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2007/06/directv_-_the_march_to_100_national_high_definition_channels.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2007/06/directv_-_the_march_to_100_national_high_definition_channels.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2007/06/directv_-_the_march_to_100_national_high_definition_channels.php&amp;phase=2&amp;title=DirecTV%20-%20The%20March%20to%20100%20National%20High%20Definition%20Channels&amp;bodytext=Unless%20you%27ve%20been%20under%20a%20rock%2C%20or%20subscribed%20to%20Cable%2C%20you%20are%20likely%20intimately%20aware%20of%20DirecTV%27s%20promise%20of%20having%20100%20national%20high%20definition%20channels%20in%20service%20by%20the%20end%20of%202007.%20Sounds%20good%2C%20doesn%27t%20it%3F%20%3CB%3E100%20National%20channels%20of%20HD%20content%21%3C%2FB%3E%20As%20you%20might%20imagine%2C%20this%20raises%20more%20than%20a%20few%20questions.%20Some%20of%20the%20more%20frequent%20ones%20I%27ve%20heard%20are%3A%0A%3Cul%3E%0A%3Cli%3EWhat%20channels%20will%20these%20be%3F%3C%2Fli%3E%0A%3Cli%3EWill%20%3CI%3E%7Binsert%20favorite%20channel%20here%7D%3C%2FI%3E%20be%20among%20them%3F%3C%2Fli%3E%0A%3Cli%3EHow%20will%20they%20carry%20100%20HD%20channels%20if%20there%20aren%27t%20100%20networks%20with%20HD%20content%3F%3C%2Fli%3E%0A%3Cli%3EHow%20much%20will%20this%20cost%20me%3F%3C%2Fli%3E%0A%3C%2Ful%3E&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p>Unless you've been under a rock, or subscribed to Cable, you are likely intimately aware of DirecTV's promise of having 100 national high definition channels in service by the end of 2007. Sounds good, doesn't it? <B>100 National channels of HD content!</B> As you might imagine, this raises more than a few questions. Some of the more frequent ones I've heard are:
<ul>
<li>What channels will these be?</li>
<li>Will <I>{insert favorite channel here}</I> be among them?</li>
<li>How will they carry 100 HD channels if there aren't 100 networks with HD content?</li>
<li>How much will this cost me?</li>
</ul>
</p>

<p>This article will attempt to answer every one of these questions. I will list DirecTV's current offerings as well as a list of those networks with which they have agreements for carriage starting this fall. I will also list those networks which are currently carried by other providers which DirecTV may pick up as part of this 100. And you know what the best part is? According to DirecTV, there are <B>no plans to increase the price</B> of the HD package above the current price of $9.99! The only unknown at this point is the quality. Will it be on par with the current offerings? Will it be better? Let's hope it's one of those two.</p><br />

<B>The Promise</B>
<p>This is apparently a point of confusion for many. DirecTV has announced the planned <B>capacity</B> for 150 national high definition channels. Many have mistakenly taken this to mean that they will have 150 HD channels by years-end, which was not promised, and is not likely. At CES this year, DirecTV announced their plans for <B>carriage</B> of 100 national high definition channels. From the January 8, 2007 press release:
<blockquote>DIRECTV, the nation's leading satellite television service provider, is hailing 2007 as the "Year of HD" with the planned launch and carriage of 100 national high-definition (HD) channels. With this substantial HD muscle, DIRECTV will offer three-times more HD programming than any other multi-channel distributor, with the majority of these channels launching in Q3.</blockquote></p>
<p>That is the quote by which they will be measured in this article.</p>
<p>All of this additional HD content is being made possible by the launch of two new satellites: DirecTV 10 and 11. These new satellites support a new transmission protocol as well as the more efficient MPEG4/AVC codec, which will allow much better use of the available bandwidth. DirecTV 10 will be launched later this summer, and will be operation in Q3 to support all this new programming. DirecTV 11 will be launched in early 2008 to support further expansion of HD programming. When these two satellites are operational, DirecTV will be able to deliver more than 1,500 local HD and digital channels and 150 national HD channels.</p><br />

<B>The Fine Print</B>
<p>Since the original announcement, DirecTV has come forward on a few occasions to help us understand how they will be getting to 100 HD channels by the end of this year. They've also dropped hints as to which channels WILL NOT be included. These "fine print" items are as follows:
<ul>
<li>Since the local East and West Networks feeds are "national HD channels", those count. There are currently 8 of these from channel 80-89.</li>
<li>In an <a href="/cgi-bin/ntlinktrack.cgi?http://www.multichannel.com/article/CA6429787.html">article at Multichannel News</a>, DirecTV Group chief financial officer Michael Palkovic indicated that some of these 100 channels will be multiple feeds from sports packages such as "NFL Sunday Ticket", adding that only 70 or 80 of the 100 promised would be considered year-round. Last season, DirecTV ran the Sunday Ticket HD NFL games on 9 channels in the 700 range.</li>
<li>In <a href="/cgi-bin/ntlinktrack.cgi?http://www.multichannel.com/article/CA6429787.html">that same article</a>, Mr. Palkovic also indicated that the Voom channels would NOT be among the 100. Specifically, he said "there is nothing like that that people would consider not really quality channels." So those are not included in the last table below.</li>
<li>In their latest press release, DirecTV stated they plan to offer the HD feeds of regional sports networks on a nationwide basis this fall, which means they will likely be counting those as well. They are included below.</li>
<li>Also in their latest press release, it was indicated that they have commitments from a number of other networks to launch their HD simulcasts, and that they will be added to the lineup for launch by year-end and will be announced at a later date. That gives us some hints as to which networks may be coming next.</li>
</ul></p><br />

<B>Current Channels</B>
<p><table class="type1b"><tr><td class="type1b_header">#</td><td class="type1b_header">Network</td><td class="type1b_header">Channel</td><td class="type1b_header">Notes</td></tr>
<tr><td class="grid">1</td><td class="grid">HBO HD (East)</td><td class="grid">70</td><td class="grid"></td></tr>
<tr><td class="grid">2</td><td class="grid">Showtime HD (East)</td><td class="grid">71</td><td class="grid"></td></tr>
<tr><td class="grid">3</td><td class="grid">ESPN2 HD</td><td class="grid">72</td><td class="grid"></td></tr>
<tr><td class="grid">4</td><td class="grid">ESPN HD</td><td class="grid">73</td><td class="grid"></td></tr>
<tr><td class="grid">5</td><td class="grid">Universal HD</td><td class="grid">74</td><td class="grid"></td></tr>
<tr><td class="grid">6</td><td class="grid">TNT HD</td><td class="grid">75</td><td class="grid">OK, so this one may be a stretch (pun intended), but we're giving them the benefit of the doubt.</td></tr>
<tr><td class="grid">7</td><td class="grid">Discovery HD Theater</td><td class="grid">76</td><td class="grid"></td></tr>
<tr><td class="grid">8</td><td class="grid">HDNet Movies</td><td class="grid">78</td><td class="grid"></td></tr>
<tr><td class="grid">9</td><td class="grid">HDNet</td><td class="grid">79</td><td class="grid"></td></tr>

<tr><td class="grid">10</td><td class="grid">CBS (East)</td><td class="grid">80</td><td class="grid"></td></tr>
<tr><td class="grid">11</td><td class="grid">CBS (West)</td><td class="grid">81</td><td class="grid"></td></tr>
<tr><td class="grid">12</td><td class="grid">NBC (East)</td><td class="grid">82</td><td class="grid"></td></tr>
<tr><td class="grid">13</td><td class="grid">NBC (West)</td><td class="grid">83</td><td class="grid"></td></tr>
<tr><td class="grid">14</td><td class="grid">ABC (East)</td><td class="grid">86</td><td class="grid"></td></tr>
<tr><td class="grid">15</td><td class="grid">ABC (West)</td><td class="grid">87</td><td class="grid"></td></tr>
<tr><td class="grid">16</td><td class="grid">Fox (East)</td><td class="grid">88</td><td class="grid"></td></tr>
<tr><td class="grid">17</td><td class="grid">Fox (West)</td><td class="grid">89</td><td class="grid"></td></tr>
<tr><td class="grid">18</td><td class="grid">RSN HD</td><td class="grid">95</td><td class="grid"></td></tr>
<tr><td class="grid">19</td><td class="grid">RSN HD</td><td class="grid">96</td><td class="grid"></td></tr>

<tr><td class="grid">20</td><td class="grid" nowrap="nowrap">National Geographic HD</td><td class="grid">98</td><td class="grid">This one is only available at select "sneak peek" times, typically in the middle of the night (i.e., not yet 24/7)</td></tr>
<tr><td class="grid">21</td><td class="grid">CD USA</td><td class="grid">101</td><td class="grid"></td></tr>
<tr><td class="grid">22</td><td class="grid">YES HD</td><td class="grid">622</td><td class="grid"></td></tr>
<tr><td class="grid">23</td><td class="grid">NESN HD</td><td class="grid">623</td><td class="grid"></td></tr>
<tr><td class="grid">24</td><td class="grid">SNY HD</td><td class="grid">625</td><td class="grid"></td></tr>
<tr><td class="grid">25</td><td class="grid">CSN HD</td><td class="grid">629</td><td class="grid"></td></tr>
<tr><td class="grid">26</td><td class="grid">NFL Sunday Ticket 1</td><td class="grid">719</td><td class="grid"></td></tr>
<tr><td class="grid">27</td><td class="grid">NFL Sunday Ticket 2</td><td class="grid">720</td><td class="grid"></td></tr>
<tr><td class="grid">28</td><td class="grid">NFL Sunday Ticket 3</td><td class="grid">721</td><td class="grid"></td></tr>
<tr><td class="grid">29</td><td class="grid">NFL Sunday Ticket 4</td><td class="grid">722</td><td class="grid"></td></tr>

<tr><td class="grid">30</td><td class="grid">NFL Sunday Ticket 5</td><td class="grid">723</td><td class="grid"></td></tr>
<tr><td class="grid">31</td><td class="grid">NFL Sunday Ticket 6</td><td class="grid">724</td><td class="grid"></td></tr>
<tr><td class="grid">32</td><td class="grid">NFL Sunday Ticket 7</td><td class="grid">725</td><td class="grid"></td></tr>
<tr><td class="grid">33</td><td class="grid">NFL Sunday Ticket 8</td><td class="grid">726</td><td class="grid"></td></tr>
<tr><td class="grid">34</td><td class="grid">NFL Sunday Ticket 9</td><td class="grid">727</td><td class="grid"></td></tr>
</table></p><br />

<B>Announced Channels</B>
<p>The following channels have been announced for availability this fall:</p>
<p><table class="type1b"><tr><td class="type1b_header">#</td><td class="type1b_header">Network</td><td class="type1b_header">Announced</td><td class="type1b_header">Notes</td></tr>
<tr><td class="grid">35</td><td class="grid">A&E HD</td><td class="grid">1/8/2007</td><td class="grid"></td></tr>
<tr><td class="grid">36</td><td class="grid">ABC Family HD</td><td class="grid">5/23/2007</td><td class="grid"></td></tr>
<tr><td class="grid">37</td><td class="grid">Animal Planet HD</td><td class="grid">5/23/2007</td><td class="grid"></td></tr>
<tr><td class="grid">38</td><td class="grid">Bravo HD</td><td class="grid">1/8/2007</td><td class="grid"></td></tr>
<tr><td class="grid">39</td><td class="grid">Big Ten Network</td><td class="grid">5/23/2007</td><td class="grid"></td></tr>

<tr><td class="grid">40</td><td class="grid">Cartoon Network HD</td><td class="grid">1/8/2007</td><td class="grid"></td></tr>
<tr><td class="grid">41</td><td class="grid">Chiller HD</td><td class="grid">5/23/2007</td><td class="grid"></td></tr>
<tr><td class="grid">42</td><td class="grid">CNBC HD</td><td class="grid">5/23/2007</td><td class="grid"></td></tr>
<tr><td class="grid">43</td><td class="grid">CNN HD</td><td class="grid">1/8/2007</td><td class="grid"></td></tr>
<tr><td class="grid">44</td><td class="grid">Discovery Channel HD</td><td class="grid">5/23/2007</td><td class="grid">This is different from Discovery HD Theater, which DirecTV already carries. Discovery Channel HD will be an HD simulcast of the "regular" Discovery Channel</td></tr>
<tr><td class="grid">45</td><td class="grid">TBD</td><td class="grid">5/23/2007</td><td class="grid" rowspan="2">Discovery will be announcing two networks to be launched at a later date and carried by DirecTV</td></tr>
<tr><td class="grid">46</td><td class="grid">TBD</td><td class="grid">5/23/2007</td></tr>
<tr><td class="grid">47</td><td class="grid">Disney Channel HD</td><td class="grid">5/23/2007</td><td class="grid"></td></tr>
<tr><td class="grid">48</td><td class="grid">ESPNews HD</td><td class="grid">5/23/2007</td><td class="grid"></td></tr>
<tr><td class="grid">49</td><td class="grid">Food Network HD</td><td class="grid">1/8/2007</td><td class="grid"></td></tr>

<tr><td class="grid">50</td><td class="grid">FX HD</td><td class="grid">1/8/2007</td><td class="grid"></td></tr>
<tr><td class="grid">51</td><td class="grid">HGTV HD</td><td class="grid">1/8/2007</td><td class="grid"></td></tr>
<tr><td class="grid">52</td><td class="grid">The Movie Channel HD</td><td class="grid">5/23/2007</td><td class="grid"></td></tr>
<tr><td class="grid">53</td><td class="grid">MTV HD</td><td class="grid">1/8/2007</td><td class="grid"></td></tr>
<tr><td class="grid">54</td><td class="grid">NFL Network HD</td><td class="grid">1/8/2007</td><td class="grid"></td></tr>
<tr><td class="grid">55</td><td class="grid">SciFi Channel HD</td><td class="grid">1/8/2007</td><td class="grid"></td></tr>
<tr><td class="grid">56</td><td class="grid">Science Channel HD</td><td class="grid">5/23/2007</td><td class="grid"></td></tr>
<tr><td class="grid">57</td><td class="grid">Speed Channel HD</td><td class="grid">1/8/2007</td><td class="grid"></td></tr>
<tr><td class="grid">58</td><td class="grid">TBS HD</td><td class="grid">1/8/2007</td><td class="grid"></td></tr>
<tr><td class="grid">59</td><td class="grid">Toon Disney HD</td><td class="grid">5/23/2007</td><td class="grid"></td></tr>

<tr><td class="grid">60</td><td class="grid">History Channel HD</td><td class="grid">1/8/2007</td><td class="grid"></td></tr>
<tr><td class="grid">61</td><td class="grid">Showtime HD (West)</td><td class="grid">5/23/2007</td><td class="grid"></td></tr>
<tr><td class="grid">62</td><td class="grid">Starz HD (East)</td><td class="grid">5/23/2007</td><td class="grid"></td></tr>
<tr><td class="grid">63</td><td class="grid">Starz HD (West)</td><td class="grid">5/23/2007</td><td class="grid"></td></tr>
<tr><td class="grid">64</td><td class="grid">Starz Edge HD</td><td class="grid">5/23/2007</td><td class="grid"></td></tr>
<tr><td class="grid">65</td><td class="grid">Starz Comedy HD</td><td class="grid">5/23/2007</td><td class="grid"></td></tr>
<tr><td class="grid">66</td><td class="grid">Starz Kids &amp; Family HD</td><td class="grid">5/23/2007</td><td class="grid"></td></tr>
<tr><td class="grid">67</td><td class="grid">Tennis Channel HD</td><td class="grid">5/23/2007</td><td class="grid"></td></tr>
<tr><td class="grid">68</td><td class="grid" nowrap="nowrap">TLC (The Learning Channel) HD</td><td class="grid">5/23/2007</td><td class="grid"></td></tr>
<tr><td class="grid">69</td><td class="grid">Versus HD</td><td class="grid">5/23/2007</td><td class="grid"></td></tr>

<tr><td class="grid">70</td><td class="grid">Weather Channel HD</td><td class="grid">1/8/2007</td><td class="grid"></td></tr>
<tr><td class="grid">71</td><td class="grid">USA Network HD</td><td class="grid">1/8/2007</td><td class="grid"></td></tr>
</table></p><br />

<B>Other Channels</B>
<p>So what will round out the 100 channels? As mentioned in "fine print" above, the nationally broadcast special sports packages will count, so we have included some of those in the table below. We have also included some other channels here that are currently carried by other networks which may end up being announced as part of DirecTV's initiative. Note that the channels listed below HAVE NOT necessarily been announced by DirecTV.</p>

<p><table class="type1b"><tr><td class="type1b_header">#</td><td class="type1b_header">Network</td><td class="type1b_header">Notes</td></tr>
<tr><td class="grid">72</td><td class="grid">NFL Sunday Ticket 10</td><td class="grid" rowspan="4">Since DirecTV carries as many as 13 games any given week, we'll assume that they will broadcast 100% HD this year and add an additional 4 channels to what they carried last year.</td></tr>
<tr><td class="grid">73</td><td class="grid">NFL Sunday Ticket 11</td></tr>
<tr><td class="grid">74</td><td class="grid">NFL Sunday Ticket 12</td></tr>
<tr><td class="grid">75</td><td class="grid">NFL Sunday Ticket 13</td></tr>
<tr><td class="grid">76</td><td class="grid">PBS (East)</td><td class="grid" rowspan="2">These are included only because they are the only "major" network missing from their current East/West feeds. Just a guess.</td></tr>
<tr><td class="grid">77</td><td class="grid">PBS (West)</td></tr>
<tr><td class="grid">78</td><td class="grid">Cinemax HD (East)</td><td class="grid"></td></tr>
<tr><td class="grid">79</td><td class="grid">Cinemax HD (West)</td><td class="grid"></td></tr>

<tr><td class="grid">80</td><td class="grid">Nickelodeon HD</td><td class="grid"></td></tr>
<tr><td class="grid">81</td><td class="grid">Outdoor Channel 2 HD</td><td class="grid"></td></tr>
<tr><td class="grid">82</td><td class="grid">Playboy HD</td><td class="grid"></td></tr>
<tr><td class="grid">83</td><td class="grid">Wealth TV HD</td><td class="grid"></td></tr>

<tr><td class="grid">84</td><td class="grid">FSN Arizona</td><td class="grid" rowspan="12">Granted, not all of these are likely to be added, but these are the networks <a href="/cgi-bin/ntlinktrack.cgi?http://msn.foxsports.com/story/1528357">as indicated by Fox Sports</a> that are slated to produce local HD programs.</td></tr>
<tr><td class="grid">85</td><td class="grid">FSN Bay Area</td></tr>
<tr><td class="grid">86</td><td class="grid">FSN Florida</td></tr>
<tr><td class="grid">87</td><td class="grid">FSN North</td></tr>
<tr><td class="grid">88</td><td class="grid">FSN Northwest</td></tr>
<tr><td class="grid">89</td><td class="grid">FSN BPittsburgh</td></tr>

<tr><td class="grid">90</td><td class="grid">FSN South</td></tr>
<tr><td class="grid">91</td><td class="grid">FSN Southwest</td></tr>
<tr><td class="grid">92</td><td class="grid">Sun Sports</td></tr>
<tr><td class="grid">93</td><td class="grid">FSN Utah</td></tr>
<tr><td class="grid">94</td><td class="grid">FSN West</td></tr>
<tr><td class="grid">95</td><td class="grid">FSN West 2</td></tr>
<tr><td class="grid">96</td><td class="grid">Comcast SportsNet - Philadelphia</td><td class="grid" rowspan="5">Again, not all of these are likely to be added, but these are the networks on which Comcast currently carries HD programming</td></tr>
<tr><td class="grid">97</td><td class="grid" nowrap="nowrap">Comcast SportsNet - Baltimore/Washington D.C.</td></tr>
<tr><td class="grid">98</td><td class="grid">Comcast SportsNet - Chicago</td></tr>
<tr><td class="grid">99</td><td class="grid">Comcast SportsNet - Sacramento</td></tr>
<tr><td class="grid">100</td><td class="grid">Comcast SportsNet - New York</td></tr>
</table></p><br />

<b>In Summary</b>
<p>There you have it, 100 exactly. I could go on with other options, but I think I've hit the most likely candidates. Regardless of who your HD provider is this is good news. If you're already with DirecTV, great. If not, your provider will certainly be wondering how they can keep up. For updates to the above tables, please check the page we have dedicated to <a href="http://www.hdtvmagazine.com/programming/satellite/directv.php">DirecTV's HD offerings</a>.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>June 11, 2007 06:45 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 611
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
			
 		<?if (1 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 1
 				AND entry_id <> 611
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2007/06/directv_-_the_march_to_100_national_high_definition_channels.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
