<?
	// If the user_id is given, then use that, otherwise load global and use currently logged in user.
	if (isset($_GET['user_id'])) {
		define('BASE_DIR', '/var/www/html');
		require(BASE_DIR .'/programming/includes/constants.php');
		require(BASE_DIR .'/includes/constants.php');
		require(BASE_DIR .'/includes/lib_mysql.php');
		require(BASE_DIR .'/includes/lib_common.php');

		$user_id = $_GET['user_id'];
		$tzo = $_GET['tzo'];
		$tzt = $_GET['tzt'];
		$show_sd = isset($_GET['show_sd']);
	} else {
		require('../../global.php');
		if (!access(ACCESS_PREMIUM)) prompt_login(PHP_SELF);
		$user_id = $_SESSION['user_id'];
		$tzo = $_SESSION['time_zone_offset'];
		$tzt = $_SESSION['time_zone_text'];
		$show_sd = $_SESSION['opt_showsd'];
	}
	$base_url = 'http://'. SERVER_NAME;

	// This may be configurable later
	$guide_width = 3; // in hours
	
	$now = (isset($_GET['s'])) ? $_GET['s'] : time();
	$today = gmdate('m/d/Y', $now);
	$buffer_time = $now + 5*MINUTES;
	$start_display_time = $buffer_time - ($buffer_time % (30*MINUTES));
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>The Daily Guide - <?=$today?></title>
	<script language="javascript" type="text/javascript" src="/scripts/overlib_mini.js">
		<!--// overLIB (c) Erik Bosrup -->
	</script>
	<script language="javascript" type="text/javascript">
		// Set OverLib Variables
		ol_width = 300;
		ol_offsety = 20;
		ol_cellpad = 0;
		ol_border=0;
	</script>
	<style>
		<?
			include(BASE_DIR .'/stylesheets/email_css.php');
			include(BASE_DIR .'/stylesheets/guide_css.php');
		?>
	</style>
</head>
<body style="background-image:none">
	<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
	<?
		require(BASE_DIR .'/includes/tracker.php');
		require(BASE_DIR .'/includes/lib_guide.php');
	?>

	<div align="center" style="background-color:#ffffff;margin:0px;">
		<table border="0" cellpadding="2" cellspacing="2" style="background-color:#333366;width:6.5in">
			<tr >
				<td style="background-color:#ffffff;">
					
					<table cellpadding="2" cellspacing="1" style="width:6.5in">
						<tr style="background-color: #666699">
							<td width="10" ><img border="0" height="10" src="<?=$base_url?>/images/pixel.gif" width="10" /></td>
							<td width="220" >&nbsp;</td>
							<td style="background-color:#333366;text-align:right;padding-right:5px;font-size:8pt;font-weight:bold;color:#ffffff;" width="100%"><?=gmdate('l, F d, Y', $now)?></td>
						</tr><tr>
							<td rowspan="4" style="background-color: #333366;text-align: right;padding-right: 5px;" ><img border="0" height="50" src="<?=$base_url?>/images/pixel.gif" width="10" /></td>
							<td colspan="2" style="background-color:#9999cc;font-size:16pt;padding-left:5px;font-family:Georgia,'Times New Roman',Times,serif;">The Daily Guide</td>
						</tr><tr>
							<td colspan="2">

            				<table border="0" cellpadding="0" cellspacing="3" align="center">
									<tr>
										<td colspan="2">
											<div style="background-color:#cccccc;width:234px;height:60px;padding:2px">Dealers Logo</div>
										</td>
            					</tr><tr>
            						<td rowspan="2" class="message">Dealer's message goes here.</td>
            						<td style="background-color:#cccccc;width:468px;height:60px;text-align:center">Ad #1</td>
            					</tr><tr>
            						<td style="background-color:#cccccc;width:468px;height:60px;text-align:center">Ad #2</td>
            					</tr>
            				</table>

							</td>
						</tr><tr>
							<td colspan="2" style="background-color:#9999cc;font-size:12pt;padding:0px 0px 3px 5px;font-family:Georgia,'Times New Roman',Times,serif;font-weight:bold">
								<?=gmdate('g:ia', $start_display_time + $tzo)?> - <?=gmdate('g:ia', $start_display_time + $guide_width*HOURS + $tzo)?> (<?=$tzt?>)
							</td>
						</tr><tr >
	  						<td colspan="2" style="">
								<?displayGuide($start_display_time, 30, $tzo, 2*$guide_width, $user_id, $show_sd);?>
							</td>
						</tr>
						<tr >
							<td style="background-color:#333366;"><img height="50" src="<?=$base_url?>/images/pixel.gif" width="10" /></td>
							<td colspan="2" style="background-color:#9999CC;text-align:center" >
								<div align="center"><div style="background-color:white;border:1px solid #333366;padding:1px;vertical-align:middle;width:150px;">
									A Service of<br>
									<a href="<?=$base_url?>/"><img src="<?=$base_url?>/images/hdtvmagazine_148x25.gif" alt="HDTV Magazine"></a>
								</div></div>
							</td>
						</tr>
					</table>
					
				</td>
			</tr>
		</table>
	</div>

</body>
</html>
