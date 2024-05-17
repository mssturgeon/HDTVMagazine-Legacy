<?
	require('../../global.php');
	if (!access(ACCESS_ADMIN_ANY)) access_denied();
	header('Content-Type: text/xml');
	
	echo '<mediawiki xmlns="http://www.mediawiki.org/xml/export-0.3/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.mediawiki.org/xml/export-0.3/ http://www.mediawiki.org/xml/export-0.3.xsd" version="0.3" xml:lang="en">'.
	'<siteinfo>'.
	'	<sitename>HDWiki</sitename>'.
	'	<base>http://www.hdtvmagazine.com/hdwiki/index.php/Main_Page</base>'.
	'	<generator>MediaWiki 1.6.10</generator>'.
	'	<case>first-letter</case>'.
	'		<namespaces>'.
	'		<namespace key="-2">Media</namespace>'.
	'		<namespace key="-1">Special</namespace>'.
	'		<namespace key="0" />'.
	'		<namespace key="1">Talk</namespace>'.
	'		<namespace key="2">User</namespace>'.
	'		<namespace key="3">User talk</namespace>'.
	'		<namespace key="4">HDWiki</namespace>'.
	'		<namespace key="5">HDWiki talk</namespace>'.
	'		<namespace key="6">Image</namespace>'.
	'		<namespace key="7">Image talk</namespace>'.
	'		<namespace key="8">MediaWiki</namespace>'.
	'		<namespace key="9">MediaWiki talk</namespace>'.
	'		<namespace key="10">Template</namespace>'.
	'		<namespace key="11">Template talk</namespace>'.
	'		<namespace key="12">Help</namespace>'.
	'		<namespace key="13">Help talk</namespace>'.
	'		<namespace key="14">Category</namespace>'.
	'		<namespace key="15">Category talk</namespace>'.
	'	</namespaces>'.
	'</siteinfo>';
	
	$sql = "SELECT id,term,definition FROM glossary";
	$result = mQuery($sql);
	while ($row = mysql_fetch_Assoc($result)) {
		if ($row[id] != 362) {
		$from = array('<br>', '<br />');
		$to = array("\n", "\n");
		$term = htmlspecialchars(str_replace($from, $to, $row[term]), ENT_COMPAT, 'UTF-8');
		$definition = htmlspecialchars(str_replace($from, $to, $row[definition]), ENT_COMPAT, 'UTF-8');
		echo "<page>
			<title>$term</title>
			<id>$row[id]</id>
			<revision>
				<id></id>
				<timestamp></timestamp>
				<contributor>
					<username></username>
					<id></id>
				</contributor>
				<text xml:space=\"preserve\">$definition</text>
			</revision>
		</page>";
		}
	}
	echo '</mediawiki>';
?>
