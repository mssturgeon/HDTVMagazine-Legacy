<?
	$ns = array(
		'content' => 'http://purl.org/rss/1.0/modules/content/',
		'wfw' => 'http://wellformedweb.org/CommentAPI/',
		'dc' => 'http://purl.org/dc/elements/1.1/',
		'feedburner' => 'http://rssnamespace.org/feedburner/ext/1.0',
		'itunes' => 'http://www.itunes.com/dtds/podcast-1.0.dtd'
	);

	function getChannelData($xml) {
		$channel = array();
		$channel['title'] = $xml->channel->title;
		$channel['link'] = $xml->channel->link;
		$channel['description'] = $xml->channel->description;
		$channel['pubDate'] = $xml->pubDate;
		$channel['timestamp'] = strtotime($xml->pubDate);
		$channel['generator'] = $xml->generator;
		$channel['language'] = $xml->language;

		return $channel;
	}

	function getArticleData($xml) {
		global $ns;

		$articles = array();
		foreach ($xml->channel->item as $item) {
			$article = array();
			$article['channel'] = $blog;
			$article['title'] = $item->title;
			$article['link'] = $item->link;
			$article['comments'] = $item->comments;
			$article['pubDate'] = $item->pubDate;
			$article['timestamp'] = strtotime($item->pubDate);
			$article['description'] = (string) trim($item->description);
			$article['isPermaLink'] = $item->guid['isPermaLink'];
			$article['guid'] = $item->guid;

			# get enclosure information
			$article['enclosure_url'] = $item->enclosure['url'];
			$article['enclosure_type'] = $item->enclosure['type'];

			# get data held in namespaces
			$content = $item->children($ns['content']);
			$dc = $item->children($ns['dc']);
			$wfw = $item->children($ns['wfw']);
			$itunes = $item->children($ns['itunes']);

			$article['creator'] = (string) $dc->creator;
			foreach ($dc->subject as $subject)
				$article['subject'][] = (string)$subject;

			foreach ($item->category as $category)
				$article['category'][] = (string)$category;

			$article['content'] = (string)trim($content->encoded);
			$article['commentRss'] = $wfw->commentRss;

			# add this article to the list
			$articles[$article['timestamp']] = $article;
		}

		return $articles;
	}

	function getContent($article) {
		global $debug;

		if ($article['content'] == '') {
			$dom = new DOMDocument();
			$dom->loadHTMLFile($article['link']);
			$dom->preserveWhiteSpace = false;

			$h2s = $dom->getElementsByTagName('h2');
			foreach($h2s as $h2) {
				if ($debug) echo "{$h2->getAttribute('class')}\n";
				if ($h2->getAttribute('class') == 'art-PostHeader') {
					$divs = $h2->parentNode->getElementsByTagName('div');
					foreach($divs as $div) {
						if ($debug) echo "\t{$div->getAttribute('class')}\n";
						if ($div->getAttribute('class') == 'art-PostContent') {
#							if ($debug) echo "nodeValue: {$div->nodeValue}\n";
#							if ($debug) echo "data: {$div->data}\n";
#							if ($debug) echo "textContent: {$div->textContent}\n";
#							if ($debug) echo "saveXML: {$dom->saveXML($div)}\n";
#							exit;
							return $dom->saveXML($div);
						}
					}
				}
			}
			if ($debug) echo "Returning empty string\n";
			return '';
		} else {
			if ($debug) echo "Returning content\n";
			return $article['content'];
		}
	}
?>