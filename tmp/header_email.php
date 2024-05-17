<?
	require_once('/var/www/html/includes/lib_common.php');
	
	$base_url = 'http://'. SERVER_NAME;
?>
	<table class="header" cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<td class="header" style="padding-bottom:10px;vertical-align:middle;">
				<a href="<?=$base_url?>/"><img src="<?=($base_url . IMG_LOGO)?>" alt="HDTV Magazine"></a>
			</td>
			<td class="header" style="padding-bottom:0px;vertical-align:top;text-align:right;font-size:8pt;font-family:\'Trebuchet MS\',Verdana,sans-serif;">
				[ <a href="<?=$base_url . PHP_SELF?>">View This Email Online</a> ]
			</td>
		</tr>
	</table>
