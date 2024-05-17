<?
	require('../global.php');

	include(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Feedback</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
	<meta name="description" content="HDTV Magazine Feedback">
	<meta name="keywords" content="hdtv,hdtv help,hdtv programming,hdtv guide,hdtv schedule,hdtv listing,hdtv guides,hdtv schedules,hdtv listings,hdtv programs,hdtv programming guide,hdtv programming schedule,hdtv program,hd guide,hd programming,hd schedule,abc hdtv programming,cbs hdtv programming,digital tv programming,digital tv schedule,digital tv schedules,dtv listings,dtv program,dtv programing,dtv programming,dtv schedule,hd programing,hd programming,hd programs,high definition guide,high definition programing,high definition programming,high definition programs,high definition schedule,nbc hdtv programming,guide,news,sports,hdtv stations">
	<script language="javascript" type="text/javascript">
		function init() {
			frm.browser.value = navigator.userAgent;
			frm.subject.focus();
		}
	</script>
</head>
<body onload="init()">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>

	<div align="center"><div style="width:600px; text-align:left">
  	<!--div class="infobox" style="float:none; width:100%">
  		<img src="/images/i_bulletins.gif" align="left" / style="padding-right:10px"><b>Notice - 23 Sept, 2008 05:30am: </b>We experienced a server outage this morning
  		that resulted in a permanent loss of data on our servers. The time period affected goes back to 26 July, 2008. We are working frantically to rebuild and restore
  		this information. Please check back later, or fill out the form below to let us know of any information that may be missing.<br />
  		<br />
  		<b>If you created a user account</b> during this period, that information has been permanantly lost. Please <a href="/profile-create.php">register again</a>
  		and <a href="<?=URL_HELP_FEEDBACK?>">send us an email</a> ... we will do something to make it up to you!<br />
  		<br />
  		Thank you,<br />
  		<br />
  		Dale Cripps &amp; Shane Sturgeon<br />
  		Publishers, HDTV Magazine
  	</div-->
		<p>
	 		Every feedback submission is read personally, so please be mindful of that when submitting. This form is intended for questions relating to
	 		the website function and performance, and <b>not intended for questions about HDTVs and related components</b>. Please use the following guidelines when
	 		submitting:
	 		<ul class="brownsquare">
	 			<li>Questions relating to High Definition products and services should be posted to the <a href="/forum">HDTV Forum</a>.</li>
	 			<li>Unsubscribe requests can be done by managing your email preferences on <a href="/profile.php">your profile</a> page</li>
	 			<li>Your username and password can be looked up by email address on the <a href="/help/login-lookup.php">Username/Password Lookup</a> page</li>
	 			<li>We do not do link exchanges. Feel free to send along your site details though, as we are always looking for partners in various areas.</li>
	 			<li>Email changes can be done by managing your email preferences on <a href="/profile.php">your profile</a> page</li>
	 		</ul>
	 		Thank you for taking a few minutes to provide your feedback.
		</p>

		<form name="frm" action="feedback-submit.php" method="post">
			<input type="hidden" name="action" value="save">
			<input type="hidden" name="user_id" value="<?=$user->data['user_id']?>">
			<input type="hidden" name="browser" value="">
			<input type="hidden" name="referer" value="<?=$_SERVER['HTTP_REFERER']?>">
			<input type="text" name="link" value="" style="display:none">
			<input type="text" name="url" value="" style="display:none">
			<table class="type1b" style="width:75%" align="center">
				<tr>
					<td class="type1b_header" colspan="2">Feedback Form</td>
				</tr><tr>
					<td class="inputLabel">Category:</td>
					<td><select name="category">
						<option value="">-- Please Select a Category --</option>
						<option value="advertising" <?($_GET['category'] == 'advertising') ? print "SELECTED" : print "";?>>Advertising Inquiry</option>
						<option value="general" <?($_GET['category'] == 'general') ? print "SELECTED" : print "";?>>General Website Question/Comment</option>
						<option value="payment cancelled" <?($_GET['category'] == 'payment cancelled') ? print "SELECTED" : print "";?>>Payment Cancelled</option>
						<!--option value="unsubscribe" <?($_GET['category'] == 'unsubscribe') ? print "SELECTED" : print "";?>>Unsubscribe</option>
						<option value="link" <?($_GET['category'] == 'equipment') ? print "SELECTED" : print "";?>>Equipment Missing from your Listings</option>
						<option value="link" <?($_GET['category'] == 'report') ? print "SELECTED" : print "";?>>HDTV Technology Report Question/Comment</option>
						<option value="link" <?($_GET['category'] == 'online_order') ? print "SELECTED" : print "";?>>Online Order</option-->
						<option value="other">Other</option>
					</select></td>
				</tr><tr>
					<td class="inputLabel">Name:</td>
					<td><input type="text" class="inputText" style="width:25ex" name="name" value="<?=trim($user->data['first_name'] .' '. $user->data['last_name'])?>"></td>
				</tr><tr>
					<td class="inputLabel" nowrap>Email Address:</td>
					<td><input type="text" class="inputText" style="width:35ex" name="user_email" value="<?=$user->data['user_email']?>"></td>
				</tr><tr>
					<td class="inputLabel">&nbsp;</td>
					<td><input type="checkbox" name="cc" value="1">Send a copy of this feedback to my email address.</td>
				</tr><tr>
					<td class="inputLabel">Subject:</td>
					<td><input type="text" class="inputText" style="width:40ex" name="subject" value=""></td>
				</tr><tr>
					<td class="inputLabel" valign="top">Comments:</td>
					<td><textarea name="comments" rows="10" width="100%"></textarea></td>
				</tr><tr>
					<td class="buttonBar" colspan="2">
						<input type="submit" name="btnSubmit" value="Send" class="inputButton">
					</td>
				</tr>
			</table>
		</form>
	</div></div>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</body>
</html>
