<?
	require('../global.php');
	if (!access(ACCESS_ADMIN_ANY)) prompt_login(PHP_SELF);

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Admin</title>
	<base target="main">
	<? require(BASE_DIR .'/includes/page_header.php'); ?>

	<script src="/js/jquery-1.9.1.js"></script>
	<script src="/js/jquery-ui-1.10.3.custom.min.js"></script>
	<script>
		$(function() {
			$("#admin-menu").menu();
		});
		function broadcastEmail(email_id, action) {
			var wh = 700;
			var ww = 800;
			var wt = (screen.height - wh) / 2;
			var wl = (screen.width - ww) / 2;
			window.open('/admin/email-create.php?action='+action+'&email_id='+email_id, 'BroadcastEmail', 'resizable=yes,scrollbars=yes,status=yes,width='+ww+',height='+wh+',top='+wt+',left='+wl);
		}
		function tweet() {
			var wh = 700;
			var ww = 800;
			var wt = (screen.height - wh) / 2;
			var wl = (screen.width - ww) / 2;
			window.open('/admin/tweet.php', 'Tweet', 'resizable=yes,scrollbars=yes,status=yes,width='+ww+',height='+wh+',top='+wt+',left='+wl);
		}
		function publishFB() {
			var wh = 700;
			var ww = 800;
			var wt = (screen.height - wh) / 2;
			var wl = (screen.width - ww) / 2;
			window.open('/admin/publish-fb.php', 'PublishFB', 'resizable=yes,scrollbars=yes,status=yes,width='+ww+',height='+wh+',top='+wt+',left='+wl);
		}
	</script>
</head>
<body style="margin:5px;">
	<ul id="admin-menu">
		<li><a href="/admin/queue-content.php">Content Queue</a></li>
		<li><a href="/admin/feeds.php">Manage Feeds</a></li>
		<li><a href="/admin/income-product.php">Admin - Income by Product</a></li>
		<li><a href="http://admin.hdtvmagazine.com:81/NetTracker/hdtvmagazine.com/index.html">Admin - NetTracker</a></li>
		<li><a href="/admin/info.php">Admin - PHP Info</a></li>
		<li><a href="/admin/serp-import.php">Admin - SERP Import</a></li>
		<li><a href="/admin/settings.php">Admin - Site Settings</a></li>
		<li><a href="/admin/status-report.php">Admin - Status Report</a></li>
		<li><a href="/admin/authors.php">Articles - Author Bios</a></li>
		<li><a href="/cgi-bin/movabletype/mt.cgi">Articles (Movable Type) Interface</a></li>
		<li><a href="/admin/email-bounced.php">Email - Bounced</a></li>
		<li><a href="javascript:top.frames['nav'].broadcastEmail()">Email - Broadcast</a></li>
		<li><a href="/admin/email-complaints.php">Email - Complaints</a></li>
		<li><a href="/admin/companies.php">Equipment - Companies</a></li>
		<li><a href="/events/admin.php">Event - Admin</a></li>
		<li><a href="javascript:publishFB()">Facebook - Publish</a></li>
		<li><a href="/news/admin.php">Inbox - News</a></li>
		<li><a href="/admin/products.php">Inbox - New Products</a></li>
		<li><a href="https://www.paypal.com/us/cgi-bin/webscr?cmd=_vt-trans-start">Paypal - Virtual Terminal</a></li>
		<li><a href="/admin/amazon.php">Inbox - Amazon Books</a></li>
		<li><a href="/admin/tms-download.php">Programming - Download TMS Files</a></li>
		<li><a href="/admin/providers.php">Programming - Provider Configuration</a></li>
		<li><a href="/admin/tips.php">Programming - Tips</a></li>
		<li><a href="/admin/stations.php">Stations - Lookup</a></li>
		<li><a href="/hdstore/admin/secure_login.php">Store - Admin</a></li>
		<li><a href="javascript:tweet()">Twitter - Send Tweet</a></li>
		<li></li>
	</ul>

	<form name="frm_search" action="/admin/users.php" method="post">
			<input type="hidden" name="action" value="search">
			<b>Find user:</b><br />
			<input type="text" name="search" value="" class="inputText" size="20" />
			<input type="submit" name="btnSubmit" value="&nbsp;Search&nbsp;" class="inputButton" />
		</form>
</body>
</html>
