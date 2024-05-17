<?
	# Filepath & Executable Constants
		define('DATA_DIR', '/var/www/data');
		define('PODCAST_PATH', '/downloads/podcast');
		define('PODCAST_DIR', BASE_DIR . PODCAST_PATH);
		define('SERVER_NAME', 'www.hdtvmagazine.com');
		define('COOKIE_DOMAIN', '.hdtvmagazine.com');
		define('BASE_URL', 'http://'. SERVER_NAME);
		define('BASE_IMG_URL', 'http://www.hdtvmagazine.us'); # DEPRECATED
		define('BASE_IMG_HOST', 'http://www.hdtvmagazine.us');

	# NEEDS REPLACING !!!!!!!!!!!!!!!!!!!!!!!!!!
		// PHP_SELF is the path to the currently executing script, relative to the document root
		define('PHP_SELF', $_SERVER['PHP_SELF']);
	# END NEEDS REPLACING !!!!!!!!!!!!!!!!!!!!!!!!!!!

	// Set Time Constants.
		define('MINUTES', 60);
		define('HOURS', 60*MINUTES);
		define('DAYS', 24*HOURS);

	// Set Filesize Constants.
		define('KB', 1024);
		define('MB', 1024*KB);
		define('GB', 1024*MB);

	// Email Subscription Constants (currently limited to mediumint - 16777215)
	// CHECK SUBSCRIPTIONS BEFORE ADDING NEW VALUE AND REMOVE USERS
		define('SUB_TEST', 1);
		define('SUB_NEW_STATIONS', 2);
		define('SUB_GUIDE_LISTING', 4);
		define('SUB_BROADCAST', 8);
		define('SUB_NEW_PRODUCTS', 16);
		define('SUB_SITE_UPDATES', 32);
		define('SUB_EVENTS', 64);
		define('SUB_BULLETINS', 128);
		define('SUB_ARTICLES', 256);
		define('SUB_TIPS_LIST', 512);
		define('SUB_GUIDE_GRID', 1024);
		define('SUB_GUIDE_BRIEF', 2048);
		define('SUB_TODAY', 4096);
		define('SUB_INTERVIEWS', 8192);
		define('SUB_HISTORY', 16384);
		define('SUB_FORUM_UPDATE', 32768);
		define('SUB_REVIEWS', 65536);
		define('SUB_STUDIES', 131072);
		define('SUB_DAILY', 262144);
		define('SUB_PODCAST', 524288);
		define('SUB_COLUMNS', 1048576);
		define('SUB_WEEKLY', 2097152);
#		define('SUB_DEFAULTS_BASIC', SUB_NEW_STATIONS | SUB_GUIDE_LISTING | SUB_BROADCAST | SUB_NEW_PRODUCTS | SUB_SITE_UPDATES | SUB_EVENTS | SUB_ARTICLES | SUB_REVIEWS | SUB_BULLETINS | SUB_INTERVIEWS | SUB_HISTORY | SUB_FORUM_UPDATE | SUB_STUDIES);
		define('SUB_DEFAULTS_BASIC', SUB_BROADCAST | SUB_NEW_PRODUCTS | SUB_SITE_UPDATES | SUB_EVENTS | SUB_ARTICLES | SUB_REVIEWS | SUB_BULLETINS | SUB_INTERVIEWS | SUB_HISTORY | SUB_FORUM_UPDATE | SUB_STUDIES | SUB_DAILY);
#		define('SUB_DEFAULTS_PREMIUM', SUB_DEFAULTS_BASIC | SUB_GUIDE_BRIEF | SUB_GUIDE_GRID | SUB_TODAY);
		define('SUB_DEFAULTS_PREMIUM', SUB_DEFAULTS_BASIC);

	// Subscription Type Mapping
		$SUB[SUB_TEST] = 'Test';
		$SUB[SUB_NEW_STATIONS] = 'New Stations';
