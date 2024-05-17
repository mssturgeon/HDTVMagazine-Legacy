<?
	include('/var/www/html/ads/links_variable.php');

	# DirectoryM Local HDTV information
	$body_footer_output = <<<EOT
<div class="item" align="left" style="margin-top:10px;"><span class="corners-top"><span></span></span>
	<h2>Local Guides</h2>
	<div class="local_info"><ul>
		<li><a href="http://local.hdtvmagazine.com/" title="All Guides">All Guides</a></li>
		<li><a href="http://local.hdtvmagazine.com/Alabama-nAlabama.html" title="Alabama" class="dmcontent_link">Alabama</a></li>
		<li><a href="http://local.hdtvmagazine.com/Alaska-nAlaska.html" title="Alaska" class="dmcontent_link">Alaska</a></li>
		<li><a href="http://local.hdtvmagazine.com/Arizona-nArizona.html" title="Arizona" class="dmcontent_link">Arizona</a></li>
		<li><a href="http://local.hdtvmagazine.com/Arkansas-nArkansas.html" title="Arkansas" class="dmcontent_link">Arkansas</a></li>
		<li><a href="http://local.hdtvmagazine.com/California-nCalifornia.html" title="California" class="dmcontent_link">California</a></li>
		<li><a href="http://local.hdtvmagazine.com/Colorado-nColorado.html" title="Colorado" class="dmcontent_link">Colorado</a></li>
		<li><a href="http://local.hdtvmagazine.com/Connecticut-nConnecticut.html" title="Connecticut" class="dmcontent_link">Connecticut</a></li>
		<li><a href="http://local.hdtvmagazine.com/DC-nDC.html" title="DC" class="dmcontent_link">DC</a></li>
		<li><a href="http://local.hdtvmagazine.com/Delaware-nDelaware.html" title="Delaware" class="dmcontent_link">Delaware</a></li>
		<li><a href="http://local.hdtvmagazine.com/Florida-nFlorida.html" title="Florida" class="dmcontent_link">Florida</a></li>
		<li><a href="http://local.hdtvmagazine.com/Georgia-nGeorgia.html" title="Georgia" class="dmcontent_link">Georgia</a></li>
		<li><a href="http://local.hdtvmagazine.com/Hawaii-nHawaii.html" title="Hawaii" class="dmcontent_link">Hawaii</a></li>
		<li><a href="http://local.hdtvmagazine.com/Idaho-nIdaho.html" title="Idaho" class="dmcontent_link">Idaho</a></li>
		<li><a href="http://local.hdtvmagazine.com/Illinois-nIllinois.html" title="Illinois" class="dmcontent_link">Illinois</a></li>
		<li><a href="http://local.hdtvmagazine.com/Indiana-nIndiana.html" title="Indiana" class="dmcontent_link">Indiana</a></li>
		<li><a href="http://local.hdtvmagazine.com/Iowa-nIowa.html" title="Iowa" class="dmcontent_link">Iowa</a></li>
		<li><a href="http://local.hdtvmagazine.com/Kansas-nKansas.html" title="Kansas" class="dmcontent_link">Kansas</a></li>
		<li><a href="http://local.hdtvmagazine.com/Kentucky-nKentucky.html" title="Kentucky" class="dmcontent_link">Kentucky</a></li>
		<li><a href="http://local.hdtvmagazine.com/Louisiana-nLouisiana.html" title="Louisiana" class="dmcontent_link">Louisiana</a></li>
		<li><a href="http://local.hdtvmagazine.com/Maine-nMaine.html" title="Maine" class="dmcontent_link">Maine</a></li>
		<li><a href="http://local.hdtvmagazine.com/Maryland-nMaryland.html" title="Maryland" class="dmcontent_link">Maryland</a></li>
		<li><a href="http://local.hdtvmagazine.com/Massachusetts-nMassachusetts.html" title="Massachusetts" class="dmcontent_link">Massachusetts</a></li>
		<li><a href="http://local.hdtvmagazine.com/Michigan-nMichigan.html" title="Michigan" class="dmcontent_link">Michigan</a></li>
		<li><a href="http://local.hdtvmagazine.com/Minnesota-nMinnesota.html" title="Minnesota" class="dmcontent_link">Minnesota</a></li>
		<li><a href="http://local.hdtvmagazine.com/Mississippi-nMississippi.html" title="Mississippi" class="dmcontent_link">Mississippi</a></li>
		<li><a href="http://local.hdtvmagazine.com/Missouri-nMissouri.html" title="Missouri" class="dmcontent_link">Missouri</a></li>
		<li><a href="http://local.hdtvmagazine.com/Montana-nMontana.html" title="Montana" class="dmcontent_link">Montana</a></li>
		<li><a href="http://local.hdtvmagazine.com/Nebraska-nNebraska.html" title="Nebraska" class="dmcontent_link">Nebraska</a></li>
		<li><a href="http://local.hdtvmagazine.com/Nevada-nNevada.html" title="Nevada" class="dmcontent_link">Nevada</a></li>
		<li><a href="http://local.hdtvmagazine.com/New_Hampshire-nNew+Hampshire.html" title="New Hampshire" class="dmcontent_link">New Hampshire</a></li>
		<li><a href="http://local.hdtvmagazine.com/New_Jersey-nNew+Jersey.html" title="New Jersey" class="dmcontent_link">New Jersey</a></li>
		<li><a href="http://local.hdtvmagazine.com/New_Mexico-nNew+Mexico.html" title="New Mexico" class="dmcontent_link">New Mexico</a></li>
		<li><a href="http://local.hdtvmagazine.com/New_York-nNew+York.html" title="New York" class="dmcontent_link">New York</a></li>
		<li><a href="http://local.hdtvmagazine.com/North_Carolina-nNorth+Carolina.html" title="North Carolina" class="dmcontent_link">North Carolina</a></li>
		<li><a href="http://local.hdtvmagazine.com/North_Dakota-nNorth+Dakota.html" title="North Dakota" class="dmcontent_link">North Dakota</a></li>
		<li><a href="http://local.hdtvmagazine.com/Ohio-nOhio.html" title="Ohio" class="dmcontent_link">Ohio</a></li>
		<li><a href="http://local.hdtvmagazine.com/Oklahoma-nOklahoma.html" title="Oklahoma" class="dmcontent_link">Oklahoma</a></li>
		<li><a href="http://local.hdtvmagazine.com/Oregon-nOregon.html" title="Oregon" class="dmcontent_link">Oregon</a></li>
		<li><a href="http://local.hdtvmagazine.com/Pennsylvania-nPennsylvania.html" title="Pennsylvania" class="dmcontent_link">Pennsylvania</a></li>
		<li><a href="http://local.hdtvmagazine.com/Rhode_Island-nRhode+Island.html" title="Rhode Island" class="dmcontent_link">Rhode Island</a></li>
		<li><a href="http://local.hdtvmagazine.com/South_Carolina-nSouth+Carolina.html" title="South Carolina" class="dmcontent_link">South Carolina</a></li>
		<li><a href="http://local.hdtvmagazine.com/South_Dakota-nSouth+Dakota.html" title="South Dakota" class="dmcontent_link">South Dakota</a></li>
		<li><a href="http://local.hdtvmagazine.com/Tennessee-nTennessee.html" title="Tennessee" class="dmcontent_link">Tennessee</a></li>
		<li><a href="http://local.hdtvmagazine.com/Texas-nTexas.html" title="Texas" class="dmcontent_link">Texas</a></li>
		<li><a href="http://local.hdtvmagazine.com/Utah-nUtah.html" title="Utah" class="dmcontent_link">Utah</a></li>
		<li><a href="http://local.hdtvmagazine.com/Vermont-nVermont.html" title="Vermont" class="dmcontent_link">Vermont</a></li>
		<li><a href="http://local.hdtvmagazine.com/Virginia-nVirginia.html" title="Virginia" class="dmcontent_link">Virginia</a></li>
		<li><a href="http://local.hdtvmagazine.com/Washington-nWashington.html" title="Washington" class="dmcontent_link">Washington</a></li>
		<li><a href="http://local.hdtvmagazine.com/West_Virginia-nWest+Virginia.html" title="West Virginia" class="dmcontent_link">West Virginia</a></li>
		<li><a href="http://local.hdtvmagazine.com/Wisconsin-nWisconsin.html" title="Wisconsin" class="dmcontent_link">Wisconsin</a></li>
		<li><a href="http://local.hdtvmagazine.com/Wyoming-nWyoming.html" title="Wyoming" class="dmcontent_link">Wyoming</a></li>
	</ul></div>
