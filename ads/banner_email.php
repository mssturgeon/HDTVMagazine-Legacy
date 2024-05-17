<?
	$ad_list = array();
	$ad = array();

/*
<!--div class="ad"><span class="corners-top"><span></span></span>
	<h2>Welcome to the new HDTV Magazine Daily</h2>
	Check out the new <a href="<?=$base_url?>/columns/">Columns</a> and <a href="<?=$base_url?>/podcast/">Podcast</a> sections.<br />
	<br />
	If you have any ideas on other sections, please <a href="<?=FULL_URL_HELP_FEEDBACK?>">let us know</a>.
<span class="corners-bottom"><span></span></span></div-->

<div class="ad" style="text-align:center"><span class="corners-top"><span></span></span>
	<script type="text/javascript" language="javascript" src="http://www.qksz.net/1e-gjrn"> </script>
<span class="corners-bottom"><span></span></span></div>

<div class="ad"><span class="corners-top"><span></span></span>
	<a href="http://www.tkqlhce.com/click-1683082-10684066" target="_blank"><img src="http://store.discovery.com/img/product/resized/00085199-353403_100.jpg?k=d2ae2dfb&pid=85199&s=catl" border="0" align="left" /></a>
	<h2>Discovery Store 50% Off Blu-ray (This Week)</h2>
	<a href="http://www.tkqlhce.com/click-1683082-10684066" target="_blank">BLU-RAY BLOWOUT</a>: Get 50% off select Blu-ray DVDs by entering promo code BLU50 at checkout. <b>Ends Saturday, 8/22!</b>
	<img src="http://www.awltovhc.com/image-1683082-10684066" width="1" height="1" border="0" />
<span class="corners-bottom"><span></span></span></div>

	$ad_list['2012-01-01'] = '<script type="text/javascript" language="javascript" src="http://www.qksz.net/1e-gjrn"> </script>';

	$ad_list['2012-01-01'] = '<a href="http://gan.doubleclick.net/gan_click?lid=41000000034751773&pubid=21000000000112466">'.
	'<img src="http://gan.doubleclick.net/gan_impression?lid=41000000030512617&pubid=21000000000112466" border=0 alt=""></a>';

*/

	# Linkshare Lightning
	$ad_list['2099-01-01'] = '<script type="text/javascript" src="http://adunit.lsl8.com/WnkH_ddDUUi6aVQM3_i_xA"></script>';

	# Amazon Electronics
	$ad_list['2099-01-02'] = '<iframe src="http://rcm.amazon.com/e/cm?t=hdtvmagazine-20&o=1&p=13&l=ur1&category=electronics&f=ifr" width="468" height="60" scrolling="no" border="0" marginwidth="0" style="border:none;" frameborder="0"></iframe>';

#	$ad_list['2012-01-02'] = '<iframe src="http://rcm.amazon.com/e/cm?t=hdtvmagazine-20&o=1&p=26&l=ur1&category=homeaudiohometheater&banner=16F0Y5VWA95TJ0V6MH82&f=ifr" width="468" height="60" scrolling="no" border="0" marginwidth="0" style="border:none;" frameborder="0"></iframe>';

#	$ad_list['2012-01-03'] = '<iframe src="http://rcm.amazon.com/e/cm?t=hdtvmagazine-20&o=1&p=42&l=ur1&category=amazon3d101&banner=18FTPZ5Z33S9V15VRJ82&f=ifr" width="234" height="60" scrolling="no" border="0" marginwidth="0" style="border:none;" frameborder="0"></iframe>';

	if ($adtest) {?>
		<div class="ad"><span class="corners-top"><span></span></span>
			<div style="color:#AAAAAA; font-size:.8em; letter-spacing:.3em; padding-bottom:5px; text-align:center; width:100%;">advertisement</div>
			Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
			Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure
			dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
			proident, sunt in culpa qui officia deserunt mollit anim id est laborum.<br />
		<span class="corners-bottom"><span></span></span></div>
	<? } else {
		foreach ($ad_list as $ad_date => $ad_html) {
			if (strtotime($ad_date) > time()) $ad[] = $ad_html;
		}
		if (count($ad) > 0) $x = rand(0, count($ad) - 1);
	}

	function getAdBanner($ad, $not) {
		if (count($ad) == 0) { // Only one ad
			if ($not) {return -1;}
			return 0;
		} elseif (count($ad) > 0) {
			do {
				$r = rand(0, count($ad) - 1);
			} while ($r == $not);
			return $r;
		} else {
			return -1;
		}
	}

	function old_getAdBanner($ad) {
		$r = rand(0, count($ad) - 1);
		return $ad($r);
	}
?>