#		$SUB[SUB_GUIDE_LISTING] = 'Daily Program Listing';
		$SUB[SUB_BROADCAST] = 'Broadcast Announcements';
		$SUB[SUB_NEW_PRODUCTS] = 'Product Updates &amp; Special Offers';
		$SUB[SUB_SITE_UPDATES] = 'Website Updates';
		$SUB[SUB_FORUM_UPDATE] = 'Daily Forum Updates';
		$SUB[SUB_EVENTS] = 'Events';
		$SUB[SUB_BULLETINS] = 'News Bulletins';
		$SUB[SUB_ARTICLES] = 'New Articles';
		$SUB[SUB_REVIEWS] = 'New Reviews';
		$SUB[SUB_STUDIES] = 'Study Notifications';
#		$SUB[SUB_TIPS_LIST] = 'Tips List';
#		$SUB[SUB_GUIDE_GRID] = 'Daily Program Grid';
#		$SUB[SUB_GUIDE_BRIEF] = 'Daily Program Brief';
#		$SUB[SUB_TODAY] = 'HDTV Today';
		$SUB[SUB_DAILY] = 'HDTV Magazine Daily';
		$SUB[SUB_PODCAST] = 'HDTV Podcast';
		$SUB[SUB_COLUMNS] = 'New Columns';
		$SUB[SUB_WEEKLY] = 'HDTV Magazine Weekly';

	### Forum Configurations ###
		# This is used to exclude certain forums from being included in the Daily Forum Update, HDTV Today email and the XML feed.
		define('EXCLUDE_FORUMS', '1,12,93,96,64,111,115,134,139');

		# Exclude the following forums from the continuous post notification script
		define('EXCLUDE_FORUM_POST_NOTIFY', '1,12,22,23,24,25,114,116,117,139');

	### composite listing configuration ###
		# This is used to determine which "blogs" are included on the RSS feed and category page
		define('INCLUDE_BLOGS_ALL', '1,4,5,7,8,9,10');
		# This is used to determine which "blogs" are included in the notification emails and subscriptions
		define('INCLUDE_BLOGS_NOTIFY', '1,7,8,9,10');
		# This is used to determine which "blogs" are included on author page and blogburst xml feed
		define('INCLUDE_BLOGS_NO_BULLETINS', '1,4,5,8,9,10');
		# This is used to determine which "blogs" are included on the home page
