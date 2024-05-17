<?xml version="1.0" encoding="<$MTPublishCharset$>"?>
<rss version="2.0">
	<!-- feed.xml template -->
	<channel>
		<title>HDTV Magazine <$MTBlogName remove_html="1" encode_xml="1"$></title>
		<link><$MTBlogURL$></link>
		<description><$MTBlogDescription remove_html="1" encode_xml="1"$></description>
		<language><$MTBlogLanguage ietf="1"$></language>
		<copyright>Copyright <$MTDate format="%Y"$></copyright>
		<lastBuildDate><MTEntries lastn="1"><$MTEntryDate format_name="rfc822"$></MTEntries></lastBuildDate>
		<generator>http://www.movabletype.org/?v=<$MTVersion$></generator>
		<docs>http://blogs.law.harvard.edu/tech/rss</docs>

		<MTEntries lastn="15">
			<item>
				<title><$MTEntryTitle remove_html="1" encode_xml="1"$></title>
				<description>
					<$MTEntryExcerpt encode_xml="1" convert_breaks="1"$>
				</description>
				<link><$MTEntryPermalink encode_xml="1"$></link>
				<guid><$MTEntryPermalink encode_xml="1"$></guid>
				<category><$MTEntryCategory remove_html="1" encode_xml="1"$></category>
				<pubDate><$MTEntryDate format_name="rfc822"$></pubDate>
				<!--author><$MTEntryAuthor$></author-->
				<source url="<$MTBlogURL$>"><$MTBlogName remove_html="1" encode_xml="1"$></source>
			</item>
		</MTEntries>

	</channel>
</rss>