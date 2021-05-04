<?php

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    throw new \Exception('please run "composer require google/apiclient:~2.0" in "' . __DIR__ .'"');
}

require_once __DIR__ . '/vendor/autoload.php';
$config = include(__DIR__.'/config.php');
session_start();
$OAUTH2_CLIENT_ID = $config['client_id'];
$OAUTH2_CLIENT_SECRET = $config['client_secret'];

$client = new Google_Client();
$client->setClientId($OAUTH2_CLIENT_ID);
$client->setClientSecret($OAUTH2_CLIENT_SECRET);
$client->setScopes([
    'https://www.googleapis.com/auth/youtube',
    'https://www.googleapis.com/auth/youtube.upload'
]);
$redirect = filter_var($config['redirect_url'],
    FILTER_SANITIZE_URL);
$client->setRedirectUri($redirect);
$client->setAccessType('offline');
$client->setApprovalPrompt('force');

// Define an object that will be used to make all API requests.
$youtube = new Google_Service_YouTube($client);

// Check if an auth token exists for the required scopes
$tokenSessionKey = 'token-' . $client->prepareScopes();
if (isset($_GET['code'])) {
    if (strval($_SESSION['state']) !== strval($_GET['state'])) {
        die('The session state did not match.');
    }

    $client->authenticate($_GET['code']);
    $_SESSION[$tokenSessionKey] = $client->getAccessToken();
    header('Location: ' . $redirect);
}

if (isset($_SESSION[$tokenSessionKey])) {
    $client->setAccessToken($_SESSION[$tokenSessionKey]);
}

// Check to ensure that the access token was successfully acquired.
if ($client->getAccessToken()) {
    file_put_contents(__DIR__.'/token.txt', json_encode($client->getAccessToken()));
    try {
        // Execute an API request that lists the streams owned by the user who
        // authorized the request.
//        $streamsResponse = $youtube->liveStreams->listLiveStreams('id,snippet', array(
//            'mine' => 'true',
//        ));
//
//        $htmlBody .= "<h3>Live Streams</h3><ul>";
//        foreach ($streamsResponse['items'] as $streamItem) {
//            $htmlBody .= sprintf('<li>%s (%s)</li>', $streamItem['snippet']['title'],
//                $streamItem['id']);
//        }
//        $htmlBody .= '</ul>';
        header('Location: /index.php');

    } catch (Google_Service_Exception $e) {
        $htmlBody = sprintf('<p>A service error occurred: <code>%s</code></p>',
            htmlspecialchars($e->getMessage()));
    } catch (Google_Exception $e) {
        $htmlBody = sprintf('<p>An client error occurred: <code>%s</code></p>',
            htmlspecialchars($e->getMessage()));
    }

    $_SESSION[$tokenSessionKey] = $client->getAccessToken();
} elseif ($OAUTH2_CLIENT_ID == 'REPLACE_ME') {
    $htmlBody = <<<END
  <h3>Client Credentials Required</h3>
  <p>
    You need to set <code>\$OAUTH2_CLIENT_ID</code> and
    <code>\$OAUTH2_CLIENT_ID</code> before proceeding.
  <p>
END;
} else {
    // If the user hasn't authorized the app, initiate the OAuth flow
    $state = mt_rand();
    $client->setState($state);
    $_SESSION['state'] = $state;

    $authUrl = $client->createAuthUrl();
    $htmlBody = <<<END
  <h3>Authorization Required</h3>
  <p>You need to <a href="$authUrl">authorize access</a> before proceeding.<p>
END;
}
?>

<!doctype html>
<html>
<head>
    <title>My Live Streams</title>
</head>
<body>
<?=$htmlBody?>
</body>
</html>
