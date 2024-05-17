<?
#	if (!$emailed) {
#		include(BASE_DIR .'/ads/banner.php');
#	} else {
#		echo '<table class="type1b" cellpadding="0" cellspacing="0">'.
#		'<tr><td class="type1b_header">Today\'s Listing brought to you by ... The HDTV Magazine Store</td></tr>'.
#		'<tr><td class="type1b"><div style="vertical-align:middle" align="center">'. getSelfAd() .'</div></td></tr>'.
#		'</table>';
#	}
?>
<!--table class="type1b" cellpadding="0" cellspacing="0">
	<tr><td class="type1b_header">Today's Listing brought to you by ... USAV, Inc.</td></tr>
	<tr><td class="type1b">
		<div style="vertical-align:middle" align="center">
			<div class="selfad"><div class="title">
				<img src="http://<?=SERVER_NAME?>/images/ads/televes.gif" alt="Televes" align="left" style="padding-right:3px" />
				DAT 75 $200 Including Insured Delivery to North America
			</div>
			<p>
				<a href="http://ultrasatellite.com/" target="_blank"><img src="http://<?=SERVER_NAME?>/images/ads/DAT-75.jpg" alt="DAT 75" align="left" style="padding-right:3px" /></a>
				The Televes Digital Televison Antenna Model DAT 75 is the finest UHF antenna for fringe reception of analog and digital/HD off-air television signals
				where durability, precise adjustment, and consistent performance are required. Appropriate for commercial, scientific, master antenna, and residential applications.<br />
				<br />
				USAV, Inc. Your Authorized Televes Distributor For Instruments, Electronics, and Antennas From the European Innovation Leader
			</p><p>
				For more information...    <a href="http://ultrasatellite.com/" target="_blank">http://ultrasatellite.com/</a>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<a href="http://antennavoodoo.com/" target="_blank">http://antennavoodoo.com/</a>
			</p>
			</div>
		</div>
	</td></tr>
</table-->
<?
	# Gamefly
	$ad[] = '<table class="type1b" cellpadding="0" cellspacing="0"><tr><td class="type1b_header">Today\'s Listing brought to you by ... Gamefly</td></tr><tr><td class="type1b" align="center"><a href="http://www.kqzyfj.com/ff108wmuiqt79EGB8GA798AFD9FF" target="_top"><img src="http://www.ftjcfx.com/2o115jy1qwuFHMOJGOIFHGINLHNN" alt="Unlimited Game Rentals Delivered - Free Trial" border="0"/></a></td></tr></table>';

	# Netflix
	$ad[] = '<a href="http://click.linksynergy.com/fs-bin/click?id=FK62p2waXuc&offerid=78684.10000090&type=4&subid=0" target="_blank"><img alt="Netflix, Inc." border="0" src="http://cdn.netflix.com/us/affiliates/banners/0804/468060B_599.gif"></a><img border="0" width="1" height="1" src="http://ad.linksynergy.com/fs-bin/show?id=FK62p2waXuc&bids=78684.10000090&type=4&subid=0">';

	# Trident Marketing
#	$ad[] = '<a href="http://www.hdtvmagazine.com/cgi-bin/ntadtrack.cgi?http://www.directsattv.com/dvd-legal.pdf"><img src="http://www.hdtvmagazine.com/images/ads/direct-sat-tv.jpg" alt="Direct Sat TV"></a>';

	$x = rand(0, count($ad) - 1);
	echo $ad[$x];
?>
