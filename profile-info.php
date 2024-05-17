<?
	require('global.php');
	require(BASE_DIR .'/includes/lib_profile.php');
	require(BASE_DIR .'/profile-overall_header.php');

	if (!$user->data['is_registered']) prompt_login(PHP_SELF);
	header('Cache-Control: no-store'); // HTTP/1.1

	$action = isset($_POST[action]) ? $_POST[action] : '';
	if ($action == 'save') {
		$interest = is_array($_POST[interest]) ? array_sum($_POST[interest]) : 0;
		$current = is_array($_POST[current]) ? array_sum($_POST[current]) : 0;
		$future = is_array($_POST[future]) ? array_sum($_POST[future]) : 0;

		$sql = "
		INSERT INTO user_demog
		(user_id, age, gender, education, marital_status, income, occupation, interest, current, invested, dvd, future)
		VALUES ('$_POST[user_id]', '$_POST[age]', '$_POST[gender]', '$_POST[education]', '$_POST[marital_status]', '$_POST[income]',
		'$_POST[occupation]', '$interest', '$current', '$_POST[invested]', '$_POST[dvd]', '$future')
		ON DUPLICATE KEY UPDATE
			age = '$_POST[age]',
			gender = '$_POST[gender]',
			education = '$_POST[education]',
			marital_status = '$_POST[marital_status]',
			income = '$_POST[income]',
			occupation = '$_POST[occupation]',
			interest = '$interest',
			current = '$current',
			invested = '$_POST[invested]',
			dvd = '$_POST[dvd]',
			future = '$future'";
		mQuery($sql);

		#js_back('Settings Saved!');
		exit;
	}

	// Get Profile Info
	$result = mQuery("SELECT * FROM user_demog WHERE user_id = ". $user->data['user_id']);
	$user = mysql_fetch_assoc($result);

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - HDTV Study</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
	<script type="text/javascript">
		function checkAll(name, flag) {
			var obj = document.getElementsByName(name);
			for (x=0; x<obj.length; x++) {
				obj[x].checked = flag;
			}
			return true;
		}
	</script>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/body_header-4.php');

		$age = array(1 => 'under 18', 2 => '18 - 24', 3 => '25 - 34', 4 => '35 - 44', 5 => '45 - 54', 6 => '55 - 64', 7 => 'over 65');
		$gender = array(1 => 'Male', 2 => 'Female');
		$education = array(1 => 'Some High School', 2 => 'High School Graduate', 3 => 'Some College', 4 => 'College Graduate', 5 => 'Graduate School');
		$marital_status = array(1 => 'Single', 2 => 'Married', 3 => 'Life Partner', 4 => 'Divorced, Separated, Widowed');
		$income = array(1 => 'Less than $15K', 2 => '$15K - $24K', 3 => '$25K - $34K', 4 => '$35K - $49K',
			5 => '$50K - $74K', 6 => '$75K - $99K', 7 => '$100K - $149K', 8 => '$150K - $199K', 9 => '$200K or more');
		$occupation = array(1=>'Administrative/Clerical', 2=>'Doctor', 3=>'Educator', 4=>'Attorney', 5=>'Managerial/Executive', 6=>'Sales',
			7=>'Skilled Labor/Construction', 8=>'Professional/Technical', 9=>'Uniform Services (i.e. Police)', 10=>'None of the above or Not Applicable', 11=>'Other');
		$interest = array(1=>'News', 2=>'Articles', 4=>'Programming', 8=>'Peer Discussion', 16=>'Reviews', 32=>'Equipment', 64=>'Books', 128=>'Sports',
			256=>'Movies', 512=>'Broadcast/OTA', 1024=>'Cable', 2048=>'Satellite', 4096=>'IPTV (HD, of course)');
		$current = array(1=>'Plasma/LCD TV', 2=>'DLP TV', 4=>'LCoS TV', 8=>'HD CRT TV', 16=>'HD Projector', 32=>'HD DVD Player', 64=>'Blu-ray Player',
			128=>'HD Satellite Dish Service', 256=>'HD Cable Service', 512=>'High-end Audio System', 1024=>'HD Camcorder', 2048=>'HD TiVo/DVR', 4096=>'Xbox 360',
			8192=>'Sony PS3', 16384=>'HDPC/Media Center');
		$invested = array(1 => 'Less than $5K', 2 => '$5K - $9K', 3 => '$10K - $14K', 4 => '$15K - $19K',
			5 => '$20K - $24K', 6 => '$25K - $29K', 7 => '$30K - $39K', 8 => '$40K - $49K', 9 => '$50K or more');
		$dvd = array(1 => 'Less than $250', 2 => '$250 - $499', 3 => '$500 - $999', 4 => '$1000 - $1999',
			5 => '$2000 - $2999', 6 => '$3000 - $4999', 7 => '$5000 or more');
		$future = array(1=>'Plasma/LCD TV', 2=>'DLP TV', 4=>'LCoS TV', 8=>'HD CRT TV', 16=>'HD Projector', 32=>'HD DVD Player', 64=>'Blu-ray Player',
			128=>'HD Satellite Dish Service', 256=>'HD Cable Service', 512=>'High-end Audio System', 1024=>'HD Camcorder', 2048=>'HD TiVo/DVR', 4096=>'Xbox 360',
			8192=>'Sony PS3', 16384=>'HDPC/Media Center');

	?>

	<form action="<?=PHP_SELF?>" method="post" name="frmProfile">
		<input type="hidden" name="action" value="save">
		<input type="hidden" name="user_id" value="<?=$user->data['user_id']?>">
		<table class="tabTable" cellpadding="0" cellspacing="0" align="center">
			<tr><td>
				<?=getTabs(URL_PROFILE_INFO);?>
			</td></tr>
			<tr><td>
				<table class="tabContent" cellpadding="10" cellspacing="0">
					<tr><td>
						<p>
							Please provide as much or as little of the following information as you see fit. This will help us to provide an accurate description of the typical
							visitor to HDTV Magazine. This will become part of an overall <b>HDTV Magazine Demographic Study</b>.
						</p><p>
							<b>Note:</b> Although this information is part of your user profile, it will never be used in conjunction with information on other parts of your profile.
							The sole purpose is to profile a typical HDTV Magazine visitor. <b>If you prefer to provide this information anonymously, you can do so via our survey here:
							<a href="http://www.surveymonkey.com/s.asp?u=79032455355">HDTV Magazine Demographic Study</a></b>.
						</p>
						<table class="type1b" align="center" cellpadding="1" cellspacing="0">
							<tr><td class="type1b_header">Personal Profile</td></tr>
							<tr><td class="type1b">
								<label for="gender" class="primary_bold">What is your gender?</label>
								<?
									foreach ($gender as $key => $value) {
										$checked = ($user[gender] == $key) ? 'checked="checked"' : '';
										echo '<div style="float:left; width:33%; vertical-align:middle;">'.
										'<input type="radio" name="gender" value="'. $key .'" '. $checked .'>'.
										'<label style="text-indent: -15px;" for="gender">'. $value .'</label>'.
										'</div>';
									}
								?>
								<br clear="all"><br />
								<label for="age" class="primary_bold">In which age group do you fall?</label>
								<?
									foreach ($age as $key => $value) {
										$checked = ($user[age] == $key) ? 'checked="checked"' : '';
										echo '<div style="float:left; width:33%; vertical-align:middle;">'.
										'<input type="radio" name="age" value="'. $key .'" '. $checked .'>'.
										'<label style="text-indent: -15px;" for="age">'. $value .'</label>'.
										'</div>';
									}
								?>
								<br clear="all"><br />
								<label for="education" class="primary_bold">What is the highest level of education that you have attained?</label>
								<?
									foreach ($education as $key => $value) {
										$checked = ($user[education] == $key) ? 'checked="checked"' : '';
										echo '<div style="float:left; width:33%; vertical-align:middle;">'.
										'<input type="radio" name="education" value="'. $key .'" '. $checked .'>'.
										'<label style="text-indent: -15px;" for="education">'. $value .'</label>'.
										'</div>';
									}
								?>
								<br clear="all"><br />
								<label for="marital_status" class="primary_bold">What is your marital status?</label>
								<?
									foreach ($marital_status as $key => $value) {
										$checked = ($user[marital_status] == $key) ? 'checked="checked"' : '';
										echo '<div style="float:left; width:33%; vertical-align:middle;">'.
										'<input type="radio" name="marital_status" value="'. $key .'" '. $checked .'>'.
										'<label style="text-indent: -15px;" for="marital_status">'. $value .'</label>'.
										'</div>';
									}
								?>
								<br clear="all"><br />
								<label for="income" class="primary_bold">What is your current household income? (US Dollars)</label>
								<?
									foreach ($income as $key => $value) {
										$checked = ($user[income] == $key) ? 'checked="checked"' : '';
										echo '<div style="float:left; width:33%; vertical-align:middle;">'.
										'<input type="radio" name="income" value="'. $key .'" '. $checked .'>'.
										'<label style="text-indent: -15px;" for="income">'. $value .'</label>'.
										'</div>';
									}
								?>
								<br clear="all"><br />
								<label for="occupation" class="primary_bold">What is your occupation?</label>
								<?
									foreach ($occupation as $key => $value) {
										$checked = ($user[occupation] == $key) ? 'checked="checked"' : '';
										echo '<div style="float:left; width:33%; vertical-align:middle;">'.
										'<input type="radio" name="occupation" value="'. $key .'" '. $checked .'>'.
										'<label style="text-indent: -15px;" for="income">'. $value .'</label>'.
										'</div>';
									}
								?>
								<br clear="all"><br />

							</td></tr>
						</table>
					</td></tr><tr><td>
						<table class="type1b" align="center">
							<tr><td class="type1b_header">Interest Profile</td></tr>
							<tr><td class="type1b">
								<label for="interest" class="primary_bold">
									As it relates to high definition, for which of the following areas do you have an interest? (please select as many as apply)</label>
								<?
									foreach ($interest as $key => $value) {
										$checked = ($user[interest] & $key) ? 'checked="checked"' : '';
										echo '<div style="float:left; width:25%; vertical-align:middle;">'.
										'<input type="checkbox" name="interest[]" value="'. $key .'" '. $checked .'>'.
										'<label style="text-indent: -15px;" for="income">'. $value .'</label>'.
										'</div>';
									}
								?>
								<br clear="all"><br />
								[<a href="javascript:void checkAll('interest[]', true)">Select All</a>] [<a href="javascript:void checkAll('interest[]', false)">Clear All</a>]
							</td></tr>
						</table>
					</td></tr><tr><td>
						<table class="type1b" align="center">
							<tr><td class="type1b_header">Current Home Entertainment System</td></tr>
							<tr><td class="type1b">
								<label for="invested" class="primary_bold">
									Approximately how much do you have invested in your home entertainment system (US Dollars)? (The next question indicates items you might inlcude)</label>
								<?
									foreach ($invested as $key => $value) {
										$checked = ($user[invested] == $key) ? 'checked="checked"' : '';
										echo '<div style="float:left; width:33%; vertical-align:middle;">'.
										'<input type="radio" name="invested" value="'. $key .'" '. $checked .'>'.
										'<label style="text-indent: -15px;" for="income">'. $value .'</label>'.
										'</div>';
									}
								?>
								<br clear="all"><br />
								<label for="dvd" class="primary_bold">
									Approximately how much do you spend annually on Movie and Game purchases/rentals for your home entertainment system (US Dollars)?</label>
								<?
									foreach ($dvd as $key => $value) {
										$checked = ($user[dvd] == $key) ? 'checked="checked"' : '';
										echo '<div style="float:left; width:33%; vertical-align:middle;">'.
										'<input type="radio" name="dvd" value="'. $key .'" '. $checked .'>'.
										'<label style="text-indent: -15px;" for="income">'. $value .'</label>'.
										'</div>';
									}
								?>
								<br clear="all"><br />
								<label for="current" class="primary_bold">
									Which of the following home electronics do you or someone in your household have? (please select all that apply)</label>
								<?
									foreach ($current as $key => $value) {
										$checked = ($user[current] & $key) ? 'checked="checked"' : '';
										echo '<div style="float:left; width:33%; vertical-align:middle;">'.
										'<input type="checkbox" name="current[]" value="'. $key .'" '. $checked .'>'.
										'<label style="text-indent: -15px;" for="income">'. $value .'</label>'.
										'</div>';
									}
								?>
								<br clear="all"><br />
								[<a href="javascript:void checkAll('current[]', true)">Select All</a>] [<a href="javascript:void checkAll('current[]', false)">Clear All</a>]
							<tr><td class="type1b">
							</td></tr>
						</table>
					</td></tr><tr><td>
						<table class="type1b" align="center">
							<tr><td class="type1b_header">Future Home Entertainment System</td></tr>
							<tr><td class="type1b">
								<label for="future" class="primary_bold">
									Which of the following home electronics are you or someone in your household planning to purchase in the next 6 months? (please select all that apply)</label>
								<?
									foreach ($future as $key => $value) {
										$checked = ($user[future] & $key) ? 'checked="checked"' : '';
										echo '<div style="float:left; width:33%; vertical-align:middle;">'.
										'<input type="checkbox" name="future[]" value="'. $key .'" '. $checked .'>'.
										'<label style="text-indent: -15px;" for="income">'. $value .'</label>'.
										'</div>';
									}
								?>
								<br clear="all"><br />
								[<a href="javascript:void checkAll('future[]', true)">Select All</a>] [<a href="javascript:void checkAll('future[]', false)">Clear All</a>]
							</td></tr>
						</table>
					</td></tr><tr><td class="buttonBar">
						<input type="submit" name="btnSubmit" value="&nbsp;Save&nbsp;" class="inputButton">
					</td></tr>
				</table>
			</td></tr>
		</table>
	</form>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</body>
</html>
