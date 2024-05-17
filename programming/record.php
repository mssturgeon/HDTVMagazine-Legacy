<?
	$gID = $_GET['ID'];
	
	// Time formatting (HH:mm)
	$startTime = "";
	$endTime = "";
	
	// Date formatting (yyyymmdd)
	$startDate = "";
	$endDate = "";
	
	// Duration formatting (h:mm)
	$duration = "";
	
	print "
	<tv-program-info version=\"1.0\">
		<program>
			<station>$networkName<station>
			<tv-mode>digital</tv-mode>
			<program-title>$title</program-title>
			<program-description>$description</program-description>
			<start-date>$startDate</start-date>
			<start-time>$startTime</start-time>
			<end-date>$endDate</end-date>
			<end-time>$endTime</end-time>
			<duration>$duration</duration>
			<rf-channel>$channel</rf-channel>
			<psip-major>$psipMajor</psip-major>
			<psip-minor>$psipMinor</psip-minor>
			<stream-number>$stream</stream-number>
		</program>
	</tv-program-info>";
?>
