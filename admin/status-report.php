<?
	// If the user_id is given, then use that, otherwise load global and use currently logged in user.
	if (isset($_GET['no_session'])) {
		define('BASE_DIR', '/var/www/html');
		require(BASE_DIR .'/includes/constants.php');
		require(BASE_DIR .'/includes/lib_common.php');
		require(BASE_DIR .'/includes/lib_mysql.php');
	} else {
		require('../global.php');
		if (!access(ACCESS_ADMIN_ANY)) prompt_login(PHP_SELF);
	}
	header('Cache-Control: no-store'); // HTTP/1.1

	$days_to_display = 14; # For article publication
 	$ts = time();
 	$datestamp = date('Y-m-d', $ts);
 	$startdate = date('Y-m-d', $ts - $days_to_display*DAYS);

 	// Get total users
		$result = mQuery("SELECT id FROM user");
		$users_reg_total = mysql_num_rows($result);

	// *** New Products ***
		$result = mQuery("SELECT man_id FROM tbl_models WHERE (edited = 0 OR type = '')");
		$num_new_products = mysql_num_rows($result);

	// *** New Events ***
		$result = mQuery("SELECT id FROM event WHERE rank = 0");
		$num_new_events = mysql_num_rows($result);

	// *** Unlinked Paypal transactions
		$result = mQuery("SELECT id FROM paypal_ipn WHERE user_id = 0 AND item_number <> 5 AND invoice = ''");
		$num_pp_unlinked = mysql_num_rows($result);

	// *** Blank Paypal transactions
		$result = mQuery("SELECT id FROM paypal_ipn WHERE user_id = 0 AND item_number <> 5 AND receiver_email = ''");
		$num_pp_blank = mysql_num_rows($result);

	// *** Unranked News Items ***
		$result = mQuery("SELECT id FROM hdtv_rss WHERE rank = 0");
		$num_unranked_news = mysql_num_rows($result);

	// *** Uncategorized News Items ***
#		$result = mQuery("SELECT id FROM hdtv_rss WHERE category_id IS NULL AND rank > 0");
#		$num_uncat_news = mysql_num_rows($result);

	// *** Unpublished Blogs ***
#		$result = mQuery("SELECT entry_id FROM mt_entry WHERE entry_blog_id = 1 AND entry_status = 1");
#		$num_draft_entries = mysql_num_rows($result);

	// *** Uncategorized Entries ***
		$sql = "
		SELECT entry_id
		FROM mt_entry LEFT JOIN mt_placement ON entry_id = placement_entry_id
		WHERE entry_blog_id <> 6
			AND entry_status = 2
			AND placement_category_id IS NULL";
		$result = mQuery($sql);
		$num_uncat_entries = mysql_num_rows($result);

	// *** Entries w/o excerpts ***
		$result = mQuery("SELECT entry_id FROM mt_entry WHERE entry_blog_id IN (". INCLUDE_BLOGS_ALL .") AND (entry_excerpt IS NULL OR entry_excerpt = '')");
		$num_entries_noexcerpt = mysql_num_rows($result);
?>
<html>
<head>
	<title>Daily Status Report (<?=date('M d, Y')?>)</title>
	<link rel="stylesheet" type="text/css" href="http://www.hdtvmagazine.com/css/main-4.css">
	<style>
		body td {font-size:8pt}
	</style>
