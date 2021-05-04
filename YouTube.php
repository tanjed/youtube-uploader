<?php
require_once __DIR__ . '/vendor/autoload.php';

class YouTube{

    private $auth_key;
    private $OAUTH2_CLIENT_ID = '569602794419-jnr7h1l40117buus33ia36cd6lr8njn0.apps.googleusercontent.com';
    private $OAUTH2_CLIENT_SECRET = 'W67kyn7EtVNpwbu0kqFl9tAV';

    function upload_video_to_youtube($video_path,$title,$description,$tags){
        $htmlBody = '';
        try{
            $client = new Google_Client();
            $client->setClientId($this->OAUTH2_CLIENT_ID);
            $client->setClientSecret($this->OAUTH2_CLIENT_SECRET);
            $client->setScopes([
                'https://www.googleapis.com/auth/youtube',
                'https://www.googleapis.com/auth/youtube.upload'
            ]);
            $client->setAccessType('offline');
            $client->setAccessToken($this->auth_key);
            $client->setApprovalPrompt('force');
            if ($client->getAccessToken()) {
                if($client->isAccessTokenExpired()) {
                    $client->refreshToken($client->getRefreshToken());
                    file_put_contents(__DIR__.'/token.txt', json_encode($client->getAccessToken()));
                }
                $youtube = new Google_Service_YouTube($client);
                $videoPath = $video_path;
                $snippet = new Google_Service_YouTube_VideoSnippet();
                $snippet->setTitle($title);
                $snippet->setDescription($description);
                $snippet->setTags($tags);
                $snippet->setCategoryId("22");
                $status = new Google_Service_YouTube_VideoStatus();
                $status->privacyStatus = "unlisted";
                $video = new Google_Service_YouTube_Video();
                $video->setSnippet($snippet);
                $video->setStatus($status);
                $chunkSizeBytes = 1 * 1024 * 1024;
                $client->setDefer(true);
                $insertRequest = $youtube->videos->insert("status,snippet", $video);
                $media = new Google_Http_MediaFileUpload(
                    $client,
                    $insertRequest,
                    'video/*',
                    null,
                    true,
                    $chunkSizeBytes
                );
                $media->setFileSize(filesize($videoPath));
                $status = false;
                $handle = fopen($videoPath, "rb");
                while (!$status && !feof($handle)) {
                    $chunk = fread($handle, $chunkSizeBytes);
                    $status = $media->nextChunk($chunk);
                }
                fclose($handle);
                $client->setDefer(false);
                $htmlBody = $status;
            }
            else{
                die('Problems creating the client');
            }
        } catch (Google_Service_Exception $e) {
            die(print_r($e));
        } catch (Google_Exception $e) {
            die($e->getMessage());
        }
        die($htmlBody);
    }

    function __construct() {
        $this->auth_key = file_get_contents(__DIR__.'/token.txt');
    }
}
