<?
	// For the benefit of PriceGrabber and the forum pages, who are sourcing this file directly
	require_once('constants.php');

	$menu_output = <<<EOT
<table class="menuItem" cellpadding="0" cellspacing="0"><tr>
	<td class="menuItem"><a href="http://www.hdtvmagazine.com/index.php">Home</a></td>
	<td class="menuItem"><a href="http://www.hdtvmagazine.com/news/index.php">News</a></td>
	<td class="menuItem"><a href="http://www.hdtvmagazine.com/reviews/index.php">Reviews</a></td>
	<td class="menuItem"><a href="http://www.hdtvmagazine.com/forum/index.php">Forum</a></td>
	<td class="menuItem"><a href="http://www.hdtvmagazine.com/articles/index.php">Articles</a></td>
	<td class="menuItem"><a href="http://www.hdtvmagazine.com/columns/index.php">Columns</a></td>
	<td class="menuItem"><a href="http://www.hdtvmagazine.com/podcast/index.php">Podcast</a></td>
	<td class="menuItem"><a href="http://www.hdtvmagazine.com/programming/guide.php">Programming</a></td>
	<td class="menuItem"><a href="http://www.hdtvmagazine.com/reports/hdtv-technology-review.php">Reports</a></td>
	<td class="menuItem"><a href="http://www.hdtvmagazine.com/studies/index.php">Studies</a></td>
	<td class="menuItem"><a href="http://www.hdtvmagazine.com/resources/index.php">Resources</a></td>
	<td class="menuItem"><a href="http://www.hdtvmagazine.com/equipment/hdtvs-best-rated.php">HDTVs &amp; Equipment</a></td>
	<td class="menuItem" style="vertical-align:middle;border-right:0px;padding-right:10px" nowrap="nowrap">
		<!-- Search Google -->
    <form action="http://www.hdtvmagazine.com/search.php" id="cse-search-box">
			<input type="hidden" name="cx" value="partner-pub-2099480674594177:k00px5y2ria" />
			<input type="hidden" name="cof" value="FORID:10" />
			<input type="hidden" name="ie" value="ISO-8859-1" />
			<input type="text" class="inputText" id="search_box" name="q" maxlength="255" value="search" onfocus="this.value='';" />
			<input type="submit" name="sa" value="Search" />
    </form>
		<!-- Search Google -->
	</td>
</tr></table>
EOT;
?>