</head>
<body style="margin:1em;">

	<h2>Daily Status Report (<?=date('M d, Y')?>)</h2>
	The following tables provide a brief system status and daily update on several key website factors. <b>If any of these values is > 0 ... that is a problem</b>:<br>
	<br>

	<div style="float:left">
		<h2>Shane's Action Items</h2>
		<table cellpadding="0" cellspacing="0" class="type1b">
			<tr>
				<td class="type1b_header">Metric Description</td>
				<td class="type1b_header">#</td>
				<td class="type1b_header">Last Update</td>
			</tr><tr>
				<td class="type1b"><a href="<?=FULL_URL_ADMIN_PRODUCTS?>">New Products</a></td>
				<td class="type1b" style="text-align:right"><?=$num_new_products?></td>
				<td class="type1b"></td>
			</tr><tr>
				<td class="type1b"><a href="<?=FULL_URL_EVENTS_ADMIN?>">New Events</a></td>
				<td class="type1b" style="text-align:right"><?=$num_new_events?></td>
				<td class="type1b"></td>
			</tr><tr>
				<td class="type1b"><a href="http://<?=SERVER_NAME?>/admin/cdbs-unmatched.php">Unmatched Stations</a></td>
				<td class="type1b" style="text-align:right"><?=$num_unmatched_stations?></td>
				<td class="type1b"></td>
			</tr><tr>
				<td class="type1b"><a href="http://<?=SERVER_NAME?>/admin/reports/fcc-stations.php">Unavailable Stations</a></td>
				<td class="type1b" style="text-align:right"><?=$num_unavailable_stations?></td>
				<td class="type1b"></td>
			</tr><tr>
				<td class="type1b"><a href="http://<?=SERVER_NAME?>/admin/digg-entries.php">Digg Entries</a></td>
				<td class="type1b" style="text-align:right"><?=$num_digg_entries?></td>
				<td class="type1b"></td>
			</tr><tr>
				<td class="type1b">Unlinked Paypal Transactions</td>
				<td class="type1b" style="text-align:right"><?=$num_pp_unlinked?></td>
				<td class="type1b"></td>
			</tr><tr>
				<td class="type1b">Blank Paypal Transactions</td>
				<td class="type1b" style="text-align:right"><?=$num_pp_blank?></td>
				<td class="type1b"></td>
			</tr><tr>
				<td class="type1b"><a href="/admin/bb-no-user.php">No 'user' entry</a></td>
				<td class="type1b" style="text-align:right"><?=$num_bb_no_user?></td>
				<td class="type1b"></td>
			</tr><tr>
				<td class="type1b"><a href="/admin/user-no-bb.php">No 'phpbb' entry (from user)</a></td>
				<td class="type1b" style="text-align:right"><?=$num_user_no_bb?></td>
				<td class="type1b"></td>
			</tr>
		</table>
	</div>

	<div style="float:left; margin-left:20px">
		<h2>Dale's Action Items</h2>
		<table cellpadding="0" cellspacing="0" class="type1b">
			<tr>
				<td class="type1b_header">Metric Description</td>
				<td class="type1b_header">#</td>
			</tr><tr>
				<td class="type1b"><a href="<?=FULL_URL_NEWS_ADMIN?>?type=rank">Unranked News Items</a></td>
				<td class="type1b" style="text-align:right"><?=$num_unranked_news?></td>
			</tr><tr>
				<td class="type1b"><a href="<?=FULL_URL_NEWS_ADMIN?>?type=category">Uncategorized News Items</a></td>
				<td class="type1b" style="text-align:right"><?=$num_uncat_news?></td>
			</tr><tr>
				<td class="type1b"><a href="http://<?=SERVER_NAME?>/cgi-bin/movabletype/mt.cgi?__mode=list_entries&blog_id=1&filter=status&filter_val=1">Unpublished Blog Entries</a></td>
				<td class="type1b" style="text-align:right"><?=$num_draft_entries?></td>
			</tr><tr>
				<td class="type1b"><a href="http://<?=SERVER_NAME?>/cgi-bin/movabletype/mt.cgi">Uncategorized Blog Entries</a></td>
				<td class="type1b" style="text-align:right"><?=$num_uncat_entries?></td>
			</tr><tr>
				<td class="type1b"><a href="http://<?=SERVER_NAME?>/cgi-bin/movabletype/mt.cgi">Entries w/o Excerpt</a></td>
				<td class="type1b" style="text-align:right"><?=$num_entries_noexcerpt?></td>
			</tr>
		</table>
	</div>

	<div style="float:left; margin-left:20px;">
		<h2>Article Publication Schedule</h2>
		<table class="standard">
			<thead>
				<th>Date</th>
				<th>Blog</th>
				<th>Article</th>
				<th>Author</th>
			<thead>
			<?
				$article_cal = array();
				$duration = 14;
				$start_date = strtotime('Tomorrow');
				$blank_text = '&lt;open&gt;';

				// Pre-populate date array
/*
				for ($x = 0; $x < $duration; $x++) {
					$date_number = $start_date + ($x*DAYS);
					$article_cal[date('D, n/j', $date_number)] = array($blank_text, '&nbsp;');
				}
*/

				// Insert known articles
				$sql = "
				SELECT DATE_FORMAT(entry_created_on, '%a, %c/%e') date, blog_name, entry_title title, author_name
				FROM mt_entry e, mt_author a, mt_blog b
				WHERE e.entry_author_id = a.author_id
					AND entry_blog_id <> 7
					AND blog_id = e.entry_blog_id
					AND entry_status = 4
				ORDER BY entry_created_on";
				$result = mQuery($sql);
				while ($row = mysql_fetch_assoc($result)) {
					$article_cal[$row[date]] = array($row[title], $row[author_name]);
					echo '<tr>'.
					'	<td nowrap>'. $row['date'] .'</td>'.
					'	<td>'. $row['blog_name'] .'</td>'.
					'	<td>'. $row['title'] .'</td>'.
					'	<td nowrap>'. $row['author_name'] .'</td>'.
					"</tr>\n";
				}

				// Output result
