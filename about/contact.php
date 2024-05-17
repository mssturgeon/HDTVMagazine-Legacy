<?
	require('../global.php');

	$ids = array('sturgeon' => 1, 'cripps' => 3, 'milbourn' => 8, 'lamaestra' => 16, 'fisher' => 20);
	$name = isset($_GET['name']) ? $_GET['name'] : 'cripps';

	# Author Bio
	$bio_result = mQuery("SELECT author_name, bio, viglink_source FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_id = $ids[$name]");
	$bio_row = mysql_fetch_assoc($bio_result);
	$bio = $bio_row['bio'];
	$author = $bio_row['author_name'];
	$author .= (substr($author, -1) == 's') ? "'" : "'s";
	$viglink_source = $bio_row['viglink_source'];

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Contact Us</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
	<meta name="microid" content="bf6ac28423b6aa7daae4faea233f9f75d748daff" />
	<script language="javascript" type="text/javascript">
		function show_card(name) {
			oLink = document.getElementsByName('link');
			for (x=0;x<oLink.length;x++) {
				if (oLink[x].id == name) {
					oLink[x].className = 'selected';
				} else {
					oLink[x].className = '';
				}
			}

			oCard = document.getElementById('card');
			oCard.src = '/images/card-'+name+'_400x229.gif';
		}
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header-4.php');
	?>

	<h1>Contacting HDTV Magazine</h1>

	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td style="vertical-align:top;padding-right:10px;">
			<? require('sidemenu.php');?>
		</td><td style="vertical-align:top;">
			<p>
				If you would like to ask a question or provide feedback on one of our <a href="<?=URL_ARTICLES?>">Articles</a> or
				<a href="<?=URL_REVIEWS?>">Reviews</a>, please use the commentary section at the end of each article.
			</p><p>
				If you are interested in advertising, you can check out our website statistics on our <a href="<?=URL_CONTACT_ADVERTISE?>">advertising</a>
				page. You can also contact our advertising agency, <a href="http://www.federatedmedia.net/authors/hdtv">Federated Media</a> for
				information on inventory availability and pricing.
			</p><p>
				You can find our general contact information on our business cards below. Click on a name to show the business card.
				You may also contact us via our general <a href="<?=URL_HELP_FEEDBACK?>">Feedback Form</a> if you prefer.
			</p>

			<table class="bare" cellpadding="3" cellspacing="0" style="margin-bottom:20px">
				<tr>
					<td style="width:200px" class="<?=(($name == 'cripps') ? 'selected' : '')?>">
						<a href="?name=cripps">Dale E. Cripps</a><br>
						Publisher &amp; Founder
					</td><td rowspan="10" style="vertical-align:top">
						<img id="card" src="/images/card-<?=$name?>_400x229.gif" alt="Business Card"><br>
					</td>
				</tr><tr>
					<td class="<?=(($name == 'sturgeon') ? 'selected' : '')?>">
						<a href="?name=sturgeon">M. Shane Sturgeon</a><br>
						Publisher &amp; Chief Technologist
					</td>
				</tr><tr>
					<td class="<?=(($name == 'lamaestra') ? 'selected' : '')?>">
						<a href="?name=lamaestra">Rodolfo La Maestra</a><br>
						Senior Technical Director
					</td>
				</tr><tr>
					<td class="<?=(($name == 'milbourn') ? 'selected' : '')?>">
						<a href="?name=milbourn">Edward M. Milbourn</a><br>
						Correspondent
					</td>
				</tr><tr>
					<td>&nbsp;</td>
				</tr><tr>
					<td>&nbsp;</td>
				</tr><tr>
					<td>&nbsp;</td>
				</tr><tr>
					<td>&nbsp;</td>
				</tr><tr>
					<td>&nbsp;</td>
				</tr><tr>
					<td>&nbsp;</td>
				</tr>
			</table>

			<?if ($bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2><?=$author?> Biography</h2>
					<?=stripslashes($bio)?>
				<span class="corners-bottom"><span></span></span></div>
			<?}?>
		</td>
	</tr></table>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</div></body>
</html>
