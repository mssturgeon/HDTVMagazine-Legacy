<?
	// For the benefit of PriceGrabber and the forum pages, who are sourcing this file directly
	require_once('constants.php');

	$menu_output = ''.
		'<table class="menuItem" cellpadding="0" cellspacing="0"><tr>'.
			'<td class="menuItem"><a href="http://www.hdtvmagazine.com/index.php">Home</a></td>'.
			'<td class="menuItem"><a href="http://www.hdtvmagazine.com/news/index.php">News</a></td>'.
			'<td class="menuItem"><a href="http://www.hdtvmagazine.com/reviews/index.php">Reviews</a></td>'.
			'<td class="menuItem"><a href="http://www.hdtvmagazine.com/forum/index.php">Forum</a></td>'.
			'<td class="menuItem"><a href="http://www.hdtvmagazine.com/articles/index.php">Articles</a></td>'.
			'<td class="menuItem"><a href="http://www.hdtvmagazine.com/columns/index.php">Columns</a></td>'.
			'<td class="menuItem"><a href="http://www.hdtvmagazine.com/podcast/index.php">Podcast</a></td>'.
			'<td class="menuItem"><a href="http://www.hdtvmagazine.com/programming/guide.php">Programming</a></td>'.
			'<td class="menuItem"><a href="http://www.hdtvmagazine.com/reports/hdtv-technology-review.php">Reports</a></td>'.
			'<td class="menuItem"><a href="http://www.hdtvmagazine.com/studies/index.php">Studies</a></td>'.
			'<td class="menuItem"><a href="http://www.hdtvmagazine.com/resources/index.php">Resources</a></td>'.
			'<td class="menuItem"><a href="http://www.hdtvmagazine.com/equipment/hdtvs-best-rated.php">HDTVs &amp; Equipment</a></td>'.
			'<td class="menuItem" style="vertical-align:middle;border-right:0px;padding-right:10px" nowrap="nowrap">'.
				'<!-- Search Google -->'.
				'<form method="get" action="http://www.hdtvmagazine.com/search.php" target="_top" id="cse-search-box">'.
					'<input type="text" class="inputText" id="search_box" name="q" '.
						'maxlength="255" value="search" onfocus="this.value=\'\';" />'.
					'<input type="hidden" name="domains" value="hdtvmagazine.com" />'.
					'<input type="hidden" name="cx" value="partner-pub-2099480674594177:ciads6huqky" />'.
					'<input type="hidden" name="sitesearch" value="hdtvmagazine.com" />'.
					'<input type="hidden" name="client" value="pub-2099480674594177" />'.
					'<input type="hidden" name="forid" value="1" />'.
					'<input type="hidden" name="ie" value="ISO-8859-1" />'.
					'<input type="hidden" name="oe" value="ISO-8859-1" />'.
					'<input type="hidden" name="safe" value="active"></input>'.
					'<input type="hidden" name="flav" value="0001"></input>'.
					'<input type="hidden" name="sig" value="kyBota6MZwmrrLCK"></input>'.
					'<input type="hidden" name="cof" value="GALT:#008000;GL:1;DIV:#'. BORDER_COLOR .';VLC:'. LINK_COLOR .';AH:center;BGC:FFFFFF;LBGC:EEEEEE;ALC:'. LINK_COLOR .';LC:'. LINK_COLOR .';T:000000;GFNT:'. LINK_COLOR .';GIMP:'. LINK_COLOR .';LH:57;LW:324;L:http://www.hdtvmagazine.us/images/hdtvmagazine.gif;S:http://www.hdtvmagazine.com/;FORID:11;" />'.
					'<input type="hidden" name="hl" value="en" />'.
				'</form>'.
				'<!-- Search Google -->'.
			'</td>'.
		"</tr></table>\n";
?>
