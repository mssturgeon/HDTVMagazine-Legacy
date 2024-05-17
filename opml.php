<?
	require('global.php');
	header('Content-type: text/x-opml');
	
	echo '<?xml version="1.0" encoding="utf-8"?>';
?>
<opml version="1.1">
<head>
	<title>HDTV Magazine Feeds</title>
	<dateCreated><?=date('r')?></dateCreated>
	<ownerName></ownerName>
</head>
<body>
	<outline text="HDTV Magazine Feeds" >
		<outline title="HDTV Magazine Main Feed" text="HDTV Magazine Main Feed" htmlUrl="http://www.hdtvmagazine.com/" type="rss" xmlUrl="http://feeds.hdtvmagazine.com/hdtv" />
		<outline title="HDTV Magazine Articles" text="HDTV Magazine Articles" htmlUrl="http://www.hdtvmagazine.com/articles/" type="rss" xmlUrl="http://feeds.hdtvmagazine.com/hdtv-articles" />
		<outline title="HDTV Reviews" text="HDTV Bulletins" htmlUrl="http://www.hdtvmagazine.com/news/" type="rss" xmlUrl="http://feeds.hdtvmagazine.com/hdtv-news" />
		<outline title="HDTV Bulletins" text="HDTV Magazine Reviews" htmlUrl="http://www.hdtvmagazine.com/reviews/" type="rss" xmlUrl="http://feeds.hdtvmagazine.com/hdtv-reviews" />
		<outline title="HDTV Bulletins" text="HDTV Magazine Forum" htmlUrl="http://www.hdtvmagazine.com/forum/" type="rss" xmlUrl="http://feeds.hdtvmagazine.com/hdtv-forum" />
		<!--outline title="HDTV Magazine History" text="HDTV Magazine History" htmlUrl="http://www.hdtvmagazine.com/history/" type="rss" xmlUrl="http://www.hdtvmagazine.com/history/index.xml" />
		<outline title="HDTV Magazine Interviews" text="HDTV Magazine Interviews" htmlUrl="" type="rss" xmlUrl="http://www.hdtvmagazine.com/history/interviews/index.xml" /-->
	</outline>
</body>
</opml>