#		define('INCLUDE_BLOGS_HOME', '1,8,10');
		define('INCLUDE_BLOGS_HOME', '1,7,8,9,10');

	// Company Flags
		define('COMP_FLAG_INV', 1);
		define('COMP_FLAG_EQUIP', 2);

	// Email Status Constants
   	define('EMAIL_DRAFT', 0);
   	define('EMAIL_SENT', 1);

	// Model Flags
		define('MODEL_TYPE_HIDE', 1);
		define('MODEL_TYPE_CRT', 2);
		define('MODEL_TYPE_LCD', 3);
		define('MODEL_TYPE_PLASMA', 4);
		define('MODEL_TYPE_DLP', 5);
		define('MODEL_TYPE_LCOS', 6);
		define('MODEL_TYPE_HDILA', 7);
		define('MODEL_TYPE_STB', 8);
		define('MODEL_TYPE_SCREEN', 9);
		define('MODEL_TYPE_ANTENNA', 10);
		define('MODEL_TYPE_CARD', 11);
		define('MODEL_TYPE_CABLE', 12);
		define('MODEL_TYPE_DVR', 13);
		define('MODEL_TYPE_VHS', 14);
		define('MODEL_TYPE_SCALER', 15);
		define('MODEL_TYPE_MISC', 16);
		define('MODEL_TYPE_CONSOLE', 17);
		define('MODEL_TYPE_CAMCORDER', 18);
		define('MODEL_TYPE_HDPLAYER', 19);
		define('MODEL_TYPE_UPCONVERTINGDVDPLAYER', 20);
		define('MODEL_TYPE_OLED', 21);
	// Model text mapping
		$MODEL_TYPE[MODEL_TYPE_HIDE] = 'Hide';
		$MODEL_TYPE[MODEL_TYPE_CRT] = 'CRT';
		$MODEL_TYPE[MODEL_TYPE_LCD] = 'LCD';
		$MODEL_TYPE[MODEL_TYPE_PLASMA] = 'Plasma';
		$MODEL_TYPE[MODEL_TYPE_DLP] = 'DLP';
		$MODEL_TYPE[MODEL_TYPE_LCOS] = 'LCoS';
		$MODEL_TYPE[MODEL_TYPE_HDILA] = 'HD-ILA';
		$MODEL_TYPE[MODEL_TYPE_OLED] = 'OLED';
		$MODEL_TYPE[MODEL_TYPE_CONSOLE] = 'Console Games (Xbox 360, PS3, etc)';
		$MODEL_TYPE[MODEL_TYPE_STB] = 'Set-Top Box/Receivers';
		$MODEL_TYPE[MODEL_TYPE_DVR] = 'DVR/Receivers';
		$MODEL_TYPE[MODEL_TYPE_VHS] = 'D-VHS';
		$MODEL_TYPE[MODEL_TYPE_CAMCORDER] = 'HD Camcorders';
		$MODEL_TYPE[MODEL_TYPE_SCREEN] = 'Projection Screens';
		$MODEL_TYPE[MODEL_TYPE_ANTENNA] = 'Antennas';
		$MODEL_TYPE[MODEL_TYPE_CARD] = 'Video Cards';
		$MODEL_TYPE[MODEL_TYPE_CABLE] = 'Cables';
		$MODEL_TYPE[MODEL_TYPE_SCALER] = 'Scalers/Upconverters';
		$MODEL_TYPE[MODEL_TYPE_MISC] = 'Miscellaneous';
		$MODEL_TYPE[MODEL_TYPE_HDPLAYER] = 'Blu-ray/HD DVD Players';
		$MODEL_TYPE[MODEL_TYPE_UPCONVERTINGDVDPLAYER] = 'Upconverting DVD Players';

	// Model Projection Type Flags
		define('MODEL_PROJ_TYPE_DIRECT', 1);
		define('MODEL_PROJ_TYPE_FRONT', 2);
		define('MODEL_PROJ_TYPE_REAR', 3);
	// Model Projection Type mapping
		$MODEL_PROJ_TYPE[MODEL_PROJ_TYPE_DIRECT] = 'Direct View';
		$MODEL_PROJ_TYPE[MODEL_PROJ_TYPE_FRONT] = 'Front Projection';
		$MODEL_PROJ_TYPE[MODEL_PROJ_TYPE_REAR] = 'Rear Projection';

	# Email Preference Constants
		define('EMAIL_PREF_TEXT', 0);
		define('EMAIL_PREF_HTML', 1);

	# Product Status Constants
		define('PROD_STATUS_INACTIVE', 0);
		define('PROD_STATUS_ACTIVE', 1);

	# Product Type Constants
		define('PROD_TYPE_BUY_NOW', 0);
		define('PROD_TYPE_SUBSCRIPTION', 1);
		define('PROD_TYPE_DOWNLOAD', 2);

	### Access Control Constants
		define('ACCESS_ADMIN', 1); // Global Access to all functions
		define('ACCESS_PREMIUM', 2); // Access to all Program Guide features
		define('ACCESS_AUTHOR', 4); // Access to author earnings screen
		define('ACCESS_ADMIN_NEWS', 8); // Access to edit and rank news stories
		define('ACCESS_DEALER', 32); // Access to the Dealer area
		define('ACCESS_ADMIN_ANY', ACCESS_ADMIN | ACCESS_ADMIN_NEWS);

	# Misc Constants
		define('BLOCK_WIDTH', 50);

	# Icons
		define('ICON_SHOW_DD', 1);
		define('ICON_SHOW_CC', 2);
		define('ICON_SHOW_NEW', 4);
		define('ICON_SHOW_LB', 8);

	# Log Type Constants
		define('LOG_TYPE_ERROR', 1);
		define('LOG_TYPE_START', 2);
		define('LOG_TYPE_END', 4);
		define('LOG_TYPE_NOTICE', 8);

	# user option Flags
		define('OPT_SHOW_SD', 1);
		define('OPT_SHOW_HD_LEVEL', 2);
		define('OPT_SHOW_POPOVER', 4);

	# Paypal Constants
