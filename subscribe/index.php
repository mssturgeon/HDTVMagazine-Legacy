<?
	require('../global.php');
	require(BASE_DIR .'/includes/lib_paypal.php');

	// Get product prices
	$product_result = mQuery("SELECT item_name, item_number, a3 FROM hdtv_products WHERE item_number IN (2,3)");
	while ($product = mysql_fetch_assoc($product_result)) {
		$amount[$product[item_number]] = $product[a3];
		$item_name[$product[item_number]] = $product[item_name];
	}

	# Apply special pricing, if any
	if ($user->data['pricing_annual'] > 0) {
			$amount[3] = $user->data['pricing_annual'];
	}

	if (access(ACCESS_PREMIUM)) { # Premium
		$basic_button = '';
		$premium_button = '';
	} elseif ($user->data[user_id] > 0) { # Basic
		$basic_button = '';
		$premium_button = '<div align="center" style="margin-top:10px">'.
		'<form name="mt"><input type="radio" name="number" value="2" />$'. $amount[2] .'/mo&nbsp;&nbsp;&nbsp;&nbsp;'.
		'<input type="radio" name="number" value="3" />$'. $amount[3] .'/yr<br />'.
		'<a href="javascript:void subscribe();"><img src="/images/btn-upgrade-premium.png" alt="Upgrade to Premium" /></a>'.
		'</form></div>';
	} else { # Unregistered
		$basic_button = '<div align="center" style="margin-top:10px"><a href="'. URL_PROFILE_CREATE .'"><img src="/images/btn-choose-basic.png" alt="Choose Basic" /></a></div>';
		$premium_button = '<div align="center" style="margin-top:10px"><a href="'. URL_PROFILE_CREATE .'?mt=premium"><img src="/images/btn-choose-premium.png" alt="Choose Premium" /></a></div>';
	}

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Subscribe</title>
	<meta name="description" content="Subscription page for HDTV Magazine" />
	<meta name="keywords" content="hdtv,hd tv,high definition,high def tv,high definition television,high definition tv,subscribe" />
	<meta name="rating" content="general" />
	<? require(BASE_DIR .'/includes/page_header.php');?>
	<script language="javascript" type="text/javascript">
		function purchase(name, number, a3, p3, t3) {
			var pp = document.forms['paypal'];

			// Normal Data
			pp.item_name.value = name;
			pp.item_number.value = number;

			// Recurring regular rate
			pp.a3.value = a3;
			pp.p3.value = p3;
			pp.t3.value = t3;

			pp.submit();
		}

		function subscribe() {
			if (document.mt.number[0].checked) {
				number = 2;
			} else if (document.mt.number[1].checked) {
				number = 3;
			} else {
				alert('Please select either a monthly or yearly subscription.');
				return false;
			}

			with (document.paypal) {
				if (number == 2) { // Monthly
					item_name.value = '<?=$item_name[2]?>';
					item_number.value = number;
					a3.value = <?=$amount[2]?>;
					t3.value = 'M';
				} else if (number == 3) { // Yearly
					item_name.value = '<?=$item_name[3]?>';
					item_number.value = number;
					a3.value = <?=$amount[3]?>;
					t3.value = 'Y';
				} else {
					return false;
				}
				p3.value = 1; // Recurring
				submit();
			}
		}

		function showComparison () {
			document.getElementById('comparison').style.display = 'inline';
			location.hash = 'comp';
		}
	</script>
