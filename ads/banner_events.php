<?
	$ad_list = array();
	$ad = array();

	# Run until 2009-06-01
#	$ad_list[] = '<a target="_blank" href="http://www.displaysearch.com/SID"'.
#		'><img src="'. BASE_IMG_URL .'/images/events/sid-business-conference_468x60.jpg" alt="2009 SID Business Conference"></a>';

	# Run until 2010-05-19
#	$ad_list[] = '<a target="_blank" href="http://www.blurayacademy.com"'.
#		'><img src="'. BASE_IMG_URL .'/images/events/blu-ray-disc-academy-2010_468x60.jpg" alt="2010 Blu-ray Disc Academy" width="468" height="60"></a>';

	$ad_list['2011-03-02'] = '<a target="_blank" href="http://www.displaysearch.com/usfpd"'.
		'><img src="'. BASE_IMG_URL .'/images/events/2011-usfpd_780x210.jpg" alt="2011 USFPD" width="780" height="210"></a>';

	$ad_list['2011-05-16'] = '<a target="_blank" href="http://www.displaysearch.com/sid"'.
		'><img src="'. BASE_IMG_URL .'/images/events/2011-sid_780x210.jpg" alt="2011 SID" width="780" height="210"></a>';

	$ad_list['2011-06-25'] = '<object width="728" height="90">
		<param name="movie" value="'. BASE_IMG_URL .'/images/events/2011-cinema-india-expo_728x90.swf">
		<embed src="'. BASE_IMG_URL .'/images/events/2011-cinema-india-expo_728x90.swf" width="728" height="90">
		</embed>
	</object>';

	$ad_list['2011-06-30'] = '<a target="_blank" href="http://www.connectionsus.com"'.
		'><img src="'. BASE_IMG_URL .'/images/events/2011-connections_728x90.gif" alt="2011 CONNECTIONS" width="728" height="90"></a>';

	$ad_list['2011-08-17'] = '<a target="_blank" href="http://www.displaysearch.com/emergingtech"'.
		'><img src="'. BASE_IMG_URL .'/images/events/2011-emerging-display-technologies_780x210.jpg" alt="2011 Emerging DIsplay Technologies" width="780" height="210"></a>';

	$ad_list['2011-09-05'] = '<a target="_blank" href="http://www.displaysearch.com/ifa-berlin"'.
		'><img src="'. BASE_IMG_URL .'/images/events/2011-ifa-displaysearch_780x210.jpg" alt="2011 IFA DisplaySearch Business Conference" width="780" height="210"></a>';

	$ad_list['2011-10-13'] = '<a target="_blank" href="http://www.ccwexpo.com/hdtv"'.
		'><img src="'. BASE_IMG_URL .'/images/events/2011-ccw-expo_728x90.gif" alt="2011 Content and Communications World Expo" width="780" height="90"></a>';

	$ad_list['2012-06-12'] = '<a target="_blank" href="www.displaysearch.com/digitalsignage2012"'.
		'><img src="'. BASE_IMG_URL .'/images/events/2012-digital-signage.jpg" alt="2012 Digital Signage Conference" width="780" height="210"></a>';

	foreach ($ad_list as $ad_date => $ad_html) {
		if (strtotime($ad_date) > time()) $ad[] = $ad_html;
	}
	if (count($ad) > 0) $x = rand(0, count($ad) - 1);
?>