/*
		define('PAYPAL_HOST', 'www.paypal.com');
		define('PAYPAL_SANDBOX_HOST', 'www.sandbox.paypal.com');
		define('PAYPAL_URL', 'https://'. PAYPAL_HOST .'/cgi-bin/webscr');
		define('PAYPAL_TEST_URL', 'https://'. PAYPAL_SANDBOX_HOST .'/cgi-bin/webscr');
		define('PAYPAL_EMAIL', 'pp@hdtvmagazine.com');
*/

	// URL Constants
		define('URL_ABOUT', '/about/index.php');
		define('URL_ABOUT_ADVERTISING', '/about/advertising.php');
		define('URL_ABOUT_CONTACT', '/about/contact.php');
		define('URL_ABOUT_MARKETING', '/about/marketing.php');
		define('URL_ABOUT_PRIVACY', '/about/privacy.php');
		define('URL_ACCESS_DENIED', '/access-denied.php');
		define('URL_ACTIVATE', '/activate.php');
		define('URL_ADMIN', '/admin/index.php');
		define('URL_ADMIN_EMAIL_BROADCAST', '/admin/email-broadcast.php');
		define('URL_ADMIN_EMAILS', '/admin/emails');
		define('URL_ADMIN_EMAILS_BLOG_NEW', '/admin/emails/blog-new.php');
		define('URL_ADMIN_EMAILS_BLOG_NEW_TEXT', '/admin/emails/blog-new-text.php');
		define('URL_ADMIN_EMAILS_SUB_EXP_TEXT', '/admin/emails/subscription-expiration-text.php');
		define('URL_ADMIN_EQUIPMENT_EDIT', '/admin/equipment-edit.php');
		define('URL_ADMIN_STATUS_REPORT', '/admin/status-report.php');
		define('URL_ADMIN_PRODUCTS', '/admin/products.php');
		define('URL_ADMIN_PRODUCTS_ALL', '/admin/products-all.php');
		define('URL_ADMIN_PRODUCTS_INVALID', '/admin/products-invalid.php');
		define('URL_ARTICLES', '/articles/index.php');
		define('URL_ARTICLES_AUTHOR', '/articles/articles-author.php');
		define('URL_ARTICLES_DIR', '/articles');
		define('URL_ARTICLES_PRINT', '/articles/print.php');
		define('URL_BULLETINS', '/news/bulletins.php');
		define('URL_CONTACT', '/about/contact.php');
		define('URL_CONTACT_ADVERTISE', '/about/advertising.php');
		define('URL_DEALER_EDIT', '/dealer/dealer-edit.php');
		define('URL_EQUIPMENT', '/equipment/index.php');
		define('URL_EQUIPMENT_MANUFACTURER', '/equipment/manufacturer.php');
		define('URL_EQUIPMENT_MODEL', '/equipment/model.php');
		define('URL_EQUIPMENT_MODEL_DETAILS', '/equipment/model-details.php');
		define('URL_EQUIPMENT_MODEL_EBAY', '/equipment/model-ebay.php');
		define('URL_EQUIPMENT_MODEL_NEWS', '/equipment/model-news.php');
		define('URL_EQUIPMENT_MODEL_REVIEWS', '/equipment/model-reviews.php');
		define('URL_EQUIPMENT_MODEL_SIMILAR', '/equipment/model-similar.php');
		define('URL_EQUIPMENT_SEARCH', '/equipment/search.php');
		define('URL_EQUIPMENT_SIZE', '/equipment/size.php');
		define('URL_EVENTS', '/events/index.php');
		define('URL_EVENTS_ADMIN', '/events/admin.php');
		// URL_FORUM moved above subscription descriptions
		define('URL_FORUM_NEWS', '/forum/viewforum.php?f=12');
		define('URL_FORUM_UPDATE', '/admin/emails/forum-update.php');
		define('URL_FORUM_UPDATE_TEXT', '/admin/emails/forum-update-text.php');
		define('URL_FORUM_VIEWTOPIC', '/forum/viewtopic.php');
		define('URL_FORUMS', '/forums/index.php');
		define('URL_GLOSSARY', '/glossary.php');
		define('URL_GUIDE', '/programming/guide.php');
		define('URL_GUIDE_BRIEF', '/programming/guide-brief.php');
		define('URL_GUIDE_DAILY', '/programming/guide-daily.php');
		define('URL_GUIDE_EMAIL', '/admin/emails/daily-grid.php');
		define('URL_GUIDE_GRID', '/programming/guide-grid.php');
		// URL_GUIDE _LISTING moved above subscription descriptions
		define('URL_GUIDE_LISTING_TEXT', '/programming/guide-listing-text.php');
		define('URL_GUIDE_MOVIE', '/programming/guide-movie.php');
		define('URL_GUIDE_PARTNER', '/programming/guide-partner.php');
		define('URL_GUIDE_PROGRAM', '/programming/guide-program.php');
		define('URL_GUIDE_STATION', '/programming/guide-station.php');
		define('URL_HELP', '/help/index.php');
		define('URL_HELP_FEEDBACK', '/help/feedback.php');
		define('URL_HELP_FEEDBACK_THANKS', '/help/feedback-thanks.php');
		define('URL_HELP_LOGIN_LOOKUP', '/help/login-lookup.php');
		define('URL_HELP_LOGIN_PASSWORD_CHANGE', '/help/login-password-change.php');
		define('URL_HELP_LOGIN_PASSWORD_RESET', '/forum/profile.php?mode=sendpassword');
		define('URL_HISTORY', '/history/index.php');
		define('URL_HISTORY_DIR', '/history');
		define('URL_HISTORY_RSS', '/history/index.xml');
		define('URL_INTERVIEWS_DIR', '/history/interviews');
		define('URL_INTERVIEWS_RSS', '/history/interviews/index.xml');
		define('URL_INVESTING', '/investing/index.php');
		define('URL_LOGIN', '/forum/login.php');
		define('URL_LOGOUT', '/logout.php');
		define('URL_NEWS', '/news/index.php');
		define('URL_NEWS_ADMIN', '/news/admin.php');
		define('URL_NEWS_ARCHIVE', '/news/archive.php');
		define('URL_NEWS_DIR', '/news');
		define('URL_POLLS', '/polls-active.php');
		define('URL_PROFILE', '/profile.php');
		define('URL_PROFILE_CREATE', '/profile-create.php');
		define('URL_PROFILE_GUIDE', '/programming/profile-guide.php');
		define('URL_PROFILE_HARDWARE', '/profile-hardware.php');
		define('URL_PROFILE_STATIONS', '/programming/profile-stations.php');
		define('URL_PROFILE_SUBSCRIPTIONS', '/profile-subscriptions.php');
		define('URL_PROG', '/programming/index.php');
		define('URL_PROG_BROADCAST', '/programming/broadcast.php');
		define('URL_PROG_BROADCAST_MARKET', '/programming/broadcast-market.php');
		define('URL_PROG_BROADCAST_MARKET_PRINT', '/programming/broadcast-market-print.php');
		define('URL_PROG_CABLE', '/programming/cable.php');
		define('URL_PROG_FEATURES', '/programming/features.php');
		define('URL_PROG_GAMING', '/programming/gaming.php');
		define('URL_PROG_SATELLITE', '/programming/satellite.php');
		define('URL_PROG_SEARCH', '/programming/search.php');
		define('URL_PROG_XBOX', '/programming/xbox.php');
		define('URL_REVIEWS', '/reviews/index.php');
		define('URL_REVIEWS_DIR', '/reviews');
		define('URL_SESSION_EXPIRED', '/errors/session_expired.php');
		define('URL_STATIONS_BY_PROVIDER', '/programming/stations/stations-by-provider.php');
		define('URL_STATIONS_BY_MARKET', '/programming/stations/stations-by-market.php');
		define('URL_STORE', '/hdstore/home.php');
		define('URL_SUBSCRIBE', '/subscribe/index.php');
		define('URL_UNSUBSCRIBE', '/unsubscribe.php');
		define('URL_WELCOME_BASIC', '/welcome-basic.php');
		define('URL_WELCOME_PREMIUM', '/welcome-premium.php');
		define('URL_XML_FEEDS', '/xml-feeds.php');

		define('FULL_URL_ABOUT', 'http://'. SERVER_NAME . URL_ABOUT);
		define('FULL_URL_ACTIVATE', 'http://'. SERVER_NAME . URL_ACTIVATE);
		define('FULL_URL_ADMIN', 'http://'. SERVER_NAME . URL_ADMIN);
		define('FULL_URL_ADMIN_EMAILS', 'http://'. SERVER_NAME . URL_ADMIN_EMAILS);
		define('FULL_URL_ADMIN_EMAILS_BLOG_NEW', 'http://'. SERVER_NAME . URL_ADMIN_EMAILS_BLOG_NEW);
		define('FULL_URL_ADMIN_EMAILS_BLOG_NEW_TEXT', 'http://'. SERVER_NAME . URL_ADMIN_EMAILS_BLOG_NEW_TEXT);
		define('FULL_URL_ADMIN_EMAILS_SUB_EXP_TEXT', 'http://'. SERVER_NAME . URL_ADMIN_EMAILS_SUB_EXP_TEXT);
		define('FULL_URL_ADMIN_STATUS_REPORT', 'http://'. SERVER_NAME . URL_ADMIN_STATUS_REPORT);
		define('FULL_URL_ADMIN_PRODUCTS', 'http://'. SERVER_NAME . URL_ADMIN_PRODUCTS);
		define('FULL_URL_ARTICLES', 'http://'. SERVER_NAME . URL_ARTICLES);
		define('FULL_URL_ARTICLES_DIR', 'http://'. SERVER_NAME . URL_ARTICLES_DIR);
		define('FULL_URL_BEST_HDTVS', 'http://'. SERVER_NAME . URL_BEST_HDTVS);
		define('FULL_URL_BULLETINS', 'http://'. SERVER_NAME . URL_BULLETINS);
		define('FULL_URL_CONTACT', 'http://'. SERVER_NAME . URL_CONTACT);
		define('FULL_URL_EQUIPMENT', 'http://'. SERVER_NAME . URL_EQUIPMENT);
		define('FULL_URL_EQUIPMENT_MODEL_REVIEWS', 'http://'. SERVER_NAME . URL_EQUIPMENT_MODEL_REVIEWS);
		define('FULL_URL_EVENTS', 'http://'. SERVER_NAME . URL_EVENTS);
		define('FULL_URL_EVENTS_ADMIN', 'http://'. SERVER_NAME . URL_EVENTS_ADMIN);
		define('FULL_URL_FORUMS', 'http://'. SERVER_NAME . URL_FORUMS);
		define('FULL_URL_FORUM', 'http://'. SERVER_NAME . URL_FORUM);
		define('FULL_URL_FORUM_UPDATE', 'http://'. SERVER_NAME . URL_FORUM_UPDATE);
		define('FULL_URL_FORUM_UPDATE_TEXT', 'http://'. SERVER_NAME . URL_FORUM_UPDATE_TEXT);
		define('FULL_URL_FORUM_VIEWTOPIC', 'http://'. SERVER_NAME . URL_FORUM_VIEWTOPIC);
		define('FULL_URL_GUIDE', 'http://'. SERVER_NAME . URL_GUIDE);
		define('FULL_URL_GUIDE_BRIEF', 'http://'. SERVER_NAME . URL_GUIDE_BRIEF);
		define('FULL_URL_GUIDE_DAILY', 'http://'. SERVER_NAME . URL_GUIDE_DAILY);
		define('FULL_URL_GUIDE_EMAIL', 'http://'. SERVER_NAME . URL_GUIDE_EMAIL);
		define('FULL_URL_GUIDE_GRID', 'http://'. SERVER_NAME . URL_GUIDE_GRID);
		define('FULL_URL_GUIDE_LISTING', 'http://'. SERVER_NAME . URL_GUIDE_LISTING);
		define('FULL_URL_GUIDE_LISTING_TEXT', 'http://'. SERVER_NAME . URL_GUIDE_LISTING_TEXT);
		define('FULL_URL_GUIDE_PROGRAM', 'http://'. SERVER_NAME . URL_GUIDE_PROGRAM);
		define('FULL_URL_GUIDE_STATION', 'http://'. SERVER_NAME . URL_GUIDE_STATION);
		define('FULL_URL_HELP', 'http://'. SERVER_NAME . URL_HELP);
		define('FULL_URL_HELP_FEEDBACK', 'http://'. SERVER_NAME . URL_HELP_FEEDBACK);
		define('FULL_URL_HELP_LOGIN_PASSWORD_CHANGE', 'http://'. SERVER_NAME . URL_HELP_LOGIN_PASSWORD_CHANGE);
		define('FULL_URL_HISTORY', 'http://'. SERVER_NAME . URL_HISTORY);
		define('FULL_URL_HOME', 'http://'. SERVER_NAME);
		define('FULL_URL_INVESTING', 'http://'. SERVER_NAME . URL_INVESTING);
		define('FULL_URL_LOGIN', 'http://'. SERVER_NAME . URL_LOGIN);
		define('FULL_URL_LOGOUT', 'http://'. SERVER_NAME . URL_LOGOUT);
		define('FULL_URL_NEWS', 'http://'. SERVER_NAME . URL_NEWS);
		define('FULL_URL_NEWS_ADMIN', 'http://'. SERVER_NAME . URL_NEWS_ADMIN);
		define('FULL_URL_NEWS_DIR', 'http://'. SERVER_NAME . URL_NEWS_DIR);
		define('FULL_URL_POLLS', 'http://'. SERVER_NAME . URL_POLLS);
		define('FULL_URL_PROG', 'http://'. SERVER_NAME . URL_PROG);
		define('FULL_URL_PROFILE', 'http://'. SERVER_NAME . URL_PROFILE);
		define('FULL_URL_PROFILE_CREATE', 'http://'. SERVER_NAME . URL_PROFILE_CREATE);
		define('FULL_URL_PROFILE_STATIONS', 'http://'. SERVER_NAME . URL_PROFILE_STATIONS);
		define('FULL_URL_PROFILE_SUBSCRIPTIONS', 'http://'. SERVER_NAME . URL_PROFILE_SUBSCRIPTIONS);
		define('FULL_URL_REVIEWS', 'http://'. SERVER_NAME . URL_REVIEWS);
		define('FULL_URL_REVIEWS_DIR', 'http://'. SERVER_NAME . URL_REVIEWS_DIR);
		define('FULL_URL_STATIONS_BY_PROVIDER', 'http://'. SERVER_NAME . URL_STATIONS_BY_PROVIDER);
		define('FULL_URL_STATIONS_BY_MARKET', 'http://'. SERVER_NAME . URL_STATIONS_BY_MARKET);
		define('FULL_URL_STORE', 'http://'. SERVER_NAME . URL_STORE);
		define('FULL_URL_SHOPPING', 'http://hdtv.pricegrabber.com');
		define('FULL_URL_SUBSCRIBE', 'http://'. SERVER_NAME . URL_SUBSCRIBE);
		define('FULL_URL_UNSUBSCRIBE', 'http://'. SERVER_NAME . URL_UNSUBSCRIBE);
		define('FULL_URL_WELCOME_BASIC', 'http://'. SERVER_NAME . URL_WELCOME_BASIC);
		define('FULL_URL_WELCOME_PREMIUM', 'http://'. SERVER_NAME . URL_WELCOME_PREMIUM);
		define('PG_URL_PRODUCT', FULL_URL_SHOPPING .'/search_getprod.php');
		define('PG_URL_TECH_SPECS', FULL_URL_SHOPPING .'/search_techspecs_full.php');

	// Images
		define('IMG_LOGO', '/images/hdtvmagazine.gif');

	// Colors
		define('PRIMARY_COLOR', '003F87'); // Pantone 294
		define('SECONDARY_COLOR', '995905'); // Pantone 154
		define('TERTIARY_COLOR', '73681F');
		define('LINK_COLOR', PRIMARY_COLOR);
		define('TEXT_COLOR', '000000');
		define('TEXT_COLOR_LIGHT', 'FFFFFF');
		define('BG_COLOR', 'EDEDED');
		define('BORDER_COLOR', 'AAAAAA');

		define('POPOVER_BORDER', BORDER_COLOR);
		define('POPOVER_BG', BG_COLOR);
		define('POPOVER_TEXT', LINK_COLOR);
		define('PROGRAM_COLOR', TEXT_COLOR);
		define('PROGRAMHD_COLOR', '000000');
		define('PROGRAMSD_COLOR', 'CCCCCC');

	// Google Channel mappings
