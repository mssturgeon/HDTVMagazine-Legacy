<?
	require_once('/var/www/html/includes/lib_common.php');
	require_once('/var/www/html/includes/lib_mysql.php');
	include('/var/www/html/includes/menu_variable.php');

	if (strpos(PHP_SELF, '/hdstore') === false) {
		if ($user->data['is_registered'] || strpos(PHP_SELF, '/hdstore') === true) {}
		else {
			$subscription_box .= '<div align="right" style="float:right; width:455px"><div class="important"><span class="corners-top"><span></span></span>'.
				'<div style="color:#800000; float:left; font-weight:bold; padding-top:4px">Free <a href="/daily.php" target="_blank">HDTV Magazine Daily</a>: </div>'.
				'<form name="frmSub" method="post" action="/profile-create.php"'.
				' onsubmit="if (!isValidEmail(this.email_address)) {return false;} else {return true;}">'.
					'<input type="hidden" name="action" value="create" />'.
					'<input type="hidden" name="type" value="email_only" />'.
					'<input type="text" name="email_address" class="inputText" style="width:15em;color:#777777" maxlength="255" value="email address"'.
					' onfocus="if (this.value == \'email address\') {this.value=\'\'; this.style.color = \'\';}" />'.
					' <input type="submit" class="inputButton" value="Subscribe" />'.
				'</form>'.
			'<span class="corners-bottom"><span></span></span></div></div>'."\n";
		}

		if ($user->data['is_registered']) {
			$login_string = 'Welcome, '. $user->data['username'] .'&nbsp;&nbsp;&bull;&nbsp;&nbsp;'.
			'<a href="'. BASE_URL .'/profile.php">Edit Profile</a>&nbsp;&nbsp;&bull;&nbsp;&nbsp;'.
			'<a href="'. BASE_URL .'/logout.php?r='. rawurlencode($_SERVER['REQUEST_URI']) .'">Sign Out</a>';
			if (!access(ACCESS_PREMIUM)) $login_string .= '&nbsp;&nbsp;&bull;&nbsp;&nbsp;<a href="'. BASE_URL .'/subscribe.php">Subscribe</a>';
		} elseif ($_SERVER['PHP_SELF'] == '/forum/login.php') {
			$login_string = '(Not logged in)&nbsp;&nbsp;&bull;&nbsp;&nbsp;<a href="'. BASE_URL .'/profile-create.php">Register</a>';
		} else {
			$redirect = '../'. substr($_SERVER['REQUEST_URI'], 1);
			$login_string = '(Not logged in)&nbsp;&nbsp;<a href="'. FULL_URL_LOGIN .'?redirect='. $redirect .'">Sign In</a>&nbsp;&nbsp;&bull;&nbsp;&nbsp;'.
			'<a href="'. FULL_URL_PROFILE_CREATE .'">Register</a>';
		}

		$login_string .= '&nbsp;&nbsp;&bull;&nbsp;&nbsp;<a href="'. BASE_URL .'/help/index.php">Help</a>';
	}

	$body_header_output = ''.
	'<div id="container">'."\n".
		'<div style="float:left;"><a href="'. BASE_URL .'/"><img src="'. BASE_IMG_URL .'/images/hdtvmagazine.gif" alt="HDTV Magazine" height="57" width="338"/></a></div>'.
#		'<div style="float:left;"><a href="'. $base_url .'/"><img src="/images/hdtvmagazine-holiday_338x58.gif" alt="HDTV Magazine" /></a></div>'.
		'<div id="headertext">'. $login_string .'</div>'.
		$subscription_box .
		'<div id="menu"><div>'.
			$menu_output .
		"</div></div>\n";
?>