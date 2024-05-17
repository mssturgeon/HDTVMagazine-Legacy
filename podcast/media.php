<?
	define('BASE_DIR', '/var/www/html');
	require(BASE_DIR .'/includes/constants.php');
	require(BASE_DIR .'/includes/lib_mysql.php');

	$debug = isset($_GET[debug]);
	if ($debug) {
		header('Content-Type: text/plain');
	} else {
		header('Content-Type: application/xml');
	}
	
	$url = 'http://www.hdtvmagazine.com'. PHP_SELF;

	echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<rss xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" version="2.0">
	<channel>
		<title>HDTV Magazine Media Channel</title>
		<link>http://phobos.apple.com/WebObjects/MZStore.woa/wa/viewPodcast?id=189033712</link>
		<language>en-us</language>
		<copyright>&#169; 2006 HDTV Magazine, Ltd.</copyright>
		<itunes:subtitle>Audio, Video, and Documents from HDTV Magazine</itunes:subtitle>
		<itunes:author>Shane Sturgeon &#38; Dale Cripps</itunes:author>
		<itunes:summary>Look for our Podcast in the iTunes Music Store</itunes:summary>
		<description>Look for our Podcast in the iTunes Music Store</description>
		<itunes:owner>
			<itunes:name>Shane Sturgeon &#38; Dale Cripps</itunes:name>
			<itunes:email>feedback@hdtvmagazine.com</itunes:email>
		</itunes:owner>
		<itunes:image href="http://www.hdtvmagazine.com/images/hdtvmagazine_300.jpg" />
		<itunes:category text="Education">
			<itunes:category text="Educational Technology" />
		</itunes:category>
		<itunes:category text="TV &amp; Film"/>
		<itunes:category text="Technology">
			<itunes:category text="Tech News" />
		</itunes:category>
<?
  	$sql = "SELECT * FROM podcast";
  	$result = mQuery($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$pubDate = date('r', $row[pubDate]);
		echo '<item><title>'. htmlspecialchars($row[title], ENT_COMPAT, 'UTF-8') .'</title>'.
		'<itunes:author>'. htmlspecialchars($row[author], ENT_COMPAT, 'UTF-8') .'</itunes:author>'.
		'<itunes:subtitle>'. htmlspecialchars($row[subtitle], ENT_COMPAT, 'UTF-8') .'</itunes:subtitle>'.
		'<itunes:summary>'. htmlspecialchars($row[summary], ENT_COMPAT, 'UTF-8') .'</itunes:summary>'.
		'<enclosure url="'. $row[url] .'" length="'. $row[length] .'" type="'. $row[type] .'" />'.
		"<guid>$url?episode=$row[id]</guid>".
		"<pubDate>$pubDate</pubDate>";
		if ($row[duration] > 0) echo "<itunes:duration>$row[duration]</itunes:duration>";
		echo '<itunes:keywords>'. htmlspecialchars($row[keywords], ENT_COMPAT, 'UTF-8') .'</itunes:keywords></item>';
	}
?>
	</channel>
</rss>
