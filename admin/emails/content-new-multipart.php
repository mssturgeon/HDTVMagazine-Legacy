<?
	$debug = isset($_GET['debug']);
	if ($debug) header('Content-Type: text/plain');

	# Include necessary libraries for automated scripts
	define('BASE_DIR', '/var/www/html');
	require_once(BASE_DIR .'/includes/constants.php');
	require_once(BASE_DIR .'/includes/lib_common.php');
	require_once(BASE_DIR .'/includes/lib_mysql.php');

	# Include phpbb library for table constants
	define('IN_PHPBB', true);
	$phpbb_root_path = BASE_DIR .'/forum/';
	$phpEx = substr(strrchr(__FILE__, '.'), 1);
	include_once($phpbb_root_path . 'common.' . $phpEx);

	# Load admindata - Used for address in email footer
	$result = mQuery("SELECT * FROM admin_settings WHERE id = 1");
	$admindata = mysql_fetch_assoc($result);

	$base_url = 'http://'. SERVER_NAME;

#	$id = isset($_GET['id']) ? $_GET['id'] : exit;
	$id = getSafeId('id');
	$boundary = isset($_GET['boundary']) ? $_GET['boundary'] : '';

#	$online_url = $_SERVER['SCRIPT_URI'] ."?id=$id";
	$online_url = $_SERVER['REQUEST_URI'] ."?id=$id";

	# Get Blog Info
	$sql = "
	SELECT DISTINCT entry_blog_id, e.entry_id, entry_excerpt, entry_created_on, entry_title, author_name, topic_replies, aux.topic_id
	FROM mt_entry e, mt_author a, aux_mt_entry aux
	LEFT JOIN ". TOPICS_TABLE ." t ON (aux.topic_id = t.topic_id)
	WHERE e.entry_id = $id
		AND e.entry_id = aux.entry_id
		AND entry_author_id = author_id";
	$result = mQuery($sql);
	if ($debug) echo "$sql\n";
	$row = mysql_fetch_assoc($result);

	$entry = getEntryInfo($row['entry_blog_id']);

	$ts = strtotime($row['entry_created_on']);
	$y = date('Y', $ts);
	$m = date('m', $ts);
	$entry['date'] = getDateString($ts);
	$entry['link'] = $base_url ."/$entry[blog_dir]/$y/$m/". dirify($row['entry_title']) .".php";
	$entry['title'] = $row['entry_title'];
	$entry['author'] = $row['author_name'];
	$entry['excerpt'] = $row['entry_excerpt'];
	$entry['topic_replies'] = $row['topic_replies'];
	$entry['topic_id'] = $row['topic_id'];

	# Get categories
	$sql = "
	SELECT c.category_label, c.category_id
	FROM mt_category c, mt_placement p
	WHERE {$row['entry_id']} = p.placement_entry_id
		AND c.category_id = p.placement_category_id";
	$res_categories = mQuery($sql);
	$entry['catlinks'] = array();
	while ($row_category = mysql_fetch_assoc($res_categories)) {
		$entry['catlinks'][] = '<a style="color:#003F87;" href="'. $base_url .'/category.php?id='. $row_category['category_id'] .'&amp;category='. urlencode(stripslashes($row_category['category_label'])) .'">'. stripslashes($row_category['category_label']) .'</a>';
	}

	if ($entry['topic_id'] != '') {
		if ($entry['topic_replies'] > 0) {
			$comments = '<img src="'. $base_url .'/images/icon_comments.gif" alt=""> '.
		'<a style="color:#003F87;" href="'. $base_url .'/forum/viewtopic.php?t='. $entry['topic_id'] .'">Comments</a> ('. $entry['topic_replies'] .')';
		} else {
			$comments = '<img src="'. $base_url .'/images/icon_comments.gif" alt=""> '.
		'<a style="color:#800000; font-weight:bold;" href="'. $base_url .'/forum/viewtopic.php?t='. $entry['topic_id'] .'">Post First Comment</a>';
		}
	}

	if ($boundary != '') {
		echo '--'. $boundary ."\r\n".
		"Content-Type: text/plain\r\n";
?>
Hello,

A new <?=$entry['entry_type']?> has been posted to HDTV Magazine <?=$entry['entry_type']?>s:

============================================================
# <?=html_entity_decode($entry['title'], ENT_QUOTES, 'UTF-8')?> #
============================================================
<?=('by '. $entry['author'])?> - <?=$entry['date']?>


Excerpt:
------------------------------------------------------------
<?=html_entity_decode(strip_tags($entry['excerpt']), ENT_QUOTES, 'UTF-8')?>

------------------------------------------------------------

Read <?=$entry['entry_type']?>:
<?=$entry['link']?>



Enjoy,

-- Dale & Shane
HDTV Magazine
<?
	if ($boundary != '') {include(BASE_DIR .'/includes/email_footer-text.php');}
	echo '--'. $boundary ."\r\n".
	"Content-Type: text/html\r\n";
}
	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>New <?=$entry['entry_type']?>: <?=$entry['title']?></title>
	<base href="<?=$base_url?>" target="_blank">
	<style>
		<? include(BASE_DIR .'/css/email.css')?>
	</style>
