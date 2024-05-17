<?
	require('../global.php');

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - About Us</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header-4.php');
	?>

	<h1>About HDTV Magazine</h1>

	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td style="vertical-align:top;padding-right:10px;">
			<? require('sidemenu.php');?>
		</td><td style="vertical-align:top;">
			<p>
				HDTV Magazine is <b>the</b> website for those who love HDTV. Our roots go back to 1984, when Dale Cripps founded The HDTV Newsletter, a dedicated professional publication distributed into 24 countries to those developing HDTV.
				On November 16, 1998, The HDTV Newsletter became the first online publication dedicated to HDTV -- HDTV Magazine. And on March 1st, 2005, HDTV Magazine was re-launched as HDTV Magazine, Ltd., a Partnership formed between
				Dale Cripps (Founder) and Shane Sturgeon (Chief Technologist). Our mission has remained consistent throughout:
			</p>
			<div align="center"><table class="quote"><tr><td class="quote">To educate the public on what HDTV is and to give voice to the culture which arises from its engagement.</td></tr></table></div>
			<p>
				HDTV is the image for the 21st Century. With little argument it is the most influential force on earth today. Nothing from technology to culture will be left untouched by its coming.  Those who own and enjoy it are the quickest to
				agree with this view.
			</p><p>
				We are proud of our role in bringing you HDTV knowledge, but there is still much more to be done. With so much new technology approaching so fast, confusion has set in. That confusion, while understandable, is hurting us
				(and even hurting our national security). We simply must put an end to its destructive stay if we are to hasten this transition and spread the benefits still faster.  We believe that good old fashioned authority does more to combat
				confusion than anything else can.  The mountaintop is where the wise man sits to tells us the meaning of life and likewise we must go to authority if knowledge is what we seek.
			</p><p>
				Those now writing for HDTV Magazine come from the ranks of pioneers and executives who are responsible for bringing HDTV to life. I have asked these authoritative contributors to give you their HDTV story as "Letters to
				America." My advice to them is to "let the public know what is coming and how to use it."
			</p>
			Our present list of HDTV Authorities includes:<br>
			<?
				$qry = "SELECT m.author_id, title, bio, author_name FROM mt_entry e, mt_author m, aux_author a".
				" WHERE entry_blog_id <> 7".
				"	AND entry_status = 2".
				"	AND entry_author_id = m.author_id".
				"	AND entry_author_id = a.author_id".
				" GROUP BY m.author_id".
				" ORDER BY entry_created_on DESC";
				$result = mQuery($qry);
				while ($author = mysql_fetch_assoc($result)) {
						echo '<a href="'. URL_ARTICLES_AUTHOR .'?id='. $author['author_id'] .'">'. $author['author_name'] .'</a>, '. $author['title'] .'<br>';
				}
			?>
			<br>
			... and many more coming!<br>
			<br>
			<b>Welcome to HDTV Magazine</b>
		</td>
	</tr></table>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</div></body>
</html>
