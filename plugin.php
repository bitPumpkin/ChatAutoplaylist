<?php

// Call set_include_path() as needed to point to your client library.
set_include_path(__DIR__ ."/google-api-php-client/src/");
require_once './google-api-php-client/src/Google/autoload.php';
require_once 'Google/Client.php';
require_once 'Google/Service/YouTube.php';
session_start();

$OAUTH2_CLIENT_ID = 'GET_YOUR_CLIENT_ID';
$OAUTH2_CLIENT_SECRET = 'GET_YOUR_CLIENT_SECRET';

$client = new Google_Client();
$client->setClientId($OAUTH2_CLIENT_ID);
$client->setClientSecret($OAUTH2_CLIENT_SECRET);
$client->setScopes('https://www.googleapis.com/auth/youtube');
$redirect = filter_var('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
    FILTER_SANITIZE_URL);
$client->setRedirectUri($redirect);
$client->setAccessType('offline');


$youtube = new Google_Service_YouTube($client);


if (isset($_GET['code'])) {
  if (strval($_SESSION['state']) !== strval($_GET['state'])) {
    die('The session state did not match.');
  }

  $client->authenticate($_GET['code']);
  $_SESSION['token'] = $client->getAccessToken();
  header('Location: ' . $redirect);
  
}

$isTokenRefreshed = false;
$indexurl = "yourURL/index.html";

if (isset($_SESSION['accessdenied'])) {
	unset($_SESSION['token']); 
	unset($_SESSION['accessdenied']); 
}

if (isset($_SESSION['token'])) {
  $client->setAccessToken($_SESSION['token']);
  if ($client->isAccessTokenExpired()) {	
	$currentTokenData = json_decode($_SESSION['token']);
	if (isset($currentTokenData->refresh_token)) {
		  $client->refreshToken($currentTokenData->refresh_token);
		  $_SESSION['token'] = $client->getAccessToken();
		  $isTokenRefreshed = true;
		  $htmlBody .= "<br><b>Service message:</b> Current access token has been refreshed by a refresh token.<br>";
		}
	}
}



if ($client->getAccessToken()) {
  
  try {
  $CreatePlaylist=true;
  $playlistId=0;
  $TodayDate=date("Y-m-d");
 
  $ChannelPlaylists = $youtube->playlists->listPlaylists("id,snippet", array('mine' => 'true'));
  
  if (empty($ChannelPlaylists))
  {
	$CreatePlaylist=true;
  }
    else
  {
		foreach ($ChannelPlaylists['items'] as $playlist)
		{		
			if ($playlist['snippet']['description']==$TodayDate)
			{
				$CreatePlaylist=false;
				$playlistId=$playlist['id'];
			}
		}
  }
} catch (Exception $exp)
{
}
 
	try {
	
	if ($CreatePlaylist && isset($_POST['streamer']))
	{
		$playlistSnippet = new Google_Service_YouTube_PlaylistSnippet();
		$playlistTitle = $_POST['streamer'] . ' Stream  ' . date("Y-m-d H:i:s");
		$playlistSnippet->setTitle($playlistTitle);
		$playlistSnippet->setDescription($TodayDate);

		$playlistStatus = new Google_Service_YouTube_PlaylistStatus();
		$playlistStatus->setPrivacyStatus('public');

		$youTubePlaylist = new Google_Service_YouTube_Playlist();
		$youTubePlaylist->setSnippet($playlistSnippet);
		$youTubePlaylist->setStatus($playlistStatus);

		$playlistResponse = $youtube->playlists->insert('snippet,status',
			$youTubePlaylist, array());
		$playlistId = $playlistResponse['id'];
	}

    $resourceId = new Google_Service_YouTube_ResourceId();
    $resourceId->setVideoId($_POST['watch']);
    $resourceId->setKind('youtube#video');

    $playlistItemSnippet = new Google_Service_YouTube_PlaylistItemSnippet();
    $playlistItemSnippet->setPlaylistId($playlistId);
    $playlistItemSnippet->setResourceId($resourceId);

    $playlistItem = new Google_Service_YouTube_PlaylistItem();
    $playlistItem->setSnippet($playlistItemSnippet);
    $playlistItemResponse = $youtube->playlistItems->insert(
        'snippet,contentDetails', $playlistItem, array());

    $htmlBody .= "<h3>New Playlist</h3><ul>";
    $htmlBody .= sprintf('<li>%s (%s)</li>',
        $playlistResponse['snippet']['title'],
        $playlistResponse['id']);
    $htmlBody .= '</ul>';

    $htmlBody .= "<h3>New PlaylistItem</h3><ul>";
    $htmlBody .= sprintf('<li>%s (%s)</li>',
        $playlistItemResponse['snippet']['title'],
        $playlistItemResponse['id']);
    $htmlBody .= '</ul>';

  } catch (Google_Service_Exception $e) {
    $htmlBody .= sprintf('<p>A service error occurred: <code>%s</code></p>',
        htmlspecialchars($e->getMessage()));
		//header('Location: ' . $indexurl);
		if ($e->getCode() == 401)
		{
			$client->revokeToken();
			unset($_SESSION['token']); 
			$state = mt_rand();
			$client->setState($state);
			$_SESSION['state'] = $state;
			$_SESSION['accessdenied'] = true;
			$authUrl = $client->createAuthUrl();
			header('Location: ' . $authUrl);
		} 
		else if ($e->getCode() == 404)
		{
			header('Location: ' . $indexurl);
		}
  } catch (Google_Exception $e) {
    $htmlBody .= sprintf('<p>An client error occurred: <code>%s</code></p>',
        htmlspecialchars($e->getMessage()));
		
  }

  $_SESSION['token'] = $client->getAccessToken();
} else {
  // If the user hasn't authorized the app, initiate the OAuth flow
  if ($_GET['linkGoogleAccount']==true)
  {
	$state = mt_rand();
	$client->setState($state);
	$_SESSION['state'] = $state;

	$authUrl = $client->createAuthUrl();
	header('Location: ' . $authUrl);
	$htmlBody = <<<END
	<h3>Authorization Required</h3>
	<p>You need to <a href="$authUrl">authorize access</a> before proceeding.<p>
END;
  } else {
		header('Location: ' . $indexurl);
  }
}
?>

<!doctype html>
<html>
<head>
<title>New Playlist</title> 
</head>
<body>
  <?=$htmlBody?>
</body>
</html>