/*
				foreach ($article_cal as $date => $article_data) {
					$class = ($article_data[0] == $blank_text) ? 'oddRow' : 'evenRow';
					echo '<tr class="'. $class .'">'.
					'	<td class="grid">'. $date .'</td>'.
					'	<td class="grid">'. $article_data[0] .'</td>'.
					'	<td class="grid">'. $article_data[1] .'</td>'.
					"</tr>\n";
				}
*/
			?>
		</table>
	</div>
	<br clear="all" />

	<div style="float:left;width:200px;margin:10px">
		<h2>Top Domains (Accounts)</h2>
		<table cellpadding="0" cellspacing="0" class="type1b">
			<tr>
				<td class="type1b_header">Domain</td>
				<td class="type1b_header">#</td>
				<td class="type1b_header">%</td>
			</tr><?
			$sql = "SELECT RIGHT(email_address, LENGTH(email_address) - LOCATE('@', email_address)) domain, COUNT(*) num
			FROM user WHERE email_invalid < 3 GROUP BY domain ORDER BY num DESC LIMIT 15";
			$result = mQuery($sql);
			while ($row = mysql_fetch_assoc($result)) {
					echo '<tr>'.
					'	<td class="type1b">'. $row['domain'] .'</td>'.
					'	<td class="type1b" style="text-align:right">'. $row['num'] .'</td>'.
					'	<td class="type1b" style="text-align:right">'. sprintf('%2.2f', $row['num'] / $users_reg_total * 100) .'</td>'.
					"</tr>\n";
			}
			?>
		</table>
	</div>
	<br clear="all">

	<h2>Statistics</h2>
	<table cellpadding="0" cellspacing="0" class="type1b" style="">
		<tr>
			<td class="type1b_header">&nbsp;</td>
			<?
			// Write header & Initialize data
				$header['users_total'] = 'Total Users';
				$header['users_reg'] = 'New Users';
				$header['users_active'] = 'Active Users';
				$header['users_pd'] = 'Paid Subs';
				$header['users_pd_m'] = 'Paid Subs (Monthly)';
				$header['twitter_followers'] = 'Twitter Followers';
				$header['facebook_fans'] = 'Facebook Fans';
				for ($x=$ts; $x >= $ts - $days_to_display*DAYS; $x-=1*DAYS) {
					$index = date('Y-m-d', $x);
					echo '<td class="type1b_header" style="text-align:center">'. date('n/j', $x) .'</td>';
					$data['users_total'][$index] = '&nbsp;';
					$data['users_reg'][$index] = '&nbsp;';
					$data['users_active'][$index] = '&nbsp;';
					$data['users_pd'][$index] = '&nbsp;';
					$data['users_pd_m'][$index] = '&nbsp;';
					$data['twitter_followers'][$index] = '&nbsp;';
					$data['facebook_fans'][$index] = '&nbsp;';
				}
				echo "</tr>\n";

			// Output data
				$sql = "SELECT * FROM admin_stats WHERE datestamp >= '$startdate' AND datestamp <= CURDATE() ORDER BY datestamp DESC";
				$result = mQuery($sql);
				while ($row = mysql_fetch_assoc($result)) {
					$data['users_total'][$row['datestamp']] = $row['users_total'];
					$data['users_reg'][$row['datestamp']] = $row['users_reg'];
					$data['users_active'][$row['datestamp']] = $row['users_active'];
					$data['users_pd'][$row['datestamp']] = $row['users_pd'];
					$data['users_pd_m'][$row['datestamp']] = $row['users_pd_m'];
					$data['twitter_followers'][$row['datestamp']] = $row['twitter_followers'];
					$data['facebook_fans'][$row['datestamp']] = $row['facebook_fans'];
				}
				foreach ($data as $type => $days) {
					echo '<tr><td class="type1b_header" style="">'. $header[$type] .'</td>';
					foreach ($days as $day => $num) {
						$class = ($day == $datestamp) ? 'grid_bold_shade' : 'grid';
						echo '<td class="'.  $class .'" style="text-align:right;">'. $num .'</td>';
					}
					echo "</tr>\n";
				}
			?>
	</table>
	<br />

	<h2>Subscribers</h2>
	<table cellpadding="0" cellspacing="0" class="type1b" style="">
		<tr>
			<td class="type1b_header">&nbsp;</td>
			<td class="type1b_header">Base64</td>
			<?
			// Write header & Initialize data
				$header = array();
				$data = array();

				foreach ($SUB as $sub_value => $sub_label) $header["sub_$sub_value"] = $sub_label;

				for ($x=$ts; $x >= $ts - $days_to_display*DAYS; $x-=1*DAYS) {
					$index = date('Y-m-d', $x);
					echo '<td class="type1b_header" style="text-align:center">'. date('n/j', $x) .'</td>';
					foreach ($SUB as $sub_value => $sub_label) {
						$data["sub_$sub_value"][$index] = $row["sub_$sub_value"];
					}
				}
				echo "</tr>\n";

			// Output data
				mysql_data_seek($result, 0);
				while ($row = mysql_fetch_assoc($result)) {
					foreach ($SUB as $sub_value => $sub_label) {
						$data["sub_$sub_value"][$row['datestamp']] = $row["sub_$sub_value"];
					}
				}
				foreach ($data as $type => $days) {
					echo '<tr><td class="type1b_header" style="">'. $header[$type] .'</td>'.
					'<td class="type1b_header" style="">'. urlencode(base64_encode(strright($type, 'sub_'))) .'</td>';
					foreach ($days as $day => $num) {
						$style = ($day == $datestamp) ? 'grid_bold_shade' : 'grid';
						echo '<td class="'. $class .'" style="text-align:right">'. $num .'</td>';
					}
					echo "</tr>\n";
				}
			?>
	</table>
	This report can be viewed anytime <a href="http://www.hdtvmagazine.com/admin/status-report.php">here</a>.
</body>
</html>
