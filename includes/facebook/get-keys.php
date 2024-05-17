<?
	header('Content-Type: text/plain');

	require('/var/www/html/includes/lib_facebook.php');


	# Connect as app: HDTV Magazine
	$config = array();
	$config['appId'] = '120965029026';
	$config['secret'] = 'a260e8304dcc4e0c8728528e4b779391';
	$config['fileUpload'] = true; // optional
	$config['cookie'] = true; // optional

	$facebook = new Facebook($config);

//The following is copied from the php sdk example.php
//You shouldn't need to change anything here

$session = $facebook->getSession();
$me = null;

if ($session) {
   try {
	$uid = $facebook->getUser();
	$me = $facebook->api('/me');
   } catch (FacebookApiException $e) {
	error_log($e);
   }
}

// login or logout url will be needed depending on current user state.
if ($me) {
	$logoutUrl = $facebook->getLogoutUrl();
} else {
	$loginUrl = $facebook->getLoginUrl();
}


?>


<!doctype html>
<html xmlns:fb="http://www.facebook.com/2008/fbml">
  <head>
    <title>php-sdk</title>
    <style>
      body, textarea {
        font-family: 'Lucida Grande', Verdana, Arial, sans-serif;
      }
      h1 a {
        text-decoration: none;
        color: #3b5998;
      }
      h1 a:hover {
        text-decoration: underline;
      }
      textarea {
	width: 400px;
	height: 100px;
      }

    </style>
  </head>
  <body>
    <!--
      We use the JS SDK to provide a richer user experience. For more info,
      look here: http://github.com/facebook/connect-js
    -->
    <div id="fb-root"></div>
    <script>
      window.fbAsyncInit = function() {
        FB.init({
          appId   : '<?php echo $facebook->getAppId(); ?>',
          session : <?php echo json_encode($session); ?>, // don't refetch the session when PHP already has it
          status  : true, // check login status
          cookie  : true, // enable cookies to allow the server to access the session
          xfbml   : true // parse XFBML
        });

        // whenever the user logs in, we refresh the page
        FB.Event.subscribe('auth.login', function() {
          window.location.reload();
        });
      };

      (function() {
        var e = document.createElement('script');
        e.src = document.location.protocol + '//connect.facebook.net/en_US/all.js';
        e.async = true;
        document.getElementById('fb-root').appendChild(e);
      }());
    </script>

    <h1><a href="example.php">Post to page</a></h1>

    <?php if ($me): ?>
    <a href="<?php echo $logoutUrl; ?>">
      <img src="http://static.ak.fbcdn.net/rsrc.php/z2Y31/hash/cxrz4k7j.gif">
    </a>
    <?php else: ?>


<?php
/******************************

	The following bit is important to note, when calling for facebook login button your need the permissions set to allow manage_pages

******************************/
?>


    <div>
      <fb:login-button autologoutlink="true" perms="publish_stream,manage_pages"></fb:login-button>
    </div>
    <?php endif ?>

    <hr />

<?php

if($me) {


	//In order to post to the page later on we need to generate an Access Token for that page, to do this we get me-accounts in the following api call
	$accounts = $facebook->api('/me/accounts');

	//Loop through the array off accounts to find the page with a matching ID to the one we need
	foreach($accounts['data'] as $account){
	  if($account['id'] == $PAGE_ID){
		$ACCESS_TOKEN = $account['access_token'];
		echo "<p>Page Access Token: $ACCESS_TOKEN</p>";
		}
	}
}


?>

    <h3>Post to wall</h3>
    <form action="example.php" method="post">
	<label>Post to wall:</label><br />
	<textarea name="message"></textarea><br />
	<input type="submit" value="Post" />
    </form>

<?php

if($me && $_POST) {

	$message = $_POST['message'];

	echo "<p>Trying to post the following:<br /> ";
	echo "$message</p>";


	//To keep this as simple as possible, we'll only be posting a message to the wall with our access token we received from accounts
	$attachment = array('message' => $message, 'access_token' => $ACCESS_TOKEN);

	$result = $facebook->api('/'.$PAGE_ID.'/feed', 'post', $attachment);

	if($result){
		echo "<p>Posted status update</p>";
	} else {
		echo "<p>Unable to post update.</p>";
	}

}
?>
</body>
</html>