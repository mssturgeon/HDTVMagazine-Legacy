<?
	$event_list = array();
	$event = array();

	$event_list['2011-05-16'] = '<a target="_blank" href="http://www.displaysearch.com/sid"'.
		'><img src="'. BASE_IMG_URL .'/images/events/2011-sid_468x60.jpg" alt="Society for Information Display 2011" width="468" height="60"></a>';

	$event_list['2011-06-25'] = '<a target="_blank" href="http://www.cinemaindiaexpo.com/"'.
		'><img src="'. BASE_IMG_URL .'/images/events/2011-cinema-india-expo_468x60.jpg" alt="Cinema India Expo 2011" width="468" height="60"></a>';

	$event_list['2011-06-30'] = '<a target="_blank" href="http://www.connectionsus.com"'.
		'><img src="'. BASE_IMG_URL .'/images/events/2011-connections_468x60.gif" alt="CONNECTIONS 2011" width="468" height="60"></a>';

	$event_list['2011-08-17'] = '<a target="_blank" href="http://www.displaysearch.com/emergingtech"'.
		'><img src="'. BASE_IMG_URL .'/images/events/2011-emerging-display-technologies_468x60.jpg" alt="2011 Emerging DIsplay Technologies" width="468" height="60"></a>';

	$event_list['2011-10-13'] = '<a target="_blank" href="http://www.ccwexpo.com/hdtv"'.
		'><img src="'. BASE_IMG_URL .'/images/events/2011-ccw-expo_468x60.gif" alt="2011 Content and Communications World Expo" width="468" height="60"></a>';

	$event_list['2011-11-09'] = '<a target="_blank" href="http://www.connectionseurope.com/"'.
		'><img src="'. BASE_IMG_URL .'/images/events/2011-connections-europe_468x60.gif" alt="2011 CONNECTIONS Europe" width="468" height="60"></a>';

	$event_list['2012-06-12'] = '<a target="_blank" href="http://www.displaysearch.com/digitalsignage2012"'.
		'><img src="'. BASE_IMG_URL .'/images/events/2012-digital-signage_468x60.gif" alt="2012 Digital Signage Conference" width="468" height="60"></a>';

	foreach ($event_list as $event_date => $event_html) {
		if (strtotime($event_date) > time()) $event[] = $event_html;
	}

	function getEventBanner($event) {
		if (count($event) > 0) {
			return rand(0, count($event) - 1);
		} else {
			return -1;
		}
	}
?>