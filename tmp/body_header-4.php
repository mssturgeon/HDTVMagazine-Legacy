<?
	# Not sure why these are here, but there must be some need
	require_once('/var/www/html/includes/lib_common.php');
	require_once('/var/www/html/includes/constants.php');

	# Build account bar and determine if subscription box is needed
	$account_bar[] = 'Welcome, '. $user->data['username'];
	if ($user->data['is_registered']) {
		$account_bar[] = 	'<a href="'. BASE_URL .'/profile.php">Edit Profile</a>';
		$account_bar[] = '<a href="'. BASE_URL .'/logout.php?r='. rawurlencode($_SERVER['REQUEST_URI']) .'">Sign Out</a>';
		if (!access(ACCESS_PREMIUM)) $account_bar[] = '<a href="'. BASE_URL .'/subscribe.php">Subscribe</a>';
	} else {
		$account_bar[] = '<a href="'. BASE_URL .'/forum/ucp.php?mode=login&redirect=/'. substr($_SERVER['REQUEST_URI'], 1) .'">Sign In</a>';
		$account_bar[] = '<a href="'. BASE_URL .'/profile-create.php">Register</a>';
	}
	$account_bar[] = '<a href="'. BASE_URL .'/help/index.php">Help</a>';
	$account_bar = join('&nbsp;&nbsp;&bull;&nbsp;&nbsp;', $account_bar);

	$logo = BASE_IMG_HOST .'/images/hdtvmagazine.png';
#	$logo = BASE_IMG_HOST .'/images/hdtvmagazine-holiday_338x58.gif';

	# Calculate who to attribute the page view to based on topic_poster
	if (strpos($_SERVER['PHP_SELF'], 'forum') !== false) {
//		require_once('/var/www/html/global.php');

		$topic_id = mysql_real_escape_string($_GET['t']);
		$sql = "SELECT topic_poster FROM ". TOPIC_TABLE ." WHERE topic_id = '$topic_id'";
//		$result = mQuery($sql);
//		$row_topic = mysql_fetch_row($result);
//		$result = $db->sql_query($sql);
//		$row_topic = $db->sql_fetchrow($result);
		$topic_poster = $row_topic['topic_poster'];

		$sql = "SELECT channel, viglink_source FROM aux_author WHERE user_id = '$topic_poster'";
//		$result = mQuery($sql);
//		$row_author = mysql_fetch_row($result);
//		$result = $db->sql_query($sql);
//		$row_author = $db->sql_fetchrow($result);
		$google_links_channel = $row_author['channel'];
		$viglink_source = $row_author['viglink_source'];
	}

?>
<div id="container">
	<? if ($user->data['hide_banner_ads'] != 1) {?>
		<!-- FM Tracking Pixel -->
		<script type="text/javascript" src="http://static.fmpub.net/site/hdtv"></script>
		<!-- FM Tracking Pixel -->
		<!-- FM Leaderboard Zone -->
		<div id="ad_leaderboard" align="center"><div><span class="corners-top"><span></span></span>
			<script type="text/javascript" src="http://static.fmpub.net/zone/529"></script>
		<span class="corners-bottom"><span></span></span></div></div>
		<!-- FM Leaderboard Zone -->
	<? }?>

	<table class="bare" cellspacing="0" width="100%"><tr><td>
		<a href="/"><img src="<?=$logo?>" alt="HDTV Magazine" height="57" width="338" /></a>
	</td><td align="right" valign="top">
		<div id="accountbar"><?=$account_bar?></div>
		<!--?=$subscription_box?-->
		<div id="searchbar">
			<form action="http://www.hdtvmagazine.com/search.php" id="cse-search-box">
				<input type="hidden" name="cx" value="partner-pub-2099480674594177:6ifgtop0tvr" />
				<input type="hidden" name="cof" value="FORID:10" />
				<input type="hidden" name="ie" value="UTF-8" />
				<input class="input" type="text" name="q" size="31" />
				<input class="button" type="submit" name="sa" value="Search" />
			</form>
		</div>
	</td></tr></table>

	<table id="header_table" cellspacing="0"><tr><td>
		<div id="mainmenu"><div><span class="corners-top"><span></span></span>
			<table><tr>
				<td><a href="/index.php">Home</a></td>
				<td><a href="/reviews/index.php">Reviews</a></td>
				<td><a href="/shop/index.php">Shop</a></td>
				<td><a href="/news/index.php">News</a></td>
				<td><a href="/forum/index.php">Forum</a></td>
				<td><a href="/articles/index.php">Articles</a></td>
				<td><a href="/columns/index.php">Columns</a></td>
				<td><a href="/podcast/index.php">Podcast</a></td>
				<td><a href="/programming/guide.php">Programming</a></td>
				<td><a href="/resources/index.php">Resources</a></td>
				<td><a href="/equipment/hdtvs-best-rated.php">HDTVs &amp; Equipment</a></td>
			</tr></table>
		<span class="corners-bottom"><span></span></span></div></div>
	</td><td>
		<div id="menuicons">
			<a href="http://www.facebook.com/HDTVMagazine"><img width="32" border="0" src="/images/i_facebook_64.png" alt="Facebook"></a>
			<a href="/rss-feeds.php"><img width="32" border="0" src="/images/i_rss_64.png" alt="RSS"></a>
			<a href="http://twitter.com/HDTVMagazine"><img width="32" border="0" src="/images/i_twitter_64.png" alt="Twitter"></a>
			<!--a href="https://plus.google.com/117358426092428192639/?prsrc=3"><img src="https://ssl.gstatic.com/images/icons/gplus-32.png" width="32" height="32" alt="Google+" /></a-->
			<a href="https://plus.google.com/117358426092428192639?prsrc=3" style="text-decoration:none;" target="_blank"><img src="https://ssl.gstatic.com/images/icons/gplus-32.png" alt="" style="border:0;width:32px;height:32px;"/></a>
		</div>
	</td></tr></table>