<span class="corners-bottom"><span></span></span></div>
EOT;

	$base_img_host = BASE_IMG_HOST;
	$current_year = date("Y");
	$body_footer_output .= <<<EOT
<div id="container-footer" align="center"><div><span class="corners-top"><span></span></span>
	<!--Creative Commons License-->
		<a target="_blank" rel="license" href="http://creativecommons.org/licenses/by-nc-nd/2.5/"><img src="$base_img_host/images/somerights20.png" alt="This work is licensed under a Creative Commons Attribution-NonCommercial-NoDerivs2.5 License" height="31" width="88" /></a><br/>
	<!--/Creative Commons License-->
	<!-- <rdf:RDF xmlns="http://web.resource.org/cc/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">
		<Work rdf:about="">
			<license rdf:resource="http://creativecommons.org/licenses/by-nc-nd/2.5/" />
			<dc:type rdf:resource="http://purl.org/dc/dcmitype/Text" />
		</Work>
		<License rdf:about="http://creativecommons.org/licenses/by-nc-nd/2.5/">
			<permits rdf:resource="http://web.resource.org/cc/Reproduction"/>
			<permits rdf:resource="http://web.resource.org/cc/Distribution"/>
			<requires rdf:resource="http://web.resource.org/cc/Notice"/>
			<requires rdf:resource="http://web.resource.org/cc/Attribution"/>
			<prohibits rdf:resource="http://web.resource.org/cc/CommercialUse"/>
		</License>
	</rdf:RDF> -->
	&copy; 1998 - $current_year HDTV Magazine, Ltd.<br />
		<a href="/">Home</a>&nbsp;&nbsp;|&nbsp;&nbsp;
		<a href="/about/advertising.php">Advertise (FM Tech)</a>&nbsp;&nbsp;|&nbsp;&nbsp;
		<a href="/about/contact.php">Contact Us</a>&nbsp;&nbsp;|&nbsp;&nbsp;
		<a href="/about/index.php">About HDTV Magazine</a>&nbsp;&nbsp;|&nbsp;&nbsp;
		<a href="/help/index.php">Help</a>&nbsp;&nbsp;|&nbsp;&nbsp;
		<a href="/rss-feeds.php" style="margin-top:3px"><img src="$base_img_host/images/livemark.png" alt=")))" align="absmiddle" height="16" width="16" /> RSS Feeds</a>
	<br />
	v3.0<br />
