<?
	if (!access(ACCESS_ADMIN)) prompt_login(PHP_SELF);
?>
<ul id="admin-menu" style="float:left;">
	<li><a href="/admin/feeds.php">Manage Feeds</a></li>

</ul>
	<a href="/admin/income-product.php">Admin - Income by Product</a><br />
	<a href="http://admin.hdtvmagazine.com:81/NetTracker/hdtvmagazine.com/index.html">Admin - NetTracker</a><br />
	<a href="/admin/info.php">Admin - PHP Info</a><br />
	<a href="/admin/serp-import.php">Admin - SERP Import</a><br />
	<a href="/admin/settings.php">Admin - Site Settings</a><br />
	<a href="/admin/status-report.php">Admin - Status Report</a><br />
	&nbsp;<br />
	<a href="/admin/authors.php">Articles - Author Bios</a><br />
	<a href="/cgi-bin/movabletype/mt.cgi">Articles (Movable Type) Interface</a><br />
	&nbsp;<br />
	<a href="/admin/email-bounced.php">Email - Bounced</a><br />
	<a href="javascript:top.frames['nav'].broadcastEmail()">Email - Broadcast</a><br />
	<a href="/admin/email-complaints.php">Email - Complaints</a><br />
	&nbsp;<br />
	<a href="/admin/companies.php">Equipment - Companies</a><br />
	&nbsp;<br />
	<a href="/events/admin.php">Event - Admin</a><br />
	&nbsp;<br />
	<a href="javascript:publishFB()">Facebook - Publish</a><br />
	&nbsp;<br />
		<!--tr><td><a href="/admin/amazon.php">Inbox - Amazon Books</a></td></tr-->
	<a href="/news/admin.php">Inbox - News</a><br />
	<a href="/admin/products.php">Inbox - New Products</a><br />
	&nbsp;<br />
	<a href="https://www.paypal.com/us/cgi-bin/webscr?cmd=_vt-trans-start">Paypal - Virtual Terminal</a><br />
	&nbsp;<br />
	<a href="/admin/tms-download.php">Programming - Download TMS Files</a><br />
	<a href="/admin/providers.php">Programming - Provider Configuration</a><br />
	<a href="/admin/tips.php">Programming - Tips</a><br />
	&nbsp;<br />
	<a href="/admin/stations.php">Stations - Lookup</a><br />
	&nbsp;<br />
	<a href="/hdstore/admin/secure_login.php">Store - Admin</a><br />
	&nbsp;<br />
	<a href="javascript:tweet()">Twitter - Send Tweet</a><br />