</head>
<body><div id="email_container">
	<table border="0" cellpadding="0" cellspacing="0" style="margin:0; padding:0; width:100%"><tr><td>
		<?
			include(BASE_DIR .'/includes/email_header-html.php');
		?>
		<table border="0" cellpadding="0" cellspacing="0" class="entry" style="border:0; margin:0; padding:0; width:100%">
			<tr><td style="font-size:15pt; font-weight:bold; color:red; text-align:right;">New <?=$entry['entry_type']?> Alert</td></tr>
			<tr><td style="font-weight:bold; margin-bottom:1em">
				A <a href="<?=$entry['link']?>" style="color:#<?=PRIMARY_COLOR?>;">New <?=$entry['entry_type']?></a> has been posted to
				<a href="<?=($base_url .'/'. $entry['blog_dir'])?>" style="color:#<?=PRIMARY_COLOR?>;">HDTV Magazine <?=$entry['entry_type']?>s</a>:
			</td></tr>
			<tr><td style="padding:10px;">
				<h4 style="color:#AAAAAA; font:.8em Arial, sans serif; margin:0; text-transform:uppercase;">
					<?=implode(' &bull; ', $entry['catlinks'])?>
				</h4>
				<h2 style="color:#<?=PRIMARY_COLOR?>; display:inline; font:2em Times New Roman, serif; font-weight:bold; margin:0;">
					<a href="<?=$entry['link']?>" style="color:#<?=PRIMARY_COLOR?>;"><?=$entry['title']?></a>
				</h2>
				<h4 style="color:#AAAAAA; font:.8em Arial, sans serif; margin:0; text-transform:uppercase;">
					<span style="text-transform:none">By</span> <?=$entry['author']?> &bull; <span style="color:#800000"> <?=$entry['date']?></span>
				</h4>
			</td></tr><tr><td class="content" style="padding:10px;">
				<?=str_replace(". ", ". \n", nl2br(stripslashes($entry['excerpt'])))?>
			</td></tr><tr><td style="padding:5px; text-align:right;">
				<a href="<?=$entry['link']?>" style="color:#<?=PRIMARY_COLOR?>">Read <?=$entry['read_text']?></a> &bull; <?=$comments?>
			</td></tr><tr><td>
				<br>
				Enjoy,<br>
				<br>
				-- Dale &amp; Shane<br>
				<a style="color:#<?=PRIMARY_COLOR?>" href="http://www.hdtvmagazine.com/">HDTV Magazine</a>
				<br>
			</td></tr><tr><td>
				<? if ($boundary != '') {include(BASE_DIR .'/includes/email_footer-html.php');}?>
			</td></tr>
		</table>
	</td></tr></table>
</div></body>
</html>
<?
	if ($boundary != '') {echo '--'. $boundary ."--\r\n";}
?>