<span class="corners-bottom"><span></span></span></div></div>
$links_output
EOT;

	# New Asynchronous Google Analytics code
	$body_footer_output .= <<<EOT
<script type="text/javascript">
	var _gaq = _gaq || [];
	_gaq.push(['_setAccount', 'UA-615543-1']);
	_gaq.push(['_setCustomVar', 1, 'Template Version', '3', 3]);
	_gaq.push(['_trackPageview']);

	(function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	})();
</script>
EOT;

	#Quantcast tag
	$body_footer_output .= <<<EOT
<!-- Start Quantcast tag -->
<script type="text/javascript">
	_qoptions={qacct:"p-dcwjcbV71qC0g"};
</script>
<script type="text/javascript" src="http://edge.quantserve.com/quant.js"></script>
<noscript>
	<img src="http://pixel.quantserve.com/pixel/p-dcwjcbV71qC0g.gif" style="display: none;" border="0" height="1" width="1" alt="Quantcast"/>
</noscript>
<!-- End Quantcast tag -->
EOT;

	# Chartbeat tag
	$body_footer_output .= <<<EOT
<!-- Chartbeat -->
<script type="text/javascript">
	var _sf_async_config={uid:2353,domain:"hdtvmagazine.com"};
	(function(){
		function loadChartbeat() {
			window._sf_endpt=(new Date()).getTime();
			var e = document.createElement('script');
			e.setAttribute('language', 'javascript');
			e.setAttribute('type', 'text/javascript');
			e.setAttribute('src',
				 (("https:" == document.location.protocol) ? "https://s3.amazonaws.com/" : "http://") +
				 "static.chartbeat.com/js/chartbeat.js");
			document.body.appendChild(e);
		}
		var oldonload = window.onload;
		window.onload = (typeof window.onload != 'function') ?
			 loadChartbeat : function() { oldonload(); loadChartbeat(); };
	})();
</script>
EOT;

	# Apture tag
	$body_footer_output .= '<script id="aptureScript" type="text/javascript" src="http://www.apture.com/js/apture.js?siteToken=kwQEuu6" charset="utf-8"></script>';

	# Ziff-Davis tag
	$body_footer_output .= <<<EOT
<!-- Demdex Tag Insertion Manager -->
<script type="text/javascript">
	var dexBaseURL=(("https:"==document.location.protocol) ? "https://a248.e.akamai.net/demdex.download.akamai.com/ziffdavis/153/" : "http://cdn.demdex.net/ziffdavis/153/");
	document.write(unescape("%3Cscript src='"+ dexBaseURL + "demdex.js' type='text/javascript' %3E%3C/script%3E"));
</script>
<!-- End Demdex Tag Insertion Manager -->
EOT;
?>