</head>
<body id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header-4.php');
	?>

	<h1>HDTV Magazine Subscriptions</h1>

	<table><tr><td style="vertical-align:top">
		<img src="/images/woman-laptop.jpg" alt="" />
	</td><td>
		<p>
			<div class="section_heading">Welcome to HDTV Magazine.</div>
			Even as an unregistered, completely anonymous visitor, you can read any article, any review,
			peruse the news, check equipment specifications, and read forum content at your leisure.
			All of this is available to the general public and requires no login or registration. In addition, you can register with our website
			as a "Member" at no cost for a number of additional benefits.
		</p>

		<p>
			<div class="section_heading">Basic Membership</div>
			Membership not only includes all information available to unregistered visitors as mentioned above, but also includes other benefits.
			First, the ability to subscribe to a number of email notices. There are currently 14 different types available on a wide range of topics.
			Second, as a registered Member you have the ability to post and reply to topics within the HDTV Forum and HD Library.
			Get advice from fellow HDTV enthusiasts and ask questions of our cadre of industry experts. All that is needed to attain this FREE
			Membership is to create an account with a valid email address (no, we do not SPAM or sell your information).
			<?=$basic_button?>
		</p>

		<p>
			<div class="section_heading">Premium Membership</div>
			For those longing for more, our Premium Membership provides access to everything! The chief benefits of Premium Membership above
			Basic Membership include the HDTV Magazine Program Guide and the ability to hide all banner advertising throughout the site.
			We provide two methods for securing your Premium Membership: 1) Annual subscription at $<?=$amount[3]?>, or
			2) Monthly subscription at $<?=$amount[2]?>. No matter which you choose, you will be
			able to try out all Premium Member benefits for 7 days before paying anything! And if you cancel within those first seven days,
			you will not be charged at all.
			<?=$premium_button?>
		</p>

		<p>
			<div class="section_heading">Benefits Comparison</div>
  			The following table provides a quick glance of the types of access you have with the various memberships mentioned above.
			If you have any questions at all, please don't hesitate to <a href="<?=URL_HELP_FEEDBACK?>">let us know</a>.
		</p><br /><br />

  		<table class="space" cellspacing="2" cellpadding="2">
  			<tr>
  				<td class="space_header">&nbsp;</td>
  				<td class="space_header" style="width:80px">Unregistered</td>
  				<td class="space_header" style="width:80px">Basic</td>
  				<td class="space_header" style="width:80px">Premium</td>
  			</tr><tr>
  				<td><b>Articles</b> from the industries leading experts</td><td style="text-align:center"><img src="/images/i_yes.gif" alt="Yes"></td><td style="text-align:center"><img src="/images/i_yes.gif" alt="Yes"></td><td style="text-align:center"><img src="/images/i_yes.gif" alt="Yes"></td>
  			</tr><tr class="evenRow">
  				<td><b>News</b>, personally pre-screened and ranked</td><td style="text-align:center"><img src="/images/i_yes.gif" alt="Yes"></td><td style="text-align:center"><img src="/images/i_yes.gif" alt="Yes"></td><td style="text-align:center"><img src="/images/i_yes.gif" alt="Yes"></td>
  			</tr><tr>
  				<td><b>Reviews</b> of HDTVs and other HD devices, movies and games</td><td style="text-align:center"><img src="/images/i_yes.gif" alt="Yes"></td><td style="text-align:center"><img src="/images/i_yes.gif" alt="Yes"></td><td style="text-align:center"><img src="/images/i_yes.gif" alt="Yes"></td>
  			</tr><tr class="evenRow">
  				<td>Read through <b>HDTV Forum</b> and HD Library posts</td><td style="text-align:center"><img src="/images/i_yes.gif" alt="Yes"></td><td style="text-align:center"><img src="/images/i_yes.gif" alt="Yes"></td><td style="text-align:center"><img src="/images/i_yes.gif" alt="Yes"></td>
  			</tr><tr>
  				<td>Specs for over 5,000 HD consumer products in the <b>HD DB</b></td><td style="text-align:center"><img src="/images/i_yes.gif" alt="Yes"></td><td style="text-align:center"><img src="/images/i_yes.gif" alt="Yes"></td><td style="text-align:center"><img src="/images/i_yes.gif" alt="Yes"></td>
  			</tr><!--tr class="evenRow">
  				<td><b>Daily Program Listing</b> email with customized channels</td><td>&nbsp;</td><td style="text-align:center"><img src="/images/i_yes.gif" alt="Yes"></td><td style="text-align:center"><img src="/images/i_yes.gif" alt="Yes"></td>
  			</tr><tr>
  				<td><b>Daily Program Grid</b> email with customized channels</b></td><td>&nbsp;</td><td style="text-align:center"><img src="/images/i_yes.gif" alt="Yes"></td><td style="text-align:center"><img src="/images/i_yes.gif" alt="Yes"></td>
  			</tr--><tr class="evenRow">
  				<td>Post new topics to the <b>HDTV Forum</b></td><td>&nbsp;</td><td style="text-align:center"><img src="/images/i_yes.gif" alt="Yes"></td><td style="text-align:center"><img src="/images/i_yes.gif" alt="Yes"></td>
  			</tr><tr>
  				<td><b>Hide "In-Text" Ads</b></td><td>&nbsp;</td><td style="text-align:center"><img src="/images/i_yes.gif" alt="Yes"></td><td style="text-align:center"><img src="/images/i_yes.gif" alt="Yes"></td>
  			</tr><tr class="evenRow">
  				<td><b>HDTV Today</b> daily email publication</td><td>&nbsp;</td><td>&nbsp;</td><td style="text-align:center"><img src="/images/i_yes.gif" alt="Yes"></td>
  			</tr><tr>
  				<td><b>10% off</b> all purchases at the <b>HDTV Magazine Store</b></td><td>&nbsp;</td><td>&nbsp;</td><td style="text-align:center"><img src="/images/i_yes.gif" alt="Yes"></td>
  			</tr><tr class="evenRow">
  				<td><b>HDTV Program Guide</b> with 14-day look-ahead</td><td>&nbsp;</td><td>&nbsp;</td><td style="text-align:center"><img src="/images/i_yes.gif" alt="Yes"></td>
  			</tr><tr>
  				<td>Searchable, sortable <b>HDTV Movie Guide</b></td><td>&nbsp;</td><td>&nbsp;</td><td style="text-align:center"><img src="/images/i_yes.gif" alt="Yes"></td>
  			</tr--><tr class="evenRow">
  				<td><b>No Banner Ads</b> - Site-wide via profile option</td><td>&nbsp;</td><td>&nbsp;</td><td style="text-align:center"><img src="/images/i_yes.gif" alt="Yes"></td>
  			</tr><!--tr class="evenRow">
  				<td>Periodic <b>Premium Member Exclusives</b> and give-aways</td><td>&nbsp;</td><td>&nbsp;</td><td style="text-align:center"><img src="/images/i_yes.gif" alt="Yes"></td>
  			</tr--><tr>
  				<td><b>Featured HDTV News</b> - Daily email of highest ranked news stories</td><td>&nbsp;</td><td>&nbsp;</td><td style="text-align:center">coming soon</td>
  			</tr><tr>
  				<td><b>Favorite HDTV Programs</b> - delivered via RSS and daily email</td><td>&nbsp;</td><td>&nbsp;</td><td style="text-align:center">coming soon</td>
  			</tr><tr class="evenRow">
  				<td><b>Full-text RSS Feeds</b> - Get entire articles via RSS</td><td>&nbsp;</td><td>&nbsp;</td><td style="text-align:center">coming soon</td>
  			</tr><tr>
  				<td class="space_header">&nbsp;</td>
  				<td class="space_header" style="width:80px">Free</td>
  				<td class="space_header" style="width:80px">Free</td>
  				<td class="space_header" style="width:80px">$<?=$amount[2]?>/mo<br />$<?=$amount[3]?>/yr</td>
  			</tr>
  		</table>
	</td></tr></table>

	<form action="<?=PAYPAL_URL?>" method="post" name="paypal">
		<input type="hidden" name="cmd" value="_xclick-subscriptions">
		<input type="hidden" name="business" value="<?=PAYPAL_EMAIL?>">
		<input type="hidden" name="item_name" value="">
		<input type="hidden" name="item_number" value="">
		<input type="hidden" name="no_note" value="1">
		<input type="hidden" name="currency_code" value="USD">
		<input type="hidden" name="a1" value="0.00">
		<input type="hidden" name="p1" value="1">
		<input type="hidden" name="t1" value="W">
		<input type="hidden" name="a3" value="">
		<input type="hidden" name="p3" value="">
		<input type="hidden" name="t3" value="">
		<input type="hidden" name="sra" value="1">
		<input type="hidden" name="src" value="1">
		<input type="hidden" name="custom" value="<?=$user->data['id']?>">
		<input type="hidden" name="return" value="http://www.hdtvmagazine.com/welcome-premium.php">
		<input type="hidden" name="cancel_return" value="http://www.hdtvmagazine.com/subscribe/payment-cancel.php">
	</form>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</body>
</html>
