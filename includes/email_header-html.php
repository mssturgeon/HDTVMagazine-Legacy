<?
	if ($online_url == '') {
#		$online_url = $_SERVER['SCRIPT_URI'];
		$online_url = $_SERVER['REQUEST_URI'];
	}
?>

<!-- Blank leading table so that Mac Mail displays properly -->
<table><tr><td></td></tr></table>

<table border="0" cellpadding="0" cellspacing="0" style="border:0; margin:0; padding:0; width:100%"><tr>
	<td>
		<!--a href="<?=$base_url?>/"><img style="border:0" src="<?=$base_url?>/images/hdtvmagazine_297x50.gif<?=$tracking?>" alt="HDTV Magazine"></a-->
		<a href="<?=$base_url?>/"><img style="border:0" src="<?=$base_url?>/images/hdtvmagazine_297x50.gif<?=$tracking?>" alt="HDTV Magazine"></a>
		<!--a href="<?=$base_url?>/"><img src="<?=$base_url?>/images/hdtvmagazine-holiday_338x58.gif<?=$tracking?>" alt="HDTV Magazine" /></a-->
	</td>
	<? if ($boundary != '') {?>
		<td align="right" style="color:#aaa; font-size:7pt; text-align:right">
			Email not displaying correctly? <a style="color:#<?=PRIMARY_COLOR?>" href="<?=$online_url?>">View it in your browser</a><br>
			<br>
			To ensure delivery, please add <a style="color:#<?=PRIMARY_COLOR?>" href="mailto:<?=$admindata['email_reply_address']?>"><?=$admindata['email_reply_address']?></a><br>
			to your address book.
			<? if ($hide_unsub !== true) {
#				echo '|	<a style="color:#'. PRIMARY_COLOR .'" href="'. $base_url .'/unsubscribe.php?u=[[unsub_code]]">Unsubscribe</a>';
			}?>
		</td>
	<?}?>
</tr></table>

<table border="0" cellpadding="0" cellspacing="0" style="border:0; margin:0; padding:0; width:100%"><tr><td>
	<div class="menu" style="margin:5px 0; background-color:#<?=PRIMARY_COLOR?>; padding:0 10px;">
		<span class="corners-top" style="font-size:1px; margin:0 -10px; background:url('<?=$base_url?>/forum/styles/HDTVMagazine4/theme/images/corners_left.png') no-repeat 0 0; line-height:1px; display: block; height: 5px;"><span style="font-size: 1px; background: url('<?=$base_url?>/forum/styles/HDTVMagazine4/theme/images/corners_right.png') no-repeat 100% 0; line-height: 1px; display:block; height:5px;"></span></span>
		<table border="0" cellpadding="0" cellspacing="0" style="background-color:#<?=PRIMARY_COLOR?>; border:0; color:white; margin:0; padding:0; width:100%"><tr>
			<td align="center"><a href="<?=$base_url?>" style="border-left: 0; color:white; padding: 3px 5px 3px 0;">Home</a></td>
			<td align="center"><a href="<?=$base_url?>/news/index.php" style="color:white; padding: 3px 5px;border-left: 1px solid white;">News</a></td>
			<td align="center"><a href="<?=$base_url?>/reviews/index.php" style="color: white; padding: 3px 5px; border-left: 1px solid white;">Reviews</a></td>
			<td align="center"><a href="<?=$base_url?>/forum/index.php" style="color: white; padding: 3px 5px; border-left: 1px solid white;">Forum</a></td>
			<td align="center"><a href="<?=$base_url?>/articles/index.php" style="color: white; padding: 3px 5px; border-left: 1px solid white;">Articles</a></td>
			<td align="center"><a href="<?=$base_url?>/columns/index.php" style="color: white; padding: 3px 5px; border-left: 1px solid white;">Columns</a></td>
			<td align="center"><a href="<?=$base_url?>/podcast/index.php" style="color: white; padding: 3px 5px; border-left: 1px solid white;">Podcasts</a></td>
			<td align="center"><a href="<?=$base_url?>/programming/index.php" style="color: white; padding: 3px 5px; border-left: 1px solid white;">Programming</a></td>
			<td align="center"><a href="<?=$base_url?>/equipment/hdtvs-best-rated.php" style="color: white; padding: 3px 5px; border-left: 1px solid white;">HDTVs</a></td>
		</tr></table>
		<span class="corners-bottom" style="font-size:1px; clear:both; margin:0 -10px; background:url('<?=$base_url?>/forum/styles/HDTVMagazine4/theme/images/corners_left.png') no-repeat 0 100%; line-height: 1px; display: block; height: 5px;"><span style="font-size: 1px; background: url('<?=$base_url?>/forum/styles/HDTVMagazine4/theme/images/corners_right.png') no-repeat 100% 100%; line-height: 1px; display: block; height: 5px;"></span></span>
	</div>
</td><td style="padding:3px 0 0 3px;" nowrap>
	<table border="0" cellpadding="0" cellspacing="0" style="border:0; margin:0; padding:0; width:100%"><tr><td align="right" style="text-align:right;" valign="bottom">
		<a href="http://twitter.com/HDTVMagazine" style="margin-left:3px"><img width="24" border="0" src="<?=$base_url?>/images/i_twitter_64.png" alt="Twitter"></a>
		<a href="<?=$base_url?>/rss-feeds.php" style="margin-left:3px;"><img width="24" border="0" src="<?=$base_url?>/images/i_rss_64.png" alt="RSS"></a>
		<a href="http://www.facebook.com/HDTVMagazine" style="margin-left:3px"><img width="24" border="0" src="<?=$base_url?>/images/i_facebook_64.png" alt="Facebook"></a>
	</td></tr></table>
</td></tr></table>