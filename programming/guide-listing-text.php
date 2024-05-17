<?
	header('Content-type: text/plain');

	// If the user_id is given, then use that, otherwise load global and use currently logged in user.
	if (isset($_GET['user_id'])) {
		define('BASE_DIR', '/var/www/html');
		require(BASE_DIR .'/includes/constants.php');
		require(BASE_DIR .'/includes/lib_mysql.php');
		require(BASE_DIR .'/includes/lib_common.php');
		require(BASE_DIR .'/includes/lib_guide.php');

		$user_id = $_GET['user_id'];
		$tzo = $_GET['tzo'];

		$result = mQuery("SELECT * FROM admin_settings");
		$admindata = mysql_fetch_array($result);
	} else {
		require('../global.php');
		require(BASE_DIR .'/includes/lib_guide.php');
		$user_id = $user->data['user_id'];
		if ($user_id == '') prompt_login(PHP_SELF);
//		if (!access(ACCESS_PREMIUM)) prompt_login(PHP_SELF);

		$tzo = $user->data['time_zone_offset'];
	}
	$base_url = 'http://'. SERVER_NAME;

	if (isset($_GET['d'])) {
		$now = strtotime($_GET['d'] .' 12:00am GMT');
		$today = $_GET['d'];
	} else {
		$now = time();
		$today = gmdate('m/d/Y', $now);
	}
?>
HDTV Magazine
~ Your Guide to High Definition Television ~

Daily Program Listing - <?
	echo "$today\n\n";

	include(BASE_DIR .'/ads/text.php');
/*
	echo "+-------------------------------------------------------------+\n".
	"| National Geographic Goes HD!                                |\n".
	"|                                                             |\n".
	"| Dare to Explore National Geographic Channel in High         |\n".
	"| Definition. You can now experience your favorite NGC shows  |\n".
	"| with amazing clarity, heart-pounding sound, and wide-screen |\n".
	"| adventure. Call your cable operator or 1-877-77-NGCHD to    |\n".
	"| to find out more.:                                          |\n".
	"| http://channel.nationalgeographic.com/channel/hd/           |\n".
	"+-------------------------------------------------------------+\n\n";
*/

	// Get Default station_num List
	$qry = "SELECT t.tf_station_num, u.label, lcase(t.tf_station_affil) affil, t.ota, tf_station_call_sign, image_file, u.channel".
	" FROM {$admindata['tbl_stat']} t, join_user_station u".
	" WHERE t.tf_station_num = u.tf_station_num".
	"	AND u.user_id = ". $user_id .
	" ORDER BY u.priority, u.label";
	$result = mQuery($qry);

	// If there is an empty data set, let the user know
	if (mysql_num_rows($result) == 0) {
		echo 'Thank you for subscribing to the Daily Program Listing.  This email provides a daily listing, by network, of all HD programming for each of your preferred stations.'.
		'  Since you do not yet have any preferred stations configured in your profile, this email is empty.  Please Edit your Profile ('. FULL_URL_PROFILE_STATIONS .') and select the stations you would like to include in this listing.';
		exit;
	}

	// Reset and Loop through stations
	while ($row = mysql_fetch_assoc($result)) {
		// Create Network table
		echo "*** {$row['channel']} {$row['tf_station_call_sign']}\t{$row['label']}\n";

		$qry = "SELECT DISTINCT p.tf_database_key, p.tf_title, tf_epi_title, tf_tv_rating, tf_org_air_date, tf_air_time, tf_duration, tf_desc_160 tf_desc, tf_dolby, program_image".
		" FROM {$admindata['tbl_prog']} p, {$admindata['tbl_sked']} s".
		" LEFT JOIN join_program_image jpi ON concat('". addslashes($row['label']) ."', '-', p.tf_title) = jpi.name".
		" WHERE s.tf_database_key = p.tf_database_key".
		"	AND FROM_UNIXTIME(s.tf_air_time + ". $tzo .", '%m/%d/%Y') = '". $today ."'".
		"	AND s.tf_station_num = ". $row['tf_station_num'] .
		"	AND options & ". OPT_SKED_HDTV .
		" ORDER BY tf_air_time ASC";
		$program_result = mQuery ($qry);

		if (mysql_num_rows($program_result) > 0) {
			// Loop through programming
			while ($program = mysql_fetch_assoc ($program_result)) {
				$episode = $program['tf_epi_title'];
				if ($episode != '') $episode = '"'. $episode .'"';
				// Ratings
				$rating = $program['tf_mpaa_rating'];
				if ($rating == '') $rating = $program['tf_tv_rating'];

				$runtime = getProgramDuration($program);
				$orig_date = getProgramDate($program);

				echo gmdate("g:ia", $program['tf_air_time'] + $tzo) ."\t{$program['tf_title']}\t$episode\n";
				echo "{$program['tf_desc']}\n";
				echo "$rating\t$runtime\t$orig_date\n\n";
			}
		} else {
			echo "No HD Programming Tonight\n\n";
		}
		echo "\n";
	}
	?>