#		$GOOGLE_CHANNEL = array();
#		$GOOGLE_CHANNEL[Banner] = '';
#		$GOOGLE_CHANNEL[Email_Banner] = '0541970625';
#		$GOOGLE_CHANNEL[Email_Leaderboard] = '1902831303';

#		$GOOGLE_CHANNEL[Button] = '5588699202';
#		$GOOGLE_CHANNEL[Leaderboard] = '4547773587';
#		$GOOGLE_CHANNEL[Links] = '9752039117';
#		$GOOGLE_CHANNEL[Medium_Rectangle] = '8730692016';
#		$GOOGLE_CHANNEL[Vertical_Banner] = '1207257610';
#		$GOOGLE_CHANNEL[Skyscraper] = '5074615241';
#		$GOOGLE_CHANNEL[Skyscraper2] = '3313832484';

#		$GOOGLE_CHANNEL[Forum_Links] = '7287181844';
#		$GOOGLE_CHANNEL[Forum_Links] = '2461175721'; # Same as 'Richard Fisher' below ... consolidating channels

#		$GOOGLE_CHANNEL['Ben Drawbaugh'] = '';
#		$GOOGLE_CHANNEL['Dale Cripps'] = '';
#		$GOOGLE_CHANNEL['Ed Milbourn'] = '0545809537';
#		$GOOGLE_CHANNEL['Eddie Fritts'] = '4960246662';
#		$GOOGLE_CHANNEL['Greg Moyer'] = '7247162022';
#		$GOOGLE_CHANNEL['Lee Wood'] = '3406227562';
#		$GOOGLE_CHANNEL['Richard Fisher'] = '2461175721';
#		$GOOGLE_CHANNEL['Robert Graves'] = '5083195557';
#		$GOOGLE_CHANNEL['Rodolfo La Maestra'] = '1806756332';
#		$GOOGLE_CHANNEL['Shane Sturgeon'] = '';
#		$GOOGLE_CHANNEL['Terry Paullin'] = '4689058888';
#		$GOOGLE_CHANNEL['The HT Guys'] = '8968328003';
#		$GOOGLE_CHANNEL['Ara Derderian & Braden Russell'] = '5015675268';
#		$GOOGLE_CHANNEL['Tom Starner'] = '1297715903';
#		$GOOGLE_CHANNEL['Doug Brott'] = '5067982290';

	# User ID mappings, used for auto-notification of new articles associated comments in the forum
