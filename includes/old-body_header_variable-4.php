<?
	require_once('/var/www/html/includes/lib_common.php');
	require_once('/var/www/html/includes/constants.php');

	if ($user->data['hide_banner_ads'] != 1) { # Write FM tracking code and ads
			$leaderboard_output = <<<EOT
<!-- FM Tracking Pixel -->
	<script type="text/javascript" src="http://static.fmpub.net/site/hdtv"></script>
<!-- FM Tracking Pixel -->
<!-- FM Leaderboard Zone -->
	<div id="ad_leaderboard" align="center"><div><span class="corners-top"><span></span></span>
		<script type="text/javascript" src="http://static.fmpub.net/zone/529"></script>
	<span class="corners-bottom"><span></span></span></div></div>
<!-- FM Leaderboard Zone -->
EOT;
		}

	# Build account bar and determine if subscription box is needed
	$account_bar[] = 'Welcome, '. $user->data['username'];
	if ($user->data['is_registered']) {
		$account_bar[] = 	'<a href="'. BASE_URL .'/profile.php">Edit Profile</a>';
		$account_bar[] = '<a href="'. BASE_URL .'/logout.php?r='. rawurlencode($_SERVER['REQUEST_URI']) .'">Sign Out</a>';
		if (!access(ACCESS_PREMIUM)) $account_bar[] = '<a href="'. BASE_URL .'/subscribe.php">Subscribe</a>';
	} else {
		$subscription_box = <<<EOT
<div align="right" style="float:right; width:455px">
	<div class="important"><span class="corners-top"><span></span></span>
		<div style="color:#800000; float:left; font-weight:bold; padding-top:4px">Free <a href="/daily.php" target="_blank">HDTV Magazine Daily</a>: </div>
		<form name="frmSub" method="post" action="/profile-create.php" onsubmit="if (!isValidEmail(this.email_address)) {return false;} else {return true;}">
			<input type="hidden" name="action" value="create" />
			<input type="hidden" name="type" value="email_only" />
			<input type="text" name="email_address" class="inputText" style="width:15em;color:#777777" maxlength="255" value="email address"
			 onfocus="if (this.value == \'email address\') {this.value=\'\'; this.style.color = \'\';}" />
			 <input type="submit" class="inputButton" value="Subscribe" />
		</form>
	<span class="corners-bottom"><span></span></span></div>
</div>
EOT;

		$account_bar[] = '<a href="'. BASE_URL .'/forum/ucp.php?mode=login&redirect=/'. substr($_SERVER['REQUEST_URI'], 1) .'">Sign In</a>';
		$account_bar[] = '<a href="'. BASE_URL .'/profile-create.php">Register</a>';
	}
	$account_bar[] = '<a href="'. BASE_URL .'/help/index.php">Help</a>';

	$body_header_output = $leaderboard_output.
	'<div id="container">'."\n".
		'<div style="float:left;"><a href="'. BASE_URL .'/"><img src="'. BASE_IMG_HOST .'/images/hdtvmagazine.png" alt="HDTV Magazine" height="57" width="338"/></a></div>'.
#		'<div style="float:left;"><a href="'. BASE_URL .'/"><img src="'. BASE_IMG_HOST .'/images/hdtvmagazine-holiday_338x58.gif" alt="HDTV Magazine" height="58" width="338"/></a></div>'.
		'<div id="accountBar">'. join('&nbsp;&nbsp;&bull;&nbsp;&nbsp;', $account_bar) .'</div>'.
		$subscription_box .
		'<div id="menu"><div>'.
			'<table class="menuItem" cellpadding="0" cellspacing="0"><tr>'.
				'<td class="menuItem"><a href="'. BASE_URL .'/index.php">Home</a></td>'.
				'<td class="menuItem"><a href="'. BASE_URL .'/news/index.php">News</a></td>'.
				'<td class="menuItem"><a href="'. BASE_URL .'/reviews/index.php">Reviews</a></td>'.
				'<td class="menuItem"><a href="'. BASE_URL .'/forum/index.php">Forum</a></td>'.
				'<td class="menuItem"><a href="'. BASE_URL .'/articles/index.php">Articles</a></td>'.
				'<td class="menuItem"><a href="'. BASE_URL .'/columns/index.php">Columns</a></td>'.
				'<td class="menuItem"><a href="'. BASE_URL .'/podcast/index.php">Podcast</a></td>'.
				'<td class="menuItem"><a href="'. BASE_URL .'/programming/guide.php">Programming</a></td>'.
				'<td class="menuItem"><a href="'. BASE_URL .'/reports/hdtv-technology-review.php">Reports</a></td>'.
				'<td class="menuItem"><a href="'. BASE_URL .'/studies/index.php">Studies</a></td>'.
				'<td class="menuItem"><a href="'. BASE_URL .'/resources/index.php">Resources</a></td>'.
				'<td class="menuItem"><a href="'. BASE_URL .'/equipment/hdtvs-best-rated.php">HDTVs &amp; Equipment</a></td>'.
				'<td class="menuItem" style="vertical-align:middle;border-right:0px;padding-right:10px" nowrap="nowrap">'.
					'<!-- Search Google -->'.
					'<form method="get" action="'. BASE_URL .'/search.php" target="_top" id="cse-search-box">'.
						'<input type="text" class="inputText" id="search_box" name="q" maxlength="255" value="search" onfocus="this.value=\'\';" />'.
						'<input type="hidden" name="domains" value="hdtvmagazine.com" />'.
						'<input type="hidden" name="cx" value="partner-pub-2099480674594177:ciads6huqky" />'.
						'<input type="hidden" name="sitesearch" value="hdtvmagazine.com" />'.
						'<input type="hidden" name="client" value="pub-2099480674594177" />'.
						'<input type="hidden" name="forid" value="1" />'.
						'<input type="hidden" name="ie" value="ISO-8859-1" />'.
						'<input type="hidden" name="oe" value="ISO-8859-1" />'.
						'<input type="hidden" name="safe" value="active"></input>'.
						'<input type="hidden" name="flav" value="0001"></input>'.
						'<input type="hidden" name="sig" value="kyBota6MZwmrrLCK"></input>'.
						'<input type="hidden" name="cof" value="GALT:#008000;GL:1;DIV:#'. BORDER_COLOR .';VLC:'. LINK_COLOR .';AH:center;BGC:FFFFFF;LBGC:EEEEEE;ALC:'. LINK_COLOR .';LC:'. LINK_COLOR .';T:000000;GFNT:'. LINK_COLOR .';GIMP:'. LINK_COLOR .';LH:57;LW:324;L:'. BASE_IMG_HOST .'/images/hdtvmagazine.gif;S:'. BASE_URL .'/;FORID:11;" />'.
						'<input type="hidden" name="hl" value="en" />'.
					'</form>'.
					'<!-- Search Google -->'.
				'</td>'.
			"</tr></table>\n".
		"</div></div>\n";
?>