<?
	set_time_limit(0);

	// BASE_DIR is the full OS path to the web document directory.  User primarily in include/require statements
	define('BASE_DIR', '/var/www/html');
	require_once(BASE_DIR .'/includes/constants.php');
	require_once(BASE_DIR .'/includes/lib_mysql.php');
	require_once(BASE_DIR .'/includes/lib_common.php');

	$debug = isset($_GET['debug']);
	if ($debug) header('Content-Type: text/plain');

### Replicate blog categories. Any created for articles (id 1) should be replicated for bulletins (id 7), columns (id 10) and podcasts (9)
	if ($debug) echo date('Y/m/d H:i:s') .": Replicating article categories\n";
	$blog_ids = array(7,9,10);
	$qry = "SELECT category_label, category_description, category_author_id, category_parent FROM mt_category WHERE category_blog_id = 1";
	$result = mQuery($qry);
	while ($row = mysql_fetch_assoc($result)) {
		foreach ($blog_ids as $blog_id) {
			$sql_category = "SELECT category_id FROM mt_category WHERE category_blog_id = '$blog_id' AND category_label = '{$row['category_label']}'";
			if ($debug) echo "$sql_category\n";

			$res_category = mQuery($sql_category);
			if (mysql_num_rows($res_category) == 0) { # Insert
				$sql = "INSERT INTO mt_category (category_blog_id, category_label, category_description, category_author_id, category_parent)
				VALUES ($blog_id, '{$row['category_label']}', '{$row['category_description']}', '{$row['category_author_id']}', '{$row['category_parent']}')";
			} elseif (mysql_num_rows($res_category) == 1) { # Update
				$row_category = mysql_fetch_assoc($res_category);
				$sql = "UPDATE mt_category SET
					category_description = '{$row['category_description']}',
					category_author_id = '{$row['category_author_id']}',
					category_parent = '{$row['category_parent']}'
				WHERE category_id = '{$row_category['category_id']}'";
			} else {
				echo "ERROR: More than one category found: $blog_id, {$row['category_label']}\n";
				echo "$sql_category\n";
			}
			if ($debug) {echo "$sql\n";} else {mQuery($sql);}
		}
	}
	if ($debug) echo date('Y/m/d H:i:s') .": DONE (Replicating article categories)\n";
?>