/*
		$AUTHOR_ID = array();
		$AUTHOR_ID['Ben Drawbaugh'] = '12458';
		$AUTHOR_ID['Dale Cripps'] = '1001';
		$AUTHOR_ID['Ed Milbourn'] = '10999';
		$AUTHOR_ID['Eddie Fritts'] = '';
		$AUTHOR_ID['Greg Moyer'] = '11001';
		$AUTHOR_ID['Lee Wood'] = '1038';
		$AUTHOR_ID['Richard Fisher'] = '2379';
		$AUTHOR_ID['Robert Graves'] = '11000';
		$AUTHOR_ID['Rodolfo La Maestra'] = '2169';
		$AUTHOR_ID['Shane Sturgeon'] = '2';
		$AUTHOR_ID['Terry Paullin'] = '5176';
		$AUTHOR_ID['The HT Guys'] = '17942';
		$AUTHOR_ID['Ara Derderian & Braden Russell'] = '17942';
		$AUTHOR_ID['Tom Starner'] = '19754';
		$AUTHOR_ID['Doug Brott'] = '26791';
		$AUTHOR_ID['Robert Fowkes'] = '2658';

		$AUTHOR_PORTRAIT['Dale Cripps'] = '/images/portraits/dale_cripps_100_b.jpg';
		$AUTHOR_PORTRAIT['Shane Sturgeon'] = '/images/portraits/shane_sturgeon_100_b.jpg';
		$AUTHOR_PORTRAIT['Ed Milbourn'] = '/images/portraits/ed_milbourn_100_b.jpg';
		$AUTHOR_PORTRAIT['Richard Fisher'] = '/images/portraits/richard_fisher_100_b.jpg';
		$AUTHOR_PORTRAIT['Rodolfo La Maestra'] = '/images/portraits/rodolfo_la_maestra_100_b.jpg';
		$AUTHOR_PORTRAIT['The HT Guys'] = '/images/portraits/the_ht_guys_100_b.jpg';
		$AUTHOR_PORTRAIT['Ara Derderian & Braden Russell'] = '/images/portraits/the_ht_guys_100_b.jpg';
*/
?>
